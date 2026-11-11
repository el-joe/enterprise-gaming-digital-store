import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/features/code_reveal/domain/entities/revealed_code.dart';
import 'package:application/features/code_reveal/domain/repositories/code_reveal_repository.dart';

/// Asks the customer for the password (the confirm-password sheet). Returns null when cancelled.
typedef PasswordPrompt = Future<String?> Function();

/// Runs the whole recent-authentication step (biometric first when enabled, then the password
/// sheet, owner decision 6) and says whether the server accepted a confirmation. The confirm
/// calls themselves happen inside (the sheet stays open on a wrong password).
typedef RecentAuthPrompt = Future<bool> Function();

/// Reveal with the recent-authentication step (MOBILE_APP_ROADMAP.md 6.3):
/// reveal -> on 423 `recent_auth_required` confirm (biometric or password) -> retry the reveal
/// exactly once. A failed confirmation is returned as is.
/// Nothing is retried automatically: 429 (the `code-reveal` limiter, 10/hour) and every other
/// failure are returned to the caller. The result is returned, never stored.
///
/// `deviceIntegrity` is sent as `X-Device-Integrity` on every reveal request (owner decision 5:
/// a signal, never a block). The caller supplies `confirmRecentAuth` (the app) or, as in M-A7,
/// `askPassword`.
class RevealCode {
  RevealCode(this._repository);

  final CodeRevealRepository _repository;

  Future<Result<RevealedCode>> call(
    RevealTarget target, {
    PasswordPrompt? askPassword,
    RecentAuthPrompt? confirmRecentAuth,
    String? deviceIntegrity,
  }) async {
    assert(
      askPassword != null || confirmRecentAuth != null,
      'A recent-auth step is required.',
    );
    final first = await _repository.reveal(
      target,
      deviceIntegrity: deviceIntegrity,
    );
    if (first.failureOrNull is! RecentAuthRequiredFailure) {
      return first;
    }
    if (confirmRecentAuth != null) {
      if (!await confirmRecentAuth()) {
        return first;
      }
    } else {
      final password = await askPassword!();
      if (password == null) {
        return first;
      }
      final confirmed = await _repository.confirmPassword(password);
      if (confirmed case Err(:final failure)) {
        return Err(failure);
      }
    }
    return _repository.reveal(target, deviceIntegrity: deviceIntegrity);
  }
}
