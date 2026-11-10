import 'package:application/features/code_reveal/domain/entities/revealed_code.dart';

/// Reveal response `{ code, pin, redemption_url, instructions }`. A plain class (no freezed, no
/// generated `toString`, `copyWith` or JSON writer) so the code cannot be serialized or printed.
final class RevealedCodeDto {
  const RevealedCodeDto({
    required this.code,
    this.pin,
    this.redemptionUrl,
    this.instructions,
  });

  factory RevealedCodeDto.fromJson(Map<String, dynamic> json) {
    final code = json['code'];
    final pin = json['pin'];
    final url = json['redemption_url'];
    final instructions = json['instructions'];
    if (code is! String ||
        (pin != null && pin is! String) ||
        (url != null && url is! String) ||
        (instructions != null && instructions is! String)) {
      throw const FormatException(
        'Reveal response does not match the contract.',
      );
    }
    return RevealedCodeDto(
      code: code,
      pin: pin as String?,
      redemptionUrl: url as String?,
      instructions: instructions as String?,
    );
  }

  final String code;
  final String? pin;
  final String? redemptionUrl;
  final String? instructions;

  RevealedCode toEntity() => RevealedCode(
    code: code,
    pin: pin,
    redemptionUrl: redemptionUrl == null ? null : Uri.parse(redemptionUrl!),
    instructions: instructions,
  );

  @override
  String toString() => 'RevealedCodeDto(<redacted>)';
}
