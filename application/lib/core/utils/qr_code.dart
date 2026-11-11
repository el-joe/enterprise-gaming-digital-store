/// A minimal QR Code (ISO/IEC 18004) encoder for short ASCII payloads such as the `otpauth://`
/// URI of two-factor enrolment (M-A16). It is a line-for-line port of the backend's in-house
/// `App\Domains\Shared\Services\QrCodeSvg` (M13), whose output was checked module for module
/// against a reference encoder; `test/core/utils/qr_code_test.dart` pins the same symbols.
///
/// Byte mode, error correction level M, versions 1 to 20 (up to 666 bytes), automatic mask
/// selection by the standard penalty rules. Written in-house so the app needs no QR or SVG
/// package: the API returns the enrolment URI, not an image, to the app.
final class QrCode {
  QrCode._(this.size, this._modules);

  /// Encodes [data] (ASCII; other characters are written as UTF-8 bytes). [mask] forces a mask
  /// pattern (0 to 7); null picks the one with the lowest penalty. Throws [ArgumentError] for data
  /// beyond version 20.
  factory QrCode.encode(String data, {int? mask}) =>
      _QrBuilder(data).build(mask);

  /// Modules per side, without the quiet zone.
  final int size;
  final List<List<bool>> _modules;

  /// Whether the module at column [x], row [y] is dark.
  bool isDark(int x, int y) => _modules[y][x];

  /// The matrix as `0`/`1` rows joined, for tests.
  String get bits =>
      [for (final row in _modules) row.map((on) => on ? '1' : '0').join()]
          .join();
}

/// Level M: [EC codewords per block, group 1 blocks, group 1 data codewords, group 2 blocks,
/// group 2 data codewords], by version (index 0 is version 1).
const _blocksM = <List<int>>[
  [10, 1, 16, 0, 0],
  [16, 1, 28, 0, 0],
  [26, 1, 44, 0, 0],
  [18, 2, 32, 0, 0],
  [24, 2, 43, 0, 0],
  [16, 4, 27, 0, 0],
  [18, 4, 31, 0, 0],
  [22, 2, 38, 2, 39],
  [22, 3, 36, 2, 37],
  [26, 4, 43, 1, 44],
  [30, 1, 50, 4, 51],
  [22, 6, 36, 2, 37],
  [22, 8, 37, 1, 38],
  [24, 4, 40, 5, 41],
  [24, 5, 41, 5, 42],
  [28, 7, 45, 3, 46],
  [28, 10, 46, 1, 47],
  [26, 9, 43, 4, 44],
  [26, 3, 44, 11, 45],
  [26, 3, 41, 13, 42],
];

final class _QrBuilder {
  _QrBuilder(String data) : _bytes = _utf8(data);

  final List<int> _bytes;
  late final int _version = _versionFor(_bytes.length);
  late final int _size = _version * 4 + 17;
  late final List<List<bool>> _modules = List.generate(
    _size,
    (_) => List.filled(_size, false),
  );
  late final List<List<bool>> _isFunction = List.generate(
    _size,
    (_) => List.filled(_size, false),
  );

  static List<int> _utf8(String data) {
    final out = <int>[];
    for (final rune in data.runes) {
      if (rune < 0x80) {
        out.add(rune);
      } else if (rune < 0x800) {
        out.addAll([0xC0 | (rune >> 6), 0x80 | (rune & 0x3F)]);
      } else if (rune < 0x10000) {
        out.addAll([
          0xE0 | (rune >> 12),
          0x80 | ((rune >> 6) & 0x3F),
          0x80 | (rune & 0x3F),
        ]);
      } else {
        out.addAll([
          0xF0 | (rune >> 18),
          0x80 | ((rune >> 12) & 0x3F),
          0x80 | ((rune >> 6) & 0x3F),
          0x80 | (rune & 0x3F),
        ]);
      }
    }
    return out;
  }

  QrCode build(int? forcedMask) {
    _drawFunctionPatterns();
    _drawCodewords(_interleave(_dataCodewords()));
    var mask = forcedMask;
    if (mask == null) {
      var best = 1 << 62;
      for (var candidate = 0; candidate < 8; candidate++) {
        _applyMask(candidate);
        _drawFormatBits(candidate);
        final penalty = _penalty();
        if (penalty < best) {
          best = penalty;
          mask = candidate;
        }
        _applyMask(candidate);
      }
    }
    _applyMask(mask!);
    _drawFormatBits(mask);
    return QrCode._(_size, [
      for (final row in _modules) List.unmodifiable(row),
    ]);
  }

  static int _versionFor(int length) {
    for (var index = 0; index < _blocksM.length; index++) {
      final version = index + 1;
      final b = _blocksM[index];
      final capacityBits = (b[1] * b[2] + b[3] * b[4]) * 8;
      if (4 + (version < 10 ? 8 : 16) + length * 8 <= capacityBits) {
        return version;
      }
    }
    throw ArgumentError('The data is too long for a QR code.');
  }

