import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/launch/presentation/controllers/startup_controller.dart';
import 'package:application/features/launch/presentation/widgets/integrity_warning_sheet.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/brand_logo.dart';
import 'package:application/shared/widgets/busy_indicator.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Splash (01.1, 01.2): runs the app config check and moves on; offline and failure states offer
/// a retry. Force update and maintenance are separate routes chosen by the guards.
class SplashPage extends ConsumerStatefulWidget {
  const SplashPage({super.key});

  @override
  ConsumerState<SplashPage> createState() => _SplashPageState();
}

class _SplashPageState extends ConsumerState<SplashPage> {
  bool _leaving = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) {
        return;
      }
      final current = ref.read(startupControllerProvider);
      if (current is StartupReady) {
        unawaited(_continue(current));
      } else {
        unawaited(ref.read(startupControllerProvider.notifier).run());
      }
    });
  }

  Future<void> _continue(StartupReady ready) async {
    if (_leaving ||
        ref.read(availabilityProvider) != AppAvailability.available) {
      return;
    }
    _leaving = true;
    final integrity = ref.read(integrityWarningControllerProvider.notifier);
    if (await integrity.shouldWarn() && mounted) {
      await showIntegrityWarningSheet(context);
      await integrity.acknowledged();
    }
    if (mounted) {
      context.go(ready.next);
    }
  }

  @override
  Widget build(BuildContext context) {
    ref.listen(startupControllerProvider, (_, next) {
      if (next is StartupReady) {
        unawaited(_continue(next));
      }
    });
    final state = ref.watch(startupControllerProvider);
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final retry = AppButton(
      label: l10n.actionTryAgain,
      icon: Icons.refresh,
      expand: true,
      onPressed: () =>
          unawaited(ref.read(startupControllerProvider.notifier).run()),
    );

    return Scaffold(
      backgroundColor: colors.surfaceCanvas,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsetsDirectional.symmetric(
            horizontal: Spacing.s4,
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Center(child: BrandLogo.mark(height: 56)),
              const SizedBox(height: Spacing.s6),
              ...switch (state) {
                StartupOffline() => [
                  StatusBanner(
                    tone: BannerTone.offline,
                    title: l10n.offlineTitle,
                    body: l10n.offlineBody,
                  ),
                  const SizedBox(height: Spacing.s5),
                  retry,
                ],
                StartupFailed(:final failure) => [
                  StatusBanner(
                    tone: BannerTone.danger,
                    title: l10n.startupErrorTitle,
                    body: [
                      failureMessage(l10n, failure),
                      if (failure.requestId != null)
                        l10n.requestReference(failure.requestId!),
                    ].join('\n'),
                  ),
                  const SizedBox(height: Spacing.s5),
                  retry,
                ],
                // Ready: the logo stays while the integrity sheet or navigation runs.
                StartupReady() => [
                  const Center(child: BrandLogo.full(height: 36)),
                ],
                StartupChecking() => [
                  const Center(child: BrandLogo.full(height: 36)),
                  const SizedBox(height: Spacing.s16),
                  Center(
                    child: BusyIndicator(semanticsLabel: l10n.loadingLabel),
                  ),
                  const SizedBox(height: Spacing.s3),
                  Text(
                    l10n.splashChecking,
                    textAlign: TextAlign.center,
                    style: type.bodySm.copyWith(color: colors.contentSecondary),
                  ),
                ],
              },
            ],
          ),
        ),
      ),
    );
  }
}
