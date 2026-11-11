import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/security/clipboard_guard.dart';
import 'package:application/core/security/device_integrity.dart';
import 'package:application/core/security/secure_screen.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/auth/auth_providers.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:application/features/auth/domain/usecases/biometric_unlock.dart';
import 'package:application/features/code_reveal/code_reveal_providers.dart';
import 'package:application/features/code_reveal/domain/entities/revealed_code.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:application/features/orders/orders_providers.dart';
import 'package:flutter/foundation.dart' show immutable;
import 'package:flutter/widgets.dart' show AppLifecycleState;
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'reveal_controller.g.dart';

/// Owner decision 7: a revealed code hides itself after this long.
const revealVisibleFor = Duration(seconds: 60);

/// Where the code screen stands (10.1 to 10.6).
enum RevealPhase {
  /// Masked (the default and the state after every hide).
  masked,

  /// The reveal request (and any re-auth) is running.
  revealing,

  /// The code is on screen until [RevealScreenState.hideAt].
  revealed,

  /// 429: the `code-reveal` limiter (10 per hour); never retried automatically.
  rateLimited,

  /// 403 `reveal_denied` (unverified email, missing guest proof, unpaid, not delivered).
  denied,

  /// 404: not this customer's unit, or gone.
  notFound,

  /// Any other failure (offline, server error): the customer may try again.
  failed,
}

/// Why a revealed code was hidden (announced to screen readers).
enum RevealHideReason { timeout, background, capture, customer, left }

/// What the screen shows about the unit, from the order detail (never the code).
@immutable
final class RevealUnitInfo {
  const RevealUnitInfo({
    required this.orderNumber,
    required this.productName,
    required this.index,
    required this.count,
    required this.status,
    required this.revealable,
    this.productSlug,
    this.maskedCode,
    this.deliveredAt,
    this.firstRevealedAt,
  });

  final String orderNumber;
  final String productName;
  final String? productSlug;

  /// Position of the unit in its line ("Code 1 of 2").
  final int index;
  final int count;
  final String status;
  final bool revealable;
  final String? maskedCode;
  final DateTime? deliveredAt;
  final DateTime? firstRevealedAt;

  static RevealUnitInfo? find(OrderDetail detail, String unitPublicId) {
    for (final line in detail.items) {
      for (var i = 0; i < line.units.length; i++) {
        final unit = line.units[i];
        if (unit.publicId == unitPublicId) {
          return RevealUnitInfo(
            orderNumber: detail.summary.number,
            productName: line.name,
            productSlug: line.productSlug,
            index: i + 1,
            count: line.units.length,
            status: unit.status,
            revealable: unit.revealable,
            maskedCode: unit.maskedCode,
            deliveredAt: unit.deliveredAt,
            firstRevealedAt: unit.firstRevealedAt,
          );
        }
      }
    }
    return null;
  }
}

/// The password sheet of the recent-auth step (10.2, 10.3).
@immutable
final class ReauthState {
  const ReauthState({this.busy = false, this.failure});

  final bool busy;

  /// The last confirmation failure (wrong password: `errors.password`, or 429).
  final Failure? failure;
}

/// State of one unit's code screen. The revealed code lives only here, only while
/// [phase] is [RevealPhase.revealed], and only as long as the screen is open (the provider is
/// auto-disposed). [toString] never prints it.
@immutable
final class RevealScreenState {
  const RevealScreenState({
    this.unit = const Loadable.loading(),
    this.phase = RevealPhase.masked,
    this.code,
    this.hideAt,
    this.retryAt,
    this.failure,
    this.reauth,
    this.biometricPending = false,
    this.captured = false,
    this.obscured = false,
    this.integrity,
    this.emailUnverified = false,
    this.instructions,
    this.lastHide,
    this.hideCount = 0,
    this.copyCount = 0,
    this.linkFailedCount = 0,
  });

  final Loadable<RevealUnitInfo> unit;
  final RevealPhase phase;

  /// The code, PIN and redemption link: only while revealed.
  final RevealedCode? code;

  /// When the code hides itself (60 s after the reveal).
  final DateTime? hideAt;

  /// When a rate-limited customer may try again (from `Retry-After`), if known.
  final DateTime? retryAt;

