import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/shared/widgets/option_tile.dart';
import 'package:application/shared/widgets/sheet_header.dart';
import 'package:flutter/material.dart';

/// One choice of a [SelectField] sheet.
@immutable
final class SelectOption<T> {
  const SelectOption({required this.value, required this.title, this.detail});

  final T value;
  final String title;
  final String? detail;
}

/// A field that opens a sheet of choices (the order and ticket pickers of 14.2 and 15.1): label
/// above, the chosen value in a 48dp field with a chevron, and an optional error below.
class SelectField<T> extends StatelessWidget {
  const SelectField({
    required this.label,
    required this.valueText,
    required this.options,
    required this.selected,
    required this.onSelected,
    this.errorText,
    this.helperText,
    this.enabled = true,
    super.key,
  });

  final String label;

  /// The chosen option's text (or the "none" text).
  final String valueText;
  final List<SelectOption<T>> options;
  final T selected;
  final ValueChanged<T> onSelected;
  final String? errorText;
  final String? helperText;
  final bool enabled;

  Future<void> _open(BuildContext context) async {
    final colors = context.colors;
    final picked = await showModalBottomSheet<SelectOption<T>>(
      context: context,
      isScrollControlled: true,
      useRootNavigator: true,
      backgroundColor: colors.surfaceRaised,
      builder: (context) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            SheetHeader(title: label),
            Flexible(
              child: ListView.separated(
                shrinkWrap: true,
                padding: const EdgeInsetsDirectional.fromSTEB(
                  Spacing.s4,
                  0,
                  Spacing.s4,
                  Spacing.s4,
                ),
                itemCount: options.length,
                separatorBuilder: (_, _) => const SizedBox(height: Spacing.s2),
                itemBuilder: (context, index) {
                  final option = options[index];
                  return OptionTile(
                    key: ValueKey('select-option-$index'),
                    title: option.title,
                    detail: option.detail,
                    selected: option.value == selected,
                    onTap: () => Navigator.of(context).pop(option),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
    if (picked != null) {
      onSelected(picked.value);
    }
  }

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final type = context.type;
    final error = errorText;
    final hasError = error != null && error.isNotEmpty;
    final tap = enabled ? () => _open(context) : null;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        ExcludeSemantics(
          child: Text(
            label,
            style: type.labelSm.copyWith(color: colors.contentSecondary),
          ),
        ),
        const SizedBox(height: Spacing.s1_5),
        Semantics(
          button: true,
          label: '$label, $valueText',
          excludeSemantics: true,
          onTap: tap,
          child: Material(
            color: colors.surfaceSunken,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(Radii.sm),
              side: BorderSide(
                color: hasError ? colors.statusDanger : colors.lineStrong,
                width: hasError ? 1.5 : 1,
              ),
            ),
            clipBehavior: Clip.antiAlias,
            child: InkWell(
              onTap: tap,
              child: ConstrainedBox(
                constraints: const BoxConstraints(minHeight: 48),
                child: Padding(
                  padding: const EdgeInsetsDirectional.symmetric(
                    horizontal: Spacing.s3,
                    vertical: Spacing.s2,
                  ),
                  child: Row(
                    children: [
                      Expanded(child: Text(valueText, style: type.bodyMd)),
                      Icon(Icons.expand_more, color: colors.contentSecondary),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
        if (hasError) ...[
          const SizedBox(height: Spacing.s1_5),
          Semantics(
            liveRegion: true,
            child: Text(
              error,
              style: type.bodyCaption.copyWith(color: colors.statusDanger),
            ),
          ),
        ] else if (helperText != null) ...[
          const SizedBox(height: Spacing.s1_5),
          Text(
            helperText!,
            style: type.bodyCaption.copyWith(color: colors.contentMuted),
          ),
        ],
      ],
    );
  }
}
