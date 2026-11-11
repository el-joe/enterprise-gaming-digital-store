import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:flutter/material.dart';

/// The card at the top of a form (13.2 the product and its order, 15.1 the order): an icon box,
/// a title and a supporting line.
class SubjectCard extends StatelessWidget {
  const SubjectCard({
    required this.title,
    this.subtitle,
    this.icon = Icons.sports_esports_outlined,
    super.key,
  });

  final String title;
  final String? subtitle;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final type = context.type;
    return MergeSemantics(
      child: DecoratedBox(
        decoration: BoxDecoration(
          color: colors.surfaceRaised,
          borderRadius: BorderRadius.circular(Radii.md),
        ),
        child: Padding(
          padding: const EdgeInsetsDirectional.all(Spacing.s3),
          child: Row(
            children: [
              ExcludeSemantics(
                child: Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: colors.surfaceOverlay,
                    borderRadius: BorderRadius.circular(Radii.sm),
                  ),
                  child: Icon(icon, size: 22, color: colors.contentPrimary),
                ),
              ),
              const SizedBox(width: Spacing.s3),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title, style: type.headingSm),
                    if (subtitle != null)
                      Text(
                        subtitle!,
                        style: type.bodySm.copyWith(
                          color: colors.contentSecondary,
                        ),
                      ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
