import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/utils/qr_code.dart';
import 'package:flutter/material.dart';

/// A QR code (18.2) drawn natively from [QrCode]: dark modules on the light inverse surface with
/// the four-module quiet zone scanners need. Always left to right (a QR code never mirrors).
class QrCodeView extends StatelessWidget {
  const QrCodeView({
    required this.code,
    required this.semanticsLabel,
    this.size = 160,
    super.key,
  });

  final QrCode code;
  final String semanticsLabel;
  final double size;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    return Semantics(
      image: true,
      label: semanticsLabel,
      child: Container(
        width: size,
        height: size,
        decoration: BoxDecoration(
          color: colors.surfaceInverse,
          borderRadius: BorderRadius.circular(Radii.md),
        ),
        child: CustomPaint(painter: _QrPainter(code, colors.contentInverse)),
      ),
    );
  }
}

class _QrPainter extends CustomPainter {
  _QrPainter(this.code, this.dark);

  static const _quietZone = 4;

  final QrCode code;
  final Color dark;

  @override
  void paint(Canvas canvas, Size size) {
    final modules = code.size + _quietZone * 2;
    final cell = size.shortestSide / modules;
    final paint = Paint()
      ..color = dark
      ..isAntiAlias = false;
    final path = Path();
    for (var y = 0; y < code.size; y++) {
      for (var x = 0; x < code.size; x++) {
        if (code.isDark(x, y)) {
          path.addRect(
            Rect.fromLTWH(
              (x + _quietZone) * cell,
              (y + _quietZone) * cell,
              cell,
              cell,
            ),
          );
        }
      }
    }
    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(_QrPainter old) => old.code != code || old.dark != dark;
}
