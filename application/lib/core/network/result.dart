import 'package:application/core/network/error_mapper.dart';
import 'package:application/core/network/failure.dart';

/// Outcome of a repository call: [Ok] with a value or [Err] with a typed [Failure].
sealed class Result<T> {
  const Result();

  R fold<R>(R Function(T value) onOk, R Function(Failure failure) onErr) {
    return switch (this) {
      Ok<T>(:final value) => onOk(value),
      Err<T>(:final failure) => onErr(failure),
    };
  }

  bool get isOk => this is Ok<T>;

  T? get valueOrNull => switch (this) {
    Ok<T>(:final value) => value,
    Err<T>() => null,
  };

  Failure? get failureOrNull => switch (this) {
    Ok<T>() => null,
    Err<T>(:final failure) => failure,
  };
}

final class Ok<T> extends Result<T> {
  const Ok(this.value);

  final T value;
}

final class Err<T> extends Result<T> {
  const Err(this.failure);

  final Failure failure;
}

/// Runs [request] and maps any thrown error to a [Failure] through [mapError].
Future<Result<T>> guardRequest<T>(Future<T> Function() request) async {
  try {
    return Ok<T>(await request());
  } on Object catch (error) {
    return Err<T>(mapError(error));
  }
}
