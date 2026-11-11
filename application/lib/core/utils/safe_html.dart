import 'package:flutter/foundation.dart' show immutable, listEquals;

/// A run of text inside an [HtmlBlock].
@immutable
final class HtmlSpan {
  const HtmlSpan(this.text, {this.bold = false, this.italic = false});

  final String text;
  final bool bold;
  final bool italic;

  @override
  bool operator ==(Object other) =>
      other is HtmlSpan &&
      other.text == text &&
      other.bold == bold &&
      other.italic == italic;

  @override
  int get hashCode => Object.hash(text, bold, italic);

  @override
  String toString() =>
      'HtmlSpan($text${bold ? ', b' : ''}${italic ? ', i' : ''})';
}

/// One block of text: a paragraph, a heading ([headingLevel] 1 to 6) or a list item ([bullet]
/// is `•` or `n.`).
@immutable
final class HtmlBlock {
  const HtmlBlock(this.spans, {this.headingLevel = 0, this.bullet});

  final List<HtmlSpan> spans;
  final int headingLevel;
  final String? bullet;

  String get text => spans.map((s) => s.text).join();

  @override
  bool operator ==(Object other) =>
      other is HtmlBlock &&
      listEquals(other.spans, spans) &&
      other.headingLevel == headingLevel &&
      other.bullet == bullet;

  @override
  int get hashCode => Object.hash(Object.hashAll(spans), headingLevel, bullet);

  @override
  String toString() => 'HtmlBlock(h$headingLevel, $bullet, $spans)';
}

final _token = RegExp(r'<!--[\s\S]*?-->|<(/?)([a-zA-Z][a-zA-Z0-9]*)\b[^>]*>');
final _space = RegExp(r'\s+');
final _entity = RegExp(r'&(#x[0-9a-fA-F]+|#[0-9]+|[a-zA-Z]+);');

const _blockTags = {
  'p',
  'div',
  'section',
  'article',
  'blockquote',
  'h1',
  'h2',
  'h3',
  'h4',
  'h5',
  'h6',
  'li',
  'tr',
  'table',
  'ul',
  'ol',
  'dl',
  'dt',
  'dd',
  'hr',
  'pre',
  'figure',
  'figcaption',
};

/// Content of these elements is dropped, never shown as text.
const _skippedTags = {'script', 'style', 'template', 'iframe', 'object'};

const _named = {
  'amp': '&',
  'lt': '<',
  'gt': '>',
  'quot': '"',
  'apos': "'",
  'nbsp': ' ',
  'ndash': '–',
  'mdash': '—',
  'hellip': '…',
  'laquo': '«',
  'raquo': '»',
  'rsquo': '’',
  'lsquo': '‘',
  'rdquo': '”',
  'ldquo': '“',
  'copy': '©',
  'reg': '®',
  'trade': '™',
};

String _decode(String text) => text.replaceAllMapped(_entity, (match) {
  final name = match.group(1)!;
  int? code;
  if (name.startsWith('#x')) {
    code = int.tryParse(name.substring(2), radix: 16);
  } else if (name.startsWith('#')) {
    code = int.tryParse(name.substring(1));
  } else {
    return _named[name.toLowerCase()] ?? match.group(0)!;
  }
  if (code == null || code <= 0 || code > 0x10FFFF) {
    return '';
  }
  return String.fromCharCode(code);
});

