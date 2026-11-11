import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

/// Labelled text field (Figma `Input` `5:921`): label above, 44dp field on `surface-sunken`, and
/// helper or error text below. [errorText] carries the server's field error (`errors.<field>` of
/// a 422) or a local check; the field then gets the danger border, an alert icon and error
/// semantics. Password fields get a 48dp show/hide toggle.
class AppTextField extends StatefulWidget {
  const AppTextField({
    required this.label,
    this.controller,
    this.errorText,
    this.helperText,
    this.obscure = false,
    this.keyboardType,
    this.textInputAction,
    this.autofillHints,
    this.onSubmitted,
    this.onChanged,
    this.enabled = true,
    this.focusNode,
    this.ltr = false,
    this.inputFormatters,
    this.textCapitalization = TextCapitalization.none,
    this.minLines,
    this.maxLines = 1,
    this.maxLength,
    super.key,
  });

  final String label;
  final TextEditingController? controller;
  final String? errorText;
  final String? helperText;
  final bool obscure;
  final TextInputType? keyboardType;
  final TextInputAction? textInputAction;
  final Iterable<String>? autofillHints;
  final ValueChanged<String>? onSubmitted;
  final ValueChanged<String>? onChanged;
  final bool enabled;
  final FocusNode? focusNode;

  /// Emails and codes are typed left to right in both languages.
  final bool ltr;
  final List<TextInputFormatter>? inputFormatters;
  final TextCapitalization textCapitalization;

  /// Multi-line fields (messages, reviews): [minLines] to [maxLines] lines; null [maxLines]
  /// grows without a limit.
  final int? minLines;
  final int? maxLines;

  /// Stops typing at this many characters (no counter is drawn; the helper text says it).
  final int? maxLength;

  @override
  State<AppTextField> createState() => _AppTextFieldState();
}

class _AppTextFieldState extends State<AppTextField> {
  late bool _hidden = widget.obscure;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final type = context.type;
    final l10n = context.l10n;
    final error = widget.errorText;
    final hasError = error != null && error.isNotEmpty;

    OutlineInputBorder border(Color color, [double width = 1]) =>
        OutlineInputBorder(
          borderRadius: BorderRadius.circular(Radii.sm),
          borderSide: BorderSide(color: color, width: width),
        );

    final suffix = <Widget>[
      if (hasError)
        Icon(Icons.warning_amber_rounded, size: 18, color: colors.statusDanger),
      if (widget.obscure)
        IconButton(
          tooltip: _hidden ? l10n.showPassword : l10n.hidePassword,
          icon: Icon(
            _hidden ? Icons.visibility_outlined : Icons.visibility_off_outlined,
            size: 20,
            color: colors.contentSecondary,
          ),
          onPressed: () => setState(() => _hidden = !_hidden),
        ),
    ];

    final field = TextField(
      controller: widget.controller,
      focusNode: widget.focusNode,
      enabled: widget.enabled,
      obscureText: _hidden,
      enableSuggestions: !widget.obscure,
      autocorrect: false,
      keyboardType: widget.keyboardType,
      textInputAction: widget.textInputAction,
      autofillHints: widget.autofillHints,
      onSubmitted: widget.onSubmitted,
      onChanged: widget.onChanged,
      inputFormatters: widget.inputFormatters,
      textCapitalization: widget.textCapitalization,
      minLines: widget.minLines,
      maxLines: widget.obscure ? 1 : widget.maxLines,
      maxLength: widget.maxLength,
      maxLengthEnforcement: MaxLengthEnforcement.enforced,
      textDirection: widget.ltr ? TextDirection.ltr : null,
      style: type.bodyMd,
      cursorColor: colors.accentPrimary,
      decoration: InputDecoration(
        isDense: true,
        filled: true,
        fillColor: colors.surfaceSunken,
        contentPadding: const EdgeInsetsDirectional.symmetric(
          horizontal: Spacing.s3,
          vertical: Spacing.s3,
        ),
        enabledBorder: hasError
            ? border(colors.statusDanger, 1.5)
            : border(colors.lineStrong),
        focusedBorder: hasError
            ? border(colors.statusDanger, 1.5)
            : border(colors.focusRing, 1.5),
        disabledBorder: border(colors.lineSubtle),
        suffixIcon: suffix.isEmpty
            ? null
            : Row(mainAxisSize: MainAxisSize.min, children: suffix),
        // The label and error are drawn outside the field (design), so the field itself carries
        // them for screen readers.
        semanticCounterText: '',
        counterText: widget.maxLength == null ? null : '',
      ),
    );

    return Semantics(
      label: widget.label,
      textField: true,
      container: true,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          ExcludeSemantics(
            child: Text(
              widget.label,
              style: type.labelSm.copyWith(color: colors.contentSecondary),
            ),
          ),
          const SizedBox(height: Spacing.s1_5),
          ConstrainedBox(
            constraints: const BoxConstraints(minHeight: 48),
            child: field,
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
          ] else if (widget.helperText != null) ...[
            const SizedBox(height: Spacing.s1_5),
            Text(
              widget.helperText!,
              style: type.bodyCaption.copyWith(color: colors.contentMuted),
            ),
          ],
        ],
      ),
    );
  }
}
