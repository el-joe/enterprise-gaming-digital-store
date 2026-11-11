import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/link_navigation.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/home/domain/entities/home.dart';
import 'package:application/features/home/presentation/controllers/home_controller.dart';
import 'package:application/features/home/presentation/widgets/hero_carousel.dart';
import 'package:application/features/home/presentation/widgets/home_sections.dart';
import 'package:application/features/notifications/notification_badge.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/store_header.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Opens the currency switcher sheet (provided by the router so features stay independent).
typedef OpenCurrencySwitcher = Future<void> Function(BuildContext context);

/// Home tab (03.1 loaded, 03.2 skeleton, 03.3 error with request id, 03.4 offline): the API's
/// hero banners, promo banners and sections in server order. Prices are the server's, in the
/// display currency; switching currency reloads the page.
class HomeScreen extends ConsumerWidget {
  const HomeScreen({required this.openCurrencySwitcher, super.key});

  /// Finds the home page in tests and goldens.
  static const pageKey = ValueKey('home-page');

  final OpenCurrencySwitcher openCurrencySwitcher;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(homeControllerProvider);
    final controller = ref.read(homeControllerProvider.notifier);
    final currency =
        ref.watch(displayCurrencyProvider) ??
        state.valueOrNull?.displayCurrency;
    return Scaffold(
      key: pageKey,
      body: SafeArea(
        bottom: false,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            StoreHeader(
              onSearch: () => context.go(Routes.search.path),
              currencyCode: currency,
              onCurrency: () => unawaited(openCurrencySwitcher(context)),
              unreadNotifications: ref.watch(unreadNotificationsProvider),
              onNotifications: () =>
                  unawaited(context.push(Routes.accountNotifications.path)),
            ),
            Expanded(
              child: switch (state) {
                Loading() => const _HomeSkeleton(),
                Failed(:final failure) => FailureView(
                  failure: failure,
                  errorTitle: context.l10n.homeErrorTitle,
                  onRetry: () => unawaited(controller.retry()),
                ),
                Loaded(:final value) => RefreshIndicator(
                  onRefresh: () async {
                    unawaited(
                      ref.read(unreadNotificationsProvider.notifier).refresh(),
                    );
                    await controller.refresh();
                  },
                  child: _HomeContent(page: value),
                ),
              },
            ),
          ],
        ),
      ),
    );
  }
}

class _HomeContent extends ConsumerWidget {
  const _HomeContent({required this.page});

  final HomePage page;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final sections = visibleSections(page);
    return ListView(
      padding: const EdgeInsetsDirectional.only(bottom: Spacing.s8),
      children: [
        Padding(
          padding: const EdgeInsetsDirectional.fromSTEB(
            Spacing.s4,
            Spacing.s2,
            Spacing.s4,
            Spacing.s4,
          ),
          child: SearchFieldButton(onTap: () => context.go(Routes.search.path)),
        ),
        if (page.heroBanners.isNotEmpty) ...[
          HeroCarousel(
            banners: page.heroBanners,
            onOpen: (banner) => followLink(context, ref, banner.link!),
          ),
          const SizedBox(height: Spacing.s6),
        ],
        for (final banner in page.promoBanners)
          Padding(
            padding: const EdgeInsetsDirectional.fromSTEB(
              Spacing.s4,
              0,
              Spacing.s4,
              Spacing.s4,
            ),
            child: PromoBannerCard(banner: banner),
          ),
        for (final section in sections) ...[
          HomeSectionView(section: section),
          const SizedBox(height: Spacing.s4),
        ],
      ],
    );
  }
}

class _HomeSkeleton extends StatelessWidget {
  const _HomeSkeleton();

  @override
  Widget build(BuildContext context) => const SkeletonRegion(
    child: SingleChildScrollView(
      physics: NeverScrollableScrollPhysics(),
      padding: EdgeInsetsDirectional.all(Spacing.s4),
      child: Column(
        children: [
          SkeletonBlock(height: HeroCarousel.height, radius: Radii.lg),
          SizedBox(height: Spacing.s6),
          ProductGridSkeleton(),
        ],
      ),
    ),
  );
}