  final Failure? failure;

  /// The password sheet is open.
  final ReauthState? reauth;

  /// The system biometric prompt is up.
  final bool biometricPending;

  /// iOS: the screen is being recorded or mirrored; the code stays hidden.
  final bool captured;

  /// The app is inactive (app switcher, system sheet): the screen is covered.
  final bool obscured;

  /// The device integrity signal sent with the reveal (owner decision 5); `compromised` shows the
  /// warning, nothing is blocked.
  final DeviceIntegrity? integrity;

  /// The signed-in customer has not verified the email: the server will deny the reveal (10.6).
  final bool emailUnverified;

  /// How to redeem, from the last reveal response (product instructions, not secret; kept after
  /// a hide so the help stays on screen).
  final String? instructions;

  final RevealHideReason? lastHide;

  /// Bumped on every hide and copy (one announcement / snackbar each).
  final int hideCount;
  final int copyCount;
  final int linkFailedCount;

  bool get isRevealed => phase == RevealPhase.revealed && code != null;

  bool get busy => phase == RevealPhase.revealing;

  RevealScreenState copyWith({
    Loadable<RevealUnitInfo>? unit,
    RevealPhase? phase,
    RevealedCode? Function()? code,
    DateTime? Function()? hideAt,
    DateTime? Function()? retryAt,
    Failure? Function()? failure,
    ReauthState? Function()? reauth,
    bool? biometricPending,
    bool? captured,
    bool? obscured,
    DeviceIntegrity? integrity,
    bool? emailUnverified,
    String? Function()? instructions,
    RevealHideReason? lastHide,
    int? hideCount,
    int? copyCount,
    int? linkFailedCount,
  }) => RevealScreenState(
    unit: unit ?? this.unit,
    phase: phase ?? this.phase,
    code: code == null ? this.code : code(),
    hideAt: hideAt == null ? this.hideAt : hideAt(),
    retryAt: retryAt == null ? this.retryAt : retryAt(),
    failure: failure == null ? this.failure : failure(),
    reauth: reauth == null ? this.reauth : reauth(),
    biometricPending: biometricPending ?? this.biometricPending,
    captured: captured ?? this.captured,
    obscured: obscured ?? this.obscured,
    integrity: integrity ?? this.integrity,
    emailUnverified: emailUnverified ?? this.emailUnverified,
    instructions: instructions == null ? this.instructions : instructions(),
    lastHide: lastHide ?? this.lastHide,
    hideCount: hideCount ?? this.hideCount,
    copyCount: copyCount ?? this.copyCount,
    linkFailedCount: linkFailedCount ?? this.linkFailedCount,
  );

  @override
  String toString() =>
      'RevealScreenState(phase: ${phase.name}, code: '
      '${code == null ? 'none' : '<redacted>'})';
}

/// One unit's code screen (10.1 to 10.6, guest 11.4). Auto-disposed: leaving the screen drops
/// the code. While the provider lives it holds screen protection (Android `FLAG_SECURE`, iOS
/// privacy overlay) and listens to iOS capture events.
///
/// Reveal: `RevealCode` with `X-Device-Integrity`; on 423 the biometric confirmation runs first
/// (when enabled), then the password sheet; one retry. 429 shows the retry time and is never
/// retried. The code hides after [revealVisibleFor], when the app goes to the background, when
/// capture starts, and when the customer taps Hide.
@riverpod
class RevealController extends _$RevealController {
  Timer? _hideTimer;
  Completer<bool>? _reauth;
  StreamSubscription<bool>? _capture;
  int _generation = 0;

  RevealTarget get _target => guest
      ? RevealTarget.guest(orderPublicId: order, unitPublicId: unit)
      : RevealTarget.account(orderPublicId: order, unitPublicId: unit);

