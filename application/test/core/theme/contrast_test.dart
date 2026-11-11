import 'dart:math' as math;

import 'package:application/core/theme/tokens/semantic_colors.dart';
import 'package:flutter/painting.dart';
import 'package:flutter_test/flutter_test.dart';

/// WCAG 2.1 contrast ratio of two opaque colours.
double contrast(Color a, Color b) {
  double channel(double c) =>
      c <= 0.03928 ? c / 12.92 : math.pow((c + 0.055) / 1.055, 2.4).toDouble();
  double luminance(Color c) =>
      0.2126 * channel(c.r) + 0.7152 * channel(c.g) + 0.0722 * channel(c.b);
  final la = luminance(a);
  final lb = luminance(b);
  return (math.max(la, lb) + 0.05) / (math.min(la, lb) + 0.05);
}

/// M-A19 contrast check against the theme tokens (dark theme, the only app theme): text pairs
/// reach WCAG AA 4.5:1, state indicators and the focus ring 3:1 (1.4.11). `content/disabled` is
/// exempt (disabled controls), and `line/*` are decorative separators and field outlines whose
/// fields also carry a visible label.
void main() {
  const c = SemanticColors.dark;
  final surfaces = {
    'canvas': c.surfaceCanvas,
    'base': c.surfaceBase,
    'raised': c.surfaceRaised,
    'overlay': c.surfaceOverlay,
    'sunken': c.surfaceSunken,
  };

  group('text on every surface reaches 4.5:1', () {
    final texts = {
      'content/primary': c.contentPrimary,
      'content/secondary': c.contentSecondary,
      'content/muted': c.contentMuted,
      'accent/text': c.accentText,
      'accent/ember-text': c.accentEmberText,
      'status/success': c.statusSuccess,
      'status/warning': c.statusWarning,
      'status/danger': c.statusDanger,
      'status/info': c.statusInfo,
    };
    for (final text in texts.entries) {
      for (final surface in surfaces.entries) {
        test('${text.key} on ${surface.key}', () {
          expect(
            contrast(text.value, surface.value),
            greaterThanOrEqualTo(4.5),
          );
        });
      }
    }
  });

  group('tinted pairs reach 4.5:1', () {
    final pairs = {
      'success on its tint': (c.statusSuccess, c.statusSuccessTint),
      'warning on its tint': (c.statusWarning, c.statusWarningTint),
      'danger on its tint': (c.statusDanger, c.statusDangerTint),
      'info on its tint': (c.statusInfo, c.statusInfoTint),
      'accent text on accent tint': (c.accentText, c.accentPrimaryTint),
      'ember text on ember tint': (c.accentEmberText, c.accentEmberTint),
      'primary on accent tint': (c.contentPrimary, c.accentPrimaryTint),
      'secondary on accent tint': (c.contentSecondary, c.accentPrimaryTint),
      'muted on accent tint': (c.contentMuted, c.accentPrimaryTint),
      'on-accent on volt': (c.contentOnAccent, c.accentPrimary),
      'on-ember on ember': (c.contentOnEmber, c.accentEmber),
      'inverse on danger solid': (c.contentInverse, c.statusDangerSolid),
    };
    for (final pair in pairs.entries) {
      test(pair.key, () {
        expect(
          contrast(pair.value.$1, pair.value.$2),
          greaterThanOrEqualTo(4.5),
        );
      });
    }
    for (final field in [
      c.fieldVolt,
      c.fieldEmber,
      c.fieldUltra,
      c.fieldAqua,
      c.fieldFlux,
      c.fieldSun,
      c.fieldInk,
    ]) {
      test('primary text on field $field', () {
        expect(contrast(c.contentPrimary, field), greaterThanOrEqualTo(4.5));
      });
    }
  });

  group('state indicators reach 3:1 (WCAG 1.4.11)', () {
    for (final surface in surfaces.entries) {
      test('focus ring on ${surface.key}', () {
        expect(contrast(c.focusRing, surface.value), greaterThanOrEqualTo(3));
      });
      test('selected volt on ${surface.key}', () {
        expect(
          contrast(c.accentPrimary, surface.value),
          greaterThanOrEqualTo(3),
        );
      });
      test('unchecked radio (content/muted) on ${surface.key}', () {
        expect(
          contrast(c.contentMuted, surface.value),
          greaterThanOrEqualTo(3),
        );
      });
    }
  });
}
