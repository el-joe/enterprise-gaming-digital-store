import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/icon_badge.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';

/// Centred empty or error state: icon badge (no illustration art), title, text and an action.
class EmptyState extends StatelessWidget {
  const EmptyState({
    required this.icon,
    required this.title,
    this.body,
    this.actionLabel,
    this.actionIcon,
    this.onAction,
    this.primaryAction = false,
    super.key,
  });

  final IconData icon;
  final String title;
  final String? body;
  final String? actionLabel;
  final IconData? actionIcon;
  final VoidCallback? onAction;
  final bool primaryAction;

  @override
  Widget build(BuildContext context) {
    final type = context.type;
    return Padding(
      padding: const EdgeInsetsDirectional.symmetric(
        horizontal: Spacing.s6,
        vertical: Spacing.s8,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          IconBadge(icon: icon, size: 72),
          const SizedBox(height: Spacing.s6),
          Semantics(
            header: true,
            child: Text(
              title,
              style: type.headingMd,
              textAlign: TextAlign.center,
            ),
          ),
          if (body != null) ...[
            const SizedBox(height: Spacing.s2),
            Text(
              body!,
              style: type.bodyMd.copyWith(
                color: context.colors.contentSecondary,
              ),
              textAlign: TextAlign.center,
            ),
          ],
          if (actionLabel != null && onAction != null) ...[
            const SizedBox(height: Spacing.s6),
            AppButton(
              label: actionLabel!,
              icon: actionIcon,
              expand: true,
              variant: primaryAction
                  ? AppButtonVariant.primary
                  : AppButtonVariant.secondary,
              onPressed: onAction,
            ),
          ],
        ],
      ),
    );
  }
}

/// The error (03.3) or offline (03.4) state of a screen that failed to load: offline shows the
/// banner and "No connection / Retry"; a server error shows the request id and "Try again".
/// 5xx bodies are never shown.
class FailureView extends StatelessWidget {
  const FailureView({
    required this.failure,
    required this.onRetry,
    this.errorTitle,
    super.key,
  });

  final Failure failure;
  final VoidCallback onRetry;

  /// Title of the error state (for example "We couldn't load the store").
  final String? errorTitle;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    if (isOfflineFailure(failure)) {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Padding(
            padding: const EdgeInsetsDirectional.all(Spacing.s4),
            child: StatusBanner(
              tone: BannerTone.offline,
              title: l10n.offlineBannerTitle,
              body: l10n.offlineBannerBody,
            ),
          ),
          Expanded(
            child: Center(
              child: SingleChildScrollView(
                child: EmptyState(
                  icon: Icons.wifi_off,
                  title: l10n.noConnectionTitle,
                  body: l10n.noConnectionBody,
                  actionLabel: l10n.actionRetry,
                  actionIcon: Icons.refresh,
                  onAction: onRetry,
                ),
              ),
            ),
          ),
        ],
      );
    }
    final requestId = failure.requestId;
    final body = switch (failure) {
      ServerFailure() || DecodingFailure() =>
        requestId == null
            ? l10n.errorServerBodyNoId
            : l10n.errorServerBody(requestId),
      _ => failureMessage(l10n, failure),
    };
    return Center(
      child: SingleChildScrollView(
        child: EmptyState(
          icon: Icons.error_outline,
          title: errorTitle ?? l10n.loadErrorTitle,
          body: body,
          actionLabel: l10n.actionTryAgain,
          actionIcon: Icons.refresh,
          onAction: onRetry,
          primaryAction: true,
        ),
      ),
    );
  }
}

/// Section title with an optional "See all" link (48dp target).
class SectionHeader extends StatelessWidget {
  const SectionHeader({required this.title, this.onSeeAll, super.key});

  final String title;
  final VoidCallback? onSeeAll;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    return ConstrainedBox(
      constraints: const BoxConstraints(minHeight: 48),
      child: Row(
        children: [
          Expanded(
            child: Semantics(
              header: true,
              child: Text(title, style: context.type.headingSm),
            ),
          ),
          if (onSeeAll != null)
            Semantics(
              button: true,
              label: l10n.seeAllSemantics(title),
              excludeSemantics: true,
              onTap: onSeeAll,
              child: TextButton(
                onPressed: onSeeAll,
                style: TextButton.styleFrom(
                  minimumSize: const Size(48, 48),
                  foregroundColor: context.colors.accentText,
                ),
                child: Text(l10n.seeAll),
              ),
            ),
        ],
      ),
    );
  }
}
