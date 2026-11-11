import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/shared/widgets/logo_slot.dart';
import 'package:application/shared/widgets/network_image_box.dart';
import 'package:application/shared/widgets/product_tile.dart';
import 'package:flutter/material.dart';

/// Platform chip (Figma `Mobile / Platform Chip` `81:358`): a plain-text tile on a field colour
/// with the platform name at the bottom. The logo shows only when the API sends one (the server
/// publishes uploaded logos only, BD8); otherwise an empty Logo Slot outside production, or
/// nothing. At least 96dp tall, one button node for screen readers.
class PlatformTile extends StatelessWidget {
  const PlatformTile({
    required this.name,
    required this.slug,
    required this.onTap,
    this.logoUrl,
    this.logoAlt,
    this.showLogoSlot = false,
    this.width,
    super.key,
  });

  final String name;
  final String slug;
  final VoidCallback onTap;
  final Uri? logoUrl;
  final String? logoAlt;
  final bool showLogoSlot;
  final double? width;

  static const height = 96.0;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    const radius = BorderRadiusDirectional.only(
      topStart: Radius.circular(Radii.sm),
      topEnd: Radius.circular(Radii.lg),
      bottomStart: Radius.circular(Radii.sm),
      bottomEnd: Radius.circular(Radii.sm),
    );
    final empty = showLogoSlot
        ? const LogoSlot()
        : const SizedBox(width: 40, height: 32);
    return Semantics(
      button: true,
      label: name,
      excludeSemantics: true,
      onTap: onTap,
      child: SizedBox(
        width: width,
        height: height,
        child: Material(
          color: fieldColorFor(slug, colors),
          shape: const RoundedRectangleBorder(borderRadius: radius),
          clipBehavior: Clip.antiAlias,
          child: InkWell(
            onTap: onTap,
            child: Padding(
              padding: const EdgeInsetsDirectional.all(Spacing.s2_5),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  SizedBox(
                    width: 40,
                    height: 32,
                    child: logoUrl == null
                        ? empty
                        : NetworkImageBox(
                            url: logoUrl,
                            fit: BoxFit.contain,
                            fallback: empty,
                          ),
                  ),
                  const Spacer(),
                  Text(
                    name,
                    style: context.type.labelMd,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
