// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'link_inbox.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning

@ProviderFor(guestLinkInbox)
final guestLinkInboxProvider = GuestLinkInboxProvider._();

final class GuestLinkInboxProvider
    extends $FunctionalProvider<GuestLinkInbox, GuestLinkInbox, GuestLinkInbox>
    with $Provider<GuestLinkInbox> {
  GuestLinkInboxProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'guestLinkInboxProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$guestLinkInboxHash();

  @$internal
  @override
  $ProviderElement<GuestLinkInbox> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  GuestLinkInbox create(Ref ref) {
    return guestLinkInbox(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(GuestLinkInbox value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<GuestLinkInbox>(value),
    );
  }
}

String _$guestLinkInboxHash() => r'7022973dc0abd85a4823d1e28d626e3f6f3d18af';
