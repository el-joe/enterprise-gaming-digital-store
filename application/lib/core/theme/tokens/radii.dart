import 'package:flutter/painting.dart';

/// Corner radii (Figma `radius/*`).
abstract final class Radii {
  static const double xs = 4;
  static const double sm = 6;
  static const double md = 10;
  static const double lg = 14;
  static const double xl = 20;
  static const double key = 28;
  static const double full = 999;
}

/// The key-cut silhouette, the brand signature: small radii on three corners and a large cut on
/// the top-end corner. Radii are directional, so the cut sits top-right in LTR and top-left in RTL.
///
/// Web equivalent: `rounded-ss-sm rounded-se-[28px] rounded-ee-sm rounded-es-sm` (cards) and
/// `rounded-se-[14px]` (buttons).
abstract final class KeyCut {
  /// Cards, panels and chips.
  static const BorderRadiusDirectional card = BorderRadiusDirectional.only(
    topStart: Radius.circular(Radii.sm),
    topEnd: Radius.circular(Radii.key),
    bottomEnd: Radius.circular(Radii.sm),
    bottomStart: Radius.circular(Radii.sm),
  );

  /// Buttons.
  static const BorderRadiusDirectional button = BorderRadiusDirectional.only(
    topStart: Radius.circular(Radii.sm),
    topEnd: Radius.circular(Radii.lg),
    bottomEnd: Radius.circular(Radii.sm),
    bottomStart: Radius.circular(Radii.sm),
  );

  /// Card shape; mirrors with the ambient text direction.
  static RoundedRectangleBorder cardShape({
    BorderSide side = BorderSide.none,
  }) => RoundedRectangleBorder(borderRadius: card, side: side);

  /// Button shape; mirrors with the ambient text direction.
  static RoundedRectangleBorder buttonShape({
    BorderSide side = BorderSide.none,
  }) => RoundedRectangleBorder(borderRadius: button, side: side);
}
