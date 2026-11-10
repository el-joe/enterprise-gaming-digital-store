import 'package:application/core/network/result.dart';
import 'package:application/features/home/domain/entities/home.dart';

abstract interface class HomeRepository {
  Future<Result<HomePage>> fetch();
}
