import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/features/code_reveal/domain/entities/revealed_code.dart';
import 'package:application/features/code_reveal/domain/repositories/code_reveal_repository.dart';
import 'package:application/features/code_reveal/domain/usecases/reveal_code.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/repo_harness.dart';

const target = RevealTarget.account(orderPublicId: 'o', unitPublicId: 'u');
const canary = RevealedCode(code: 'FIXTURE-CODE-0000');

class ScriptedReveal implements CodeRevealRepository {
  ScriptedReveal(this.reveals, {Result<void>? confirmation})
    : confirmation = confirmation ?? const Ok<void>(null);

  final List<Result<RevealedCode>> reveals;
  final Result<void> confirmation;
  final List<String> calls = [];

  @override
  Future<Result<void>> confirmPassword(String password) async {
    calls.add('confirm:$password');
    return confirmation;
  }

  @override
  Future<Result<RevealedCode>> reveal(RevealTarget target) async {
    calls.add('reveal');
    return reveals.removeAt(0);
  }
}

void main() {
  test(
    '423: asks for the password, confirms, then retries the reveal once',
    () async {
      final repo = ScriptedReveal([
        const Err(RecentAuthRequiredFailure()),
        const Ok(canary),
      ]);
      var prompts = 0;

      final result = await RevealCode(repo)(
        target,
        askPassword: () async {
          prompts++;
          return 'password';
        },
      );

      expect(okValue(result).code, 'FIXTURE-CODE-0000');
      expect(prompts, 1);
      expect(repo.calls, ['reveal', 'confirm:password', 'reveal']);
    },
  );

  test(
    'a second 423 after confirming is returned, not retried again',
    () async {
      final repo = ScriptedReveal([
        const Err(RecentAuthRequiredFailure()),
        const Err(RecentAuthRequiredFailure()),
      ]);

      final result = await RevealCode(repo)(
        target,
        askPassword: () async => 'password',
      );

      failureOf<RecentAuthRequiredFailure>(result);
      expect(repo.calls, ['reveal', 'confirm:password', 'reveal']);
    },
  );

  test('a cancelled password sheet stops without confirming', () async {
    final repo = ScriptedReveal([const Err(RecentAuthRequiredFailure())]);

    failureOf<RecentAuthRequiredFailure>(
      await RevealCode(repo)(target, askPassword: () async => null),
    );
    expect(repo.calls, ['reveal']);
  });

  test(
    'a wrong password returns the confirmation failure and does not reveal',
    () async {
      final repo = ScriptedReveal(
        [const Err(RecentAuthRequiredFailure())],
        confirmation: const Err(
          ValidationFailure({
            'password': ['The password is incorrect.'],
          }),
        ),
      );

      failureOf<ValidationFailure>(
        await RevealCode(repo)(target, askPassword: () async => 'wrong'),
      );
      expect(repo.calls, ['reveal', 'confirm:wrong']);
    },
  );

  test('never retries on 429 or any other failure, and never asks for the password', () async {
    for (final failure in const <Failure>[
      RateLimitedFailure(retryAfter: Duration(hours: 1)),
      ForbiddenFailure('reveal_denied'),
      NotFoundFailure(),
      NetworkFailure(),
    ]) {
      final repo = ScriptedReveal([Err(failure)]);
      var prompts = 0;

      final result = await RevealCode(repo)(
        target,
        askPassword: () async {
          prompts++;
          return 'password';
        },
      );

      expect(result.failureOrNull, same(failure));
      expect(repo.calls, ['reveal']);
      expect(prompts, 0);
    }
  });
}
