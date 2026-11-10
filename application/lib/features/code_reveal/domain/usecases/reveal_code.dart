import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/features/code_reveal/domain/entities/revealed_code.dart';
import 'package:application/features/code_reveal/domain/repositories/code_reveal_repository.dart';

/// Asks the customer for the password (the confirm-password sheet). Returns null when cancelled.
typedef PasswordPrompt = Future<String?> Function();

/// Reveal with the recent-authentication step (MOBILE_APP_ROADMAP.md 6.3):
/// reveal -> on 423 `recent_auth_required` ask for the password -> `confirm-password` -> retry the
/// reveal exactly once. A failed confirmation is returned as is (the sheet shows its field error).
/// Nothing is retried automatically: 429 (the `code-reveal` limiter, 10/hour) and every other
/// failure are returned to the caller. The result is returned, never stored.
class RevealCode {
  RevealCode(this._repository);

  final CodeRevealRepository _repository;

  Future<Result<RevealedCode>> call(
    RevealTarget target, {
    required PasswordPrompt askPassword,
  }) async {
    final first = await _repository.reveal(target);
    if (first.failureOrNull is! RecentAuthRequiredFailure) {
      return first;
    }
    final password = await askPassword();
    if (password == null) {
      return first;
    }
    final confirmed = await _repository.confirmPassword(password);
    if (confirmed case Err(:final failure)) {
      return Err(failure);
    }
    return _repository.reveal(target);
  }
}
