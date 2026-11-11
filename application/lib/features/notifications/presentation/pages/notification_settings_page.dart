import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/notifications/presentation/controllers/notifications_controllers.dart';
import 'package:application/features/push/domain/entities/push.dart';
import 'package:application/features/push/presentation/controllers/push_controllers.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_list_tile.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

String _categoryTitle(AppLocalizations l10n, String category) =>
    switch (category) {
      'orders' => l10n.preferenceCategoryOrders,
      'support' => l10n.preferenceCategorySupport,
      'marketing' => l10n.preferenceCategoryMarketing,
      _ => l10n.preferenceCategorySecurity,
    };

String _channelTitle(AppLocalizations l10n, String channel) =>
    switch (channel) {
      'mail' => l10n.preferenceChannelMail,
      'sms' => l10n.preferenceChannelSms,
      'push' => l10n.preferenceChannelPush,
      _ => l10n.preferenceChannelInbox,
    };

/// Notification settings (16.3): per category (orders, support, offers, security) the channels
/// the API has (in the app, push for orders and support, email, SMS). Required cells are on and
/// locked ("Required"); each switch saves at once and comes back with a message when the server
/// refuses. The push switches are the account's (every signed-in device); below them a banner
/// says when this phone does not allow notifications and offers the system prompt (M-A18).
class NotificationSettingsPage extends ConsumerStatefulWidget {
  const NotificationSettingsPage({super.key});

  @override
  ConsumerState<NotificationSettingsPage> createState() =>
      _NotificationSettingsPageState();
}

class _NotificationSettingsPageState
    extends ConsumerState<NotificationSettingsPage>
    with WidgetsBindingObserver {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    unawaited(ref.read(pushNotificationsProvider.notifier).refreshPermission());
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  /// Back from the system settings: the permission may have changed.
  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      unawaited(
        ref.read(pushNotificationsProvider.notifier).refreshPermission(),
      );
    }
  }

  @override
  Widget build(BuildContext context) => _NotificationSettingsView(ref: ref);
}

class _NotificationSettingsView extends StatelessWidget {
  const _NotificationSettingsView({required this.ref});

  final WidgetRef ref;

  Future<void> _toggle(
    BuildContext context,
    WidgetRef ref,
    String category,
    String channel,
    bool enabled,
  ) async {
    final l10n = context.l10n;
    final failure = await ref
        .read(notificationPreferencesControllerProvider.notifier)
        .toggle(category, channel, enabled);
    if (failure != null && context.mounted) {
      showAppSnackbar(
        context,
        message: switch (failure) {
          DomainFailure(:final message?) => message,
          _ => l10n.preferenceSaveFailed(failureDetail(l10n, failure)),
        },
        tone: SnackbarTone.danger,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = ref.watch(notificationPreferencesControllerProvider);
    final push = ref.watch(pushNotificationsProvider);
    final controller = ref.read(
      notificationPreferencesControllerProvider.notifier,
    );
    return Scaffold(
      appBar: MobileAppBar(title: l10n.notificationSettingsTitle),
      body: SafeArea(
        top: false,
        child: switch (state.preferences) {
          Loading() => const Padding(
            padding: EdgeInsetsDirectional.all(Spacing.s4),
            child: SkeletonRegion(
              child: Column(
                children: [
                  SkeletonBlock(height: 64, radius: Radii.md),
                  SizedBox(height: Spacing.s2),
                  SkeletonBlock(height: 64, radius: Radii.md),
                  SizedBox(height: Spacing.s2),
                  SkeletonBlock(height: 64, radius: Radii.md),
                ],
              ),
            ),
          ),
          Failed(:final failure) => FailureView(
            failure: failure,
            errorTitle: l10n.preferencesLoadError,
            onRetry: () => unawaited(controller.retry()),
          ),
          Loaded() => ListView(
            padding: const EdgeInsetsDirectional.fromSTEB(
              Spacing.s4,
              Spacing.s2,
              Spacing.s4,
              Spacing.s6,
            ),
            children: [
              for (final category in preferenceCategories) ...[
                ConstrainedBox(
                  constraints: const BoxConstraints(minHeight: 36),
                  child: Align(
                    alignment: AlignmentDirectional.centerStart,
                    child: Semantics(
                      header: true,
                      child: Text(
                        _categoryTitle(l10n, category),
                        style: context.type.labelMd.copyWith(
                          color: context.colors.contentSecondary,
                        ),
                      ),
                    ),
                  ),
                ),
                for (final channel in preferenceChannels)
                  if (state.cell(category, channel) case final cell?) ...[
                    AppListTile(
                      key: ValueKey('preference-$category-$channel'),
                      title: _channelTitle(l10n, channel),
                      subtitle: cell.mandatory
                          ? l10n.preferenceRequired
                          : switch (channel) {
                              'sms' => l10n.preferenceSmsNote,
                              'push' => l10n.preferencePushNote,
                              _ => null,
                            },
                      semanticsLabel: [
                        l10n.preferenceSemantics(
                          _categoryTitle(l10n, category),
                          _channelTitle(l10n, channel),
                        ),
                        if (cell.mandatory) l10n.preferenceRequired,
                      ].join(', '),
                      switchValue: cell.enabled,
                      onSwitchChanged: cell.mandatory
                          ? null
                          : (value) => unawaited(
                              _toggle(context, ref, category, channel, value),
                            ),
                    ),
                    const SizedBox(height: Spacing.s2),
                  ],
                const SizedBox(height: Spacing.s2),
              ],
              if (!push.started)
                StatusBanner(
                  tone: BannerTone.info,
                  title: l10n.preferencePushTitle,
                  body: l10n.preferencePushUnavailable,
                )
              else if (!push.granted) ...[
                StatusBanner(
                  tone: BannerTone.warning,
                  title: l10n.preferencePushOffTitle,
                  body: push.permission == PushPermission.denied
                      ? l10n.preferencePushDeniedBody
                      : l10n.preferencePushOffBody,
                ),
                const SizedBox(height: Spacing.s3),
                AppButton(
                  key: const ValueKey('push-allow'),
                  label: l10n.pushExplainerAllow,
                  icon: Icons.notifications_active_outlined,
                  variant: AppButtonVariant.secondary,
                  expand: true,
                  onPressed: () => unawaited(
                    ref
                        .read(pushNotificationsProvider.notifier)
                        .requestPermission(),
                  ),
                ),
              ],
            ],
          ),
        },
      ),
    );
  }
}
