import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/launch/presentation/controllers/onboarding_controller.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_chip.dart';
import 'package:application/shared/widgets/brand_logo.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/option_tile.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class _StepLabel extends StatelessWidget {
  const _StepLabel(this.current);

  final int current;

  @override
  Widget build(BuildContext context) => Text(
    context.l10n.onboardingStep(current, 2),
    style: context.type.labelSm.copyWith(color: context.colors.contentMuted),
  );
}

/// Onboarding step 1 (01.5): English or Arabic; the choice applies at once and mirrors the screen.
class OnboardingLanguagePage extends ConsumerWidget {
  const OnboardingLanguagePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final current = Localizations.localeOf(context).languageCode;
    // Watched so the controller lives as long as the page.
    ref.watch(onboardingControllerProvider);
    final controller = ref.read(onboardingControllerProvider.notifier);
    return FlowScaffold(
      appBar: const MobileAppBar(
        leading: AppBarLeading.none,
        titleWidget: BrandLogo.full(height: 24),
        trailing: _StepLabel(1),
      ),
      body: [
        const SizedBox(height: Spacing.s4),
        FlowHeading(
          title: l10n.onboardingLanguageTitle,
          body: l10n.onboardingLanguageBody,
        ),
        const SizedBox(height: Spacing.s5),
        OptionTile(
          title: l10n.languageEnglish,
          detail: l10n.languageEnglishDetail,
          titleDirection: TextDirection.ltr,
          selected: current == 'en',
          onTap: () => unawaited(controller.chooseLanguage('en')),
        ),
        const SizedBox(height: Spacing.s3),
        OptionTile(
          title: l10n.languageArabic,
          detail: l10n.languageArabicDetail,
          titleDirection: TextDirection.rtl,
          selected: current == 'ar',
          onTap: () => unawaited(controller.chooseLanguage('ar')),
        ),
      ],
      footer: [
        AppButton(
          label: l10n.actionContinue,
          expand: true,
          onPressed: () => context.push(Routes.onboardingCurrency.path),
        ),
      ],
    );
  }
}

/// Onboarding step 2 (01.6): display currency chips from `GET /app/config` and the charge-currency
/// notice (owner decision 1). The country is not asked: the server takes it from the profile or
/// GeoIP.
class OnboardingCurrencyPage extends ConsumerWidget {
  const OnboardingCurrencyPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final state = ref.watch(onboardingControllerProvider);
    final controller = ref.read(onboardingControllerProvider.notifier);

    Future<void> finish({required bool save}) async {
      await controller.finish(saveCurrency: save);
      if (context.mounted) {
        context.go(Routes.home.path);
      }
    }

    return FlowScaffold(
      appBar: MobileAppBar(
        onLeading: () => context.canPop()
            ? context.pop()
            : context.go(Routes.onboardingLanguage.path),
        trailing: const _StepLabel(2),
      ),
      body: [
        const SizedBox(height: Spacing.s4),
        FlowHeading(
          title: l10n.onboardingCurrencyTitle,
          body: l10n.onboardingCurrencyBody,
        ),
        const SizedBox(height: Spacing.s5),
        Text(
          l10n.displayCurrencyLabel,
          style: context.type.labelMd.copyWith(
            color: context.colors.contentSecondary,
          ),
        ),
        const SizedBox(height: Spacing.s1),
        Directionality(
          // Currency codes are Latin data; the chip row keeps the reading order of the locale.
          textDirection: Directionality.of(context),
          child: Wrap(
            spacing: Spacing.s2,
            children: [
              for (final currency in state.currencies)
                AppChip(
                  label: currency.info.code,
                  semanticsLabel: '${currency.info.code}, ${currency.name}',
                  selected: state.selectedCurrency == currency.info.code,
                  onTap: () => controller.chooseCurrency(currency.info.code),
                ),
            ],
          ),
        ),
        const SizedBox(height: Spacing.s4),
        StatusBanner(
          tone: BannerTone.info,
          title: l10n.chargeNoticeTitle,
          body: l10n.chargeNoticeBody,
        ),
      ],
      footer: [
        AppButton(
          label: l10n.startShopping,
          expand: true,
          onPressed: state.selectedCurrency == null
              ? null
              : () => unawaited(finish(save: true)),
        ),
        AppButton(
          label: l10n.skipForNow,
          variant: AppButtonVariant.ghost,
          expand: true,
          onPressed: () => unawaited(finish(save: false)),
        ),
      ],
    );
  }
}
