import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/motion.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/product/domain/entities/product.dart';
import 'package:application/features/product/domain/variant_options.dart';
import 'package:application/features/product/presentation/controllers/product_controllers.dart';
import 'package:application/features/product/presentation/widgets/product_widgets.dart';
import 'package:application/features/product/presentation/widgets/review_widgets.dart';
import 'package:application/features/reviews/review_eligibility.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/html_text.dart';
import 'package:application/shared/widgets/logo_slot.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Product detail (05.1 to 05.6, 19.2 at 360 wide): gallery, title, notices, region and amount,
/// the delivery-field form, description, instructions, terms and reviews, with the price,
/// quantity and "Add to cart" in a sticky footer. All state lives in [ProductController]; this
/// page renders it and forwards taps. Text field controllers and the scroll position are the
/// only local state.
class ProductPage extends ConsumerStatefulWidget {
  const ProductPage({required this.slug, super.key});

  final String slug;

  @override
  ConsumerState<ProductPage> createState() => _ProductPageState();
}

class _ProductPageState extends ConsumerState<ProductPage> {
  final _scroll = ScrollController();
  final _textControllers = <String, TextEditingController>{};
  final _fieldKeys = <String, GlobalKey>{};
  bool _showTitle = false;

  /// The app bar shows the product name once the title block has scrolled away.
  static const _titleOffset = 220.0;

  @override
  void initState() {
    super.initState();
    _scroll.addListener(() {
      final show = _scroll.hasClients && _scroll.offset > _titleOffset;
      if (show != _showTitle) {
        setState(() => _showTitle = show);
      }
    });
  }

  @override
  void dispose() {
    _scroll.dispose();
    for (final controller in _textControllers.values) {
      controller.dispose();
    }
    super.dispose();
  }

  TextEditingController _controllerFor(String key) =>
      _textControllers.putIfAbsent(
        key,
        () => TextEditingController(
          text: ref.read(productControllerProvider(widget.slug)).values[key],
        ),
      );

  GlobalKey _keyFor(String key) => _fieldKeys.putIfAbsent(key, GlobalKey.new);

  Future<void> _add() async {
    final controller = ref.read(
      productControllerProvider(widget.slug).notifier,
    );
    final added = await controller.addToCart();
    if (added || !mounted) {
      return;
    }
    // Bring the first field with a problem into view.
    final state = ref.read(productControllerProvider(widget.slug));
    for (final field
        in state.offer?.detail.deliveryFields ?? const <DeliveryField>[]) {
      if (state.fieldIssues.containsKey(field.key)) {
        final target = _fieldKeys[field.key]?.currentContext;
        if (target != null && target.mounted) {
          await Scrollable.ensureVisible(
            target,
            alignment: 0.3,
            duration: MotionTokens.resolve(context, MotionTokens.base),
          );
        }
        return;
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final provider = productControllerProvider(widget.slug);
    final state = ref.watch(provider);
    final controller = ref.read(provider.notifier);
    ref.listen(provider.select((s) => s.addedCount), (previous, next) {
      if (next > (previous ?? 0)) {
        showAppSnackbar(
          context,
          message: l10n.addedToCart,
          actionLabel: l10n.viewCart,
          onAction: () => context.go(Routes.cart.path),
        );
      }
    });
    // A reload (retry, currency or language change) starts a fresh form.
    ref.listen(provider.select((s) => s.content is Loading), (_, loading) {
      if (loading) {
        for (final text in _textControllers.values) {
          text.clear();
        }
      }
    });

    final name = state.offer?.detail.card.name;
    return Scaffold(
      backgroundColor: context.colors.surfaceCanvas,
      appBar: MobileAppBar(
        title: _showTitle ? name : null,
        onLeading: () =>
            context.canPop() ? context.pop() : context.go(Routes.home.path),
      ),
      body: switch (state.content) {
        Loading() => const SingleChildScrollView(child: ProductSkeleton()),
        Failed(:final failure) when failure is NotFoundFailure => Center(
          child: SingleChildScrollView(
            child: EmptyState(
              icon: Icons.remove_shopping_cart_outlined,
              title: l10n.productNotFoundTitle,
              body: l10n.productNotFoundBody,
              actionLabel: l10n.backToStore,
              onAction: () => context.go(Routes.home.path),
            ),
          ),
        ),
        Failed(:final failure) => FailureView(
          failure: failure,
          errorTitle: l10n.productLoadErrorTitle,
          onRetry: () => unawaited(controller.retry()),
        ),
        Loaded() => _content(context, state, controller),
      },
      bottomNavigationBar: state.offer == null
          ? null
          : ProductFooter(
              state: state,
              onIncrement: controller.increment,
              onDecrement: controller.decrement,
              onAdd: () => unawaited(_add()),
            ),
    );
  }

  Widget _content(
    BuildContext context,
    ProductPageState state,
    ProductController controller,
  ) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final offer = state.offer!;
    final product = offer.detail;
    final selected = state.selected;
    final failure = state.addFailure;
    final description = product.description;
    final instructions = product.instructions;
    final terms = product.terms;

    final children = <Widget>[
      ProductGallery(
        product: product,
        showLogoSlot: ref.watch(showLogoSlotsProvider),
      ),
      ProductTitleBlock(product: product),
      if (state.priceMayVary)
        StatusBanner(
          tone: BannerTone.warning,
          title: l10n.priceMayVaryTitle,
          body: l10n.priceMayVaryBody,
        ),
      if (state.unavailable)
        StatusBanner(
          tone: BannerTone.danger,
          title: l10n.productUnavailableTitle,
          body: l10n.productUnavailableBody,
        ),
      if (failure != null)
        StatusBanner(
          tone: BannerTone.danger,
          title: l10n.addFailedTitle,
          body: state.addFailureText ?? failureMessage(l10n, failure),
        ),
      if (state.showRegions) ...[
        FieldLabel(l10n.productRegion),
        RegionSelector(
          regions: state.regions,
          selected: state.region,
          onSelected: controller.selectRegion,
        ),
      ],
      if (state.amounts.isNotEmpty) ...[
        FieldLabel(l10n.productAmount),
        AmountSelector(
          amounts: state.amounts,
          selected: state.variantId,
          onSelected: controller.selectVariant,
        ),
        if (selected?.availability == Availability.limited)
          Text(
            l10n.variantLimited,
            style: type.bodyCaption.copyWith(color: colors.statusWarning),
          ),
      ],
      if (product.deliveryFields.isNotEmpty)
        DeliveryFieldsForm(
          fields: product.deliveryFields,
          values: state.values,
          issues: state.fieldIssues,
          controllerFor: _controllerFor,
          keyFor: _keyFor,
          onChanged: controller.setValue,
        ),
      if (description != null && description.trim().isNotEmpty)
        TextSection(title: l10n.aboutProduct, child: HtmlText(description)),
      if (instructions != null && instructions.trim().isNotEmpty)
        TextSection(
          title: l10n.howToRedeem,
          child: Text(
            instructions,
            style: type.bodyMd.copyWith(color: colors.contentSecondary),
          ),
        ),
      if (terms != null && terms.trim().isNotEmpty)
        TextSection(
          title: l10n.termsTitle,
          child: Text(
            terms,
            style: type.bodyMd.copyWith(color: colors.contentSecondary),
          ),
        ),
      ProductReviewsSection(slug: widget.slug),
    ];

    return ListView.separated(
      controller: _scroll,
      padding: const EdgeInsetsDirectional.fromSTEB(
        Spacing.s4,
        Spacing.s2,
        Spacing.s4,
        Spacing.s6,
      ),
      itemCount: children.length,
      separatorBuilder: (_, _) => const SizedBox(height: Spacing.s4),
      itemBuilder: (context, index) => children[index],
    );
  }
}

/// Reviews on the product page (05.2): the rating summary, the first three reviews and "See all
/// reviews" when there are more.
class ProductReviewsSection extends ConsumerWidget {
  const ProductReviewsSection({required this.slug, super.key});

