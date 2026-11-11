import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/app_theme.dart';
import 'package:application/features/home/domain/entities/home.dart';
import 'package:application/features/home/presentation/widgets/hero_carousel.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:application/shared/widgets/network_image_box.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/catalog_fakes.dart';

/// Owner decision 8: auto-advance every 5 s, pause on touch, swipe or focus (no resume while the
/// interaction lasts), stop off-screen, never advance with reduced motion.
void main() {
  final banners = [
    for (var n = 1; n <= 3; n++)
      HomeBanner(
        id: n,
        placement: 'hero',
        title: 'Slide $n',
        link: LinkTarget(
          type: 'product',
          value: 'p$n',
          url: Uri.parse('https://boxcards.test/products/p$n'),
        ),
      ),
  ];

  const interval = Duration(seconds: 5);

  Future<List<HomeBanner>> pump(
    WidgetTester tester, {
    bool reduceMotion = false,
    bool tickers = true,
    Locale locale = const Locale('en'),
    Widget Function(Widget carousel)? wrap,
  }) async {
    tester.view
      ..physicalSize = const Size(390, 844)
      ..devicePixelRatio = 1;
    addTearDown(tester.view.reset);
    final opened = <HomeBanner>[];
    final carousel = HeroCarousel(banners: banners, onOpen: opened.add);
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          catalogImagesProvider.overrideWithValue(RecordingImages().call),
        ],
        child: MaterialApp(
          theme: AppTheme.dark(arabic: locale.languageCode == 'ar'),
          locale: locale,
          supportedLocales: AppLocales.supported,
          localizationsDelegates: AppLocalizations.localizationsDelegates,
          home: MediaQuery(
            data: MediaQueryData(
              size: const Size(390, 844),
              disableAnimations: reduceMotion,
            ),
            child: TickerMode(
              enabled: tickers,
              child: Scaffold(
                body: wrap == null
                    ? ListView(children: [carousel])
                    : wrap(carousel),
              ),
            ),
          ),
        ),
      ),
    );
    await tester.pump();
    return opened;
  }

  int page(WidgetTester tester) =>
      tester.widget<PageView>(find.byType(PageView)).controller!.page!.round();

  Future<void> wait(WidgetTester tester, Duration duration) async {
    await tester.pump(duration);
    // Let the page animation finish.
    await tester.pump(const Duration(seconds: 1));
  }

  testWidgets('auto-advances every 5 seconds and wraps around', (tester) async {
    await pump(tester);
    expect(page(tester), 0);
    await wait(tester, const Duration(seconds: 4));
    expect(page(tester), 0, reason: 'not before 5 s');
    await wait(tester, const Duration(seconds: 1));
    expect(page(tester), 1);
    await wait(tester, interval);
    expect(page(tester), 2);
    await wait(tester, interval);
    expect(page(tester), 0);
  });

  testWidgets('advances in RTL too (pages follow the reading direction)', (
    tester,
  ) async {
    await pump(tester, locale: const Locale('ar'));
    await wait(tester, interval);
    expect(page(tester), 1);
    expect(find.text('Slide 2'), findsOneWidget);
  });

  testWidgets(
    'pauses while touched and restarts a full interval after release',
    (tester) async {
      await pump(tester);
      final gesture = await tester.startGesture(
        tester.getCenter(find.byType(PageView)),
      );
      await wait(tester, const Duration(seconds: 12));
      expect(page(tester), 0, reason: 'no advance while the finger is down');

      await gesture.up();
      await wait(tester, const Duration(seconds: 3));
      expect(page(tester), 0);
      await wait(tester, const Duration(seconds: 2));
      expect(page(tester), 1);
    },
  );

  testWidgets('a swipe changes the page and does not resume during the drag', (
    tester,
  ) async {
    await pump(tester);
    final gesture = await tester.startGesture(
      tester.getCenter(find.byType(PageView)),
    );
    for (var step = 0; step < 10; step++) {
      await gesture.moveBy(const Offset(-30, 0));
      await tester.pump(const Duration(milliseconds: 16));
    }
    await wait(tester, const Duration(seconds: 8));
    await gesture.up();
    await tester.pumpAndSettle();
    expect(page(tester), 1, reason: 'the swipe moved one page');
    await wait(tester, const Duration(seconds: 4));
    expect(page(tester), 1);
    await wait(tester, const Duration(seconds: 1));
    expect(page(tester), 2);
  });

  testWidgets('pauses while a slide has keyboard or accessibility focus', (
    tester,
  ) async {
    await pump(tester);
    await tester.sendKeyEvent(LogicalKeyboardKey.tab);
    await tester.pump();
    expect(FocusManager.instance.primaryFocus?.context, isNotNull);
    await wait(tester, const Duration(seconds: 15));
    expect(page(tester), 0);

    FocusManager.instance.primaryFocus!.unfocus();
    await tester.pump();
    await wait(tester, interval);
    expect(page(tester), 1);
  });

  testWidgets('never auto-advances with reduce motion on', (tester) async {
    await pump(tester, reduceMotion: true);
    await wait(tester, const Duration(seconds: 30));
    expect(page(tester), 0);
    // The customer can still swipe.
    await tester.drag(find.byType(PageView), const Offset(-300, 0));
    await tester.pumpAndSettle();
    expect(page(tester), 1);
  });

  testWidgets('stops when its tab or route is inactive', (tester) async {
    await pump(tester, tickers: false);
    await wait(tester, const Duration(seconds: 20));
    expect(page(tester), 0);
  });

  testWidgets('stops when scrolled off-screen and resumes when back', (
    tester,
  ) async {
    final controller = ScrollController();
    addTearDown(controller.dispose);
    await pump(
      tester,
      wrap: (carousel) => ListView(
        controller: controller,
        children: [carousel, const SizedBox(height: 2000)],
      ),
    );
    controller.jumpTo(1200);
    await tester.pump();
    await wait(tester, const Duration(seconds: 20));
    controller.jumpTo(0);
    await tester.pump();
    expect(page(tester), 0, reason: 'nothing advanced while off-screen');
    await wait(tester, interval);
    expect(page(tester), 1);
  });

  testWidgets('a single banner never moves; slides open their link', (
    tester,
  ) async {
    final opened = <HomeBanner>[];
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          catalogImagesProvider.overrideWithValue(RecordingImages().call),
        ],
        child: MaterialApp(
          theme: AppTheme.dark(arabic: false),
          localizationsDelegates: AppLocalizations.localizationsDelegates,
          supportedLocales: AppLocales.supported,
          home: Scaffold(
            body: HeroCarousel(banners: [banners.first], onOpen: opened.add),
          ),
        ),
      ),
    );
    await wait(tester, const Duration(seconds: 20));
    expect(page(tester), 0);
    await tester.tap(find.text('Slide 1'));
    expect(opened.single.id, 1);
  });
}
