import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/theme/app_theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Root widget: router, dark theme, locale. `MaterialApp` derives the text direction from the
/// locale, so switching to Arabic mirrors the whole tree.
class App extends ConsumerWidget {
  const App({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final locale = ref.watch(appLocaleProvider);
    final theme = AppTheme.dark(arabic: AppLocales.isRtl(locale));
    return MaterialApp.router(
      onGenerateTitle: (context) => context.l10n.appTitle,
      debugShowCheckedModeBanner: false,
      theme: theme,
      darkTheme: theme,
      themeMode: ThemeMode.dark,
      locale: locale,
      supportedLocales: AppLocales.supported,
      localizationsDelegates: AppLocalizations.localizationsDelegates,
      routerConfig: ref.watch(appRouterProvider),
    );
  }
}
