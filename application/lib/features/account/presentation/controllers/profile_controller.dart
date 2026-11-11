import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/account/account_providers.dart';
import 'package:application/features/account/domain/entities/account.dart';
import 'package:application/features/account/presentation/controllers/account_controllers.dart';
import 'package:application/features/catalog/catalog_providers.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'profile_controller.g.dart';

/// A field the profile form checks before sending (the server checks again).
enum ProfileFieldProblem { nameRequired, nameTooLong }

@immutable
final class ProfileFormState {
  const ProfileFormState({
    this.countries = const Loadable.loading(),
    this.countryCode,
    this.countryChanged = false,
    this.saving = false,
    this.problems = const {},
    this.failure,
    this.resendAvailableAt,
    this.resending = false,
    this.resendFailure,
  });

  /// `GET /storefront/countries` for the country field.
  final Loadable<List<Country>> countries;

  /// The chosen country (the profile's until the customer picks another).
  final String? countryCode;
  final bool countryChanged;
  final bool saving;

  /// Local checks, by API field name (`name`).
  final Map<String, ProfileFieldProblem> problems;

  /// The last save's failure: a 422 holds the server's field messages (`name`, `phone`,
  /// `country_code`), shown beside the fields; anything else is shown in a banner.
  final Failure? failure;

  /// "Resend verification email" is disabled until then (after a send or a 429).
  final DateTime? resendAvailableAt;
  final bool resending;
  final Failure? resendFailure;

  /// The server's message for [field] of a 422, if any.
  String? serverError(String field) => switch (failure) {
    final ValidationFailure validation => validation.firstErrorFor(field),
    _ => null,
  };

  /// A failure that is not about one field.
  Failure? get bannerFailure => switch (failure) {
    final ValidationFailure validation
        when const [
          'name',
          'phone',
          'country_code',
        ].any((f) => validation.firstErrorFor(f) != null) =>
      null,
    _ => failure,
  };

  /// The country to show: the chosen one, else the profile's.
  String? countryFor(Profile? profile) =>
      countryChanged ? countryCode : profile?.countryCode;

  bool canResend(DateTime now) =>
      !resending &&
      (resendAvailableAt == null || !now.isBefore(resendAvailableAt!));

  ProfileFormState copyWith({
    Loadable<List<Country>>? countries,
    String? countryCode,
    bool? countryChanged,
    bool? saving,
    Map<String, ProfileFieldProblem>? problems,
    Failure? Function()? failure,
    DateTime? Function()? resendAvailableAt,
    bool? resending,
    Failure? Function()? resendFailure,
  }) => ProfileFormState(
    countries: countries ?? this.countries,
    countryCode: countryCode ?? this.countryCode,
    countryChanged: countryChanged ?? this.countryChanged,
    saving: saving ?? this.saving,
    problems: problems ?? this.problems,
    failure: failure == null ? this.failure : failure(),
    resendAvailableAt: resendAvailableAt == null
        ? this.resendAvailableAt
        : resendAvailableAt(),
    resending: resending ?? this.resending,
    resendFailure: resendFailure == null ? this.resendFailure : resendFailure(),
  );
}

/// What a save did.
enum ProfileSaveOutcome { saved, unchanged, invalid, failed }

/// Profile (17.2): name, phone and country are edited here (`PATCH /account/profile`, only the
/// keys that changed); the email cannot be changed in the app (no verified change flow), but an
/// unverified address can get its verification email again (60 s cooldown or `Retry-After`).
/// Language and display currency use the shared settings flows.
@riverpod
class ProfileFormController extends _$ProfileFormController {
  static const nameMaxLength = 120;
  static const resendCooldown = Duration(seconds: 60);

  @override
  ProfileFormState build() {
    unawaited(_loadCountries());
    final profile = ref.read(accountProfileControllerProvider)?.valueOrNull;
    return ProfileFormState(countryCode: profile?.countryCode);
  }

