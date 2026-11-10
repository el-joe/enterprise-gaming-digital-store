import 'dart:ui';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/dio_client.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/features/account/data/datasources/account_remote_data_source.dart';
import 'package:application/features/account/data/repositories/account_repository_impl.dart';
import 'package:application/features/account/domain/repositories/account_repository.dart';
import 'package:application/features/account/domain/usecases/update_regional_settings.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'account_providers.g.dart';

@Riverpod(keepAlive: true)
AccountRemoteDataSource accountRemoteDataSource(Ref ref) =>
    AccountRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
AccountRepository accountRepository(Ref ref) =>
    AccountRepositoryImpl(ref.watch(accountRemoteDataSourceProvider));

@riverpod
UpdateRegionalSettings updateRegionalSettings(Ref ref) =>
    UpdateRegionalSettings(
      ref.watch(accountRepositoryProvider),
      applyLocale: (code) =>
          ref.read(appLocaleProvider.notifier).select(Locale(code)),
      applyCurrency: (code) =>
          ref.read(displayCurrencyProvider.notifier).select(code),
    );
