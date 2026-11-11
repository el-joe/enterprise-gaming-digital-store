import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/account/domain/entities/account.dart';
import 'package:application/features/account/presentation/controllers/account_controllers.dart';
import 'package:application/features/account/presentation/controllers/profile_controller.dart';
import 'package:application/features/account/presentation/widgets/account_widgets.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:application/features/catalog/presentation/widgets/currency_sheet.dart';
import 'package:application/features/checkout/presentation/widgets/checkout_widgets.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_list_tile.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/countdown.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:application/shared/widgets/status_pill.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Profile (17.2): name, email (read only, with the verified badge and the verification email
/// resend), phone, country, language and display currency. Server field errors (422) show beside
/// their fields.
class ProfilePage extends ConsumerWidget {
  const ProfilePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final profile = ref.watch(accountProfileControllerProvider);
    final appBar = MobileAppBar(title: l10n.accountProfile);
    return switch (profile) {
      Loaded(:final value) => _ProfileForm(
        key: ValueKey('profile-form-${value.publicId}'),
        profile: value,
      ),
      Failed(:final failure) => Scaffold(
        appBar: appBar,
        body: Center(
          child: SingleChildScrollView(
            child: FailureView(
              failure: failure,
              onRetry: () => unawaited(
                ref.read(accountProfileControllerProvider.notifier).reload(),
              ),
            ),
          ),
        ),
      ),
      _ => Scaffold(
        appBar: appBar,
        body: const SkeletonRegion(
          child: Padding(
            padding: EdgeInsetsDirectional.all(Spacing.s4),
            child: Column(
              children: [
                SkeletonBlock(height: 66),
                SizedBox(height: Spacing.s3),
                SkeletonBlock(height: 88),
                SizedBox(height: Spacing.s3),
                SkeletonBlock(height: 66),
              ],
            ),
          ),
        ),
      ),
    };
  }
}

class _ProfileForm extends ConsumerStatefulWidget {
  const _ProfileForm({required this.profile, super.key});

  final Profile profile;

  @override
  ConsumerState<_ProfileForm> createState() => _ProfileFormState();
}

class _ProfileFormState extends ConsumerState<_ProfileForm> {
  late final _name = TextEditingController(text: widget.profile.name);
  late final _phone = TextEditingController(text: widget.profile.phone ?? '');

  @override
  void dispose() {
    _name.dispose();
    _phone.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    final l10n = context.l10n;
    FocusScope.of(context).unfocus();
    final outcome = await ref
        .read(profileFormControllerProvider.notifier)
        .save(name: _name.text, phone: _phone.text);
    if (!mounted) {
      return;
    }
    switch (outcome) {
      case ProfileSaveOutcome.saved:
        showAppSnackbar(context, message: l10n.profileSaved);
      case ProfileSaveOutcome.unchanged:
        showAppSnackbar(
          context,
          message: l10n.profileUnchanged,
          tone: SnackbarTone.info,
        );
      case ProfileSaveOutcome.invalid || ProfileSaveOutcome.failed:
        break;
    }
  }