  Future<void> _loadCountries() async {
    final result = await ref.read(catalogRepositoryProvider).countries();
    if (!ref.mounted) {
      return;
    }
    state = state.copyWith(
      countries: switch (result) {
        Ok(:final value) => Loadable.loaded(value),
        Err(:final failure) => Loadable.failed(failure),
      },
    );
  }

  Future<void> retryCountries() async {
    state = state.copyWith(countries: const Loadable.loading());
    await _loadCountries();
  }

  void chooseCountry(String code) {
    state = state.copyWith(
      countryCode: code,
      countryChanged: true,
      failure: () => null,
    );
  }

  /// Clears the message of a field the customer is editing.
  void edited() {
    if (state.failure != null || state.problems.isNotEmpty) {
      state = state.copyWith(problems: const {}, failure: () => null);
    }
  }

  /// Sends the fields that differ from the loaded profile. A blank phone clears it.
  Future<ProfileSaveOutcome> save({
    required String name,
    required String phone,
  }) async {
    if (state.saving) {
      return ProfileSaveOutcome.unchanged;
    }
    final profile = ref.read(accountProfileControllerProvider)?.valueOrNull;
    if (profile == null) {
      return ProfileSaveOutcome.failed;
    }
    final trimmedName = name.trim();
    final trimmedPhone = phone.trim();
    final problems = <String, ProfileFieldProblem>{
      if (trimmedName.isEmpty) 'name': ProfileFieldProblem.nameRequired,
      if (trimmedName.runes.length > nameMaxLength)
        'name': ProfileFieldProblem.nameTooLong,
    };
    if (problems.isNotEmpty) {
      state = state.copyWith(problems: problems, failure: () => null);
      return ProfileSaveOutcome.invalid;
    }
    final update = ProfileUpdate(
      name: trimmedName == profile.name ? null : trimmedName,
      phone: trimmedPhone == (profile.phone ?? '')
          ? null
          : FieldUpdate(trimmedPhone.isEmpty ? null : trimmedPhone),
      countryCode:
          state.countryChanged && state.countryCode != profile.countryCode
          ? FieldUpdate(state.countryCode)
          : null,
    );
    if (update.toBody().isEmpty) {
      state = state.copyWith(problems: const {}, failure: () => null);
      return ProfileSaveOutcome.unchanged;
    }
    state = state.copyWith(
      saving: true,
      problems: const {},
      failure: () => null,
    );
    final result = await ref
        .read(accountRepositoryProvider)
        .updateProfile(update);
    if (!ref.mounted) {
      return result.isOk ? ProfileSaveOutcome.saved : ProfileSaveOutcome.failed;
    }
    switch (result) {
      case Ok(:final value):
        ref.read(accountProfileControllerProvider.notifier).replace(value);
        state = state.copyWith(
          saving: false,
          countryCode: value.countryCode,
          countryChanged: false,
        );
        return ProfileSaveOutcome.saved;
      case Err(:final failure):
        state = state.copyWith(saving: false, failure: () => failure);
        return ProfileSaveOutcome.failed;
    }
  }

  /// `POST /account/email/verification-notification`. Returns whether the email was sent.
  Future<bool> resendVerification() async {
    final now = ref.read(clockProvider)();
    if (!state.canResend(now)) {
      return false;
    }
    state = state.copyWith(resending: true, resendFailure: () => null);
    final result = await ref
        .read(accountRepositoryProvider)
        .resendVerificationEmail();
    if (!ref.mounted) {
      return result.isOk;
    }
    final after = ref.read(clockProvider)();
    switch (result) {
      case Ok():
        state = state.copyWith(
          resending: false,
          resendAvailableAt: () => after.add(resendCooldown),
        );
        return true;
      case Err(failure: final RateLimitedFailure failure):
        state = state.copyWith(
          resending: false,
          resendAvailableAt: () =>
              after.add(failure.retryAfter ?? resendCooldown),
          resendFailure: () => failure,
        );
        return false;
      case Err(:final failure):
        state = state.copyWith(resending: false, resendFailure: () => failure);
        return false;
    }
  }
}
