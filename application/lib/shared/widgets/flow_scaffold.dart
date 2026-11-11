import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:flutter/material.dart';

/// Page frame of the launch, onboarding and auth screens: app bar, a scrolling body with 16dp
/// logical margins, and a sticky footer on `surface-base` with a top hairline for the primary
/// actions (as drawn in every M-A8 auth frame).
class FlowScaffold extends StatelessWidget {
  const FlowScaffold({
    required this.body,
    this.appBar,
    this.footer = const [],
    super.key,
  });

  final PreferredSizeWidget? appBar;
  final List<Widget> body;
  final List<Widget> footer;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    return Scaffold(
      backgroundColor: colors.surfaceCanvas,
      appBar: appBar,
      body: SafeArea(
        top: appBar == null,
        bottom: footer.isEmpty,
        child: SingleChildScrollView(
          padding: const EdgeInsetsDirectional.fromSTEB(
            Spacing.s4,
            Spacing.s2,
            Spacing.s4,
            Spacing.s6,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: body,
          ),
        ),
      ),
      bottomNavigationBar: footer.isEmpty
          ? null
          : DecoratedBox(
              decoration: BoxDecoration(
                color: colors.surfaceBase,
                border: Border(top: BorderSide(color: colors.lineSubtle)),
              ),
              child: SafeArea(
                top: false,
                child: Padding(
                  padding: const EdgeInsetsDirectional.symmetric(
                    horizontal: Spacing.s4,
                    vertical: Spacing.s3,
                  ),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      for (var i = 0; i < footer.length; i++) ...[
                        if (i > 0) const SizedBox(height: Spacing.s2),
                        footer[i],
                      ],
                    ],
                  ),
                ),
              ),
            ),
    );
  }
}

/// Title block of a flow page: `display/sm` title (a heading for screen readers) and an optional
/// `body/md` supporting paragraph.
class FlowHeading extends StatelessWidget {
  const FlowHeading({required this.title, this.body, super.key});

  final String title;
  final String? body;

  @override
  Widget build(BuildContext context) {
    final type = context.type;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Semantics(header: true, child: Text(title, style: type.displaySm)),
        if (body != null) ...[
          const SizedBox(height: Spacing.s3),
          Text(
            body!,
            style: type.bodyMd.copyWith(color: context.colors.contentSecondary),
          ),
        ],
      ],
    );
  }
}
