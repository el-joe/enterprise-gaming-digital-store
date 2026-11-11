import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:flutter/material.dart';

/// The bell of the home header with the unread count (`Badge`, volt): "Notifications, 2
/// unread" for screen readers. 48dp.
class NotificationBell extends StatelessWidget {
  const NotificationBell({
    required this.unread,
    required this.onPressed,
    super.key,
  });

  final int unread;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final label = unread > 0
        ? l10n.notificationBellUnread(unread)
        : l10n.accountNotifications;
    return Semantics(
      button: true,
      label: label,
      excludeSemantics: true,
      onTap: onPressed,
      child: SizedBox.square(
        dimension: 48,
        child: IconButton(
          key: const ValueKey('home-notifications'),
          onPressed: onPressed,
          icon: Badge(
            isLabelVisible: unread > 0,
            backgroundColor: colors.accentPrimary,
            textColor: colors.contentOnAccent,
            label: Text(
              unread > 99 ? '99+' : '$unread',
              textDirection: TextDirection.ltr,
            ),
            child: const Icon(Icons.notifications_none),
          ),
        ),
      ),
    );
  }
}
