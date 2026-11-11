import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/catalog/presentation/controllers/currency_switcher_controller.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/option_tile.dart';
import 'package:application/shared/widgets/sheet_header.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Opens the display currency sheet (03.5). After a change every priced screen reloads in the
/// new currency and a snackbar confirms it.
Future<void> showCurrencySheet(BuildContext context) async {
  final messenger = ScaffoldMessenger.maybeOf(context);
  final l10n = context.l10n;
  final applied = await showModalBottomSheet<String>(
    context: context,
    isScrollControlled: true,
    useRootNavigator: true,
    backgroundColor: context.colors.surfaceRaised,
    builder: (context) => const CurrencySheet(),
  );
  if (applied != null && messenger != null && context.mounted) {
    showAppSnackbar(context, message: l10n.currencyChanged(applied));
  }
}

class CurrencySheet extends ConsumerWidget {
  const CurrencySheet({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final state = ref.watch(currencySwitcherControllerProvider);
    final controller = ref.read(currencySwitcherControllerProvider.notifier);
    final failure = state.failure;
    return SafeArea(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SheetHeader(title: l10n.displayCurrencyLabel),
          Flexible(
            child: SingleChildScrollView(
              padding: const EdgeInsetsDirectional.fromSTEB(
                Spacing.s4,
                0,
                Spacing.s4,
                Spacing.s4,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(
                    l10n.currencySheetBody,
                    style: context.type.bodySm.copyWith(
                      color: context.colors.contentSecondary,
                    ),
                  ),
                  const SizedBox(height: Spacing.s3),
                  if (failure != null) ...[
                    StatusBanner(
                      tone: BannerTone.danger,
                      title: failureMessage(l10n, failure),
                    ),
                    const SizedBox(height: Spacing.s3),
                  ],
                  ...switch (state.options) {
                    Loading() => [
                      const Center(
                        child: Padding(
                          padding: EdgeInsetsDirectional.all(Spacing.s6),
                          child: CircularProgressIndicator(),
                        ),
                      ),
                    ],
                    Failed(:final failure) => [
                      SizedBox(
                        height: 320,
                        child: FailureView(
                          failure: failure,
                          onRetry: () => unawaited(controller.retry()),
                        ),
                      ),
                    ],
                    Loaded(:final value) => [
                      for (final option in value) ...[
                        OptionTile(
                          title: option.info.code,
                          detail: option.name == option.info.code
                              ? null
                              : option.name,
                          titleDirection: TextDirection.ltr,
                          selected: state.selected == option.info.code,
                          onTap: () => controller.choose(option.info.code),
                        ),
                        const SizedBox(height: Spacing.s2),
                      ],
                    ],
                  },
                ],
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsetsDirectional.fromSTEB(
              Spacing.s4,
              0,
              Spacing.s4,
              Spacing.s4,
            ),
            child: AppButton(
              label: l10n.currencyApply,
              expand: true,
              loading: state.applying,
              onPressed: state.selected == null
                  ? null
                  : () async {
                      final code = state.selected!;
                      final done = await controller.apply();
                      if (done && context.mounted) {
                        Navigator.of(context).pop(code);
                      }
                    },
            ),
          ),
        ],
      ),
    );
  }
}
