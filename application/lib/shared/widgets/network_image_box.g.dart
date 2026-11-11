// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'network_image_box.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning

@ProviderFor(catalogImages)
final catalogImagesProvider = CatalogImagesProvider._();

final class CatalogImagesProvider
    extends
        $FunctionalProvider<
          CatalogImageProviderFactory,
          CatalogImageProviderFactory,
          CatalogImageProviderFactory
        >
    with $Provider<CatalogImageProviderFactory> {
  CatalogImagesProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'catalogImagesProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$catalogImagesHash();

  @$internal
  @override
  $ProviderElement<CatalogImageProviderFactory> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  CatalogImageProviderFactory create(Ref ref) {
    return catalogImages(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(CatalogImageProviderFactory value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<CatalogImageProviderFactory>(value),
    );
  }
}

String _$catalogImagesHash() => r'f0cfc643fcea7309442334d30a2331d270a12026';
