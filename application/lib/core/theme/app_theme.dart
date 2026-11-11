import 'package:application/core/theme/reduced_motion_transitions.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/semantic_colors.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/theme/tokens/typography.dart';
import 'package:flutter/material.dart';

/// Builds the app theme from the tokens. The app is dark first and has no light theme.
abstract final class AppTheme {
  static ThemeData dark({required bool arabic}) {
    const c = SemanticColors.dark;
    final type = AppTypography(arabic: arabic, color: c.contentPrimary);

    final colorScheme = ColorScheme(
      brightness: Brightness.dark,
      primary: c.accentPrimary,
      onPrimary: c.contentOnAccent,
      primaryContainer: c.accentPrimaryTint,
      onPrimaryContainer: c.accentText,
      secondary: c.accentUltra,
      onSecondary: c.contentInverse,
      secondaryContainer: c.accentUltraTint,
      onSecondaryContainer: c.contentPrimary,
      tertiary: c.accentEmber,
      onTertiary: c.contentOnEmber,
      tertiaryContainer: c.accentEmberTint,
      onTertiaryContainer: c.accentEmberText,
      error: c.statusDanger,
      onError: c.contentInverse,
      errorContainer: c.statusDangerTint,
      onErrorContainer: c.statusDanger,
      surface: c.surfaceBase,
      onSurface: c.contentPrimary,
      onSurfaceVariant: c.contentSecondary,
      surfaceDim: c.surfaceSunken,
      surfaceBright: c.surfaceOverlay,
      surfaceContainerLowest: c.surfaceSunken,
      surfaceContainerLow: c.surfaceBase,
      surfaceContainer: c.surfaceRaised,
      surfaceContainerHigh: c.surfaceOverlay,
      surfaceContainerHighest: c.surfaceHover,
      outline: c.lineDefault,
      outlineVariant: c.lineSubtle,
      shadow: c.surfaceCanvas,
      scrim: c.surfaceCanvas,
      inverseSurface: c.surfaceInverse,
      onInverseSurface: c.contentInverse,
      inversePrimary: c.accentPrimaryTint,
    );

    final textTheme = TextTheme(
      displayLarge: type.displayXl,
      displayMedium: type.displayLg,
      displaySmall: type.displayMd,
      headlineLarge: type.displaySm,
      headlineMedium: type.headingLg,
      headlineSmall: type.headingMd,
      titleLarge: type.headingLg,
      titleMedium: type.headingMd,
      titleSmall: type.headingSm,
      bodyLarge: type.bodyLg,
      bodyMedium: type.bodyMd,
      bodySmall: type.bodySm,
      labelLarge: type.labelLg,
      labelMedium: type.labelMd,
      labelSmall: type.labelSm,
    );

    const buttonPadding = EdgeInsetsDirectional.symmetric(
      horizontal: Spacing.s5,
      vertical: Spacing.s3,
    );
    const buttonMinSize = Size(
      LayoutTokens.minTouchTarget,
      LayoutTokens.minTouchTarget,
    );

    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      colorScheme: colorScheme,
      scaffoldBackgroundColor: c.surfaceCanvas,
      canvasColor: c.surfaceCanvas,
      fontFamily: FontFamilies.readexPro,
      textTheme: textTheme,
      dividerColor: c.lineSubtle,
      focusColor: c.focusRing,
      hoverColor: c.surfaceHover,
      // Platform page transitions, cross-faded in place under reduced motion (M-A19).
      pageTransitionsTheme: ReducedMotionPageTransitionsBuilder.theme(),
      appBarTheme: AppBarTheme(
        backgroundColor: c.surfaceCanvas,
        foregroundColor: c.contentPrimary,
        surfaceTintColor: c.surfaceCanvas,
        elevation: 0,
        centerTitle: false,
        titleTextStyle: type.headingMd,
      ),
      cardTheme: CardThemeData(
        color: c.surfaceRaised,
        surfaceTintColor: c.surfaceRaised,
        elevation: 0,
        margin: EdgeInsets.zero,
        shape: KeyCut.cardShape(side: BorderSide(color: c.lineSubtle)),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: c.accentPrimary,
          foregroundColor: c.contentOnAccent,
          disabledBackgroundColor: c.surfaceOverlay,
          disabledForegroundColor: c.contentDisabled,
          textStyle: type.labelLg,
          padding: buttonPadding,
          minimumSize: buttonMinSize,
          shape: KeyCut.buttonShape(),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: c.contentPrimary,
          disabledForegroundColor: c.contentDisabled,
          side: BorderSide(color: c.lineStrong),
          textStyle: type.labelLg,
          padding: buttonPadding,
          minimumSize: buttonMinSize,
          shape: KeyCut.buttonShape(),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: c.accentText,
          disabledForegroundColor: c.contentDisabled,
          textStyle: type.labelLg,
          padding: buttonPadding,
          minimumSize: buttonMinSize,
          shape: KeyCut.buttonShape(),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: c.surfaceSunken,
        hintStyle: type.bodyMd.copyWith(color: c.contentMuted),
        labelStyle: type.labelMd.copyWith(color: c.contentSecondary),
        errorStyle: type.bodySm.copyWith(color: c.statusDanger),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(Radii.md),
          borderSide: BorderSide(color: c.lineDefault),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(Radii.md),
          borderSide: BorderSide(color: c.lineDefault),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(Radii.md),
          borderSide: BorderSide(color: c.focusRing, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(Radii.md),
          borderSide: BorderSide(color: c.statusDanger),
        ),
      ),
      snackBarTheme: SnackBarThemeData(
        backgroundColor: c.surfaceOverlay,
        contentTextStyle: type.bodyMd,
        behavior: SnackBarBehavior.floating,
      ),
      extensions: [const AppColors(c), type],
    );
  }
}
