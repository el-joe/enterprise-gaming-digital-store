import 'package:application/core/network/failure.dart';
import 'package:flutter/foundation.dart';

/// State of a screen that loads one value: loading, loaded or failed with a typed [Failure].
///
/// Controllers use it instead of `AsyncValue` so a failed load stays failed until the customer
/// retries (Riverpod 3 would otherwise retry a throwing provider on its own).
@immutable
sealed class Loadable<T> {
  const Loadable();

  const factory Loadable.loading() = Loading<T>;
  const factory Loadable.loaded(T value) = Loaded<T>;
  const factory Loadable.failed(Failure failure) = Failed<T>;

  T? get valueOrNull => switch (this) {
    Loaded<T>(:final value) => value,
    _ => null,
  };
}

final class Loading<T> extends Loadable<T> {
  const Loading();
}

final class Loaded<T> extends Loadable<T> {
  const Loaded(this.value);

  final T value;
}

final class Failed<T> extends Loadable<T> {
  const Failed(this.failure);

  final Failure failure;
}

/// Whether [failure] means the device is offline (no connection or a timeout), which screens
/// show as the offline state instead of the error state.
bool isOfflineFailure(Failure failure) =>
    failure is NetworkFailure || failure is TimeoutFailure;