/// Turns the product `description` (HTML already limited by the server's sanitizer allow-list,
/// API_DOCUMENTATION.md 2c) into plain text blocks. Nothing is executed or loaded: tags only
/// shape the text (paragraphs, headings, list items, line breaks, bold and italic); links become
/// their text; images, scripts and any unknown markup are dropped.
List<HtmlBlock> parseSafeHtml(String html) {
  final blocks = <HtmlBlock>[];
  var spans = <HtmlSpan>[];
  var heading = 0;
  String? bullet;
  var bold = 0;
  var italic = 0;
  var skipping = 0;
  final lists = <({bool ordered, int count})>[];
  var cellOpen = false;

  void flush({bool closing = true}) {
    final merged = <HtmlSpan>[];
    for (final span in spans) {
      if (merged.isNotEmpty &&
          merged.last.bold == span.bold &&
          merged.last.italic == span.italic) {
        merged.last = HtmlSpan(
          merged.last.text + span.text,
          bold: span.bold,
          italic: span.italic,
        );
      } else {
        merged.add(span);
      }
    }
    // Trim the block's edges (and spaces around line breaks).
    if (merged.isNotEmpty) {
      final first = merged.first;
      merged.first = HtmlSpan(
        first.text.replaceFirst(RegExp(r'^[ \n]+'), ''),
        bold: first.bold,
        italic: first.italic,
      );
      final last = merged.last;
      merged.last = HtmlSpan(
        last.text.replaceFirst(RegExp(r'[ \n]+$'), ''),
        bold: last.bold,
        italic: last.italic,
      );
      for (var i = 0; i < merged.length; i++) {
        merged[i] = HtmlSpan(
          merged[i].text.replaceAll(RegExp(r' *\n *'), '\n'),
          bold: merged[i].bold,
          italic: merged[i].italic,
        );
      }
      merged.removeWhere((span) => span.text.isEmpty);
    }
    spans = [];
    cellOpen = false;
    if (merged.isNotEmpty) {
      blocks.add(HtmlBlock(merged, headingLevel: heading, bullet: bullet));
    } else if (!closing) {
      // An opening tag right after `<li>` or `<h2>` keeps the item's marker.
      return;
    }
    heading = 0;
    bullet = null;
  }

  void addText(String raw) {
    if (skipping > 0) {
      return;
    }
    final text = _decode(raw.replaceAll(_space, ' '));
    if (text.isEmpty) {
      return;
    }
    final last = spans.isEmpty ? null : spans.last.text;
    final collapsed =
        (last == null || last.endsWith(' ') || last.endsWith('\n'))
        ? text.replaceFirst(RegExp('^ '), '')
        : text;
    if (collapsed.isEmpty) {
      return;
    }
    spans.add(HtmlSpan(collapsed, bold: bold > 0, italic: italic > 0));
  }

  var position = 0;
  for (final match in _token.allMatches(html)) {
    addText(html.substring(position, match.start));
    position = match.end;
    final name = match.group(2)?.toLowerCase();
    if (name == null) {
      continue; // comment
    }
    final closing = match.group(1) == '/';
    if (_skippedTags.contains(name)) {
      skipping = closing ? (skipping - 1).clamp(0, 1 << 20) : skipping + 1;
      continue;
    }
    if (skipping > 0) {
      continue;
    }
    switch (name) {
      case 'br':
        if (spans.isNotEmpty) {
          spans.add(HtmlSpan('\n', bold: bold > 0, italic: italic > 0));
        }
      case 'strong' || 'b':
        bold = closing ? (bold - 1).clamp(0, 1 << 20) : bold + 1;
      case 'em' || 'i':
        italic = closing ? (italic - 1).clamp(0, 1 << 20) : italic + 1;
      case 'td' || 'th':
        if (!closing && cellOpen && spans.isNotEmpty) {
          spans.add(const HtmlSpan(' · '));
        }
        if (!closing) {
          cellOpen = true;
        }
      case 'ul' || 'ol':
        flush();
        if (closing) {
          if (lists.isNotEmpty) {
            lists.removeLast();
          }
        } else {
          lists.add((ordered: name == 'ol', count: 0));
        }
      case 'li':
        flush(closing: closing);
        if (!closing) {
          if (lists.isEmpty) {
            bullet = '•';
          } else {
            final list = lists.removeLast();
            final count = list.count + 1;
            lists.add((ordered: list.ordered, count: count));
            bullet = list.ordered ? '$count.' : '•';
          }
        }
      case final tag when _blockTags.contains(tag):
        flush(closing: closing);
        if (!closing && tag.length == 2 && tag.startsWith('h')) {
          heading = int.tryParse(tag.substring(1)) ?? 0;
        }
      default:
        break; // a, span, img, u, small, ...: text only
    }
  }
  addText(html.substring(position));
  flush();
  return blocks;
}
