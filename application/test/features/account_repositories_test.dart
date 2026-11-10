import 'package:application/core/network/failure.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:application/core/network/token_refresher.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/account/data/datasources/account_remote_data_source.dart';
import 'package:application/features/account/data/repositories/account_repository_impl.dart';
import 'package:application/features/account/domain/entities/account.dart';
import 'package:application/features/auth/data/datasources/auth_remote_data_source.dart';
import 'package:application/features/auth/data/repositories/auth_repository_impl.dart';
import 'package:application/features/auth/domain/entities/auth_entities.dart';
import 'package:application/features/code_reveal/data/datasources/code_reveal_remote_data_source.dart';
import 'package:application/features/code_reveal/data/repositories/code_reveal_repository_impl.dart';
import 'package:application/features/code_reveal/domain/entities/revealed_code.dart';
import 'package:application/features/notifications/data/datasources/notifications_remote_data_source.dart';
import 'package:application/features/notifications/data/repositories/notifications_repository_impl.dart';
import 'package:application/features/notifications/domain/entities/notifications.dart';
import 'package:application/features/reviews/data/datasources/reviews_remote_data_source.dart';
import 'package:application/features/reviews/data/repositories/reviews_repository_impl.dart';
import 'package:application/features/security/data/datasources/security_remote_data_source.dart';
import 'package:application/features/security/data/repositories/security_repository_impl.dart';
import 'package:application/features/support/data/datasources/support_remote_data_source.dart';
import 'package:application/features/support/data/repositories/support_repository_impl.dart';
import 'package:application/features/wallet/data/datasources/wallet_remote_data_source.dart';
import 'package:application/features/wallet/data/repositories/wallet_repository_impl.dart';
import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/pump_app.dart';
import '../helpers/repo_harness.dart';

const order1 = '01jf0000000000000000000001';
const unit2 = '01JF0000000000000000000002';
const ticket1 = '01jf0000000000000000000001';

Map<String, String> signedIn() => {
  SecureStore.accessTokenKey: 'access-1',
  SecureStore.refreshTokenKey: 'refresh-1',
};

