import 'dart:ui';

import 'package:application/core/theme/tokens/color_tokens.dart';
import 'package:flutter/foundation.dart';

/// Semantic colours (Figma `Theme` collection, mode `Dark`). The app is dark first; the
/// `Admin Light` mode belongs to the web admin and is not used here.
@immutable
final class SemanticColors {
  const SemanticColors({
    required this.surfaceCanvas,
    required this.surfaceBase,
    required this.surfaceRaised,
    required this.surfaceOverlay,
    required this.surfaceHover,
    required this.surfaceSunken,
    required this.surfaceInverse,
    required this.lineSubtle,
    required this.lineDefault,
    required this.lineStrong,
    required this.contentPrimary,
    required this.contentSecondary,
    required this.contentMuted,
    required this.contentDisabled,
    required this.contentInverse,
    required this.contentOnAccent,
    required this.contentOnEmber,
    required this.accentPrimary,
    required this.accentPrimaryHover,
    required this.accentPrimaryPressed,
    required this.accentPrimaryTint,
    required this.accentText,
    required this.accentEmber,
    required this.accentEmberTint,
    required this.accentEmberText,
    required this.accentUltra,
    required this.accentUltraTint,
    required this.focusRing,
    required this.statusSuccess,
    required this.statusSuccessTint,
    required this.statusWarning,
    required this.statusWarningTint,
    required this.statusDanger,
    required this.statusDangerTint,
    required this.statusDangerSolid,
    required this.statusInfo,
    required this.statusInfoTint,
    required this.fieldVolt,
    required this.fieldEmber,
    required this.fieldUltra,
    required this.fieldAqua,
    required this.fieldFlux,
    required this.fieldSun,
    required this.fieldInk,
  });

  /// `Theme` / Dark.
  static const dark = SemanticColors(
    surfaceCanvas: ColorTokens.ink950,
    surfaceBase: ColorTokens.ink900,
    surfaceRaised: ColorTokens.ink850,
    surfaceOverlay: ColorTokens.ink800,
    surfaceHover: ColorTokens.ink800,
    surfaceSunken: ColorTokens.ink950,
    surfaceInverse: ColorTokens.ink50,
    lineSubtle: ColorTokens.ink800,
    lineDefault: ColorTokens.ink700,
    lineStrong: ColorTokens.ink600,
    contentPrimary: ColorTokens.ink50,
    contentSecondary: ColorTokens.ink300,
    contentMuted: ColorTokens.ink400,
    contentDisabled: ColorTokens.ink500,
    contentInverse: ColorTokens.ink950,
    contentOnAccent: ColorTokens.ink950,
    contentOnEmber: ColorTokens.ink950,
    accentPrimary: ColorTokens.volt500,
    accentPrimaryHover: ColorTokens.volt300,
    accentPrimaryPressed: ColorTokens.volt600,
    accentPrimaryTint: ColorTokens.volt900,
    accentText: ColorTokens.volt500,
    accentEmber: ColorTokens.ember500,
    accentEmberTint: ColorTokens.ember900,
    accentEmberText: ColorTokens.ember400,
    accentUltra: ColorTokens.ultra400,
    accentUltraTint: ColorTokens.ultra900,
    focusRing: ColorTokens.ultra400,
    statusSuccess: ColorTokens.green400,
    statusSuccessTint: ColorTokens.green900,
    statusWarning: ColorTokens.sun400,
    statusWarningTint: ColorTokens.sun900,
    statusDanger: ColorTokens.red400,
    statusDangerTint: ColorTokens.red900,
    statusDangerSolid: ColorTokens.red500,
    statusInfo: ColorTokens.blue400,
    statusInfoTint: ColorTokens.blue900,
    fieldVolt: ColorTokens.volt900,
    fieldEmber: ColorTokens.ember900,
    fieldUltra: ColorTokens.ultra900,
    fieldAqua: ColorTokens.aqua900,
    fieldFlux: ColorTokens.flux900,
    fieldSun: ColorTokens.sun900,
    fieldInk: ColorTokens.ink800,
  );

