import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/features/account/account_providers.dart';
import 'package:application/features/account/domain/entities/account.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'devices_controller.g.dart';

@immutable
final class DevicesState {
  const DevicesState({
    this.content = const Loadable.loading(),
    this.revoking,
    this.signingOutOthers = false,
  });

  /// `GET /account/devices`: this device first, then the others by last use.
  final Loadable<DeviceList> content;

  /// The id of the device being signed out.
  final String? revoking;
  final bool signingOutOthers;

  List<Device> get devices => content.valueOrNull?.devices ?? const [];

  /// Devices other than this one (sign out of all other devices is offered only with some).
  bool get hasOthers => devices.any((device) => !device.current);

  DevicesState copyWith({
    Loadable<DeviceList>? content,
    String? Function()? revoking,
    bool? signingOutOthers,
  }) => DevicesState(
    content: content ?? this.content,
    revoking: revoking == null ? this.revoking : revoking(),
    signingOutOthers: signingOutOthers ?? this.signingOutOthers,
  );
}

/// Result of signing one device out.
sealed class RevokeOutcome {
  const RevokeOutcome();
}

final class DeviceSignedOut extends RevokeOutcome {
  const DeviceSignedOut();
}

final class RevokeFailed extends RevokeOutcome {
  const RevokeFailed(this.failure);

  final Failure failure;
}

/// Devices (18.5, 18.6): the signed-in app devices and, with the database session driver,
/// browser sessions. Signing one out is `DELETE /account/devices/{id}` (this device is never
/// offered: 409 `current_device`, use sign out instead; 404 means it was already gone, so the
/// list is reloaded). "Sign out of all other devices" is `POST /devices/sign-out-others` with the
/// current password; this device stays signed in.
@riverpod
class DevicesController extends _$DevicesController {
  int _generation = 0;

  @override
  DevicesState build() {
    if (ref.watch(sessionProvider) != SessionStatus.signedIn) {
      return const DevicesState(content: Loadable.loaded(_none));
    }
    unawaited(_load());
    return const DevicesState();
  }

  static const _none = DeviceList(devices: [], webSessionsListed: false);

  Future<void> reload() async {
    state = state.copyWith(content: const Loadable.loading());
    await _load();
  }

  Future<void> _load() async {
    final generation = ++_generation;
    final result = await ref.read(accountRepositoryProvider).devices();
    if (!ref.mounted || generation != _generation) {
      return;
    }
    state = state.copyWith(
      content: switch (result) {
        Ok(:final value) => Loadable.loaded(_sorted(value)),
        Err(:final failure) => Loadable.failed(failure),
      },
    );
  }

  static DeviceList _sorted(DeviceList list) {
    final devices = [...list.devices]
      ..sort((a, b) {
        if (a.current != b.current) {
          return a.current ? -1 : 1;
        }
        final aUsed = a.lastUsedAt ?? a.createdAt;
        final bUsed = b.lastUsedAt ?? b.createdAt;
        if (aUsed == null || bUsed == null) {
          return aUsed == null ? (bUsed == null ? 0 : 1) : -1;
        }
        return bUsed.compareTo(aUsed);
      });
    return list.copyWith(devices: devices);
  }

  Future<RevokeOutcome?> revoke(Device device) async {
    if (device.current || state.revoking != null) {
      return null;
    }
    state = state.copyWith(revoking: () => device.id);
    final result = await ref
        .read(accountRepositoryProvider)
        .revokeDevice(device.id);
    if (!ref.mounted) {
      return null;
    }
    switch (result) {
      case Ok():
        _drop(device.id);
        return const DeviceSignedOut();
      case Err(failure: NotFoundFailure()):
        // Already signed out elsewhere: the device is gone either way.
        _drop(device.id);
        unawaited(_load());
        return const DeviceSignedOut();
      case Err(:final failure):
        state = state.copyWith(revoking: () => null);
        return RevokeFailed(failure);
    }
  }

  void _drop(String id) {
    final list = state.content.valueOrNull;
    state = state.copyWith(
      revoking: () => null,
      content: list == null
          ? null
          : Loadable.loaded(
              list.copyWith(
                devices: [
                  for (final device in list.devices)
                    if (device.id != id) device,
                ],
              ),
            ),
    );
  }

  /// Signs every other device out. Returns null on success, else the failure (a 422 holds the
  /// server's message for `current_password`). The password is only passed through.
  Future<Failure?> signOutOthers(String currentPassword) async {
    if (state.signingOutOthers) {
      return null;
    }
    state = state.copyWith(signingOutOthers: true);
    final result = await ref
        .read(accountRepositoryProvider)
        .signOutOtherDevices(currentPassword: currentPassword);
    if (!ref.mounted) {
      return result.failureOrNull;
    }
    state = state.copyWith(signingOutOthers: false);
    if (result.isOk) {
      final list = state.content.valueOrNull;
      if (list != null) {
        state = state.copyWith(
          content: Loadable.loaded(
            list.copyWith(
              devices: [
                for (final device in list.devices)
                  if (device.current) device,
              ],
            ),
          ),
        );
      }
      unawaited(_load());
    }
    return result.failureOrNull;
  }
}
