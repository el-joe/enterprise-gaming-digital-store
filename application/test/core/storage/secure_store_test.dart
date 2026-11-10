import 'package:application/core/storage/secure_store.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/memory_stores.dart';

void main() {
  late Map<String, String> data;
  late SecureStore store;

  setUp(() {
    data = {};
    store = SecureStore(storage: memorySecureStorage(data));
  });

  test('token round trip', () async {
    expect(await store.readTokens(), isNull);
    expect(await store.hasSession(), isFalse);

    await store.writeTokens(
      const AuthTokens(accessToken: 'a1', refreshToken: 'r1'),
    );
    final tokens = await store.readTokens();
    expect(tokens?.accessToken, 'a1');
    expect(tokens?.refreshToken, 'r1');
    expect(await store.readAccessToken(), 'a1');
    expect(await store.hasSession(), isTrue);
    expect(
      data.keys,
      containsAll([SecureStore.accessTokenKey, SecureStore.refreshTokenKey]),
    );

    await store.clearTokens();
    expect(await store.readTokens(), isNull);
    expect(data, isEmpty);
  });

  test('a lone access token is not a session', () async {
    data[SecureStore.accessTokenKey] = 'a1';
    expect(await store.readTokens(), isNull);
  });

  test('cart and guest order tokens are scoped and removable', () async {
    await store.writeCartToken('cart-1');
    await store.writeGuestOrderToken('ord_1', 'g-1');
    await store.writeGuestOrderToken('ord_2', 'g-2');
    expect(await store.readCartToken(), 'cart-1');
    expect(await store.readGuestOrderToken('ord_1'), 'g-1');
    expect(await store.readGuestOrderToken('ord_2'), 'g-2');

    await store.deleteGuestOrderToken('ord_1');
    expect(await store.readGuestOrderToken('ord_1'), isNull);
    await store.deleteCartToken();
    expect(await store.readCartToken(), isNull);

    await store.wipe();
    expect(data, isEmpty);
  });
}