void main() {
  group('AuthRepository', () {
    late Harness h;
    late int signedInCalls;
    late int signedOutCalls;
    late AuthRepositoryImpl repo;

    void build({Map<String, String>? stored}) {
      h = Harness(stored: stored);
      signedInCalls = 0;
      signedOutCalls = 0;
      final refreshDio = Dio(BaseOptions(baseUrl: testEnv.apiV1BaseUrl));
      repo = AuthRepositoryImpl(
        remote: AuthRemoteDataSource(h.dio),
        store: h.store,
        refresher: (token) {
          refreshDio.httpClientAdapter = h.adapter;
          return MobileTokenRefresher(refreshDio).call(token);
        },
        onSignedIn: () => signedInCalls++,
        onSignedOut: () => signedOutCalls++,
      );
    }

    test('register stores the token pair in secure storage', () async {
      build();
      h.serveFixture('auth/register_created');

      final user = okValue(
        await repo.register(
          name: 'Sara Customer',
          email: 'sara@example.com',
          password: 'long-enough-passphrase',
          passwordConfirmation: 'long-enough-passphrase',
          deviceName: 'Pixel 9',
        ),
      );

      expect(user.emailVerified, isFalse);
      expect(h.stored[SecureStore.accessTokenKey], 'fixture-access-token');
      expect(h.stored[SecureStore.refreshTokenKey], 'fixture-refresh-token');
      expect(signedInCalls, 1);
      final request = h.adapter.requests.single;
      expect(request.headers.containsKey('Authorization'), isFalse);
      expect(request.extra[RequestExtras.sensitive], isTrue);
    });

    test('register maps field errors', () async {
      build();
      h.serveFixture('auth/register_validation_failed');
      final failure = failureOf<ValidationFailure>(
        await repo.register(
          name: '',
          email: 'not-an-email',
          password: 'short',
          passwordConfirmation: 'other',
          deviceName: 'Pixel 9',
        ),
      );
      expect(
        failure.fieldErrors.keys,
        containsAll(['email', 'name', 'password']),
      );
      expect(h.stored, isEmpty);
    });

    test('login signs in, or returns the two-factor challenge without storing tokens', () async {
      build();
      h.serveFixture('auth/login_ok');
      final outcome = okValue(
        await repo.login(
          email: 'sara@example.com',
          password: 'password',
          deviceName: 'Pixel 9',
        ),
      );
      expect(outcome, isA<LoginSignedIn>());
      expect(h.stored[SecureStore.accessTokenKey], 'fixture-access-token');

      build();
      h.serveFixture('auth/login_two_factor_required');
      final challenge = okValue(
        await repo.login(
          email: 'totp@example.com',
          password: 'password',
          deviceName: 'Pixel 9',
        ),
      );
      expect(
        (challenge as LoginTwoFactorRequired).challengeToken,
        'fixture-token',
      );
      expect(h.stored, isEmpty);
      expect(signedInCalls, 0);

      h.serveFixture('auth/two_factor_invalid');
      expect(
        failureOf<DomainFailure>(
          await repo.completeTwoFactor(
            challengeToken: 'fixture-token',
            code: '000000',
          ),
        ).code,
        'two_factor_invalid',
      );

      h.serveFixture('auth/two_factor_challenge_ok');
      okValue(
        await repo.completeTwoFactor(
          challengeToken: 'fixture-token',
          code: '123456',
        ),
      );
      expect(h.stored[SecureStore.refreshTokenKey], 'fixture-refresh-token');
      expect(signedInCalls, 1);

      h.serveFixture('auth/two_factor_expired');
      expect(
        failureOf<DomainFailure>(
          await repo.completeTwoFactor(
            challengeToken: 'fixture-token',
            recoveryCode: 'x',
          ),
        ).code,
        'two_factor_expired',
      );
    });

    test(
      'login maps wrong credentials and the rate limit with Retry-After',
      () async {
        build();
        h.serveFixture('auth/login_invalid_credentials');
        failureOf<ValidationFailure>(
          await repo.login(
            email: 'sara@example.com',
            password: 'wrong-password',
            deviceName: 'Pixel 9',
          ),
        );

        h.serveFixture('auth/login_rate_limited');
        final limited = failureOf<RateLimitedFailure>(
          await repo.login(
            email: 'sara@example.com',
            password: 'wrong-password',
            deviceName: 'Pixel 9',
          ),
        );
        expect(limited.retryAfter, const Duration(seconds: 60));
        expect(h.adapter.requests, hasLength(1));
      },
    );

    test('me reads the user; an unknown token is unauthenticated', () async {
      build(stored: signedIn());
      h.serveFixture('auth/me_ok');
      expect(okValue(await repo.me()).publicId, '01jf0000000000000000000001');
      expect(h.adapter.sentHeaders.single['Authorization'], 'Bearer access-1');

      build();
      h.serveFixture('auth/me_unauthenticated');
      failureOf<UnauthenticatedFailure>(await repo.me());
    });

    test('refresh rotates the stored pair', () async {
      build(stored: signedIn());
      h.serveFixture('auth/refresh_ok');

      okValue(await repo.refresh());

      expect(h.stored[SecureStore.accessTokenKey], 'fixture-access-token');
      expect(h.stored[SecureStore.refreshTokenKey], 'fixture-refresh-token');
      expect(
        (h.adapter.requests.single.data as Map)['refresh_token'],
        'refresh-1',
      );
    });

    test('a refused (reused) refresh token signs out', () async {
      build(stored: signedIn());
      h.serveFixture('auth/refresh_token_invalid');

      failureOf<UnauthenticatedFailure>(await repo.refresh());

      expect(h.stored, isEmpty);
      expect(signedOutCalls, 1);
    });

    test('logout and logout-all revoke, then wipe secure storage', () async {
      for (final name in ['logout_ok', 'logout_all_ok']) {
        build(
          stored: {
            ...signedIn(),
            '${SecureStore.guestOrderTokenPrefix}$order1': 'g',
            SecureStore.cartTokenKey: 'c',
          },
        );
        h.serveFixture('auth/$name');
        okValue(
          name == 'logout_ok' ? await repo.logout() : await repo.logoutAll(),
        );
        expect(h.stored, isEmpty);
        expect(signedOutCalls, 1);
      }
    });
  });

  group('CodeRevealRepository', () {
    CodeRevealRepositoryImpl repoOn(Harness h) =>
        CodeRevealRepositoryImpl(CodeRevealRemoteDataSource(h.dio));

    test('confirms the password and reveals the canary', () async {
      final h = Harness(stored: signedIn());
      final repo = repoOn(h);

      h.serveFixture('code_reveal/confirm_password_ok');
      okValue(await repo.confirmPassword('password'));

      h.serveFixture('code_reveal/reveal_ok');
      final code = okValue(
        await repo.reveal(
          const RevealTarget.account(
            orderPublicId: order1,
            unitPublicId: unit2,
          ),
        ),
      );
      expect(code.code, 'FIXTURE-CODE-0000');
      expect(code.pin, 'FIXTURE-PIN-0000');
      expect(code.redemptionUrl?.host, 'redeem.example.com');
      expect(code.toString(), isNot(contains('FIXTURE-CODE')));
      final request = h.adapter.requests.single;
      expect(request.extra[RequestExtras.sensitive], isTrue);
      expect(request.extra[RequestExtras.noRetry], isTrue);
    });

    test('maps every reveal failure and never retries', () async {
      final h = Harness(stored: signedIn());
      final repo = repoOn(h);
      const target = RevealTarget.account(
        orderPublicId: order1,
        unitPublicId: unit2,
      );

      h.serveFixture('code_reveal/reveal_recent_auth_required');
      failureOf<RecentAuthRequiredFailure>(await repo.reveal(target));

      h.serveFixture('code_reveal/reveal_rate_limited');
      expect(
        failureOf<RateLimitedFailure>(await repo.reveal(target)).retryAfter,
        const Duration(hours: 1),
      );
      expect(h.adapter.requests, hasLength(1));

      h.serveFixture('code_reveal/reveal_not_found');
      failureOf<NotFoundFailure>(await repo.reveal(target));

      h.serveFixture('code_reveal/confirm_password_invalid');
      failureOf<ValidationFailure>(
        await repo.confirmPassword('wrong-password'),
      );
    });

    test('a guest reveal needs both proofs on the stored token', () async {
      final h = Harness(
        stored: {'${SecureStore.guestOrderTokenPrefix}$order1': 'guest-token'},
      );
      final repo = repoOn(h);
      const target = RevealTarget.guest(
        orderPublicId: order1,
        unitPublicId: unit2,
      );

      h.serveFixture('code_reveal/guest_reveal_denied');
      expect(
        failureOf<ForbiddenFailure>(await repo.reveal(target)).code,
        'reveal_denied',
      );

      h.serveFixture('code_reveal/guest_reveal_ok');
      expect(okValue(await repo.reveal(target)).code, 'FIXTURE-CODE-0000');
      expect(
        h.adapter.sentHeaders.single['X-Guest-Order-Token'],
        'guest-token',
      );
    });
  });

  group('WalletRepository', () {
    test('reads the wallet and the statement', () async {
      final h = Harness(stored: signedIn());
      final repo = WalletRepositoryImpl(WalletRemoteDataSource(h.dio));

      h.serveFixture('wallet/wallet_ok');
      final wallet = okValue(await repo.wallet());
      expect(wallet.balance.amountText, '25.00');
      expect(wallet.topUpAvailable, isFalse);

      h.serveFixture('wallet/wallet_empty');
      expect(okValue(await repo.wallet()).balance.amountText, '0.00');

      h.serveFixture('wallet/transactions_ok');
      final statement = okValue(await repo.transactions());
      expect(statement.items.single.amount, '25.000000');
      expect(statement.items.single.type, 'credit_refund');
    });
  });

  group('ReviewsRepository', () {
    test('lists eligible items, submits and maps review errors', () async {
      final h = Harness(stored: signedIn());
      final repo = ReviewsRepositoryImpl(ReviewsRemoteDataSource(h.dio));

      h.serveFixture('reviews/eligible_ok');
      expect(okValue(await repo.eligible()).single.productSlug, 'orbit-card');

      h.serveFixture('reviews/create_created');
      final review = okValue(
        await repo.submit(
          orderItemId: 1,
          rating: 5,
          title: 'Instant',
          body: 'Code arrived in a minute.',
        ),
      );
      expect(review.status, 'pending');

      h.serveFixture('reviews/list_ok');
      expect(okValue(await repo.myReviews()).items.single.rating, 5);

      h.serveFixture('reviews/create_exists');
      expect(
        failureOf<ConflictFailure>(await repo.submit(orderItemId: 1, rating: 4))
            .code,
        'review_exists',
      );

      h.serveFixture('reviews/create_not_eligible');
      expect(
        failureOf<DomainFailure>(
          await repo.submit(orderItemId: 999999, rating: 4),
        ).code,
        'review_not_eligible',
      );

      h.serveFixture('reviews/create_email_unverified');
      failureOf<EmailUnverifiedFailure>(
        await repo.submit(orderItemId: 1, rating: 4),
      );
    });
  });

  group('SupportRepository', () {
    test('opens, reads and answers tickets, and maps their errors', () async {
      final h = Harness(stored: signedIn());
      final repo = SupportRepositoryImpl(SupportRemoteDataSource(h.dio));

      h.serveFixture('support/ticket_created');
      final ticket = okValue(
        await repo.openTicket(
          category: 'order',
          subject: 'Code missing',
          body: 'Nothing arrived.',
          orderPublicId: '01jf0000000000000000000002',
        ),
      );
      expect(ticket.messages.single.from, 'customer');
      expect(ticket.order?.orderNumber, 'FIXTURE-0002');

      h.serveFixture('support/ticket_validation_failed');
      failureOf<ValidationFailure>(
        await repo.openTicket(category: 'billing', subject: '', body: ''),
      );

      h.serveFixture('support/message_created');
      expect(
        okValue(await repo.reply(ticket1, body: 'Any news?')).status,
        'pending_staff',
      );

      h.serveFixture('support/tickets_list');
      expect(okValue(await repo.tickets()).items.single.messages, isEmpty);

      h.serveFixture('support/ticket_detail');
      expect(okValue(await repo.ticket(ticket1)).messages, hasLength(2));

      h.serveFixture('support/ticket_not_found');
      failureOf<NotFoundFailure>(
        await repo.ticket('01JF0000000000000000000001'),
      );

      h.serveFixture('support/message_ticket_closed');
      expect(
        failureOf<ConflictFailure>(await repo.reply(ticket1, body: 'Hello?'))
            .code,
        'ticket_closed',
      );
    });

    test('requests a refund and refuses a second open request', () async {
      final h = Harness(stored: signedIn());
      final repo = SupportRepositoryImpl(SupportRemoteDataSource(h.dio));

      h.serveFixture('support/refund_request_created');
      final request = okValue(
        await repo.requestRefund(
          orderPublicId: order1,
          reason: 'The code was already redeemed.',
        ),
      );
      expect(request.status, 'requested');
      expect(request.refund, isNull);

      h.serveFixture('support/refund_request_open');
      expect(
        failureOf<ConflictFailure>(
          await repo.requestRefund(
            orderPublicId: order1,
            reason: 'Asking again, please.',
          ),
        ).code,
        'refund_request_open',
      );

      h.serveFixture('support/refund_requests_list');
      expect(okValue(await repo.refundRequests()).items, hasLength(1));
    });
  });

  group('NotificationsRepository', () {
    test('reads and marks the inbox and the preferences', () async {
      final h = Harness(stored: signedIn());
      final repo = NotificationsRepositoryImpl(
        NotificationsRemoteDataSource(h.dio),
      );

      h.serveFixture('notifications/list_ok');
      final inbox = okValue(await repo.inbox());
      expect(inbox.unreadCount, 2);
      expect(inbox.notifications.first.link?.type, 'ticket');
      expect(inbox.notifications.last.isRead, isFalse);

      h.serveFixture('notifications/unread_count_ok');
      expect(okValue(await repo.unreadCount()), 2);

      const id = '00000000-0000-4000-8000-000000000001';
      h.serveFixture('notifications/mark_read_ok');
      expect(okValue(await repo.markRead(id)).isRead, isTrue);

      h.serveFixture('notifications/mark_read_not_found');
      failureOf<NotFoundFailure>(
        await repo.markRead('00000000-0000-4000-8000-000000000001'),
      );

      h.serveFixture('notifications/read_all_ok');
      expect(okValue(await repo.markAllRead()), 1);

      h.serveFixture('notifications/preferences_ok');
      final preferences = okValue(await repo.preferences());
      expect(preferences, hasLength(12));
      expect(
        preferences
            .where((p) => p.mandatory)
            .map((p) => '${p.category}.${p.channel}'),
        ['orders.mail', 'security.mail'],
      );

      h.serveFixture('notifications/preferences_updated');
      okValue(
        await repo.updatePreferences(const [
          PreferenceChange(
            category: 'marketing',
            channel: 'mail',
            enabled: true,
          ),
        ]),
      );

      h.serveFixture('notifications/preferences_mandatory');
      final mandatory = failureOf<DomainFailure>(
        await repo.updatePreferences(const [
          PreferenceChange(category: 'orders', channel: 'mail', enabled: false),
        ]),
      );
      expect(mandatory.code, 'notification_preference_mandatory');
      expect(mandatory.errors.keys, ['preferences.0.enabled']);
    });
  });

  group('AccountRepository', () {
    test('profile, password, devices and verification', () async {
      final h = Harness(stored: signedIn());
      final repo = AccountRepositoryImpl(AccountRemoteDataSource(h.dio));

      h.serveFixture('account/profile_ok');
      expect(okValue(await repo.profile()).locale, isNull);

      h.serveFixture('account/profile_updated');
      final updated = okValue(
        await repo.updateProfile(
          const ProfileUpdate(
            name: 'Sara C.',
            locale: FieldUpdate('ar'),
            phone: FieldUpdate('+20 100 123 4567'),
          ),
        ),
      );
      expect(updated.phone, '+201001234567');
      expect(h.adapter.requests.single.data, {
        'name': 'Sara C.',
        'locale': 'ar',
        'phone': '+20 100 123 4567',
      });

      h.serveFixture('account/profile_validation_failed');
      failureOf<ValidationFailure>(
        await repo.updateProfile(
          const ProfileUpdate(locale: FieldUpdate('fr')),
        ),
      );

      h.serveFixture('account/password_validation_failed');
      failureOf<ValidationFailure>(
        await repo.changePassword(
          currentPassword: 'wrong-password',
          password: 'short',
          passwordConfirmation: 'short',
        ),
      );

      h.serveFixture('account/password_updated');
      expect(
        okValue(
          await repo.changePassword(
            currentPassword: 'password',
            password: 'a-much-longer-passphrase',
            passwordConfirmation: 'a-much-longer-passphrase',
          ),
        ).mobileFamilies,
        1,
      );

      h.serveFixture('account/devices_ok');
      final devices = okValue(await repo.devices());
      expect(
        devices.devices.where((d) => d.current).single.deviceName,
        'Pixel 9',
      );
      expect(devices.webSessionsListed, isFalse);

      h.serveFixture('account/device_current');
      expect(
        failureOf<ConflictFailure>(
          await repo.revokeDevice('01JF0000000000000000000001'),
        ).code,
        'current_device',
      );

      h.serveFixture('account/device_revoked');
      okValue(await repo.revokeDevice('01JF0000000000000000000001'));

      h.serveFixture('account/device_not_found');
      failureOf<NotFoundFailure>(await repo.revokeDevice('unknown-device'));

      h.serveFixture('account/verification_notification_ok');
      okValue(await repo.resendVerificationEmail());
    });

    test('a null field update clears the field', () {
      expect(const ProfileUpdate(phone: FieldUpdate(null)).toBody(), {
        'phone': null,
      });
      expect(const ProfileUpdate().toBody(), isEmpty);
    });
  });

  group('SecurityRepository', () {
    test('enrols, confirms and disables two-factor authentication', () async {
      final h = Harness(stored: signedIn());
      final repo = SecurityRepositoryImpl(SecurityRemoteDataSource(h.dio));

      h.serveFixture('security/two_factor_enable_invalid_password');
      failureOf<ValidationFailure>(
        await repo.enableTwoFactor(currentPassword: 'wrong-password'),
      );

      h.serveFixture('security/two_factor_enable_ok');
      final enrollment = okValue(
        await repo.enableTwoFactor(currentPassword: 'password'),
      );
      expect(enrollment.otpauthUri.scheme, 'otpauth');
      expect(enrollment.toString(), isNot(contains('fixture-secret')));

      h.serveFixture('security/two_factor_confirm_invalid');
      expect(
        failureOf<DomainFailure>(await repo.confirmTwoFactor(code: '000000'))
            .code,
        'two_factor_invalid',
      );

      h.serveFixture('security/two_factor_confirm_ok');
      final codes = okValue(await repo.confirmTwoFactor(code: '123456'));
      expect(codes.codes, hasLength(8));
      expect(codes.toString(), isNot(contains('fixture-recovery')));

      h.serveFixture('security/two_factor_already_enabled');
      expect(
        failureOf<ConflictFailure>(
          await repo.enableTwoFactor(currentPassword: 'password'),
        ).code,
        'two_factor_already_enabled',
      );

      h.serveFixture('security/two_factor_disable_ok');
      okValue(await repo.disableTwoFactor(currentPassword: 'password'));
      expect(h.adapter.requests.single.data, {'current_password': 'password'});
      expect(h.stored.keys.toSet(), signedIn().keys.toSet());
    });
  });
}