  List<int> _dataCodewords() {
    final b = _blocksM[_version - 1];
    final capacity = b[1] * b[2] + b[3] * b[4];
    final bits = <int>[];
    void append(int value, int length) {
      for (var i = length - 1; i >= 0; i--) {
        bits.add((value >> i) & 1);
      }
    }

    append(4, 4);
    append(_bytes.length, _version < 10 ? 8 : 16);
    for (final byte in _bytes) {
      append(byte, 8);
    }
    final terminator = capacity * 8 - bits.length;
    append(0, terminator < 4 ? terminator : 4);
    append(0, (8 - bits.length % 8) % 8);

    final codewords = <int>[];
    for (var i = 0; i < bits.length; i += 8) {
      var value = 0;
      for (var j = 0; j < 8; j++) {
        value = (value << 1) | bits[i + j];
      }
      codewords.add(value);
    }
    for (var pad = 0xEC; codewords.length < capacity; pad ^= 0xEC ^ 0x11) {
      codewords.add(pad);
    }
    return codewords;
  }

  List<int> _interleave(List<int> data) {
    final b = _blocksM[_version - 1];
    final ecLength = b[0];
    final divisor = _rsDivisor(ecLength);
    final lengths = [
      for (var i = 0; i < b[1]; i++) b[2],
      for (var i = 0; i < b[3]; i++) b[4],
    ];
    final blocks = <(List<int>, List<int>)>[];
    var offset = 0;
    for (final length in lengths) {
      final chunk = data.sublist(offset, offset + length);
      offset += length;
      blocks.add((chunk, _rsRemainder(chunk, divisor)));
    }
    final result = <int>[];
    final longest = b[2] > b[4] ? b[2] : b[4];
    for (var i = 0; i < longest; i++) {
      for (final (chunk, _) in blocks) {
        if (i < chunk.length) {
          result.add(chunk[i]);
        }
      }
    }
    for (var i = 0; i < ecLength; i++) {
      for (final (_, ec) in blocks) {
        result.add(ec[i]);
      }
    }
    return result;
  }

  static List<int> _rsDivisor(int degree) {
    final result = List.filled(degree, 0);
    result[degree - 1] = 1;
    var root = 1;
    for (var i = 0; i < degree; i++) {
      for (var j = 0; j < degree; j++) {
        result[j] = _gfMultiply(result[j], root);
        if (j + 1 < degree) {
          result[j] ^= result[j + 1];
        }
      }
      root = _gfMultiply(root, 0x02);
    }
    return result;
  }

  static List<int> _rsRemainder(List<int> data, List<int> divisor) {
    final result = List.filled(divisor.length, 0, growable: true);
    for (final byte in data) {
      final factor = byte ^ result.removeAt(0);
      result.add(0);
      for (var i = 0; i < divisor.length; i++) {
        result[i] ^= _gfMultiply(divisor[i], factor);
      }
    }
    return result;
  }

  static int _gfMultiply(int x, int y) {
    var z = 0;
    for (var i = 7; i >= 0; i--) {
      z = (z << 1) ^ ((z >> 7) * 0x11D);
      z ^= ((y >> i) & 1) * x;
    }
    return z & 0xFF;
  }

  void _drawFunctionPatterns() {
    for (var i = 0; i < _size; i++) {
      _setFunction(6, i, on: i.isEven);
      _setFunction(i, 6, on: i.isEven);
    }
    _drawFinder(3, 3);
    _drawFinder(_size - 4, 3);
    _drawFinder(3, _size - 4);

    final positions = _alignmentPositions();
    final count = positions.length;
    for (var i = 0; i < count; i++) {
      for (var j = 0; j < count; j++) {
        final corner =
            (i == 0 && j == 0) ||
            (i == 0 && j == count - 1) ||
            (i == count - 1 && j == 0);
        if (!corner) {
          _drawAlignment(positions[i], positions[j]);
        }
      }
    }
    _drawFormatBits(0);
    _drawVersion();
  }

  List<int> _alignmentPositions() {
    if (_version == 1) {
      return const [];
    }
    final count = _version ~/ 7 + 2;
    final step = (_version * 8 + count * 3 + 5) ~/ (count * 4 - 4) * 2;
    final result = [6];
    for (var position = _size - 7; result.length < count; position -= step) {
      result.insert(1, position);
    }
    return result;
  }

  void _drawFormatBits(int mask) {
    final data = mask; // Level M is 0b00.
    var remainder = data;
    for (var i = 0; i < 10; i++) {
      remainder = (remainder << 1) ^ ((remainder >> 9) * 0x537);
    }
    final bits = ((data << 10) | remainder) ^ 0x5412;

    for (var i = 0; i <= 5; i++) {
      _setFunction(8, i, on: _bit(bits, i));
    }
    _setFunction(8, 7, on: _bit(bits, 6));
    _setFunction(8, 8, on: _bit(bits, 7));
    _setFunction(7, 8, on: _bit(bits, 8));
    for (var i = 9; i < 15; i++) {
      _setFunction(14 - i, 8, on: _bit(bits, i));
    }
    for (var i = 0; i < 8; i++) {
      _setFunction(_size - 1 - i, 8, on: _bit(bits, i));
    }
    for (var i = 8; i < 15; i++) {
      _setFunction(8, _size - 15 + i, on: _bit(bits, i));
    }
    _setFunction(8, _size - 8, on: true);
  }

