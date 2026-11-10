import 'package:flutter/painting.dart';

/// Shadow and glow effects (Figma effect styles `shadow/*`, `glow/*`).
abstract final class ElevationTokens {
  /// `0 8px 24px rgb(0 0 0 / .35)`.
  static const card = [
    BoxShadow(color: Color(0x59000000), offset: Offset(0, 8), blurRadius: 24),
  ];

  /// `0 16px 40px rgb(0 0 0 / .45)`.
  static const raised = [
    BoxShadow(color: Color(0x73000000), offset: Offset(0, 16), blurRadius: 40),
  ];

  /// `0 32px 80px rgb(0 0 0 / .6)`.
  static const overlay = [
    BoxShadow(color: Color(0x99000000), offset: Offset(0, 32), blurRadius: 80),
  ];

  /// `0 0 28px rgb(200 255 46 / .28)` (volt-500).
  static const glowVolt = [BoxShadow(color: Color(0x47C8FF2E), blurRadius: 28)];

  /// `0 0 28px rgb(255 90 43 / .3)` (ember-500).
  static const glowEmber = [
    BoxShadow(color: Color(0x4DFF5A2B), blurRadius: 28),
  ];
}
