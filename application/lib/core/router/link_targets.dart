import 'package:application/core/router/routes.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:flutter/foundation.dart';

/// Where a storefront link (banner, home item) leads in the app.
@immutable
sealed class LinkDestination {
  const LinkDestination();
}

/// An in-app location for go_router.
final class InAppDestination extends LinkDestination {
  const InAppDestination(this.location);

  final String location;

  @override
  bool operator ==(Object other) =>
      other is InAppDestination && other.location == location;

  @override
  int get hashCode => location.hashCode;
}

/// A web page opened in the system browser (`https` only).
final class ExternalDestination extends LinkDestination {
  const ExternalDestination(this.url);

  final Uri url;

  @override
  bool operator ==(Object other) =>
      other is ExternalDestination && other.url == url;

  @override
  int get hashCode => url.hashCode;
}

/// Maps a `LinkTargetResolver` target (API_DOCUMENTATION.md 2c: `product`, `category`,
/// `platform`, `search`, `url`; inbox `order`, `ticket`) to an app destination. An empty value,
/// an unknown type or a non-https URL leads nowhere (null), so a bad banner never crashes or
/// opens an unsafe page.
LinkDestination? destinationFor(LinkTarget link) {
  final value = link.value.trim();
  if (value.isEmpty) {
    return null;
  }
  return switch (link.type) {
    'product' => InAppDestination(Routes.productLocation(value)),
    'category' => InAppDestination(Routes.listingLocation(category: value)),
    'platform' => InAppDestination(Routes.listingLocation(platform: value)),
    'search' => InAppDestination(Routes.searchLocation(value)),
    // Order and ticket screens arrive with M-A14 and M-A17; until then the tab.
    'order' => InAppDestination(Routes.orders.path),
    'url' => _external(value),
    _ => null,
  };
}

LinkDestination? _external(String value) {
  final url = Uri.tryParse(value);
  if (url == null || url.scheme != 'https' || url.host.isEmpty) {
    return null;
  }
  return ExternalDestination(url);
}

/// The destination of a search suggestion by its morph `type`: products (and variants, whose
/// slug is the product's) open the product, platforms and categories open the listing. Other
/// types are searched as text.
String suggestionLocation({
  required String type,
  required String slug,
  required String title,
}) => switch (type) {
  'product' || 'product_variant' => Routes.productLocation(slug),
  'gaming_platform' || 'platform' => Routes.listingLocation(platform: slug),
  'category' => Routes.listingLocation(category: slug),
  _ => Routes.searchLocation(title),
};