  @override
  RevealScreenState build(String order, String unit, bool guest) {
    // Keep the reveal providers (auto-disposed, never holding a code) alive with this screen.
    ref
      ..watch(revealCodeProvider)
      ..watch(codeRevealRepositoryProvider);
    final security = ref.read(screenSecurityProvider);
    unawaited(security.enable());
    _capture = security.captureChanges.listen(_onCapture);
    unawaited(security.isCaptured().then(_onCapture));
    ref.onDispose(() {
      _hideTimer?.cancel();
      _hideTimer = null;
      unawaited(_capture?.cancel());
      unawaited(security.disable());
      final pending = _reauth;
      _reauth = null;
      if (pending != null && !pending.isCompleted) {
        pending.complete(false);
      }
    });
    unawaited(_loadUnit());
    final unverified = !guest && ref.read(emailVerificationProvider) == false;
    return RevealScreenState(emailUnverified: unverified);
  }

  Future<void> _loadUnit() async {
    final repository = ref.read(ordersRepositoryProvider);
    final Result<OrderDetail> result;
    if (guest) {
      final guestResult = await repository.guestDetail(order);
      result = switch (guestResult) {
        Ok(:final value) => Ok(value.order),
        Err(:final failure) => Err(failure),
      };
    } else {
      result = await repository.detail(order);
    }
    if (!ref.mounted) {
      return;
    }
    state = state.copyWith(
      unit: switch (result) {
        Ok(:final value) => switch (RevealUnitInfo.find(value, unit)) {
          final RevealUnitInfo info => Loadable.loaded(info),
          null => const Loadable.failed(NotFoundFailure()),
        },
        Err(:final failure) => Loadable.failed(failure),
      },
    );
  }

  Future<void> retryLoad() async {
    state = state.copyWith(unit: const Loadable.loading());
    await _loadUnit();
  }

  /// "Reveal code". [prompt] are the texts of the system biometric prompt.
  Future<void> reveal(BiometricPromptText prompt) async {
    if (state.busy || state.isRevealed || state.captured) {
      return;
    }
    final retryAt = state.retryAt;
    if (state.phase == RevealPhase.rateLimited &&
        retryAt != null &&
        ref.read(clockProvider)().isBefore(retryAt)) {
      return;
    }
    final generation = ++_generation;
    state = state.copyWith(
      phase: RevealPhase.revealing,
      failure: () => null,
      retryAt: () => null,
    );
    final integrity = await _integrity();
    if (!ref.mounted || generation != _generation) {
      return;
    }
    state = state.copyWith(integrity: integrity);
    final result = await ref.read(revealCodeProvider)(
      _target,
      deviceIntegrity: integrity.headerValue,
      confirmRecentAuth: () => _confirmRecentAuth(prompt),
    );
    if (!ref.mounted || generation != _generation) {
      return;
    }
    switch (result) {
      case Ok(:final value):
        _show(value);
      case Err(failure: RecentAuthRequiredFailure()):
        // The customer closed the re-auth step: back to masked, nothing to report.
        state = state.copyWith(phase: RevealPhase.masked);
      case Err(failure: final RateLimitedFailure failure):
        final after = failure.retryAfter;
        state = state.copyWith(
          phase: RevealPhase.rateLimited,
          failure: () => failure,
          retryAt: () =>
              after == null ? null : ref.read(clockProvider)().add(after),
        );
      case Err(failure: ForbiddenFailure() || EmailUnverifiedFailure()):
        state = state.copyWith(
          phase: RevealPhase.denied,
          failure: () => result.failureOrNull,
        );
      case Err(failure: NotFoundFailure()):
        state = state.copyWith(
          phase: RevealPhase.notFound,
          failure: () => result.failureOrNull,
        );
      case Err(:final failure):
        state = state.copyWith(
          phase: RevealPhase.failed,
          failure: () => failure,
        );
    }
  }

  Future<DeviceIntegrity> _integrity() async {
    try {
      return await ref.read(deviceIntegrityProvider.future);
    } on Object {
      return DeviceIntegrity.unknown;
    }
  }

  /// Biometric first (when this device has unlock on), then the password sheet.
  Future<bool> _confirmRecentAuth(BiometricPromptText prompt) async {
    if (guest) {
      return false;
    }
    state = state.copyWith(biometricPending: true);
    final check = await ref.read(confirmRecentAuthWithBiometricProvider)(
      prompt,
    );
    if (!ref.mounted) {
      return false;
    }
    state = state.copyWith(biometricPending: false);
    if (check.check == BiometricCheck.confirmed) {
      return true;
    }
    final pending = Completer<bool>();
    _reauth = pending;
    state = state.copyWith(reauth: () => const ReauthState());
    return pending.future;
  }

