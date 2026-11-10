import 'package:application/features/security/domain/entities/two_factor.dart';

/// Plain decoders (no generated `toString` or JSON writer) for values shown once.
abstract final class TwoFactorModels {
  static TwoFactorEnrollment enrollment(Map<String, dynamic> json) {
    final secret = json['secret'];
    final uri = json['otpauth_uri'];
    if (secret is! String || uri is! String) {
      throw const FormatException(
        'Two-factor enrolment does not match the contract.',
      );
    }
    return TwoFactorEnrollment(secret: secret, otpauthUri: Uri.parse(uri));
  }

  static RecoveryCodes recoveryCodes(Map<String, dynamic> json) {
    final codes = json['recovery_codes'];
    if (codes is! List || codes.any((code) => code is! String)) {
      throw const FormatException('recovery_codes must be a list of strings.');
    }
    return RecoveryCodes(List.unmodifiable(codes.cast<String>()));
  }
}
