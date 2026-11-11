import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/shared/widgets/countdown.dart';

/// The localized message for a [Failure] (MOBILE_APP_ROADMAP.md 2.3). Server text is never shown
/// for 5xx; field messages of a 422 are shown beside their fields by the pages instead.
String failureMessage(AppLocalizations l10n, Failure failure) =>
    switch (failure) {
      NetworkFailure() => l10n.failureNetwork,
      TimeoutFailure() => l10n.failureTimeout,
      UnauthenticatedFailure() => l10n.failureUnauthenticated,
      TwoFactorRequiredFailure() => l10n.failureUnauthenticated,
      EmailUnverifiedFailure() => l10n.failureEmailUnverified,
      ForbiddenFailure() => l10n.failureForbidden,
      NotFoundFailure() => l10n.failureNotFound,
      RecentAuthRequiredFailure() ||
      BiometricLockedFailure() => l10n.failureRecentAuth,
      ValidationFailure() => l10n.failureValidation,
      RateLimitedFailure(:final retryAfter) =>
        retryAfter == null
            ? l10n.failureRateLimitedNoTime
            : l10n.failureRateLimited(formatCountdown(retryAfter)),
      MaintenanceFailure() => l10n.failureMaintenance,
      ServerFailure() || DecodingFailure() => l10n.failureServer,
      // Domain and conflict codes the screen does not handle itself: the server's message is
      // already localized by Accept-Language (API_DOCUMENTATION.md 1c).
      DomainFailure(:final message) ||
      ConflictFailure(:final message) => message ?? l10n.failureGeneric,
      CancelledFailure() || UnexpectedFailure() => l10n.failureGeneric,
    };
