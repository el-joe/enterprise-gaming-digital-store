import 'dart:async';

import 'package:application/core/money/pricing_context.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/features/cart/cart_providers.dart';
import 'package:application/features/product/domain/delivery_field_rules.dart';
import 'package:application/features/product/domain/entities/product.dart';
import 'package:application/features/product/domain/variant_options.dart';
import 'package:application/features/product/product_providers.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:decimal/decimal.dart';
import 'package:flutter/foundation.dart' show immutable, mapEquals;
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'product_controllers.g.dart';

/// The product page's data: the detail (2c) and its variant offers (2a), both in the display
/// currency of `meta.display_currency`.
@immutable
final class ProductOffer {
  const ProductOffer({required this.detail, required this.variants});

  final ProductDetail detail;
  final List<VariantOffer> variants;
}

/// A problem with one delivery field: a local check (localized by the page) or the server's
/// message from `errors.delivery_values.{key}` (already localized by `Accept-Language`).
@immutable
sealed class FieldIssue {
  const FieldIssue();
}

final class LocalFieldIssue extends FieldIssue {
  const LocalFieldIssue(this.error);

  final DeliveryFieldError error;

  @override
  bool operator ==(Object other) =>
      other is LocalFieldIssue && other.error == error;

  @override
  int get hashCode => error.hashCode;
}

final class ServerFieldIssue extends FieldIssue {
  const ServerFieldIssue(this.message);

  final String message;

  @override
  bool operator ==(Object other) =>
      other is ServerFieldIssue && other.message == message;

  @override
  int get hashCode => message.hashCode;
}

/// State of the product page (05.1 to 05.6).
@immutable
final class ProductPageState {
  const ProductPageState({
    this.content = const Loadable.loading(),
    this.region,
    this.variantId,
    this.quantity = 1,
    this.values = const {},
    this.fieldIssues = const {},
    this.quantityError,
    this.adding = false,
    this.addFailure,
    this.addFailureText,
    this.addedCount = 0,
    this.submitted = false,
  });

  final Loadable<ProductOffer> content;

  /// Selected region code (null for variants without a region).
  final String? region;

  /// Selected variant `public_id`, null when nothing in the region can be bought.
  final String? variantId;
  final int quantity;

  /// Delivery values as typed, by field key.
  final Map<String, String> values;
  final Map<String, FieldIssue> fieldIssues;

  /// The server's `errors.quantity` (for example the per-order limit counting the cart).
  final String? quantityError;

  /// Add to cart is running.
  final bool adding;

  /// Why the last add to cart failed (not a field error): shown in a banner.
  final Failure? addFailure;

  /// The server's own text for [addFailure] when it sent one for the item (`errors.variant_id`,
  /// the domain message); otherwise the page maps [addFailure].
  final String? addFailureText;

  /// Successful adds so far; the page shows the "Added to cart" snackbar on each increment.
  final int addedCount;

  /// Add to cart was tried once: local checks now run as the customer types.
  final bool submitted;

  ProductOffer? get offer => content.valueOrNull;

  List<RegionOption> get regions {
    final offer = this.offer;
    return offer == null
        ? const []
        : regionOptions(offer.variants, offer.detail.regions);
  }

  /// The region selector is shown only when there is a choice.
  bool get showRegions => regions.length > 1;

  /// Variants of the selected region, in the server's order.
  List<VariantOffer> get amounts {
    final offer = this.offer;
    return offer == null ? const [] : variantsIn(offer.variants, region);
  }

  VariantOffer? get selected {
    for (final variant in offer?.variants ?? const <VariantOffer>[]) {
      if (variant.publicId == variantId) {
        return variant;
      }
    }
    return null;
  }

  int get maxQuantity {
    final offer = this.offer;
    return offer == null ? 1 : maxQuantityFor(offer.detail);
  }

  /// Nothing of the product can be bought (no variant, or every variant blocked).
  bool get unavailable {
    final offer = this.offer;
    return offer != null && offer.variants.every((v) => blockOf(v) != null);
  }

  /// `price_may_vary` of the product or the selected variant (stale exchange rate): the final
  /// price is confirmed at checkout.
  bool get priceMayVary =>
      (offer?.detail.card.priceMayVary ?? false) ||
      (selected?.priceMayVary ?? false);

