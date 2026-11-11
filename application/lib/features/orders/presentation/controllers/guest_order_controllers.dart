import 'dart:async';

import 'package:application/core/config/app_env.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/deep_links.dart';
import 'package:application/core/router/link_inbox.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/checkout/checkout_providers.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:application/features/orders/orders_providers.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'guest_order_controllers.g.dart';

/// Time before "Resend code" is offered again (the `otp` limiter allows 5 codes per 10 minutes).
const guestOtpResendAfter = Duration(seconds: 60);

/// Steps of guest order access (11.1 to 11.5).
enum GuestStep {
  /// Opening the signed link, or loading the order.
  loading,

  /// The link was verified; the customer types the order email to get a code (11.1).
  email,

  /// The code was sent; the customer types it (11.2, 11.3).
  code,

  /// The order with its codes (11.4).
  order,

  /// The signed link is expired or invalid (11.5).
  linkExpired,

  /// Nothing proves access on this device: open the email's link again.
  needLink,

  /// A load failed (offline, server error); retry.
  failed,
}

@immutable
final class GuestOrderState {
  const GuestOrderState({
    this.step = GuestStep.loading,
    this.email = '',
    this.busy = false,
    this.failure,
    this.order,
    this.resendAt,
    this.codeError,
  });

  final GuestStep step;

  /// The email typed for the code (memory only, for "Resend code").
  final String email;
  final bool busy;

  /// The last failure: field errors of the email step, a 429, or the load failure.
  final Failure? failure;

  /// `otp_invalid` and the like: the server's text under the code boxes (11.3).
  final String? codeError;

  final GuestOrderDetail? order;

  /// When "Resend code" is offered again.
  final DateTime? resendAt;

  GuestOrderState copyWith({
    GuestStep? step,
    String? email,
    bool? busy,
    Failure? Function()? failure,
    GuestOrderDetail? Function()? order,
    DateTime? Function()? resendAt,
    String? Function()? codeError,
  }) => GuestOrderState(
    step: step ?? this.step,
    email: email ?? this.email,
    busy: busy ?? this.busy,
    failure: failure == null ? this.failure : failure(),
    order: order == null ? this.order : order(),
    resendAt: resendAt == null ? this.resendAt : resendAt(),
    codeError: codeError == null ? this.codeError : codeError(),
  );
}

/// Guest order access for existing guest web orders (owner decision 4: no guest checkout; access
/// stays for orders placed as a guest). The signed delivery link (from [GuestLinkInbox]) is
/// opened through its `/mobile` twin, then the email one-time code is sent and verified; the
/// guest order token in secure storage carries both proofs, and the order detail and reveals use
/// it (`GuestOrderTokenInterceptor`). Without a waiting link the stored token is tried.
@riverpod
class GuestOrderController extends _$GuestOrderController {
  Uri? _link;
  int _generation = 0;

  @override
  GuestOrderState build(String order) {
    _link = ref.read(guestLinkInboxProvider).take(order);
    // After the first state exists (the start may update it synchronously).
    unawaited(Future<void>.microtask(_start));
    return const GuestOrderState();
  }

  Future<void> _start() async {
    final link = _link;
    if (link == null) {
      await _loadOrder(fromStoredToken: true);
      return;
    }
    final generation = ++_generation;
    final result = await ref
        .read(checkoutRepositoryProvider)
        .openGuestLink(link);
    if (!ref.mounted || generation != _generation) {
      return;
    }
    switch (result) {
      case Ok(:final value) when !value.otpRequired:
        await _loadOrder();
      case Ok():
        state = state.copyWith(step: GuestStep.email, failure: () => null);
      case Err(failure: ForbiddenFailure() || NotFoundFailure()):
        state = state.copyWith(step: GuestStep.linkExpired);
      case Err(:final failure):
        state = state.copyWith(step: GuestStep.failed, failure: () => failure);
    }
  }

  Future<void> _loadOrder({bool fromStoredToken = false}) async {
    final generation = ++_generation;
    state = state.copyWith(step: GuestStep.loading, failure: () => null);
    final result = await ref.read(ordersRepositoryProvider).guestDetail(order);
    if (!ref.mounted || generation != _generation) {
      return;
    }
    state = switch (result) {
      Ok(:final value) => state.copyWith(
        step: GuestStep.order,
        order: () => value,
        busy: false,
      ),
      Err(failure: NotFoundFailure()) when fromStoredToken => state.copyWith(
        step: GuestStep.needLink,
      ),
      Err(:final failure) => state.copyWith(
        step: GuestStep.failed,
        failure: () => failure,
        busy: false,
      ),
    };
  }

