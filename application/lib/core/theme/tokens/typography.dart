import 'package:flutter/painting.dart';

/// Bundled font families (pubspec.yaml). Changa and Readex Pro are variable fonts.
abstract final class FontFamilies {
  static const changa = 'Changa';
  static const readexPro = 'ReadexPro';
  static const ibmPlexMono = 'IBMPlexMono';
}

TextStyle _style({
  required String family,
  required double size,
  required double lineHeight,
  required int weight,
  double letterSpacingPercent = 0,
}) {
  return TextStyle(
    fontFamily: family,
    fontSize: size,
    height: lineHeight / size,
    fontWeight: FontWeight.values[(weight ~/ 100) - 1],
    // Variable fonts need the axis value; static IBM Plex Mono files ignore it.
    fontVariations: [FontVariation.weight(weight.toDouble())],
    letterSpacing: size * letterSpacingPercent / 100,
    leadingDistribution: TextLeadingDistribution.even,
  );
}

/// Type scale (Figma text styles; docs/DESIGN_TOKENS.md "Type scale"), Latin rules.
/// Use [TypeScale.forArabic] for Arabic text.
abstract final class TypeScale {
  /// `display/hero`: changa 800, 72/76, letter spacing -1%.
  static final TextStyle displayHero = _style(
    family: FontFamilies.changa,
    size: 72,
    lineHeight: 76,
    weight: 800,
    letterSpacingPercent: -1,
  );

  /// `display/xl`: changa 800, 56/60, letter spacing -1%.
  static final TextStyle displayXl = _style(
    family: FontFamilies.changa,
    size: 56,
    lineHeight: 60,
    weight: 800,
    letterSpacingPercent: -1,
  );

  /// `display/lg`: changa 700, 40/46, letter spacing -0.5%.
  static final TextStyle displayLg = _style(
    family: FontFamilies.changa,
    size: 40,
    lineHeight: 46,
    weight: 700,
    letterSpacingPercent: -0.5,
  );

  /// `display/md`: changa 700, 32/38.
  static final TextStyle displayMd = _style(
    family: FontFamilies.changa,
    size: 32,
    lineHeight: 38,
    weight: 700,
  );

  /// `display/sm`: changa 700, 26/32.
  static final TextStyle displaySm = _style(
    family: FontFamilies.changa,
    size: 26,
    lineHeight: 32,
    weight: 700,
  );

  /// `heading/lg`: readexPro 600, 22/30.
  static final TextStyle headingLg = _style(
    family: FontFamilies.readexPro,
    size: 22,
    lineHeight: 30,
    weight: 600,
  );

  /// `heading/md`: readexPro 600, 18/26.
  static final TextStyle headingMd = _style(
    family: FontFamilies.readexPro,
    size: 18,
    lineHeight: 26,
    weight: 600,
  );

  /// `heading/sm`: readexPro 600, 16/24.
  static final TextStyle headingSm = _style(
    family: FontFamilies.readexPro,
    size: 16,
    lineHeight: 24,
    weight: 600,
  );

  /// `body/lg`: readexPro 400, 16/26.
  static final TextStyle bodyLg = _style(
    family: FontFamilies.readexPro,
    size: 16,
    lineHeight: 26,
    weight: 400,
  );

  /// `body/md`: readexPro 400, 14/22.
  static final TextStyle bodyMd = _style(
    family: FontFamilies.readexPro,
    size: 14,
    lineHeight: 22,
    weight: 400,
  );

  /// `body/sm`: readexPro 400, 13/20.
  static final TextStyle bodySm = _style(
    family: FontFamilies.readexPro,
    size: 13,
    lineHeight: 20,
    weight: 400,
  );

  /// `body/caption`: readexPro 400, 12/16.
  static final TextStyle bodyCaption = _style(
    family: FontFamilies.readexPro,
    size: 12,
    lineHeight: 16,
    weight: 400,
  );

  /// `label/lg`: readexPro 500, 16/24.
  static final TextStyle labelLg = _style(
    family: FontFamilies.readexPro,
    size: 16,
    lineHeight: 24,
    weight: 500,
  );

  /// `label/md`: readexPro 500, 14/20.
  static final TextStyle labelMd = _style(
    family: FontFamilies.readexPro,
    size: 14,
    lineHeight: 20,
    weight: 500,
  );

  /// `label/sm`: readexPro 500, 12/16.
  static final TextStyle labelSm = _style(
    family: FontFamilies.readexPro,
    size: 12,
    lineHeight: 16,
    weight: 500,
  );

  /// `overline`: changa 600, 12/16, letter spacing 12%.
  static final TextStyle overline = _style(
    family: FontFamilies.changa,
    size: 12,
    lineHeight: 16,
    weight: 600,
    letterSpacingPercent: 12,
  );

  /// `price/xl`: changa 700, 44/48.
  static final TextStyle priceXl = _style(
    family: FontFamilies.changa,
    size: 44,
    lineHeight: 48,
    weight: 700,
  );

  /// `price/lg`: changa 700, 28/32.
  static final TextStyle priceLg = _style(
    family: FontFamilies.changa,
    size: 28,
    lineHeight: 32,
    weight: 700,
  );

  /// `price/md`: changa 700, 20/26.
  static final TextStyle priceMd = _style(
    family: FontFamilies.changa,
    size: 20,
    lineHeight: 26,
    weight: 700,
  );

  /// `price/sm`: changa 600, 16/20.
  static final TextStyle priceSm = _style(
    family: FontFamilies.changa,
    size: 16,
    lineHeight: 20,
    weight: 600,
  );

  /// `mono/lg`: ibmPlexMono 500, 20/28, letter spacing 4%.
  static final TextStyle monoLg = _style(
    family: FontFamilies.ibmPlexMono,
    size: 20,
    lineHeight: 28,
    weight: 500,
    letterSpacingPercent: 4,
  );

  /// `mono/md`: ibmPlexMono 400, 13/20.
  static final TextStyle monoMd = _style(
    family: FontFamilies.ibmPlexMono,
    size: 13,
    lineHeight: 20,
    weight: 400,
  );

  /// `mono/sm`: ibmPlexMono 400, 12/16.
  static final TextStyle monoSm = _style(
    family: FontFamilies.ibmPlexMono,
    size: 12,
    lineHeight: 16,
    weight: 400,
  );

  /// Arabic rules: no letter spacing and about 15% more line height from 20 px up.
  /// Uppercase is never applied to Arabic (see [overlineText]).
  static TextStyle forArabic(TextStyle style) {
    final size = style.fontSize ?? 14;
    final height = style.height;
    return style.copyWith(
      letterSpacing: 0,
      height: size >= 20 && height != null ? height * 1.15 : height,
    );
  }

  /// The overline style is uppercase in Latin scripts only.
  static String overlineText(String text, {required bool arabic}) =>
      arabic ? text : text.toUpperCase();
}
