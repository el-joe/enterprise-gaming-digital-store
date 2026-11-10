import 'dart:ui';

/// Primitive colours (Figma `Primitives` collection, docs/DESIGN_TOKENS.md "Core colours").
///
/// Widgets never use these directly: they read `SemanticColors` through the theme.
abstract final class ColorTokens {
  static const ink0 = Color(0xFFFFFFFF);
  static const ink50 = Color(0xFFF6F7F9);
  static const ink100 = Color(0xFFECEEF2);
  static const ink200 = Color(0xFFD7DBE3);
  static const ink300 = Color(0xFFB3BBC9);
  static const ink400 = Color(0xFF8791A3);
  static const ink500 = Color(0xFF5A6374);
  static const ink600 = Color(0xFF383F4D);
  static const ink700 = Color(0xFF252A35);
  static const ink800 = Color(0xFF191D26);
  static const ink850 = Color(0xFF12151C);
  static const ink900 = Color(0xFF0C0E13);
  static const ink950 = Color(0xFF07080B);

  static const volt300 = Color(0xFFE4FF85);
  static const volt500 = Color(0xFFC8FF2E);
  static const volt600 = Color(0xFFA6DB10);
  static const volt800 = Color(0xFF4A6404);
  static const volt900 = Color(0xFF222E05);

  static const ember400 = Color(0xFFFF7A52);
  static const ember500 = Color(0xFFFF5A2B);
  static const ember600 = Color(0xFFE2421A);
  static const ember900 = Color(0xFF3B1408);

  static const ultra300 = Color(0xFFA7A9FF);
  static const ultra400 = Color(0xFF7C7EFF);
  static const ultra500 = Color(0xFF5A50FF);
  static const ultra600 = Color(0xFF4335DB);
  static const ultra900 = Color(0xFF1B1754);

  static const aqua400 = Color(0xFF37E3C8);
  static const aqua500 = Color(0xFF12C4AB);
  static const aqua900 = Color(0xFF07372F);

  static const flux400 = Color(0xFFFF5CC8);
  static const flux500 = Color(0xFFEB2FA8);
  static const flux900 = Color(0xFF3D0C2C);

  static const sun400 = Color(0xFFFFC53D);
  static const sun500 = Color(0xFFF5A800);
  static const sun700 = Color(0xFF8A5E00);
  static const sun900 = Color(0xFF3B2A04);

  static const red100 = Color(0xFFFFE3E7);
  static const red400 = Color(0xFFFF6B7A);
  static const red500 = Color(0xFFF23D52);
  static const red700 = Color(0xFFB81F33);
  static const red900 = Color(0xFF3E0F17);

  static const blue100 = Color(0xFFDDEEFF);
  static const blue400 = Color(0xFF5CB2FF);
  static const blue500 = Color(0xFF2E90F5);
  static const blue700 = Color(0xFF135FAE);
  static const blue900 = Color(0xFF0B2A47);

  static const green100 = Color(0xFFD9F7E8);
  static const green400 = Color(0xFF4EE39B);
  static const green500 = Color(0xFF1EC77C);
  static const green700 = Color(0xFF0E7F4D);
  static const green900 = Color(0xFF0A3522);
}
