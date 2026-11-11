import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'network_image_box.g.dart';

/// Builds the image provider for an API media URL. The app uses `cached_network_image` (disk
/// and memory cache, catalog media only); tests override it so no plugin or network is needed.
typedef CatalogImageProviderFactory = ImageProvider Function(Uri url);

@Riverpod(keepAlive: true)
CatalogImageProviderFactory catalogImages(Ref ref) =>
    (url) => CachedNetworkImageProvider(url.toString());

/// An image from an API URL (`image.url` of the storefront resources) and nothing else: no
/// bundled or generated art. While it loads, and when it fails or the URL is not http(s), the
/// [fallback] (a token-coloured plain tile) shows instead. Decoded at the laid-out width so large
/// originals do not sit in memory.
class NetworkImageBox extends ConsumerWidget {
  const NetworkImageBox({
    required this.url,
    required this.fallback,
    this.fit = BoxFit.cover,
    this.semanticLabel,
    super.key,
  });

  final Uri? url;
  final Widget fallback;
  final BoxFit fit;

  /// The image's `alt`; null hides the image from screen readers (the tile labels itself).
  final String? semanticLabel;

  /// Decode widths are rounded up to a multiple of this (physical pixels), so tiles whose laid-out
  /// widths differ by a few pixels (2 columns, rotation, text scale) share one decoded image in the
  /// image cache instead of decoding the same original again (M-A19).
  static const decodeWidthStep = 64;

  /// Upper bound of a decode: no catalog slot needs more than a full-width phone image at 3x.
  static const maxDecodeWidth = 1536;

  /// The decode width for a slot [physicalWidth] pixels wide.
  static int decodeWidthFor(double physicalWidth) {
    final steps = (physicalWidth / decodeWidthStep).ceil().clamp(1, 1 << 20);
    return (steps * decodeWidthStep).clamp(decodeWidthStep, maxDecodeWidth);
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final source = url;
    if (source == null ||
        !(source.isScheme('https') || source.isScheme('http'))) {
      return fallback;
    }
    final factory = ref.watch(catalogImagesProvider);
    final ratio = MediaQuery.devicePixelRatioOf(context);
    return LayoutBuilder(
      builder: (context, constraints) {
        final width = constraints.hasBoundedWidth
            ? decodeWidthFor(constraints.maxWidth * ratio)
            : null;
        final provider = ResizeImage.resizeIfNeeded(
          width,
          null,
          factory(source),
        );
        return Image(
          image: provider,
          fit: fit,
          width: constraints.hasBoundedWidth ? constraints.maxWidth : null,
          height: constraints.hasBoundedHeight ? constraints.maxHeight : null,
          semanticLabel: semanticLabel,
          excludeFromSemantics: semanticLabel == null,
          gaplessPlayback: true,
          frameBuilder: (context, child, frame, synchronous) =>
              frame == null && !synchronous ? fallback : child,
          errorBuilder: (context, error, stackTrace) => fallback,
        );
      },
    );
  }
}
