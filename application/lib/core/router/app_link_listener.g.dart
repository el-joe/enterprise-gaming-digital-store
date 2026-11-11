// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'app_link_listener.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning

@ProviderFor(linkSource)
final linkSourceProvider = LinkSourceProvider._();

final class LinkSourceProvider
    extends $FunctionalProvider<LinkSource, LinkSource, LinkSource>
    with $Provider<LinkSource> {
  LinkSourceProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'linkSourceProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$linkSourceHash();

  @$internal
  @override
  $ProviderElement<LinkSource> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  LinkSource create(Ref ref) {
    return linkSource(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(LinkSource value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<LinkSource>(value),
    );
  }
}

String _$linkSourceHash() => r'ba078e7094f13e70a7a3acf6b14bd513515328f0';

/// Routes app links (MOBILE_APP_ROADMAP.md 2.1 `deep_links.dart`, ADR-039):
///
/// - `https://<APP_LINK_HOST>/app/payments/return/{order}` and `/cancel/{order}`: hands the order
///   to [PaymentFlow] (which only ever asks the server; the link marks nothing paid) and opens
///   its payment screen.
/// - `/products/{slug}` (also `/en|ar/products/{slug}`): pushes the product page (M-A12).
/// - `/account/orders/{order}` (also `/en|ar/...`, the customer delivery email): opens the order
///   detail (M-A14); signed out, the guard asks to sign in and returns there.
/// - `/api/v1/checkout/guest/orders/{order}/access?expires=&signature=` (the guest delivery
///   email): hands the signed link to the guest order screen ([GuestLinkInbox]) and opens it
///   (M-A14). The screen calls its `/mobile` twin; the link opens nothing by itself.
///
/// Links are parsed by [parseDeepLink] (https and the configured host only). Links that arrive
/// while the launch check, onboarding, maintenance or force update is on screen wait until the
/// app reaches its normal pages. Signed-in-only links the guards send to sign in first. Email
/// verification and password reset links are parsed but not routed: they are not in the intent
/// filters, so they keep opening in the browser.

@ProviderFor(AppLinkListener)
final appLinkListenerProvider = AppLinkListenerProvider._();

/// Routes app links (MOBILE_APP_ROADMAP.md 2.1 `deep_links.dart`, ADR-039):
///
/// - `https://<APP_LINK_HOST>/app/payments/return/{order}` and `/cancel/{order}`: hands the order
///   to [PaymentFlow] (which only ever asks the server; the link marks nothing paid) and opens
///   its payment screen.
/// - `/products/{slug}` (also `/en|ar/products/{slug}`): pushes the product page (M-A12).
/// - `/account/orders/{order}` (also `/en|ar/...`, the customer delivery email): opens the order
///   detail (M-A14); signed out, the guard asks to sign in and returns there.
/// - `/api/v1/checkout/guest/orders/{order}/access?expires=&signature=` (the guest delivery
///   email): hands the signed link to the guest order screen ([GuestLinkInbox]) and opens it
///   (M-A14). The screen calls its `/mobile` twin; the link opens nothing by itself.
///
/// Links are parsed by [parseDeepLink] (https and the configured host only). Links that arrive
/// while the launch check, onboarding, maintenance or force update is on screen wait until the
/// app reaches its normal pages. Signed-in-only links the guards send to sign in first. Email
/// verification and password reset links are parsed but not routed: they are not in the intent
/// filters, so they keep opening in the browser.
final class AppLinkListenerProvider
    extends $NotifierProvider<AppLinkListener, void> {
  /// Routes app links (MOBILE_APP_ROADMAP.md 2.1 `deep_links.dart`, ADR-039):
  ///
  /// - `https://<APP_LINK_HOST>/app/payments/return/{order}` and `/cancel/{order}`: hands the order
  ///   to [PaymentFlow] (which only ever asks the server; the link marks nothing paid) and opens
  ///   its payment screen.
  /// - `/products/{slug}` (also `/en|ar/products/{slug}`): pushes the product page (M-A12).
  /// - `/account/orders/{order}` (also `/en|ar/...`, the customer delivery email): opens the order
  ///   detail (M-A14); signed out, the guard asks to sign in and returns there.
  /// - `/api/v1/checkout/guest/orders/{order}/access?expires=&signature=` (the guest delivery
  ///   email): hands the signed link to the guest order screen ([GuestLinkInbox]) and opens it
  ///   (M-A14). The screen calls its `/mobile` twin; the link opens nothing by itself.
  ///
  /// Links are parsed by [parseDeepLink] (https and the configured host only). Links that arrive
  /// while the launch check, onboarding, maintenance or force update is on screen wait until the
  /// app reaches its normal pages. Signed-in-only links the guards send to sign in first. Email
  /// verification and password reset links are parsed but not routed: they are not in the intent
  /// filters, so they keep opening in the browser.
  AppLinkListenerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'appLinkListenerProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$appLinkListenerHash();

  @$internal
  @override
  AppLinkListener create() => AppLinkListener();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(void value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<void>(value),
    );
  }
}

String _$appLinkListenerHash() => r'366285e727a503fc9a44357f3bf04bac82820677';

/// Routes app links (MOBILE_APP_ROADMAP.md 2.1 `deep_links.dart`, ADR-039):
///
/// - `https://<APP_LINK_HOST>/app/payments/return/{order}` and `/cancel/{order}`: hands the order
///   to [PaymentFlow] (which only ever asks the server; the link marks nothing paid) and opens
///   its payment screen.
/// - `/products/{slug}` (also `/en|ar/products/{slug}`): pushes the product page (M-A12).
/// - `/account/orders/{order}` (also `/en|ar/...`, the customer delivery email): opens the order
///   detail (M-A14); signed out, the guard asks to sign in and returns there.
/// - `/api/v1/checkout/guest/orders/{order}/access?expires=&signature=` (the guest delivery
///   email): hands the signed link to the guest order screen ([GuestLinkInbox]) and opens it
///   (M-A14). The screen calls its `/mobile` twin; the link opens nothing by itself.
///
/// Links are parsed by [parseDeepLink] (https and the configured host only). Links that arrive
/// while the launch check, onboarding, maintenance or force update is on screen wait until the
/// app reaches its normal pages. Signed-in-only links the guards send to sign in first. Email
/// verification and password reset links are parsed but not routed: they are not in the intent
/// filters, so they keep opening in the browser.

abstract class _$AppLinkListener extends $Notifier<void> {
  void build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<void, void>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<void, void>,
              void,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