  bool get canAdd => !adding && selected != null && blockOf(selected!) == null;

  ProductPageState copyWith({
    Loadable<ProductOffer>? content,
    String? Function()? region,
    String? Function()? variantId,
    int? quantity,
    Map<String, String>? values,
    Map<String, FieldIssue>? fieldIssues,
    String? Function()? quantityError,
    bool? adding,
    Failure? Function()? addFailure,
    String? Function()? addFailureText,
    int? addedCount,
    bool? submitted,
  }) => ProductPageState(
    content: content ?? this.content,
    region: region == null ? this.region : region(),
    variantId: variantId == null ? this.variantId : variantId(),
    quantity: quantity ?? this.quantity,
    values: values ?? this.values,
    fieldIssues: fieldIssues ?? this.fieldIssues,
    quantityError: quantityError == null ? this.quantityError : quantityError(),
    adding: adding ?? this.adding,
    addFailure: addFailure == null ? this.addFailure : addFailure(),
    addFailureText: addFailureText == null
        ? this.addFailureText
        : addFailureText(),
    addedCount: addedCount ?? this.addedCount,
    submitted: submitted ?? this.submitted,
  );

  @override
  bool operator ==(Object other) =>
      other is ProductPageState &&
      identical(other.content, content) &&
      other.region == region &&
      other.variantId == variantId &&
      other.quantity == quantity &&
      mapEquals(other.values, values) &&
      mapEquals(other.fieldIssues, fieldIssues) &&
      other.quantityError == quantityError &&
      other.adding == adding &&
      identical(other.addFailure, addFailure) &&
      other.addFailureText == addFailureText &&
      other.addedCount == addedCount &&
      other.submitted == submitted;

  @override
  int get hashCode => Object.hash(
    content,
    region,
    variantId,
    quantity,
    values.length,
    fieldIssues.length,
    quantityError,
    adding,
    addFailure,
    addFailureText,
    addedCount,
    submitted,
  );
}

/// The product page for a slug: loads the detail and the variant offers together, holds the
/// region and amount selection, the quantity and the delivery-field form, and adds to the cart
/// (`POST /storefront/cart/items`; a guest's `X-Cart-Token` is captured and sent back by the
/// cart token interceptor). A display currency or language change reloads it.
@riverpod
class ProductController extends _$ProductController {
  int _generation = 0;

  @override
  ProductPageState build(String slug) {
    ref.watch(pricingContextProvider);
    unawaited(_load());
    return const ProductPageState();
  }

  Future<void> retry() async {
    state = const ProductPageState();
    await _load();
  }

  void selectRegion(String? code) {
    final offer = state.offer;
    if (offer == null || code == state.region) {
      return;
    }
    state = state.copyWith(
      region: () => code,
      variantId: () =>
          variantAfterRegionChange(offer.variants, code, state.selected),
      addFailure: () => null,
      addFailureText: () => null,
    );
  }

  /// Selects an amount of the current region; amounts that cannot be bought stay unselected.
  void selectVariant(String publicId) {
    final variant = state.amounts
        .where((v) => v.publicId == publicId)
        .firstOrNull;
    if (variant == null || blockOf(variant) != null) {
      return;
    }
    state = state.copyWith(
      variantId: () => publicId,
      addFailure: () => null,
      addFailureText: () => null,
    );
  }

  void increment() => setQuantity(state.quantity + 1);

  void decrement() => setQuantity(state.quantity - 1);

  /// Sets the quantity within 1 and the product's limit.
  void setQuantity(int quantity) {
    final next = quantity.clamp(1, state.maxQuantity);
    if (next == state.quantity) {
      return;
    }
    state = state.copyWith(quantity: next, quantityError: () => null);
  }

  /// Stores a typed delivery value. `number` fields get Western digits. A server error for the
  /// field clears; after the first add attempt the local check runs again.
  void setValue(String key, String raw) {
    final field = _field(key);
    if (field == null) {
      return;
    }
    final value = field.type == DeliveryFieldType.number
        ? westernDigits(raw)
        : raw;
    final issues = Map<String, FieldIssue>.of(state.fieldIssues)..remove(key);
    if (state.submitted) {
      final error = validateDeliveryValue(field, value);
      if (error != null) {
        issues[key] = LocalFieldIssue(error);
      }
    }
    state = state.copyWith(
      values: {...state.values, key: value},
      fieldIssues: issues,
    );
  }

