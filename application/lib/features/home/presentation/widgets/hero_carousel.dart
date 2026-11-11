import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/motion.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/carousel_autoplay.dart';
import 'package:application/features/home/domain/entities/home.dart';
import 'package:application/shared/widgets/network_image_box.dart';
import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';

/// The home hero carousel (03.1): API banners only (the mobile image, else the desktop one; a
/// plain token tile while loading or without an image). Auto-advance follows owner decision 8
/// through [CarouselAutoplay]; this widget only reports touch, focus, visibility and reduced
/// motion to it and moves the page when told.
class HeroCarousel extends StatefulWidget {
  const HeroCarousel({
    required this.banners,
    required this.onOpen,
    this.interval = CarouselAutoplay.defaultInterval,
    super.key,
  });

  final List<HomeBanner> banners;
  final ValueChanged<HomeBanner> onOpen;
  final Duration interval;

  static const height = 200.0;

  @override
  State<HeroCarousel> createState() => _HeroCarouselState();
}

class _HeroCarouselState extends State<HeroCarousel> {
  late final PageController _pages = PageController();
  late final CarouselAutoplay _autoplay = CarouselAutoplay(
    onAdvance: _advance,
    interval: widget.interval,
  );
  late final AppLifecycleListener _lifecycle;
  ScrollPosition? _scroll;
  int _index = 0;

  @override
  void initState() {
    super.initState();
    _lifecycle = AppLifecycleListener(onStateChange: (_) => _report());
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final scroll = Scrollable.maybeOf(context)?.position;
    if (!identical(scroll, _scroll)) {
      _scroll?.removeListener(_report);
      _scroll = scroll?..addListener(_report);
    }
    _report();
    WidgetsBinding.instance.addPostFrameCallback((_) => _report());
  }

  @override
  void didUpdateWidget(HeroCarousel oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (_index >= widget.banners.length) {
      _index = 0;
    }
    _report();
  }

  @override
  void dispose() {
    _scroll?.removeListener(_report);
    _lifecycle.dispose();
    _autoplay.dispose();
    _pages.dispose();
    super.dispose();
  }

  /// On screen: the tab and route are active (TickerMode), the app is in the foreground and the
  /// carousel intersects its scroll viewport.
  bool get _visible {
    if (!mounted || !TickerMode.valuesOf(context).enabled) {
      return false;
    }
    final lifecycle = WidgetsBinding.instance.lifecycleState;
    if (lifecycle != null && lifecycle != AppLifecycleState.resumed) {
      return false;
    }
    final box = context.findRenderObject();
    final scroll = _scroll;
    if (box is! RenderBox || !box.hasSize || !box.attached) {
      return false;
    }
    if (scroll == null) {
      return true;
    }
    final viewport = RenderAbstractViewport.maybeOf(box);
    if (viewport == null) {
      return true;
    }
    final reveal = viewport.getOffsetToReveal(box, 0).offset;
    final top = reveal - scroll.pixels;
    return top < scroll.viewportDimension && top + box.size.height > 0;
  }

  void _report() {
    if (!mounted) {
      return;
    }
    _autoplay.update(
      slides: widget.banners.length,
      visible: _visible,
      reduceMotion: MediaQuery.disableAnimationsOf(context),
    );
  }

