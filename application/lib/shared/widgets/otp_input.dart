import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

/// One-time code input (Figma `Mobile / OTP Input` `75:751`): a single hidden text field with
/// one-time-code autofill, drawn as [length] boxes. Digits are always laid out left to right,
/// also in Arabic. The current box gets the focus ring; [hasError] outlines every box in danger.
class OtpInput extends StatefulWidget {
  const OtpInput({
    required this.semanticsLabel,
    this.controller,
    this.length = 6,
    this.hasError = false,
    this.onCompleted,
    this.onChanged,
    this.autofocus = true,
    super.key,
  });

  final String semanticsLabel;
  final TextEditingController? controller;
  final int length;
  final bool hasError;
  final ValueChanged<String>? onCompleted;
  final ValueChanged<String>? onChanged;
  final bool autofocus;

  @override
  State<OtpInput> createState() => _OtpInputState();
}

class _OtpInputState extends State<OtpInput> {
  late final TextEditingController _controller =
      widget.controller ?? TextEditingController();
  final FocusNode _focus = FocusNode();

  @override
  void initState() {
    super.initState();
    _controller.addListener(_changed);
    _focus.addListener(_changed);
  }

  void _changed() => setState(() {});

  @override
  void dispose() {
    _controller.removeListener(_changed);
    if (widget.controller == null) {
      _controller.dispose();
    }
    _focus.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final text = _controller.text;
    final boxes = List<Widget>.generate(widget.length, (index) {
      final filled = index < text.length;
      final current =
          _focus.hasFocus &&
          (index == text.length ||
              (text.length == widget.length && index == widget.length - 1));
      final borderColor = widget.hasError
          ? colors.statusDanger
          : current
          ? colors.focusRing
          : colors.lineStrong;
      return Expanded(
        child: Padding(
          padding: const EdgeInsetsDirectional.symmetric(
            horizontal: Spacing.s1,
          ),
          child: AspectRatio(
            aspectRatio: 1,
            child: DecoratedBox(
              decoration: BoxDecoration(
                color: colors.surfaceSunken,
                border: Border.all(
                  color: borderColor,
                  width: current || widget.hasError ? 1.5 : 1,
                ),
                borderRadius: BorderRadius.circular(Radii.md),
              ),
              child: Center(
                child: Text(
                  filled ? text[index] : '',
                  style: context.type.headingLg,
                ),
              ),
            ),
          ),
        ),
      );
    });

    return Directionality(
      textDirection: TextDirection.ltr,
      child: Semantics(
        label: widget.semanticsLabel,
        value: text,
        textField: true,
        onTap: _focus.requestFocus,
        child: GestureDetector(
          behavior: HitTestBehavior.opaque,
          onTap: _focus.requestFocus,
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 360, minHeight: 48),
            child: Stack(
              children: [
                // The real input: invisible, but focusable and autofillable.
                Positioned.fill(
                  child: Opacity(
                    opacity: 0,
                    child: ExcludeSemantics(
                      child: TextField(
                        controller: _controller,
                        focusNode: _focus,
                        autofocus: widget.autofocus,
                        keyboardType: TextInputType.number,
                        autofillHints: const [AutofillHints.oneTimeCode],
                        showCursor: false,
                        enableInteractiveSelection: false,
                        maxLength: widget.length,
                        inputFormatters: [
                          FilteringTextInputFormatter.digitsOnly,
                          LengthLimitingTextInputFormatter(widget.length),
                        ],
                        decoration: const InputDecoration(
                          counterText: '',
                          border: InputBorder.none,
                        ),
                        onChanged: (value) {
                          widget.onChanged?.call(value);
                          if (value.length == widget.length) {
                            widget.onCompleted?.call(value);
                          }
                        },
                      ),
                    ),
                  ),
                ),
                IgnorePointer(child: Row(children: boxes)),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