  /// Validates every field as the server does, then adds the selection to the cart. Returns
  /// whether the item was added.
  Future<bool> addToCart() async {
    final offer = state.offer;
    final variant = state.selected;
    if (offer == null || variant == null || !state.canAdd) {
      return false;
    }
    final fields = offer.detail.deliveryFields;
    final local = validateDeliveryValues(fields, state.values);
    if (local.isNotEmpty) {
      state = state.copyWith(
        submitted: true,
        fieldIssues: {
          for (final entry in local.entries)
            entry.key: LocalFieldIssue(entry.value),
        },
        addFailure: () => null,
        addFailureText: () => null,
      );
      return false;
    }
    final generation = _generation;
    state = state.copyWith(
      submitted: true,
      adding: true,
      fieldIssues: const {},
      quantityError: () => null,
      addFailure: () => null,
      addFailureText: () => null,
    );
    final result = await ref
        .read(cartRepositoryProvider)
        .addItem(
          variantPublicId: variant.publicId,
          quantity: state.quantity,
          deliveryValues: deliveryValuesToSend(fields, state.values),
        );
    if (!ref.mounted || generation != _generation) {
      return false;
    }
    switch (result) {
      case Ok(:final value):
        ref.read(cartCountProvider.notifier).updateFrom(value);
        state = state.copyWith(adding: false, addedCount: state.addedCount + 1);
        return true;
      case Err(:final failure):
        state = _withAddFailure(state.copyWith(adding: false), failure);
        return false;
    }
  }

  /// Binds a refused add: `errors.delivery_values.{key}` beside its field, `errors.quantity`
  /// beside the quantity, anything else (for example `errors.variant_id` "out of stock") in the
  /// banner with the server's text.
  ProductPageState _withAddFailure(ProductPageState current, Failure failure) {
    const prefix = 'delivery_values.';
    final errors = switch (failure) {
      ValidationFailure(:final fieldErrors) => fieldErrors,
      DomainFailure(:final errors) => errors,
      _ => const <String, List<String>>{},
    };
    final issues = <String, FieldIssue>{};
    String? quantityError;
    String? general;
    final known = {
      for (final field
          in current.offer?.detail.deliveryFields ?? const <DeliveryField>[])
        field.key,
    };
    for (final entry in errors.entries) {
      final message = entry.value.isEmpty ? null : entry.value.first;
      if (message == null) {
        continue;
      }
      final key = entry.key;
      if (key.startsWith(prefix) &&
          known.contains(key.substring(prefix.length))) {
        issues[key.substring(prefix.length)] = ServerFieldIssue(message);
      } else if (key == 'quantity') {
        quantityError = message;
      } else {
        general ??= message;
      }
    }
    final fieldOnly =
        general == null && (issues.isNotEmpty || quantityError != null);
    return current.copyWith(
      fieldIssues: issues,
      quantityError: () => quantityError,
      addFailure: () => fieldOnly ? null : failure,
      addFailureText: () => fieldOnly
          ? null
          : general ??
                switch (failure) {
                  DomainFailure(:final message) => message,
                  _ => null,
                },
    );
  }

  DeliveryField? _field(String key) {
    for (final field
        in state.offer?.detail.deliveryFields ?? const <DeliveryField>[]) {
      if (field.key == key) {
        return field;
      }
    }
    return null;
  }

  Future<void> _load() async {
    final generation = ++_generation;
    final repository = ref.read(productRepositoryProvider);
    final results = await Future.wait([
      repository.detail(slug),
      repository.variants(slug),
    ]);
    if (!ref.mounted || generation != _generation) {
      return;
    }
    final detail = results[0] as Result<ProductDetail>;
    final variants = results[1] as Result<ProductVariants>;
    switch ((detail, variants)) {
      case (Ok(value: final product), Ok(value: final offers)):
        final selection = initialSelection(offers.variants, product.regions);
        state = ProductPageState(
          content: Loadable.loaded(
            ProductOffer(detail: product, variants: offers.variants),
          ),
          region: selection.region,
          variantId: selection.variantId,
        );
      case (Err(:final failure), _) || (_, Err(:final failure)):
        state = ProductPageState(content: Loadable.failed(failure));
    }
  }
}

