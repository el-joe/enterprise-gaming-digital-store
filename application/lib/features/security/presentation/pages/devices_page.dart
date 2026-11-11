import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/date_format.dart';
import 'package:application/features/account/domain/entities/account.dart';
import 'package:application/features/account/presentation/widgets/account_widgets.dart';
import 'package:application/features/security/presentation/controllers/devices_controller.dart';
import 'package:application/features/security/presentation/widgets/security_widgets.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/busy_indicator.dart';
import 'package:application/shared/widgets/directional_icons.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// The name a device row shows: the app's device name, else "Web browser" or "Unnamed device".
String deviceLabel(AppLocalizations l10n, Device device) {
  final name = device.deviceName?.trim();
  if (name != null && name.isNotEmpty) {
    return name;
  }
  return device.type == 'web' ? l10n.deviceWebBrowser : l10n.deviceUnnamed;
}

/// Devices (18.5, 18.6): this device first ("Current"), the others with their last use and
/// masked IP and a "Sign out" each (confirmation dialog), the note when browser sessions cannot
/// be listed, and "Sign out of all other devices" with the password.
class DevicesPage extends ConsumerWidget {
  const DevicesPage({super.key});

  Future<void> _revoke(
    BuildContext context,
    WidgetRef ref,
    Device device,
  ) async {
    final l10n = context.l10n;
    final name = deviceLabel(l10n, device);
    final confirmed = await showConfirmDialog(
      context,
      title: l10n.deviceRevokeTitle(name),
      body: l10n.deviceRevokeBody,
      action: l10n.deviceRevokeAction,
    );
    if (!confirmed || !context.mounted) {
      return;
    }
    final outcome = await ref
        .read(devicesControllerProvider.notifier)
        .revoke(device);
    if (!context.mounted) {
      return;
    }
    switch (outcome) {
      case DeviceSignedOut():
        showAppSnackbar(context, message: l10n.deviceSignedOut(name));
      case RevokeFailed(:final failure):
        showAppSnackbar(
          context,
          message: failureDetail(l10n, failure),
          tone: SnackbarTone.danger,
        );
      case null:
        break;
    }
  }

  Future<void> _signOutOthers(BuildContext context, WidgetRef ref) async {
    final l10n = context.l10n;
    final done = await showPasswordActionSheet(
      context,
      title: l10n.signOutOthersTitle,
      body: l10n.signOutOthersBody,
      actionLabel: l10n.signOutOthersAction,
      cancelLabel: l10n.actionCancel,
      onSubmit: ref.read(devicesControllerProvider.notifier).signOutOthers,
    );
    if (done && context.mounted) {
      showAppSnackbar(context, message: l10n.signOutOthersDone);
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final state = ref.watch(devicesControllerProvider);
    final appBar = MobileAppBar(title: l10n.devicesPageTitle);

    switch (state.content) {
      case Loading():
        return Scaffold(
          appBar: appBar,
          body: const SkeletonRegion(
            child: Padding(
              padding: EdgeInsetsDirectional.all(Spacing.s4),
              child: Column(
                children: [
                  SkeletonBlock(height: 64, radius: Radii.md),
                  SizedBox(height: Spacing.s2),
                  SkeletonBlock(height: 72, radius: Radii.md),
                ],
              ),
            ),
          ),
        );
      case Failed(:final failure):
        return Scaffold(
          appBar: appBar,
          body: Center(
            child: SingleChildScrollView(
              child: FailureView(
                failure: failure,
                onRetry: () => unawaited(
                  ref.read(devicesControllerProvider.notifier).reload(),
                ),
              ),
            ),
          ),
        );
      case Loaded(:final value):
        return FlowScaffold(
          appBar: appBar,
          body: [
            for (final device in value.devices) ...[
              _DeviceTile(
                device: device,
                busy: state.revoking == device.id,
                onSignOut: state.revoking == null
                    ? () => unawaited(_revoke(context, ref, device))
                    : null,
              ),
              const SizedBox(height: Spacing.s2),
            ],
            if (!state.hasOthers) ...[
              StatusBanner(tone: BannerTone.info, title: l10n.devicesOnlyThis),
              const SizedBox(height: Spacing.s2),
            ],
            if (!value.webSessionsListed)
              Text(
                l10n.devicesWebNotListed,
                style: type.bodySm.copyWith(color: colors.contentMuted),
              ),
          ],
          footer: [
            if (state.hasOthers)
              AppButton(
                key: const ValueKey('devices-sign-out-others'),
                label: l10n.signOutOthersAction,
                icon: DirectionalIcons.logout,
                variant: AppButtonVariant.secondary,
                expand: true,
                loading: state.signingOutOthers,
                onPressed: () => unawaited(_signOutOthers(context, ref)),
              ),
          ],
        );
    }
  }
}

/// A device row (18.5): icon, name, "This device · last used" or the last use and masked IP,
/// and "Current" or a 48dp "Sign out" button.
class _DeviceTile extends StatelessWidget {
  const _DeviceTile({
    required this.device,
    required this.busy,
    required this.onSignOut,
  });

  final Device device;
  final bool busy;
  final VoidCallback? onSignOut;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final name = deviceLabel(l10n, device);
    final lastUsed = device.lastUsedAt ?? device.createdAt;
    final details = [
      if (device.current) l10n.deviceThisDevice,
      if (lastUsed != null)
        l10n.deviceLastUsed(AppDates.dayTime(context, lastUsed)),
      if (device.ip case final ip? when ip.isNotEmpty)
        l10n.deviceIp(ltrIsolate(ip)),
    ].join(' · ');
    return Container(
      constraints: const BoxConstraints(minHeight: 64),
      padding: const EdgeInsetsDirectional.symmetric(
        horizontal: Spacing.s3,
        vertical: Spacing.s2,
      ),
      decoration: BoxDecoration(
        color: colors.surfaceRaised,
        borderRadius: BorderRadius.circular(Radii.md),
      ),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: colors.surfaceOverlay,
              borderRadius: BorderRadius.circular(Radii.md),
            ),
            child: Icon(
              device.type == 'web' ? Icons.language : Icons.smartphone,
              color: colors.contentPrimary,
            ),
          ),
          const SizedBox(width: Spacing.s3),
          Expanded(
            child: Semantics(
              container: true,
              label: details.isEmpty ? name : '$name, $details',
              excludeSemantics: true,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(name, style: type.labelLg),
                  if (details.isNotEmpty)
                    Text(
                      details,
                      style: type.bodySm.copyWith(
                        color: colors.contentSecondary,
                      ),
                    ),
                ],
              ),
            ),
          ),
          const SizedBox(width: Spacing.s2),
          if (device.current)
            Text(
              l10n.deviceCurrentBadge,
              style: type.labelMd.copyWith(color: colors.contentSecondary),
            )
          else
            Semantics(
              button: true,
              label: l10n.deviceSignOutSemantics(name),
              excludeSemantics: true,
              onTap: onSignOut,
              child: busy
                  ? const SizedBox.square(
                      dimension: 48,
                      child: Center(child: BusyIndicator()),
                    )
                  : TextButton(
                      onPressed: onSignOut,
                      style: TextButton.styleFrom(
                        foregroundColor: colors.contentPrimary,
                        minimumSize: const Size(48, 48),
                        padding: const EdgeInsetsDirectional.symmetric(
                          horizontal: Spacing.s2,
                        ),
                      ),
                      child: Text(l10n.deviceSignOut),
                    ),
            ),
        ],
      ),
    );
  }
}
