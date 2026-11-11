import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:flutter/material.dart';

/// Bottom sheet header (Figma `Mobile / Sheet Header` `75:589`): drag handle, title and a 48dp
/// close button at the end.
class SheetHeader extends StatelessWidget {
  const SheetHeader({required this.title, this.onClose, super.key});

  final String title;
  final VoidCallback? onClose;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        const SizedBox(height: Spacing.s2),
        Container(
          width: 32,
          height: 4,
          decoration: BoxDecoration(
            color: colors.lineStrong,
            borderRadius: BorderRadius.circular(Radii.full),
          ),
        ),
        Padding(
          padding: const EdgeInsetsDirectional.only(
            start: Spacing.s4,
            end: Spacing.s1,
          ),
          child: Row(
            children: [
              Expanded(
                child: Semantics(
                  header: true,
                  child: Text(title, style: context.type.headingMd),
                ),
              ),
              IconButton(
                tooltip: context.l10n.actionClose,
                icon: const Icon(Icons.close),
                onPressed: onClose ?? () => Navigator.of(context).maybePop(),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