/// State of a product's reviews (05.2 and the full list): approved reviews newest first with
/// the rating summary from `meta.rating_avg` and `meta.rating_count`.
@immutable
final class ReviewsState {
  const ReviewsState({
    this.items = const [],
    this.page,
    this.ratingAvg,
    this.ratingCount = 0,
    this.loading = true,
    this.failure,
    this.loadingMore = false,
    this.loadMoreFailure,
  });

  final List<ProductReview> items;
  final PageInfo? page;

  /// Decimal string (`4.50`), null until loaded.
  final String? ratingAvg;
  final int ratingCount;
  final bool loading;
  final Failure? failure;
  final bool loadingMore;
  final Failure? loadMoreFailure;

  bool get hasMore => page?.hasNextPage ?? false;

  int get total => page?.total ?? items.length;

  ReviewsState copyWith({
    List<ProductReview>? items,
    PageInfo? page,
    bool? loading,
    bool? loadingMore,
    Failure? Function()? loadMoreFailure,
  }) => ReviewsState(
    items: items ?? this.items,
    page: page ?? this.page,
    ratingAvg: ratingAvg,
    ratingCount: ratingCount,
    loading: loading ?? this.loading,
    failure: failure,
    loadingMore: loadingMore ?? this.loadingMore,
    loadMoreFailure: loadMoreFailure == null
        ? this.loadMoreFailure
        : loadMoreFailure(),
  );
}

/// Reviews of a product, 10 per page, appended by [loadMore] while `meta` has more pages.
/// Writing a review is M-A17.
@riverpod
class ProductReviewsController extends _$ProductReviewsController {
  static const perPage = 10;
  int _generation = 0;

  @override
  ReviewsState build(String slug) {
    unawaited(_loadFirst());
    return const ReviewsState();
  }

  Future<void> retry() {
    state = const ReviewsState();
    return _loadFirst();
  }

  Future<void> loadMore() async {
    final current = state;
    if (current.loading ||
        current.loadingMore ||
        current.failure != null ||
        !current.hasMore) {
      return;
    }
    final generation = _generation;
    state = current.copyWith(loadingMore: true, loadMoreFailure: () => null);
    final result = await ref
        .read(productRepositoryProvider)
        .reviews(
          slug,
          page: (current.page?.currentPage ?? 1) + 1,
          perPage: perPage,
        );
    if (!ref.mounted || generation != _generation) {
      return;
    }
    state = switch (result) {
      Ok(:final value) => ReviewsState(
        items: [...state.items, ...value.reviews],
        page: value.page,
        ratingAvg: value.ratingAvg,
        ratingCount: value.ratingCount,
        loading: false,
      ),
      Err(:final failure) => state.copyWith(
        loadingMore: false,
        loadMoreFailure: () => failure,
      ),
    };
  }

  Future<void> _loadFirst() async {
    final generation = ++_generation;
    final result = await ref
        .read(productRepositoryProvider)
        .reviews(slug, perPage: perPage);
    if (!ref.mounted || generation != _generation) {
      return;
    }
    state = switch (result) {
      Ok(:final value) => ReviewsState(
        items: value.reviews,
        page: value.page,
        ratingAvg: value.ratingAvg,
        ratingCount: value.ratingCount,
        loading: false,
      ),
      Err(:final failure) => ReviewsState(loading: false, failure: failure),
    };
  }
}

/// A server rating average (`4.50`) for display without trailing zeros (`4.5`), computed on the
/// decimal string, never through floating point. Unparseable input is shown as sent.
String ratingLabel(String average) {
  final value = Decimal.tryParse(average);
  return value == null ? average : value.toString();
}

/// Stars for an average: whole stars, plus a half star from .5 up (0 to 5 in all).
({int full, bool half}) ratingStars(String average) {
  final value = Decimal.tryParse(average) ?? Decimal.zero;
  final clamped = value < Decimal.zero
      ? Decimal.zero
      : value > Decimal.fromInt(5)
      ? Decimal.fromInt(5)
      : value;
  final full = clamped.floor().toBigInt().toInt();
  final half = clamped - Decimal.fromInt(full) >= Decimal.parse('0.5');
  return (full: full, half: half);
}
