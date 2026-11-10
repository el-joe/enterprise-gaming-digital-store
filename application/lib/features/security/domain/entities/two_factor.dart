/// A started two-factor enrolment. The [secret] and [otpauthUri] are shown once (QR code and
/// text) and never stored; [toString] is redacted.
final class TwoFactorEnrollment {
  const TwoFactorEnrollment({required this.secret, required this.otpauthUri});

  final String secret;
  final Uri otpauthUri;

  @override
  String toString() => 'TwoFactorEnrollment(<redacted>)';
}

/// Recovery codes, shown once after confirmation and never stored; [toString] is redacted.
final class RecoveryCodes {
  const RecoveryCodes(this.codes);

  final List<String> codes;

  @override
  String toString() => 'RecoveryCodes(${codes.length} codes, <redacted>)';
}