  /// Retry after a failed load (opens the link again when one was given).
  Future<void> retry() async {
    state = state.copyWith(step: GuestStep.loading, failure: () => null);
    if (state.order == null && _link != null) {
      await _start();
    } else {
      await _loadOrder(fromStoredToken: _link == null);
    }
  }

  /// "Send code" (11.1): always the same answer from the server, whatever the email.
  Future<void> sendCode(String email) async {
    final trimmed = email.trim();
    if (state.busy || trimmed.isEmpty) {
      return;
    }
    state = state.copyWith(busy: true, email: trimmed, failure: () => null);
    final result = await ref
        .read(checkoutRepositoryProvider)
        .sendGuestOtp(orderPublicId: order, email: trimmed);
    if (!ref.mounted) {
      return;
    }
    state = switch (result) {
      Ok() => state.copyWith(
        busy: false,
        step: GuestStep.code,
        codeError: () => null,
        resendAt: () => ref.read(clockProvider)().add(guestOtpResendAfter),
      ),
      Err(:final failure) => state.copyWith(
        busy: false,
        failure: () => failure,
        resendAt: () => switch (failure) {
          RateLimitedFailure(:final retryAfter?) =>
            ref.read(clockProvider)().add(retryAfter),
          _ => state.resendAt,
        },
      ),
    };
  }

  /// "Resend code" after the cooldown.
  Future<void> resendCode() async {
    final at = state.resendAt;
    if (at != null && ref.read(clockProvider)().isBefore(at)) {
      return;
    }
    await sendCode(state.email);
  }

  /// Back from the code step to change the email.
  void changeEmail() {
    state = state.copyWith(
      step: GuestStep.email,
      codeError: () => null,
      failure: () => null,
    );
  }

  /// "Verify" (11.2): the new token replaces the stored one; then the order loads.
  Future<void> verifyCode(String code) async {
    final trimmed = code.trim();
    if (state.busy || trimmed.length != 6) {
      return;
    }
    state = state.copyWith(
      busy: true,
      codeError: () => null,
      failure: () => null,
    );
    final result = await ref
        .read(checkoutRepositoryProvider)
        .verifyGuestOtp(orderPublicId: order, code: trimmed);
    if (!ref.mounted) {
      return;
    }
    switch (result) {
      case Ok():
        state = state.copyWith(busy: false);
        await _loadOrder();
      case Err(failure: DomainFailure(:final message)):
        state = state.copyWith(busy: false, codeError: () => message ?? '');
      case Err(failure: final ValidationFailure failure):
        state = state.copyWith(
          busy: false,
          codeError: () => failure.firstErrorFor('code') ?? '',
        );
      case Err(:final failure):
        state = state.copyWith(busy: false, failure: () => failure);
    }
  }
}

/// State of the "open a guest order" page.
@immutable
final class GuestLinkEntryState {
  const GuestLinkEntryState({this.invalid = false, this.submitted = 0});

  /// The pasted text is not a delivery link of this store.
  final bool invalid;
  final int submitted;
}

/// "Open a guest order": the customer pastes the link of the delivery email (the reveal needs
/// that link's proof, so an order number alone cannot open codes).
@riverpod
class GuestLinkEntry extends _$GuestLinkEntry {
  @override
  GuestLinkEntryState build() => const GuestLinkEntryState();

  /// The guest order to open, or null (and [GuestLinkEntryState.invalid]) when [text] is not a
  /// signed guest access link on the store's host.
  String? submit(String text) {
    final uri = Uri.tryParse(text.trim());
    final link = uri == null
        ? null
        : parseDeepLink(uri, appLinkHost: ref.read(appEnvProvider).appLinkHost);
    if (link is! GuestOrderAccessLink) {
      state = GuestLinkEntryState(
        invalid: true,
        submitted: state.submitted + 1,
      );
      return null;
    }
    ref.read(guestLinkInboxProvider).put(link.orderPublicId, link.signedUri);
    state = GuestLinkEntryState(submitted: state.submitted + 1);
    return link.orderPublicId;
  }

  void clearError() {
    if (state.invalid) {
      state = GuestLinkEntryState(submitted: state.submitted);
    }
  }
}
