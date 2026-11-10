import 'package:application/core/network/dio_client.dart';
import 'package:application/features/reviews/data/datasources/reviews_remote_data_source.dart';
import 'package:application/features/reviews/data/repositories/reviews_repository_impl.dart';
import 'package:application/features/reviews/domain/repositories/reviews_repository.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'reviews_providers.g.dart';

@Riverpod(keepAlive: true)
ReviewsRemoteDataSource reviewsRemoteDataSource(Ref ref) =>
    ReviewsRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
ReviewsRepository reviewsRepository(Ref ref) =>
    ReviewsRepositoryImpl(ref.watch(reviewsRemoteDataSourceProvider));
