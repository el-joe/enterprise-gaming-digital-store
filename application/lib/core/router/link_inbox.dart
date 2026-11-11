import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'link_inbox.g.dart';

/// Signed guest order links (the delivery email, `GuestOrderAccessLink`) waiting for the guest
/// order screen, by order public id. The app-link listener and the "paste your link" page put a
/// link here; the guest order controller takes it once. The signed link is a one-purpose proof
/// (it opens nothing by itself, API_DOCUMENTATION.md 1c) and is never written to storage.
class GuestLinkInbox {
  final _links = <String, Uri>{};

  void put(String orderPublicId, Uri signedLink) =>
      _links[orderPublicId] = signedLink;

  /// The waiting link for [orderPublicId], removed from the inbox.
  Uri? take(String orderPublicId) => _links.remove(orderPublicId);

  /// The waiting link for [orderPublicId], left in the inbox.
  Uri? peek(String orderPublicId) => _links[orderPublicId];
}

@Riverpod(keepAlive: true)
GuestLinkInbox guestLinkInbox(Ref ref) => GuestLinkInbox();
