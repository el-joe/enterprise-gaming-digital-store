import 'package:application/core/theme/theme_extensions.dart';
import 'package:flutter/material.dart';

/// The 56dp round icon on a volt tint that heads auth and notice screens (shield, mail, update).
class IconBadge extends StatelessWidget {
  const IconBadge({required this.icon, this.size = 56, super.key});

  final IconData icon;
  final double size;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    return ExcludeSemantics(
      child: Container(
        width: size,
        height: size,
        decoration: BoxDecoration(
          color: colors.accentPrimaryTint,
          shape: BoxShape.circle,
        ),
        child: Icon(icon, color: colors.accentText, size: size * 0.45),
      ),
    );
  }
}
