import 'package:application/core/config/app_env.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:flutter/material.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'logo_slot.g.dart';

/// The empty `Logo Slot` (Figma `4:409`, BD8): a dashed 40x32 box reading "LOGO", where an
/// approved brand logo goes once rights are confirmed. Shown outside production only; in
/// production tiles show the plain-text name (component description). Decorative for screen
/// readers.
class LogoSlot extends StatelessWidget {
  const LogoSlot({super.key});

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    return ExcludeSemantics(
      child: Directionality(
        textDirection: TextDirection.ltr,
        child: CustomPaint(
          painter: _DashedBorderPainter(color: colors.contentPrimary),
          child: Container(
            width: 40,
            height: 32,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: colors.contentPrimary.withValues(alpha: 0.06),
              borderRadius: BorderRadius.circular(Radii.xs),
            ),
            // A stand-in for a logo image, which does not grow with the text size: the word
            // shrinks to fit the fixed slot instead of wrapping out of it at large text.
            child: FittedBox(
              fit: BoxFit.scaleDown,
              child: Text(
                'LOGO',
                maxLines: 1,
                style: context.type.bodyCaption.copyWith(
                  color: colors.contentSecondary,
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _DashedBorderPainter extends CustomPainter {
  const _DashedBorderPainter({required this.color});

  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1;
    final path = Path()
      ..addRRect(
        RRect.fromRectAndRadius(
          Offset.zero & size,
          const Radius.circular(Radii.xs),
        ),
      );
    for (final metric in path.computeMetrics()) {
      for (var distance = 0.0; distance < metric.length; distance += 6) {
        canvas.drawPath(metric.extractPath(distance, distance + 3), paint);
      }
    }
  }

  @override
  bool shouldRepaint(_DashedBorderPainter oldDelegate) =>
      oldDelegate.color != color;
}

/// Whether empty Logo Slots are drawn (dev and staging); production shows plain-text tiles only.
@Riverpod(keepAlive: true)
bool showLogoSlots(Ref ref) => ref.watch(appEnvProvider).flavor != Flavor.prod;
