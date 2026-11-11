import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:flutter/material.dart';

/// Key-cut of the stepper (`radius/lg` on the top end), as the amount tiles.
const _stepperRadius = BorderRadiusDirectional.only(
  topStart: Radius.circular(Radii.sm),
  topEnd: Radius.circular(Radii.lg),
  bottomStart: Radius.circular(Radii.sm),
  bottomEnd: Radius.circular(Radii.sm),
);

/// Quantity stepper (05.1 footer, cart lines in 06.1): 48dp minus and plus buttons around the count; the buttons
/// disable at 1 and at the per-order limit.
class QuantityStepper extends StatelessWidget {
  const QuantityStepper({
    required this.quantity,
    required this.max,
    required this.onIncrement,
    required this.onDecrement,
    this.enabled = true,
    super.key,
  });

  final int quantity;
  final int max;
  final VoidCallback onIncrement;
  final VoidCallback onDecrement;
  final bool enabled;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    Widget button(IconData icon, String tooltip, VoidCallback? onPressed) =>
        SizedBox.square(
          dimension: 48,
          child: IconButton(
            tooltip: tooltip,
            icon: Icon(icon, size: 18),
            color: colors.contentPrimary,
            disabledColor: colors.contentDisabled,
            onPressed: onPressed,
          ),
        );
    // Minus, count, plus read left to right in both languages (as drawn in the AR frames).
    return Directionality(
      textDirection: TextDirection.ltr,
      child: DecoratedBox(
        decoration: BoxDecoration(
          color: colors.surfaceRaised,
          border: Border.all(color: colors.lineDefault),
          borderRadius: _stepperRadius,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            button(
              Icons.remove,
              l10n.decreaseQuantity,
              enabled && quantity > 1 ? onDecrement : null,
            ),
            Semantics(
              liveRegion: true,
              label: l10n.quantitySemantics(quantity),
              excludeSemantics: true,
              child: SizedBox(
                width: 32,
                child: Text(
                  '$quantity',
                  textAlign: TextAlign.center,
                  textDirection: TextDirection.ltr,
                  style: context.type.labelMd,
                ),
              ),
            ),
            button(
              Icons.add,
              l10n.increaseQuantity,
              enabled && quantity < max ? onIncrement : null,
            ),
          ],
        ),
      ),
    );
  }
}