  /// `color/surface/canvas` (dark: ink-950).
  final Color surfaceCanvas;

  /// `color/surface/base` (dark: ink-900).
  final Color surfaceBase;

  /// `color/surface/raised` (dark: ink-850).
  final Color surfaceRaised;

  /// `color/surface/overlay` (dark: ink-800).
  final Color surfaceOverlay;

  /// `color/surface/hover` (dark: ink-800).
  final Color surfaceHover;

  /// `color/surface/sunken` (dark: ink-950).
  final Color surfaceSunken;

  /// `color/surface/inverse` (dark: ink-50).
  final Color surfaceInverse;

  /// `color/line/subtle` (dark: ink-800).
  final Color lineSubtle;

  /// `color/line/default` (dark: ink-700).
  final Color lineDefault;

  /// `color/line/strong` (dark: ink-600).
  final Color lineStrong;

  /// `color/content/primary` (dark: ink-50).
  final Color contentPrimary;

  /// `color/content/secondary` (dark: ink-300).
  final Color contentSecondary;

  /// `color/content/muted` (dark: ink-400).
  final Color contentMuted;

  /// `color/content/disabled` (dark: ink-500).
  final Color contentDisabled;

  /// `color/content/inverse` (dark: ink-950).
  final Color contentInverse;

  /// `color/content/on-accent` (dark: ink-950).
  final Color contentOnAccent;

  /// `color/content/on-ember` (dark: ink-950).
  final Color contentOnEmber;

  /// `color/accent/primary` (dark: volt-500).
  final Color accentPrimary;

  /// `color/accent/primary-hover` (dark: volt-300).
  final Color accentPrimaryHover;

  /// `color/accent/primary-pressed` (dark: volt-600).
  final Color accentPrimaryPressed;

  /// `color/accent/primary-tint` (dark: volt-900).
  final Color accentPrimaryTint;

  /// `color/accent/text` (dark: volt-500).
  final Color accentText;

  /// `color/accent/ember` (dark: ember-500).
  final Color accentEmber;

  /// `color/accent/ember-tint` (dark: ember-900).
  final Color accentEmberTint;

  /// `color/accent/ember-text` (dark: ember-400).
  final Color accentEmberText;

  /// `color/accent/ultra` (dark: ultra-400).
  final Color accentUltra;

  /// `color/accent/ultra-tint` (dark: ultra-900).
  final Color accentUltraTint;

  /// `color/focus/ring` (dark: ultra-400).
  final Color focusRing;

  /// `color/status/success` (dark: green-400).
  final Color statusSuccess;

  /// `color/status/success-tint` (dark: green-900).
  final Color statusSuccessTint;

  /// `color/status/warning` (dark: sun-400).
  final Color statusWarning;

  /// `color/status/warning-tint` (dark: sun-900).
  final Color statusWarningTint;

  /// `color/status/danger` (dark: red-400).
  final Color statusDanger;

  /// `color/status/danger-tint` (dark: red-900).
  final Color statusDangerTint;

  /// `color/status/danger-solid` (dark: red-500).
  final Color statusDangerSolid;

  /// `color/status/info` (dark: blue-400).
  final Color statusInfo;

  /// `color/status/info-tint` (dark: blue-900).
  final Color statusInfoTint;

  /// `color/field/volt` (dark: volt-900).
  final Color fieldVolt;

  /// `color/field/ember` (dark: ember-900).
  final Color fieldEmber;

  /// `color/field/ultra` (dark: ultra-900).
  final Color fieldUltra;

  /// `color/field/aqua` (dark: aqua-900).
  final Color fieldAqua;

  /// `color/field/flux` (dark: flux-900).
  final Color fieldFlux;

  /// `color/field/sun` (dark: sun-900).
  final Color fieldSun;

  /// `color/field/ink` (dark: ink-800).
  final Color fieldInk;
}
