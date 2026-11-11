import 'package:application/features/checkout/presentation/pages/checkout_page.dart';
import 'package:application/features/checkout/presentation/widgets/checkout_widgets.dart';
import 'package:application/features/code_reveal/presentation/widgets/reveal_card.dart';
import 'package:application/features/product/presentation/widgets/product_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter/semantics.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/product_fakes.dart';
import '../helpers/pump_app.dart';
import '../helpers/reveal_fakes.dart';
import '../helpers/screens.dart';

/// M-A19: screen-reader order on the complex screens follows the task, in LTR and RTL: what you
/// are buying or revealing first, the choices next, the action last.
void main() {
  // Tall enough that each page builds every part at once.
  const tall = Size(390, 2400);

  ScreenCase screen(String name) =>
      mainScreens.firstWhere((s) => s.name == name);

  /// Index in the simulated screen-reader traversal of the node that reads [finder].
  int at(WidgetTester tester, List<SemanticsNode> order, Finder finder) {
    final id = tester.getSemantics(finder.first).id;
    final index = order.indexWhere((node) => node.id == id);
    expect(index, isNonNegative, reason: '$finder is not in the traversal');
    return index;
  }

  void expectInOrder(List<int> indexes) {
    for (var i = 1; i < indexes.length; i++) {
      expect(
        indexes[i],
        greaterThan(indexes[i - 1]),
        reason: 'position $i comes before position ${i - 1}: $indexes',
      );
    }
  }

  for (final lang in ['en', 'ar']) {
    final l10n = l10nFor(lang);

    testWidgets(
      'checkout: country, payment options, terms, summary, pay ($lang)',
      (tester) async {
        final handle = tester.ensureSemantics();
        await openScreen(tester, screen('checkout'), lang, size: tall);
        final order = tester.semantics
            .simulatedAccessibilityTraversal()
            .toList();
        expectInOrder([
          at(tester, order, find.byType(CountryField)),
          at(tester, order, find.text(l10n.checkoutPayment)),
          at(tester, order, find.byType(PaymentOptionTile)),
          at(tester, order, find.text(l10n.checkoutAcceptTerms)),
          at(tester, order, find.byKey(const ValueKey('charged-line'))),
          at(
            tester,
            order,
            find
                .descendant(
                  of: find.byType(CheckoutPage),
                  matching: find.byType(FilledButton),
                )
                .last,
          ),
        ]);
        handle.dispose();
      },
    );

    testWidgets('product: title, options, field, reviews, price, add ($lang)', (
      tester,
    ) async {
      final handle = tester.ensureSemantics();
      await openScreen(tester, screen('product'), lang, size: tall);
      final order = tester.semantics.simulatedAccessibilityTraversal().toList();
      expectInOrder([
        at(tester, order, find.text(fixtureProduct(lang).card.name)),
        at(tester, order, find.text(l10n.productRegion)),
        at(tester, order, find.byType(AmountTile)),
        at(tester, order, find.byType(TextField)),
        at(tester, order, find.text(l10n.reviewsTitle)),
        at(tester, order, find.text(l10n.priceLabel)),
        at(tester, order, find.text(l10n.addToCart)),
      ]);
      handle.dispose();
    });

    testWidgets('code reveal: code, reveal, then copy and hide ($lang)', (
      tester,
    ) async {
      final handle = tester.ensureSemantics();
      await openScreen(tester, screen('code reveal'), lang, size: tall);
      var order = tester.semantics.simulatedAccessibilityTraversal().toList();
      expectInOrder([
        at(
          tester,
          order,
          find.bySemanticsLabel(
            RegExp(RegExp.escape(l10n.revealMaskedSemanticsNoHint)),
          ),
        ),
        at(tester, order, find.text(l10n.revealButton)),
        at(tester, order, find.text(l10n.revealHowTo)),
      ]);

      await tester.tap(find.text(l10n.revealButton));
      await tester.pumpAndSettle();
      expect(find.text(canaryCode), findsOneWidget);
      order = tester.semantics.simulatedAccessibilityTraversal().toList();
      expectInOrder([
        at(tester, order, find.bySemanticsLabel(l10n.revealShownSemantics)),
        at(
          tester,
          order,
          find.descendant(
            of: find.byType(RevealCard),
            matching: find.text(l10n.revealCopy),
          ),
        ),
        at(tester, order, find.text(l10n.revealHide)),
        at(tester, order, find.text(l10n.revealHowTo)),
      ]);
      // Mask the code again, as the customer would: this also ends the auto-hide timer.
      await tester.tap(find.text(l10n.revealHide));
      await tester.pumpAndSettle();
      expect(find.text(canaryCode), findsNothing);
      handle.dispose();
    });
  }
}
