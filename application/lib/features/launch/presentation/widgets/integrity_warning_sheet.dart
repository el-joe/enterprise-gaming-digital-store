import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/sheet_header.dart';
import 'package:flutter/material.dart';

/// The one-time rooted or jailbroken device warning (owner decision 5: warn, never block).
Future<void> showIntegrityWarningSheet(BuildContext context) =>
    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: context.colors.surfaceRaised,
      builder: (context) => const IntegrityWarningSheet(),
    );

class IntegrityWarningSheet extends StatelessWidget {
  const IntegrityWarningSheet({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    return SafeArea(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SheetHeader(title: l10n.integrityTitle),
          Padding(
            padding: const EdgeInsetsDirectional.fromSTEB(
              Spacing.s4,
              Spacing.s2,
              Spacing.s4,
              Spacing.s4,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(Icons.gpp_maybe_outlined, color: colors.statusWarning),
                    const SizedBox(width: Spacing.s3),
                    Expanded(
                      child: Text(
                        l10n.integrityBody,
                        style: context.type.bodyMd.copyWith(
                          color: colors.contentSecondary,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: Spacing.s6),
                AppButton(
                  label: l10n.integrityAcknowledge,
                  expand: true,
                  onPressed: () => Navigator.of(context).pop(),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
