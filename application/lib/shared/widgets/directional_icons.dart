import 'package:flutter/widgets.dart';

/// Material icons that point along the reading direction but are not flagged to mirror in the
/// Flutter icon set. Same glyphs with `matchTextDirection`, so `Icon` mirrors them in RTL by
/// itself; never swap icons by hand for RTL (M-A19). Icons that already mirror themselves
/// (`Icons.chevron_right`, `Icons.arrow_back`, `Icons.open_in_new`, `Icons.send_outlined`,
/// `Icons.star_half`) are used as they are.
abstract final class DirectionalIcons {
  /// `Icons.logout` (the arrow leaves through the end side).
  static const IconData logout = IconData(
    0xe3b3,
    fontFamily: 'MaterialIcons',
    matchTextDirection: true,
  );
}
