import 'dart:async';

import 'package:application/core/security/clipboard_guard.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Runs a clipboard clear that fell due while the app was in the background
/// ([ClipboardGuard.resumed]). Sits above the navigator for the app's lifetime.
class ClipboardResumeHook extends ConsumerStatefulWidget {
  const ClipboardResumeHook({required this.child, super.key});

  final Widget child;

  @override
  ConsumerState<ClipboardResumeHook> createState() =>
      _ClipboardResumeHookState();
}

class _ClipboardResumeHookState extends ConsumerState<ClipboardResumeHook> {
  late final AppLifecycleListener _lifecycle;

  @override
  void initState() {
    super.initState();
    _lifecycle = AppLifecycleListener(
      onResume: () => unawaited(ref.read(clipboardGuardProvider).resumed()),
    );
  }

  @override
  void dispose() {
    _lifecycle.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => widget.child;
}
