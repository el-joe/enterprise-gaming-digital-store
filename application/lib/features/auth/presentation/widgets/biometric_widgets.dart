import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:application/features/auth/domain/usecases/biometric_unlock.dart';
import 'package:application/features/auth/presentation/controllers/biometric_controllers.dart';
import 'package:application/features/auth/presentation/widgets/password_sheet.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_list_tile.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/brand_logo.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// System prompt texts in the app language.
BiometricPromptText biometricPrompt(AppLocalizations l10n, String title) =>
    BiometricPromptText(title: title, cancel: l10n.actionCancel);

/// Snackbar after an enrolment attempt (nothing for a cancel).
void showEnrolOutcome(BuildContext context, EnrolOutcome outcome) {
  final l10n = context.l10n;
  switch (outcome) {
    case EnrolDone():
      showAppSnackbar(context, message: l10n.biometricEnabled);
    case EnrolCancelled():
      break;
    case EnrolUnavailable():
      showAppSnackbar(
        context,
        message: l10n.biometricUnavailable,
        tone: SnackbarTone.info,
      );
    case EnrolFailed(:final failure):
      showAppSnackbar(
        context,
        message: failure == null
            ? l10n.biometricFailed
            : failureMessage(l10n, failure),
        tone: SnackbarTone.danger,
      );
  }
}

/// The biometric unlock switch (the Security screen of M-A16 reuses it). Hidden where the device
/// has no strong biometric.
class BiometricUnlockTile extends ConsumerWidget {
  const BiometricUnlockTile({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final support = ref.watch(biometricSupportProvider).value;
    final enabled = ref.watch(biometricUnlockSettingsProvider);
    if (support != BiometricSupport.available && enabled.value != true) {
      return const SizedBox.shrink();
    }
    final settings = ref.read(biometricUnlockSettingsProvider.notifier);
    return AppListTile(
      icon: Icons.fingerprint,
      title: l10n.biometricSettingTitle,
      subtitle: l10n.biometricSettingSubtitle,
      switchValue: enabled.value ?? false,
      onSwitchChanged: enabled.isLoading
          ? null
          : (on) async {
              if (on) {
                final outcome = await settings.enable(
                  prompt: biometricPrompt(l10n, l10n.biometricPromptEnrol),
                  askPassword: (previous) =>
                      showPasswordSheet(context, previous: previous),
                );
                if (context.mounted) {
                  showEnrolOutcome(context, outcome);
                }
              } else {
                await settings.disable();
                if (context.mounted) {
                  showAppSnackbar(context, message: l10n.biometricDisabled);
                }
              }
            },
    );
  }
}

/// Covers the app while it is locked (owner decision 6) and reports background and foreground
/// changes to [AppLock]. Placed above the navigator, so navigation state is kept.
class AppLockGate extends ConsumerStatefulWidget {
  const AppLockGate({required this.child, super.key});

  final Widget child;

  @override
  ConsumerState<AppLockGate> createState() => _AppLockGateState();
}

class _AppLockGateState extends ConsumerState<AppLockGate> {
  late final AppLifecycleListener _lifecycle;

  @override
  void initState() {
    super.initState();
    _lifecycle = AppLifecycleListener(
      onHide: () => ref.read(appLockProvider.notifier).backgrounded(),
      onShow: () => unawaited(ref.read(appLockProvider.notifier).resumed()),
    );
  }

  @override
  void dispose() {
    _lifecycle.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final locked = ref.watch(appLockProvider.select((state) => state.locked));
    return Stack(
      fit: StackFit.expand,
      children: [
        // The locked app stays mounted but is hidden from sight and from screen readers.
        Offstage(offstage: locked, child: widget.child),
        // Above the navigator there is no Overlay, which the password field needs.
        if (locked)
          Overlay(
            initialEntries: [OverlayEntry(builder: (_) => const LockScreen())],
          ),
      ],
    );
  }
}

/// The unlock screen: biometric prompt (started automatically once), or the password.
class LockScreen extends ConsumerStatefulWidget {
  const LockScreen({super.key});

  @override
  ConsumerState<LockScreen> createState() => _LockScreenState();
}

class _LockScreenState extends ConsumerState<LockScreen> {
  final _password = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted && !ref.read(appLockProvider).passwordMode) {
        unawaited(_biometric());
      }
    });
  }

  @override
  void dispose() {
    _password.dispose();
    super.dispose();
  }

  Future<void> _biometric() {
    final l10n = context.l10n;
    return ref
        .read(appLockProvider.notifier)
        .unlockWithBiometric(biometricPrompt(l10n, l10n.biometricPromptUnlock));
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = ref.watch(appLockProvider);
    final lock = ref.read(appLockProvider.notifier);
    final failure = state.failure;
    final passwordError = switch (failure) {
      null => null,
      ValidationFailure() =>
        failure.firstErrorFor('password') ?? failureMessage(l10n, failure),
      _ => failureMessage(l10n, failure),
    };
    return Material(
      color: context.colors.surfaceCanvas,
      child: FlowScaffold(
        body: [
          const SizedBox(height: Spacing.s16),
          const Center(child: BrandLogo.mark(height: 64)),
          const SizedBox(height: Spacing.s6),
          FlowHeading(title: l10n.lockTitle, body: l10n.lockBody),
          if (state.biometricTurnedOff) ...[
            const SizedBox(height: Spacing.s4),
            StatusBanner(
              tone: BannerTone.warning,
              title: l10n.lockBiometricChanged,
            ),
          ],
          if (state.passwordMode) ...[
            const SizedBox(height: Spacing.s5),
            AppTextField(
              label: l10n.passwordLabel,
              controller: _password,
              obscure: true,
              errorText: passwordError,
              autofillHints: const [AutofillHints.password],
              textInputAction: TextInputAction.done,
              onSubmitted: (value) => unawaited(lock.unlockWithPassword(value)),
            ),
          ],
        ],
        footer: [
          if (state.passwordMode)
            AppButton(
              label: l10n.unlockAction,
              expand: true,
              loading: state.busy,
              onPressed: () =>
                  unawaited(lock.unlockWithPassword(_password.text)),
            )
          else ...[
            AppButton(
              label: l10n.lockUseBiometric,
              icon: Icons.fingerprint,
              expand: true,
              loading: state.busy,
              onPressed: () => unawaited(_biometric()),
            ),
            AppButton(
              label: l10n.lockUsePassword,
              variant: AppButtonVariant.ghost,
              expand: true,
              onPressed: lock.usePassword,
            ),
          ],
        ],
      ),
    );
  }
}