  void _advance() {
    if (!_pages.hasClients || widget.banners.length < 2) {
      return;
    }
    final next = (_index + 1) % widget.banners.length;
    _pages.animateToPage(
      next,
      duration: MotionTokens.slow,
      curve: MotionTokens.easing,
    );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final banners = widget.banners;
    // Re-check after layout changes (for example when the list above grows).
    WidgetsBinding.instance.addPostFrameCallback((_) => _report());
    return Semantics(
      container: true,
      label: l10n.heroCarouselLabel,
      child: Focus(
        canRequestFocus: false,
        skipTraversal: true,
        onFocusChange: (focused) => _autoplay.focusChanged(focused: focused),
        child: Listener(
          onPointerDown: (_) => _autoplay.touchStarted(),
          onPointerUp: (_) => _autoplay.touchEnded(),
          onPointerCancel: (_) => _autoplay.touchEnded(),
          child: Column(
            children: [
              SizedBox(
                height: HeroCarousel.height,
                child: PageView.builder(
                  controller: _pages,
                  itemCount: banners.length,
                  onPageChanged: (index) {
                    setState(() => _index = index);
                    _autoplay.pageChanged();
                  },
                  itemBuilder: (context, index) => Padding(
                    padding: const EdgeInsetsDirectional.symmetric(
                      horizontal: Spacing.s4,
                    ),
                    child: _HeroSlide(
                      banner: banners[index],
                      label: l10n.heroSlideLabel(
                        index + 1,
                        banners.length,
                        banners[index].title ?? '',
                      ),
                      onOpen: banners[index].link == null
                          ? null
                          : () => widget.onOpen(banners[index]),
                    ),
                  ),
                ),
              ),
              if (banners.length > 1) ...[
                const SizedBox(height: Spacing.s2),
                ExcludeSemantics(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      for (var i = 0; i < banners.length; i++)
                        AnimatedContainer(
                          duration: MotionTokens.resolve(
                            context,
                            MotionTokens.base,
                          ),
                          margin: const EdgeInsetsDirectional.symmetric(
                            horizontal: 3,
                          ),
                          width: i == _index ? 18 : 6,
                          height: 6,
                          decoration: BoxDecoration(
                            color: i == _index
                                ? colors.accentPrimary
                                : colors.lineStrong,
                            borderRadius: BorderRadius.circular(Radii.full),
                          ),
                        ),
                    ],
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _HeroSlide extends StatelessWidget {
  const _HeroSlide({
    required this.banner,
    required this.label,
    required this.onOpen,
  });

  final HomeBanner banner;
  final String label;
  final VoidCallback? onOpen;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final type = context.type;
    final image = banner.mobileImage ?? banner.desktopImage;
    final cta = banner.ctaLabel;
    return Semantics(
      button: onOpen != null,
      label: label,
      excludeSemantics: true,
      onTap: onOpen,
      child: Material(
        color: colors.surfaceRaised,
        shape: RoundedRectangleBorder(
          borderRadius: const BorderRadiusDirectional.only(
            topStart: Radius.circular(Radii.md),
            topEnd: Radius.circular(Radii.key),
            bottomStart: Radius.circular(Radii.md),
            bottomEnd: Radius.circular(Radii.md),
          ),
          side: BorderSide(color: colors.lineSubtle),
        ),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: onOpen,
          child: Stack(
            fit: StackFit.expand,
            children: [
              NetworkImageBox(
                url: image?.url,
                fallback: ColoredBox(color: colors.surfaceRaised),
              ),
              // Scrim from the reading start so the server's text stays legible on any image.
              DecoratedBox(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: AlignmentDirectional.centerStart,
                    end: AlignmentDirectional.centerEnd,
                    colors: [
                      colors.surfaceCanvas.withValues(alpha: 0.85),
                      colors.surfaceCanvas.withValues(alpha: 0),
                    ],
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsetsDirectional.all(Spacing.s5),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    if (banner.title != null)
                      Text(
                        banner.title!,
                        style: type.displaySm,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    if (banner.subtitle != null) ...[
                      const SizedBox(height: Spacing.s2),
                      Text(
                        banner.subtitle!,
                        style: type.bodyMd.copyWith(
                          color: colors.contentSecondary,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                    if (cta != null && onOpen != null) ...[
                      const SizedBox(height: Spacing.s4),
                      Container(
                        padding: const EdgeInsetsDirectional.symmetric(
                          horizontal: Spacing.s3,
                          vertical: Spacing.s2,
                        ),
                        decoration: BoxDecoration(
                          color: colors.accentPrimary,
                          borderRadius: BorderRadius.circular(Radii.sm),
                        ),
                        child: Text(
                          cta,
                          style: type.labelMd.copyWith(
                            color: colors.contentOnAccent,
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