  /// "Confirm and reveal" in the password sheet. A wrong password keeps the sheet open with the
  /// server's message (10.3).
  Future<void> submitPassword(String password) async {
    final current = state.reauth;
    final pending = _reauth;
    if (current == null || current.busy || pending == null) {
      return;
    }
    if (password.isEmpty) {
      return;
    }
    state = state.copyWith(reauth: () => const ReauthState(busy: true));
    final result = await ref
        .read(codeRevealRepositoryProvider)
        .confirmPassword(password);
    if (!ref.mounted || !identical(pending, _reauth)) {
      return;
    }
    switch (result) {
      case Ok():
        _reauth = null;
        state = state.copyWith(reauth: () => null);
        pending.complete(true);
      case Err(:final failure):
        state = state.copyWith(reauth: () => ReauthState(failure: failure));
    }
  }

  /// The password sheet was closed without confirming.
  void cancelReauth() {
    final pending = _reauth;
    _reauth = null;
    state = state.copyWith(reauth: () => null);
    if (pending != null && !pending.isCompleted) {
      pending.complete(false);
    }
  }

  void _show(RevealedCode code) {
    final hideAt = ref.read(clockProvider)().add(revealVisibleFor);
    _hideTimer?.cancel();
    _hideTimer = Timer(revealVisibleFor, () => hide(RevealHideReason.timeout));
    state = state.copyWith(
      phase: RevealPhase.revealed,
      code: () => code,
      hideAt: () => hideAt,
      instructions: () => code.instructions ?? state.instructions,
    );
  }

  /// Masks the code again and drops the reference to it.
  void hide([RevealHideReason reason = RevealHideReason.customer]) {
    _hideTimer?.cancel();
    _hideTimer = null;
    if (!ref.mounted || !state.isRevealed) {
      return;
    }
    state = state.copyWith(
      phase: RevealPhase.masked,
      code: () => null,
      hideAt: () => null,
      lastHide: reason,
      hideCount: state.hideCount + 1,
    );
  }

  /// App lifecycle (from the screen's `AppLifecycleListener`): inactive covers the screen (app
  /// switcher, system sheets); hidden, paused or detached also hides the code; resumed
  /// uncovers.
  void lifecycleChanged(AppLifecycleState lifecycle) {
    switch (lifecycle) {
      case AppLifecycleState.resumed:
        state = state.copyWith(obscured: false);
      case AppLifecycleState.inactive:
        state = state.copyWith(obscured: true);
      case AppLifecycleState.hidden ||
          AppLifecycleState.paused ||
          AppLifecycleState.detached:
        hide(RevealHideReason.background);
        state = state.copyWith(obscured: true);
    }
  }

  void _onCapture(bool captured) {
    if (!ref.mounted) {
      return;
    }
    if (captured) {
      hide(RevealHideReason.capture);
    }
    state = state.copyWith(captured: captured);
  }

  /// "Copy code": the clipboard is cleared after 60 s (owner decision 7).
  Future<void> copyCode() async {
    final code = state.code;
    if (!state.isRevealed || code == null) {
      return;
    }
    await ref.read(clipboardGuardProvider).copy(code.code);
    if (ref.mounted) {
      state = state.copyWith(copyCount: state.copyCount + 1);
    }
  }

  /// "Open redemption page": https only, in the system browser (the app goes to the
  /// background, so the code hides).
  Future<void> openRedemption() async {
    final url = state.code?.redemptionUrl;
    if (url == null || url.scheme != 'https') {
      return;
    }
    final opened = await ref.read(nativeBridgeProvider).openExternalUrl(url);
    if (!opened && ref.mounted) {
      state = state.copyWith(linkFailedCount: state.linkFailedCount + 1);
    }
  }

  /// The rate-limit window is over: the reveal button is offered again.
  void rateLimitOver() {
    if (state.phase == RevealPhase.rateLimited) {
      state = state.copyWith(
        phase: RevealPhase.masked,
        retryAt: () => null,
        failure: () => null,
      );
    }
  }
}
