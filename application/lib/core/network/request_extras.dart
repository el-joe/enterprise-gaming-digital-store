/// Keys for `RequestOptions.extra` understood by the interceptors.
abstract final class RequestExtras {
  /// `false` sends the request without a bearer token and skips refresh (login, refresh itself).
  static const authenticate = 'authenticate';

  /// Set by the auth interceptor on the single retry after a refresh.
  static const authRetried = 'authRetried';

  /// The `Idempotency-Key` to send (stored with the pending checkout).
  static const idempotencyKey = 'idempotencyKey';

  /// `true` disables automatic retries for this request.
  static const noRetry = 'noRetry';

  /// Number of automatic retries already made for this request.
  static const retryAttempt = 'retryAttempt';

  /// `true` keeps request and response bodies out of debug logs (in addition to the path rules).
  static const sensitive = 'sensitive';
}