  void _drawVersion() {
    if (_version < 7) {
      return;
    }
    var remainder = _version;
    for (var i = 0; i < 12; i++) {
      remainder = (remainder << 1) ^ ((remainder >> 11) * 0x1F25);
    }
    final bits = (_version << 12) | remainder;
    for (var i = 0; i < 18; i++) {
      final on = _bit(bits, i);
      final a = _size - 11 + i % 3;
      final b = i ~/ 3;
      _setFunction(a, b, on: on);
      _setFunction(b, a, on: on);
    }
  }

  void _drawFinder(int x, int y) {
    for (var dy = -4; dy <= 4; dy++) {
      for (var dx = -4; dx <= 4; dx++) {
        final distance = dx.abs() > dy.abs() ? dx.abs() : dy.abs();
        final xx = x + dx;
        final yy = y + dy;
        if (xx >= 0 && xx < _size && yy >= 0 && yy < _size) {
          _setFunction(xx, yy, on: distance != 2 && distance != 4);
        }
      }
    }
  }

  void _drawAlignment(int x, int y) {
    for (var dy = -2; dy <= 2; dy++) {
      for (var dx = -2; dx <= 2; dx++) {
        final distance = dx.abs() > dy.abs() ? dx.abs() : dy.abs();
        _setFunction(x + dx, y + dy, on: distance != 1);
      }
    }
  }

  void _setFunction(int x, int y, {required bool on}) {
    _modules[y][x] = on;
    _isFunction[y][x] = true;
  }

  void _drawCodewords(List<int> codewords) {
    var bitIndex = 0;
    final total = codewords.length * 8;
    for (var right = _size - 1; right >= 1; right -= 2) {
      if (right == 6) {
        right = 5;
      }
      for (var vertical = 0; vertical < _size; vertical++) {
        for (var j = 0; j < 2; j++) {
          final x = right - j;
          final upward = ((right + 1) & 2) == 0;
          final y = upward ? _size - 1 - vertical : vertical;
          if (!_isFunction[y][x] && bitIndex < total) {
            _modules[y][x] = _bit(codewords[bitIndex >> 3], 7 - (bitIndex & 7));
            bitIndex++;
          }
        }
      }
    }
  }

  void _applyMask(int mask) {
    for (var y = 0; y < _size; y++) {
      for (var x = 0; x < _size; x++) {
        final invert = switch (mask) {
          0 => (x + y) % 2 == 0,
          1 => y % 2 == 0,
          2 => x % 3 == 0,
          3 => (x + y) % 3 == 0,
          4 => (x ~/ 3 + y ~/ 2) % 2 == 0,
          5 => x * y % 2 + x * y % 3 == 0,
          6 => (x * y % 2 + x * y % 3) % 2 == 0,
          _ => ((x + y) % 2 + x * y % 3) % 2 == 0,
        };
        if (invert && !_isFunction[y][x]) {
          _modules[y][x] = !_modules[y][x];
        }
      }
    }
  }

  /// Penalty rules N1 to N4 (runs, 2x2 blocks, finder-like patterns, dark balance).
  int _penalty() {
    var penalty = 0;
    final lines = <List<bool>>[];
    for (var i = 0; i < _size; i++) {
      lines
        ..add(_modules[i])
        ..add([for (var y = 0; y < _size; y++) _modules[y][i]]);
    }
    for (final line in lines) {
      var run = 1;
      for (var i = 1; i <= _size; i++) {
        if (i < _size && line[i] == line[i - 1]) {
          run++;
          continue;
        }
        if (run >= 5) {
          penalty += 3 + run - 5;
        }
        run = 1;
      }
      final text = line.map((on) => on ? '1' : '0').join();
      penalty +=
          40 * (_count(text, '10111010000') + _count(text, '00001011101'));
    }

    var dark = 0;
    for (var y = 0; y < _size; y++) {
      for (var x = 0; x < _size; x++) {
        if (_modules[y][x]) {
          dark++;
        }
        if (x < _size - 1 && y < _size - 1) {
          final on = _modules[y][x];
          if (on == _modules[y][x + 1] &&
              on == _modules[y + 1][x] &&
              on == _modules[y + 1][x + 1]) {
            penalty += 3;
          }
        }
      }
    }
    final total = _size * _size;
    penalty +=
        10 * (((dark * 20 - total * 10).abs() + total - 1) ~/ total) - 10;
    return penalty;
  }

  /// Non-overlapping occurrences, as PHP's `substr_count`.
  static int _count(String text, String pattern) {
    var count = 0;
    var index = text.indexOf(pattern);
    while (index >= 0) {
      count++;
      index = text.indexOf(pattern, index + pattern.length);
    }
    return count;
  }

  static bool _bit(int value, int index) => ((value >> index) & 1) != 0;
}
