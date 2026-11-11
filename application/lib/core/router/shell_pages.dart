import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/cart/cart_providers.dart';
import 'package:application/shared/widgets/app_bottom_nav.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/brand_logo.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// The tab shell (M-A11; the Cart tab shows the cart's unit count since M-A12 and the cart since M-A13): Home, Browse, Cart, Orders, Account with the Mobile Bottom Nav
/// (`75:568`). Each tab keeps its own navigation stack; tapping the current tab returns to its
/// first page.
class MainShell extends ConsumerWidget {
  const MainShell({required this.shell, super.key});

  final StatefulNavigationShell shell;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final cartUnits = ref.watch(cartCountProvider);
    return Scaffold(
      body: shell,
      bottomNavigationBar: AppBottomNav(
        currentIndex: shell.currentIndex,
        onSelected: (index) =>
            shell.goBranch(index, initialLocation: index == shell.currentIndex),
        items: [
          AppNavItem(
            label: l10n.navHome,
            icon: Icons.home_outlined,
            selectedIcon: Icons.home,
          ),
          AppNavItem(
            label: l10n.navBrowse,
            icon: Icons.grid_view_outlined,
            selectedIcon: Icons.grid_view,
          ),
          AppNavItem(
            label: l10n.navCart,
            icon: Icons.shopping_bag_outlined,
            selectedIcon: Icons.shopping_bag,
            badgeCount: cartUnits,
            badgeSemantics: l10n.cartBadgeSemantics(cartUnits),
          ),
          AppNavItem(
            label: l10n.navOrders,
            icon: Icons.receipt_long_outlined,
            selectedIcon: Icons.receipt_long,
          ),
          AppNavItem(
            label: l10n.navAccount,
            icon: Icons.person_outline,
            selectedIcon: Icons.person,
          ),
        ],
      ),
    );
  }
}

/// A screen whose milestone is still to come (refund request, notifications and support, M-A17): its title and a "coming in the next update" note. No fake data.
class ComingSoonPage extends StatelessWidget {
  const ComingSoonPage({required this.title, this.root = true, super.key});

  final String title;

  /// A tab root (no back button) or a pushed screen.
  final bool root;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    return Scaffold(
      appBar: MobileAppBar(
        title: title,
        leading: root ? AppBarLeading.none : AppBarLeading.back,
        onLeading: root
            ? null
            : () => context.canPop()
                  ? context.pop()
                  : context.go(Routes.home.path),
      ),
      body: Center(
        child: SingleChildScrollView(
          child: EmptyState(
            icon: Icons.construction_outlined,
            title: l10n.comingSoonTitle,
            body: l10n.comingSoonBody,
          ),
        ),
      ),
    );
  }
}

enum NoticeKind { maintenance, updateRequired, notFound }

/// Full-screen notice for unknown routes (maintenance and force update have their own pages since
/// M-A10, `features/launch`).
class NoticePage extends StatelessWidget {
  const NoticePage({required this.kind, super.key});

  final NoticeKind kind;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final (title, body) = switch (kind) {
      NoticeKind.maintenance => (l10n.maintenanceTitle, l10n.maintenanceBody),
      NoticeKind.updateRequired => (
        l10n.updateRequiredTitle,
        l10n.updateRequiredBody,
      ),
      NoticeKind.notFound => (l10n.notFoundTitle, l10n.notFoundBody),
    };
    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsetsDirectional.all(Spacing.s6),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const BrandLogo.mark(),
              const SizedBox(height: Spacing.s6),
              Text(title, style: type.headingLg),
              const SizedBox(height: Spacing.s2),
              Text(
                body,
                style: type.bodyMd.copyWith(color: colors.contentSecondary),
              ),
              if (kind == NoticeKind.notFound) ...[
                const SizedBox(height: Spacing.s6),
                AppButton(
                  label: l10n.backToHome,
                  onPressed: () => context.go(Routes.home.path),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
