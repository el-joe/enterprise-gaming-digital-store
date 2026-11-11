import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:flutter/material.dart';

/// Tones of [StatusBanner] (Figma `Mobile / Status Banner` `75:836`).
enum BannerTone { info, success, warning, danger, offline }

/// Inline status banner: icon, title and supporting text on the tone's tint with a 1px border.
/// Announced as a polite live region so screen readers read errors and notices once.
class StatusBanner extends StatelessWidget {
  const StatusBanner({
    required this.tone,
    required this.title,
    this.body,
    super.key,
  });

  final BannerTone tone;
  final String title;
  final String? body;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final type = context.type;
    final (Color tint, Color border, IconData icon) = switch (tone) {
      BannerTone.info => (
        colors.statusInfoTint,
        colors.statusInfo,
        Icons.info_outline,
      ),
      BannerTone.success => (
        colors.statusSuccessTint,
        colors.statusSuccess,
        Icons.check,
      ),
      BannerTone.warning => (
        colors.statusWarningTint,
        colors.statusWarning,
        Icons.warning_amber_rounded,
      ),
      BannerTone.danger => (
        colors.statusDangerTint,
        colors.statusDanger,
        Icons.warning_amber_rounded,
      ),
      BannerTone.offline => (
        colors.surfaceRaised,
        colors.lineStrong,
        Icons.language,
      ),
    };
    return Semantics(
      liveRegion: true,
      container: true,
      child: DecoratedBox(
        decoration: BoxDecoration(
          color: tint,
          border: Border.all(color: border),
          borderRadius: BorderRadius.circular(Radii.md),
        ),
        child: Padding(
          padding: const EdgeInsetsDirectional.symmetric(
            horizontal: Spacing.s4,
            vertical: Spacing.s3,
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(
                icon,
                size: 20,
                color: tone == BannerTone.offline
                    ? colors.contentSecondary
                    : border,
              ),
              const SizedBox(width: Spacing.s3),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title, style: type.labelMd),
                    if (body != null) ...[
                      const SizedBox(height: Spacing.s0_5),
                      Text(
                        body!,
                        style: type.bodySm.copyWith(
                          color: colors.contentSecondary,
                        ),
                      ),
                    ],
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
