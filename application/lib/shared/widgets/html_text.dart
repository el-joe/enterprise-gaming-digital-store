import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/safe_html.dart';
import 'package:flutter/material.dart';

/// Server HTML (the product description) shown as native text: paragraphs, headings, list items
/// and bold or italic runs from [parseSafeHtml]. No web view, no script, no remote loading.
class HtmlText extends StatelessWidget {
  const HtmlText(this.html, {super.key});

  final String html;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final type = context.type;
    final body = type.bodyMd.copyWith(color: colors.contentSecondary);
    final blocks = parseSafeHtml(html);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        for (var i = 0; i < blocks.length; i++) ...[
          if (i > 0) const SizedBox(height: Spacing.s2),
          _block(blocks[i], body, type.headingSm),
        ],
      ],
    );
  }

  Widget _block(HtmlBlock block, TextStyle body, TextStyle heading) {
    final base = block.headingLevel > 0 ? heading : body;
    final text = Text.rich(
      TextSpan(
        style: base,
        children: [
          for (final span in block.spans)
            TextSpan(
              text: span.text,
              style: TextStyle(
                fontWeight: span.bold ? FontWeight.w600 : null,
                fontVariations: span.bold
                    ? const [FontVariation('wght', 600)]
                    : null,
                fontStyle: span.italic ? FontStyle.italic : null,
              ),
            ),
        ],
      ),
    );
    final bullet = block.bullet;
    final content = block.headingLevel > 0
        ? Semantics(header: true, child: text)
        : text;
    if (bullet == null) {
      return content;
    }
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: Spacing.s6,
          child: Text(bullet, style: body),
        ),
        Expanded(child: content),
      ],
    );
  }
}
