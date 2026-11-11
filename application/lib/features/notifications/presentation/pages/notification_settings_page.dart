import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/notifications/presentation/controllers/notifications_controllers.dart';
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
      _ => l10n.preferenceChannelInbox,
    };

/// Notification settings (16.3): per category (orders, support, offers, security) the channels
/// the API has (in the app, email, SMS). Required cells are on and locked ("Required"); each
/// switch saves at once and comes back with a message when the server refuses. Push is M-A18:
/// the note says it is coming.
class NotificationSettingsPage extends ConsumerWidget {
  const NotificationSettingsPage({super.key});

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
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final state = ref.watch(notificationPreferencesControllerProvider);
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
                          : channel == 'sms'
                          ? l10n.preferenceSmsNote
                          : null,
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
              StatusBanner(
                tone: BannerTone.info,
                title: l10n.preferencePushTitle,
                body: l10n.preferencePushBody,
              ),
            ],
          ),
        },
      ),
    );
  }
}
