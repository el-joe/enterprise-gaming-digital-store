import 'package:application/core/theme/tokens/semantic_colors.dart';
import 'package:application/core/theme/tokens/typography.dart';
import 'package:flutter/material.dart';

/// Semantic colours exposed through the theme.
@immutable
final class AppColors extends ThemeExtension<AppColors> {
  const AppColors(this.colors);

  final SemanticColors colors;

  @override
  AppColors copyWith({SemanticColors? colors}) =>
      AppColors(colors ?? this.colors);

  /// Only one (dark) palette exists, so interpolation switches at the midpoint.
  @override
  AppColors lerp(covariant AppColors? other, double t) =>
      other == null || t < 0.5 ? this : other;
}

/// The type scale for the current script. Every style starts in `content-primary`; widgets
/// change the colour with `copyWith`. Arabic styles follow [TypeScale.forArabic]. Mono styles
/// are for LTR data only (codes, SKUs) and keep their Latin metrics.
@immutable
final class AppTypography extends ThemeExtension<AppTypography> {
  const AppTypography({required this.arabic, required this.color});

  final bool arabic;
  final Color color;

  TextStyle _adapt(TextStyle style) =>
      (arabic ? TypeScale.forArabic(style) : style).copyWith(color: color);

  TextStyle get displayHero => _adapt(TypeScale.displayHero);

  TextStyle get displayXl => _adapt(TypeScale.displayXl);

  TextStyle get displayLg => _adapt(TypeScale.displayLg);

  TextStyle get displayMd => _adapt(TypeScale.displayMd);

  TextStyle get displaySm => _adapt(TypeScale.displaySm);

  TextStyle get headingLg => _adapt(TypeScale.headingLg);

  TextStyle get headingMd => _adapt(TypeScale.headingMd);

  TextStyle get headingSm => _adapt(TypeScale.headingSm);

  TextStyle get bodyLg => _adapt(TypeScale.bodyLg);

  TextStyle get bodyMd => _adapt(TypeScale.bodyMd);

  TextStyle get bodySm => _adapt(TypeScale.bodySm);

  TextStyle get bodyCaption => _adapt(TypeScale.bodyCaption);

  TextStyle get labelLg => _adapt(TypeScale.labelLg);

  TextStyle get labelMd => _adapt(TypeScale.labelMd);

  TextStyle get labelSm => _adapt(TypeScale.labelSm);

  TextStyle get overline => _adapt(TypeScale.overline);

  TextStyle get priceXl => _adapt(TypeScale.priceXl);

  TextStyle get priceLg => _adapt(TypeScale.priceLg);

  TextStyle get priceMd => _adapt(TypeScale.priceMd);

  TextStyle get priceSm => _adapt(TypeScale.priceSm);

  TextStyle get monoLg => TypeScale.monoLg.copyWith(color: color);

  TextStyle get monoMd => TypeScale.monoMd.copyWith(color: color);

  TextStyle get monoSm => TypeScale.monoSm.copyWith(color: color);

  /// Applies the overline case rule for the current script.
  String overlineText(String text) =>
      TypeScale.overlineText(text, arabic: arabic);

  @override
  AppTypography copyWith({bool? arabic, Color? color}) =>
      AppTypography(arabic: arabic ?? this.arabic, color: color ?? this.color);

  @override
  AppTypography lerp(covariant AppTypography? other, double t) =>
      other == null || t < 0.5 ? this : other;
}

extension AppThemeX on BuildContext {
  SemanticColors get colors => Theme.of(this).extension<AppColors>()!.colors;

  AppTypography get type => Theme.of(this).extension<AppTypography>()!;
}
