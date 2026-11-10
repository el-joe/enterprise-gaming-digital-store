/// Spacing scale (Figma `Layout` collection `spacing/*`, base 4 px). Names follow the token step:
/// `s2_5` is `spacing/2_5` = 10 px.
abstract final class Spacing {
  static const double s0 = 0;
  static const double s0_5 = 2;
  static const double s1 = 4;
  static const double s1_5 = 6;
  static const double s2 = 8;
  static const double s2_5 = 10;
  static const double s3 = 12;
  static const double s4 = 16;
  static const double s5 = 20;
  static const double s6 = 24;
  static const double s8 = 32;
  static const double s10 = 40;
  static const double s12 = 48;
  static const double s16 = 64;
  static const double s20 = 80;
  static const double s24 = 96;
}

/// Layout tokens (docs/DESIGN_TOKENS.md "Layout"; Figma `breakpoint/*`).
abstract final class LayoutTokens {
  static const double breakpointSm = 375;
  static const double breakpointMd = 768;
  static const double breakpointLg = 1280;
  static const double breakpointXl = 1440;

  /// Page margins per breakpoint band (sm / md / lg).
  static const double marginSm = 16;
  static const double marginMd = 32;
  static const double marginLg = 80;

  /// Grid gutters per breakpoint band (sm / md / lg).
  static const double gutterSm = 12;
  static const double gutterMd = 16;
  static const double gutterLg = 24;

  static const int columnsSm = 4;
  static const int columnsMd = 8;
  static const int columnsLg = 12;

  static const double contentMaxWidth = 1280;

  /// Minimum touch target (MOBILE_APP_ROADMAP.md M-A8 accessibility notes).
  static const double minTouchTarget = 48;

  /// Page margin for a viewport [width].
  static double marginFor(double width) {
    if (width >= breakpointLg) {
      return marginLg;
    }
    if (width >= breakpointMd) {
      return marginMd;
    }
    return marginSm;
  }
}
