import 'package:application/core/theme/app_theme.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/color_tokens.dart';
import 'package:application/core/theme/tokens/motion.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/semantic_colors.dart';
import 'package:application/core/theme/tokens/typography.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('key-cut corner sits top-end and mirrors in RTL', () {
    final ltr = KeyCut.card.resolve(TextDirection.ltr);
    final rtl = KeyCut.card.resolve(TextDirection.rtl);
    expect(ltr.topRight, const Radius.circular(Radii.key));
    expect(ltr.topLeft, const Radius.circular(Radii.sm));
    expect(rtl.topLeft, const Radius.circular(Radii.key));
    expect(rtl.topRight, const Radius.circular(Radii.sm));
    expect(
      KeyCut.button.resolve(TextDirection.rtl).topLeft,
      const Radius.circular(Radii.lg),
    );
  });

  test('semantic dark colours resolve to the documented primitives', () {
    const c = SemanticColors.dark;
    expect(c.surfaceCanvas, const Color(0xFF07080B));
    expect(c.surfaceRaised, ColorTokens.ink850);
    expect(c.accentPrimary, const Color(0xFFC8FF2E));
    expect(c.contentOnAccent, ColorTokens.ink950);
    expect(c.focusRing, ColorTokens.ultra400);
    expect(c.statusDangerTint, ColorTokens.red900);
  });

  test('type scale matches the Figma text styles', () {
    expect(TypeScale.displayHero.fontSize, 72);
    expect(TypeScale.displayHero.height! * 72, closeTo(76, 0.001));
    expect(TypeScale.displayHero.fontWeight, FontWeight.w800);
    expect(TypeScale.overline.letterSpacing, closeTo(12 * 0.12, 0.0001));
    expect(TypeScale.bodyMd.fontFamily, FontFamilies.readexPro);
    expect(TypeScale.monoMd.fontFamily, FontFamilies.ibmPlexMono);
  });

  test('Arabic rules drop letter spacing and add line height from 20 px', () {
    final hero = TypeScale.forArabic(TypeScale.displayHero);
    expect(hero.letterSpacing, 0);
    expect(hero.height, closeTo(TypeScale.displayHero.height! * 1.15, 0.0001));
    final body = TypeScale.forArabic(TypeScale.bodyMd);
    expect(body.height, TypeScale.bodyMd.height);
    expect(TypeScale.overlineText('store', arabic: false), 'STORE');
    expect(TypeScale.overlineText('المتجر', arabic: true), 'المتجر');
  });

  test('theme is dark first and exposes the extensions', () {
    final theme = AppTheme.dark(arabic: false);
    expect(theme.brightness, Brightness.dark);
    expect(theme.scaffoldBackgroundColor, ColorTokens.ink950);
    expect(theme.extension<AppColors>()!.colors, SemanticColors.dark);
    expect(theme.extension<AppTypography>()!.arabic, isFalse);
    expect(
      AppTheme.dark(arabic: true)
          .extension<AppTypography>()!
          .headingLg
          .letterSpacing,
      0,
    );
  });

  testWidgets('reduced motion collapses durations', (tester) async {
    late Duration normal;
    late Duration reduced;
    await tester.pumpWidget(
      Builder(
        builder: (context) {
          normal = MotionTokens.resolve(context, MotionTokens.base);
          return MediaQuery(
            data: const MediaQueryData(disableAnimations: true),
            child: Builder(
              builder: (inner) {
                reduced = MotionTokens.resolve(inner, MotionTokens.base);
                return const SizedBox();
              },
            ),
          );
        },
      ),
    );
    expect(normal, const Duration(milliseconds: 200));
    expect(reduced, Duration.zero);
  });
}
