import 'package:freezed_annotation/freezed_annotation.dart';

part 'account.freezed.dart';

/// `GET /account/profile` (API_DOCUMENTATION.md 4c).
@freezed
abstract class Profile with _$Profile {
  const factory Profile({
    required String publicId,
    required String name,
    required String email,
    required bool emailVerified,
    required bool twoFactorEnabled,
    required DateTime createdAt,

    /// E.164, for example `+201001234567`.
    String? phone,

    /// `en`, `ar` or null; the app sends it as `Accept-Language` after sign-in.
    String? locale,
    String? displayCurrency,
    String? countryCode,
  }) = _Profile;
}

/// A value to set, where `FieldUpdate(null)` clears the field. Leaving the field out of
/// [ProfileUpdate] keeps it unchanged.
final class FieldUpdate<T> {
  const FieldUpdate(this.value);

  final T? value;
}

/// `PATCH /account/profile`: only the keys given change.
final class ProfileUpdate {
  const ProfileUpdate({
    this.name,
    this.locale,
    this.displayCurrency,
    this.countryCode,
    this.phone,
  });

  final String? name;
  final FieldUpdate<String>? locale;
  final FieldUpdate<String>? displayCurrency;
  final FieldUpdate<String>? countryCode;
  final FieldUpdate<String>? phone;

  Map<String, Object?> toBody() => {
    'name': ?name,
    if (locale != null) 'locale': locale!.value,
    if (displayCurrency != null) 'display_currency': displayCurrency!.value,
    if (countryCode != null) 'country_code': countryCode!.value,
    if (phone != null) 'phone': phone!.value,
  };
}

/// Sign-ins revoked by a password change or by "sign out other devices" (the caller stays
/// signed in).
@freezed
abstract class PasswordChangeResult with _$PasswordChangeResult {
  const factory PasswordChangeResult({
    required int mobileFamilies,
    required int webSessions,
  }) = _PasswordChangeResult;
}

/// A signed-in device: a mobile sign-in (refresh-token family) or a web session.
@freezed
abstract class Device with _$Device {
  const factory Device({
    required String id,

    /// `mobile` or `web`.
    required String type,
    required bool current,
    String? deviceName,
    String? userAgent,

    /// Masked (`203.0.113.x`).
    String? ip,
    DateTime? createdAt,
    DateTime? lastUsedAt,
    DateTime? expiresAt,
  }) = _Device;
}

@freezed
abstract class DeviceList with _$DeviceList {
  const factory DeviceList({
    required List<Device> devices,

    /// False when the server's session driver cannot list browser sessions.
    required bool webSessionsListed,
  }) = _DeviceList;
}