  final String slug;

  static const preview = 3;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final provider = productReviewsControllerProvider(slug);
    final state = ref.watch(provider);
    final Widget body;
    if (state.loading) {
      body = const ReviewsSkeleton();
    } else if (state.failure != null) {
      body = Row(
        children: [
          Expanded(
            child: Text(
              l10n.reviewsLoadFailed,
              style: type.bodyMd.copyWith(color: colors.contentSecondary),
            ),
          ),
          TextButton(
            style: TextButton.styleFrom(
              minimumSize: const Size(48, 48),
              foregroundColor: colors.accentText,
            ),
            onPressed: () => unawaited(ref.read(provider.notifier).retry()),
            child: Text(l10n.actionRetry),
          ),
        ],
      );
    } else if (state.items.isEmpty) {
      body = Text(
        l10n.reviewsEmptyTitle,
        style: type.bodyMd.copyWith(color: colors.contentSecondary),
      );
    } else {
      body = Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          RatingSummary(
            average: state.ratingAvg ?? '0',
            count: state.ratingCount,
          ),
          for (final review in state.items.take(preview)) ...[
            const SizedBox(height: Spacing.s3),
            ReviewCard(review: review),
          ],
          if (state.total > preview) ...[
            const SizedBox(height: Spacing.s3),
            AppButton(
              label: l10n.seeAllReviews,
              variant: AppButtonVariant.secondary,
              expand: true,
              onPressed: () =>
                  unawaited(context.push(Routes.productReviewsLocation(slug))),
            ),
          ],
        ],
      );
    }
    // Only for a verified purchase the API lists as ready to review (M-A17).
    final eligible = ref.watch(eligibleReviewForProductProvider(slug));
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Semantics(
          header: true,
          child: Text(l10n.reviewsTitle, style: type.headingSm),
        ),
        const SizedBox(height: Spacing.s2),
        body,
        if (eligible != null) ...[
          const SizedBox(height: Spacing.s3),
          AppButton(
            key: const ValueKey('product-write-review'),
            label: l10n.reviewWriteAction,
            icon: Icons.rate_review_outlined,
            variant: AppButtonVariant.secondary,
            expand: true,
            onPressed: () => unawaited(
              context.push(Routes.writeReviewLocation(eligible.orderItemId)),
            ),
          ),
        ],
      ],
    );
  }
}