  Future<void> _resend() async {
    final l10n = context.l10n;
    final sent = await ref
        .read(profileFormControllerProvider.notifier)
        .resendVerification();
    if (sent && mounted) {
      showAppSnackbar(context, message: l10n.verificationSent);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final profile =
        ref.watch(accountProfileControllerProvider)?.valueOrNull ??
        widget.profile;
    final state = ref.watch(profileFormControllerProvider);
    final form = ref.read(profileFormControllerProvider.notifier);
    final currency = ref.watch(displayCurrencyProvider);
    final arabic = AppLocales.isRtl(Localizations.localeOf(context));
    final banner = state.bannerFailure;

    String? nameError() => switch (state.problems['name']) {
      ProfileFieldProblem.nameRequired => l10n.fieldRequired,
      ProfileFieldProblem.nameTooLong => l10n.profileNameTooLong,
      null => state.serverError('name'),
    };

    final countries = state.countries.valueOrNull ?? const <Country>[];
    final countryCode = state.countryFor(profile);
    Country? selected;
    for (final country in countries) {
      if (country.code == countryCode) {
        selected = country;
      }
    }

    return FlowScaffold(
      appBar: MobileAppBar(title: l10n.accountProfile),
      body: [
        if (banner != null) ...[
          StatusBanner(
            tone: BannerTone.danger,
            title: failureDetail(l10n, banner),
          ),
          const SizedBox(height: Spacing.s4),
        ],
        AppTextField(
          key: const ValueKey('profile-name'),
          label: l10n.profileNameLabel,
          controller: _name,
          errorText: nameError(),
          autofillHints: const [AutofillHints.name],
          textCapitalization: TextCapitalization.words,
          textInputAction: TextInputAction.next,
          onChanged: (_) => form.edited(),
        ),
        const SizedBox(height: Spacing.s3),
        _EmailField(email: profile.email, verified: profile.emailVerified),
        if (!profile.emailVerified) ...[
          const SizedBox(height: Spacing.s3),
          StatusBanner(
            tone: BannerTone.warning,
            title: l10n.profileUnverifiedTitle,
            body: state.resendFailure == null
                ? l10n.profileUnverifiedBody
                : failureMessage(l10n, state.resendFailure!),
          ),
          const SizedBox(height: Spacing.s2),
          _ResendButton(
            availableAt: state.resendAvailableAt,
            busy: state.resending,
            onPressed: () => unawaited(_resend()),
          ),
        ],
        const SizedBox(height: Spacing.s3),
        AppTextField(
          key: const ValueKey('profile-phone'),
          label: l10n.profilePhoneLabel,
          controller: _phone,
          ltr: true,
          keyboardType: TextInputType.phone,
          autofillHints: const [AutofillHints.telephoneNumber],
          helperText: l10n.profilePhoneHelper,
          errorText: state.serverError('phone'),
          textInputAction: TextInputAction.done,
          onChanged: (_) => form.edited(),
        ),
        const SizedBox(height: Spacing.s3),
        if (state.countries case Failed())
          AppListTile(
            key: const ValueKey('profile-country-retry'),
            title: l10n.profileCountryLabel,
            subtitle: l10n.profileCountriesFailed,
            icon: Icons.public,
            value: l10n.actionRetry,
            onTap: () => unawaited(form.retryCountries()),
          )
        else
          CountryField(
            key: const ValueKey('profile-country'),
            label: l10n.profileCountryLabel,
            countries: countries,
            selected: selected,
            enabled: countries.isNotEmpty,
            onSelected: form.chooseCountry,
          ),
        if (state.serverError('country_code') case final error?) ...[
          const SizedBox(height: Spacing.s1),
          Text(error, style: type.bodySm.copyWith(color: colors.statusDanger)),
        ],
        const SizedBox(height: Spacing.s4),
        AccountSectionLabel(l10n.profileRegionalSection),
        AppListTile(
          key: const ValueKey('profile-language'),
          title: l10n.languageSetting,
          icon: Icons.language,
          value: arabic ? l10n.languageArabic : l10n.languageEnglish,
          onTap: () => unawaited(showLanguageSheet(context)),
        ),
        const SizedBox(height: Spacing.s2),
        AppListTile(
          key: const ValueKey('profile-currency'),
          title: l10n.displayCurrencyLabel,
          icon: Icons.sell_outlined,
          value: currency ?? l10n.displayCurrencyAutomatic,
          onTap: () => unawaited(showCurrencySheet(context)),
        ),
      ],
      footer: [
        AppButton(
          key: const ValueKey('profile-save'),
          label: l10n.profileSave,
          expand: true,
          loading: state.saving,
          onPressed: () => unawaited(_save()),
        ),
      ],
    );
  }
}

/// The read-only email (17.2) with its badge and the "contact support" helper.
class _EmailField extends StatelessWidget {
  const _EmailField({required this.email, required this.verified});

  final String email;
  final bool verified;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final badge = verified
        ? l10n.accountVerifiedBadge
        : l10n.accountUnverifiedBadge;
    return Semantics(
      container: true,
      label: '${l10n.profileEmailLabel}, $email, $badge',
      hint: l10n.profileEmailHelper,
      excludeSemantics: true,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            l10n.profileEmailLabel,
            style: type.labelSm.copyWith(color: colors.contentMuted),
          ),
          const SizedBox(height: Spacing.s1_5),
          DecoratedBox(
            decoration: BoxDecoration(
              color: colors.surfaceRaised,
              borderRadius: BorderRadius.circular(6),
            ),
            child: ConstrainedBox(
              constraints: const BoxConstraints(minHeight: 48),
              child: Padding(
                padding: const EdgeInsetsDirectional.symmetric(
                  horizontal: Spacing.s3,
                  vertical: Spacing.s2,
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: Text(
                        email,
                        textDirection: TextDirection.ltr,
                        textAlign: TextAlign.start,
                        style: type.bodyMd.copyWith(color: colors.contentMuted),
                      ),
                    ),
                    const SizedBox(width: Spacing.s2),
                    StatusPill(
                      label: badge,
                      tone: verified
                          ? StatusPillTone.success
                          : StatusPillTone.warning,
                    ),
                  ],
                ),
              ),
            ),
          ),
          const SizedBox(height: Spacing.s1_5),
          Text(
            l10n.profileEmailHelper,
            style: type.bodySm.copyWith(color: colors.contentSecondary),
          ),
        ],
      ),
    );
  }
}

/// "Resend email", or "You can resend in m:ss" while the cooldown runs.
class _ResendButton extends ConsumerWidget {
  const _ResendButton({
    required this.availableAt,
    required this.busy,
    required this.onPressed,
  });

  final DateTime? availableAt;
  final bool busy;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final until = availableAt;
    if (until == null) {
      return Align(
        alignment: AlignmentDirectional.centerStart,
        child: AppButton(
          key: const ValueKey('profile-resend'),
          label: l10n.resendEmail,
          variant: AppButtonVariant.secondary,
          loading: busy,
          onPressed: onPressed,
        ),
      );
    }
    return CountdownBuilder(
      until: until,
      clock: ref.watch(clockProvider),
      builder: (context, remaining) => Align(
        alignment: AlignmentDirectional.centerStart,
        child: AppButton(
          key: const ValueKey('profile-resend'),
          label: remaining > Duration.zero
              ? l10n.resendIn(formatCountdown(remaining))
              : l10n.resendEmail,
          variant: AppButtonVariant.secondary,
          loading: busy,
          onPressed: remaining > Duration.zero ? null : onPressed,
        ),
      ),
    );
  }
}
