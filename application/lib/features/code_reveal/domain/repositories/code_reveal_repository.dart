import 'package:application/core/network/result.dart';
import 'package:application/features/code_reveal/domain/entities/revealed_code.dart';

abstract interface class CodeRevealRepository {
  /// Records a recent authentication on this access token (15 minutes).
  Future<Result<void>> confirmPassword(String password);

  /// 423 `recent_auth_required` (account), 403 `reveal_denied`, 404, 429. Every attempt is
  /// audited by the server; the client never retries automatically.
  Future<Result<RevealedCode>> reveal(RevealTarget target);
}
