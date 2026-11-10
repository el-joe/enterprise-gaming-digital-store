import 'package:application/core/l10n/l10n.dart';
import 'package:flutter/widgets.dart';

/// Brand assets copied from `backend/resources/assets/images/brand/`. Never recoloured,
/// stretched or mirrored (docs/DESIGN_TOKENS.md logo rules).
abstract final class BrandAssets {
  static const logo = 'assets/brand/logo-master.png';
  static const mark = 'assets/brand/mark-master.png';

  /// Master sizes, for the aspect ratio.
  static const logoSize = Size(1885, 424);
  static const markSize = Size(538, 538);
}

/// The boxcards.shop logo at a token height (full logo S 28 / M 36 / L 64; mark S 24 / M 32 / L 64).
class BrandLogo extends StatelessWidget {
  const BrandLogo.full({super.key, this.height = 28}) : _mark = false;

  const BrandLogo.mark({super.key, this.height = 32}) : _mark = true;

  final double height;
  final bool _mark;

  @override
  Widget build(BuildContext context) {
    final size = _mark ? BrandAssets.markSize : BrandAssets.logoSize;
    return Semantics(
      label: context.l10n.brandLogoLabel,
      image: true,
      child: ExcludeSemantics(
        // Logos are never mirrored in RTL.
        child: Directionality(
          textDirection: TextDirection.ltr,
          child: Image.asset(
            _mark ? BrandAssets.mark : BrandAssets.logo,
            height: height,
            width: height * size.width / size.height,
            fit: BoxFit.contain,
          ),
        ),
      ),
    );
  }
}
