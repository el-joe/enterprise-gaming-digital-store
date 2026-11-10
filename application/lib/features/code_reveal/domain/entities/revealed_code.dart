/// A revealed code (API_DOCUMENTATION.md 4, 4a). Held in memory only for as long as the screen
/// shows it: never cached, persisted, logged or kept alive by a provider. [toString] is redacted
/// so it cannot leak through logs or error reports.
final class RevealedCode {
  const RevealedCode({
    required this.code,
    this.pin,
    this.redemptionUrl,
    this.instructions,
  });

  final String code;
  final String? pin;
  final Uri? redemptionUrl;
  final String? instructions;

  @override
  String toString() => 'RevealedCode(<redacted>)';
}

/// The unit to reveal: an account order unit, or a guest order unit (guest order token with the
/// link and OTP proofs).
final class RevealTarget {
  const RevealTarget.account({
    required this.orderPublicId,
    required this.unitPublicId,
  }) : guest = false;

  const RevealTarget.guest({
    required this.orderPublicId,
    required this.unitPublicId,
  }) : guest = true;

  final String orderPublicId;
  final String unitPublicId;
  final bool guest;
}
