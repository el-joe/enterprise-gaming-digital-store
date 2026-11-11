import 'dart:async';

import 'package:application/core/config/app_version.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/launch/presentation/controllers/startup_controller.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/brand_logo.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/icon_badge.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Force update (01.3): this build is below `min_supported_version`.
class UpdateRequiredPage extends ConsumerWidget {
  const UpdateRequiredPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final platform = appPlatformName();
    final minimum = platform == null
        ? null
        : ref
              .watch(latestAppConfigProvider)
              ?.versions[platform]
              ?.minSupportedVersion;
    return FlowScaffold(
      body: [
        const SizedBox(height: Spacing.s24),
        const Align(
          alignment: AlignmentDirectional.centerStart,
          child: IconBadge(icon: Icons.download_outlined, size: 64),
        ),
        const SizedBox(height: Spacing.s4),
        FlowHeading(
          title: l10n.updateRequiredTitle,
          body: l10n.updateRequiredBody,
        ),
        if (minimum != null) ...[
          const SizedBox(height: Spacing.s4),
          // Version numbers are left-to-right isolates inside the sentence; the mono face has
          // no Arabic glyphs, so Arabic uses the caption style.
          Text(
            l10n.updateRequiredVersions(
              '\u2066$appVersion\u2069',
              '\u2066$minimum\u2069',
            ),
            style:
                (Localizations.localeOf(context).languageCode == 'ar'
                        ? type.bodyCaption
                        : type.monoSm)
                    .copyWith(color: colors.contentMuted),
          ),
        ],
      ],
      footer: [
        AppButton(
          label: l10n.updateNow,
          icon: Icons.download_outlined,
          expand: true,
          onPressed: () async {
            final opened = await ref
                .read(startupControllerProvider.notifier)
                .openStoreListing();
            if (!opened && context.mounted) {
              showAppSnackbar(
                context,
                message: l10n.updateStoreUnavailable,
                tone: SnackbarTone.info,
              );
            }
          },
        ),
      ],
    );
  }
}

/// Maintenance (01.4): the server's localized message, retry and support.
class MaintenancePage extends ConsumerWidget {
  const MaintenancePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final config = ref.watch(latestAppConfigProvider);
    final checking = ref.watch(startupControllerProvider) is StartupChecking;
    return FlowScaffold(
      body: [
        const SizedBox(height: Spacing.s16),
        const Center(child: BrandLogo.mark(height: 96)),
        const SizedBox(height: Spacing.s8),
        FlowHeading(
          title: l10n.maintenanceTitle,
          body: config?.maintenanceMessage ?? l10n.maintenanceBody,
        ),
        const SizedBox(height: Spacing.s4),
        StatusBanner(
          tone: BannerTone.info,
          title: l10n.maintenanceOrdersSafeTitle,
          body: l10n.maintenanceOrdersSafeBody,
        ),
      ],
      footer: [
        AppButton(
          label: l10n.actionTryAgain,
          icon: Icons.refresh,
          expand: true,
          loading: checking,
          onPressed: () =>
              unawaited(ref.read(startupControllerProvider.notifier).run()),
        ),
        if (config?.links.support != null)
          AppButton(
            label: l10n.contactSupport,
            variant: AppButtonVariant.ghost,
            expand: true,
            onPressed: () async {
              final opened = await ref
                  .read(startupControllerProvider.notifier)
                  .openSupport();
              if (!opened && context.mounted) {
                showAppSnackbar(
                  context,
                  message: l10n.linkOpenFailed,
                  tone: SnackbarTone.danger,
                );
              }
            },
          ),
      ],
    );
  }
}
