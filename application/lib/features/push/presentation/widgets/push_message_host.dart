import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/app_link_listener.dart';
import 'package:application/features/push/domain/push_routing.dart';
import 'package:application/features/push/presentation/controllers/push_controllers.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Shows a push that arrives while the app is open (the system shows nothing then) as an in-app
/// snackbar with "Open" when it leads to an order or a ticket (M-A18). Sits above the navigator in
/// the app's builder, below the app's `ScaffoldMessenger`.
class PushMessageHost extends ConsumerWidget {
  const PushMessageHost({required this.child, super.key});

  final Widget child;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    ref.listen(foregroundPushProvider, (previous, next) {
      if (next == null || next == previous) {
        return;
      }
      final payload = next.payload;
      final text = [
        payload.title,
        payload.body,
      ].whereType<String>().where((part) => part.trim().isNotEmpty).join('\n');
      final link = deepLinkForPush(payload);
      showAppSnackbar(
        context,
        message: text.isEmpty ? context.l10n.pushNewNotification : text,
        tone: SnackbarTone.info,
        actionLabel: link == null ? null : context.l10n.pushOpen,
        onAction: link == null
            ? null
            : () => ref.read(appLinkListenerProvider.notifier).open(link),
      );
    });
    return child;
  }
}
