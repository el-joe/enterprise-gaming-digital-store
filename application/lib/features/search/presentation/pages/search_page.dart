import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/link_navigation.dart';
import 'package:application/core/router/link_targets.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/search/domain/entities/search.dart';
import 'package:application/features/search/presentation/controllers/search_controller.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/pill_button.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Search (04.6, 04.7): suggestions while typing, results after submit, the local history
/// (clearable, per term or all) and popular terms. [initialQuery] (`?q=`, for example from a
/// `search` banner link) is submitted on open.
class SearchPage extends ConsumerStatefulWidget {
  const SearchPage({this.initialQuery, super.key});

  final String? initialQuery;

  @override
  ConsumerState<SearchPage> createState() => _SearchPageState();
}

class _SearchPageState extends ConsumerState<SearchPage> {
  late final _field = TextEditingController(text: widget.initialQuery);
  final _focus = FocusNode();

  StoreSearchController get _controller =>
      ref.read(storeSearchControllerProvider.notifier);

  @override
  void initState() {
    super.initState();
    final initial = widget.initialQuery;
    if (initial != null && initial.trim().isNotEmpty) {
      unawaited(Future.microtask(() => _controller.submit(initial)));
    } else {
      _focus.requestFocus();
    }
  }

  @override
  void didUpdateWidget(SearchPage oldWidget) {
    super.didUpdateWidget(oldWidget);
    final initial = widget.initialQuery;
    if (initial != oldWidget.initialQuery &&
        initial != null &&
        initial.trim().isNotEmpty) {
      _use(initial);
    }
  }

  @override
  void dispose() {
    _field.dispose();
    _focus.dispose();
    super.dispose();
  }

  /// Puts [term] in the field and submits it.
  void _use(String term) {
    _field.value = TextEditingValue(
      text: term,
      selection: TextSelection.collapsed(offset: term.length),
    );
    _focus.unfocus();
    unawaited(_controller.submit(term));
  }

  /// Puts [term] in the field to keep typing (suggestions follow).
  void _fill(String term) {
    _field.value = TextEditingValue(
      text: term,
      selection: TextSelection.collapsed(offset: term.length),
    );
    _controller.onQueryChanged(term);
    _focus.requestFocus();
  }

  void _openSuggestion(SearchSuggestion suggestion) => openLocation(
    context,
    suggestionLocation(
      type: suggestion.type,
      slug: suggestion.slug,
      title: suggestion.title,
    ),
  );

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final state = ref.watch(storeSearchControllerProvider);
    return Scaffold(
      body: SafeArea(
        bottom: false,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Padding(
              padding: const EdgeInsetsDirectional.fromSTEB(
                Spacing.s1,
                Spacing.s2,
                Spacing.s4,
                Spacing.s2,
              ),
              child: Row(
                children: [
                  SizedBox.square(
                    dimension: 48,
                    child: IconButton(
                      icon: const Icon(Icons.arrow_back),
                      tooltip: l10n.actionBack,
                      onPressed: () => context.canPop()
                          ? context.pop()
                          : context.go(Routes.browse.path),
                    ),
                  ),
                  const SizedBox(width: Spacing.s1),
                  Expanded(
                    child: TextField(
                      controller: _field,
                      focusNode: _focus,
                      textInputAction: TextInputAction.search,
                      maxLength: StoreSearchController.maxLength,
                      onChanged: _controller.onQueryChanged,
                      onSubmitted: (text) =>
                          unawaited(_controller.submit(text)),
                      style: context.type.bodyMd,
                      decoration: InputDecoration(
                        hintText: l10n.searchFieldHint,
                        counterText: '',
                        prefixIcon: Icon(
                          Icons.search,
                          size: 20,
                          color: colors.contentMuted,
                        ),
                        suffixIcon: state.query.isEmpty
                            ? null
                            : IconButton(
                                icon: const Icon(Icons.close),
                                tooltip: l10n.actionClose,
                                onPressed: () => _fill(''),
                              ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: state.noResults
                  ? Center(
                      child: SingleChildScrollView(
                        child: EmptyState(
                          icon: Icons.search_off,
                          title: l10n.searchNoResultsTitle(state.query),
                          body: l10n.searchNoResultsBody,
                        ),
                      ),
                    )
                  : ListView(
                      padding: const EdgeInsetsDirectional.only(
                        bottom: Spacing.s8,
                      ),
                      children: [
                        ..._suggestions(context, state),
                        ..._history(context, state),
                        ..._popular(context, state),
                      ],
                    ),
            ),
          ],
        ),
      ),
    );
  }

  List<Widget> _suggestions(BuildContext context, SearchState state) {
    final l10n = context.l10n;
    final suggestions = state.suggestions;
    if (suggestions == null) {
      return const [];
    }
    final title = state.submitted ? l10n.searchResults : l10n.searchSuggestions;
    return [
      _SectionTitle(title: title),
      ...switch (suggestions) {
        Loading() => [
          const SkeletonRegion(
            child: Padding(
              padding: EdgeInsetsDirectional.symmetric(
                horizontal: Spacing.s4,
                vertical: Spacing.s2,
              ),
              child: Column(
                children: [
                  SkeletonBlock(height: 40),
                  SizedBox(height: Spacing.s3),
                  SkeletonBlock(height: 40),
                ],
              ),
            ),
          ),
        ],
        Failed(:final failure) => [
          Padding(
            padding: const EdgeInsetsDirectional.symmetric(
              horizontal: Spacing.s4,
            ),
            child: Row(
              children: [
                Expanded(
                  child: Text(
                    '${l10n.searchFailed} ${failureMessage(l10n, failure)}',
                    style: context.type.bodySm.copyWith(
                      color: context.colors.contentSecondary,
                    ),
                  ),
                ),
                AppButton(
                  label: l10n.actionRetry,
                  variant: AppButtonVariant.ghost,
                  onPressed: () => unawaited(_controller.retry()),
                ),
              ],
            ),
          ),
        ],
        Loaded(:final value) => [
          for (final suggestion in value)
            _SuggestionRow(
              suggestion: suggestion,
              onOpen: () => _openSuggestion(suggestion),
              onFill: () => _fill(suggestion.title),
            ),
        ],
      },
    ];
  }

  List<Widget> _history(BuildContext context, SearchState state) {
    final l10n = context.l10n;
    if (state.history.isEmpty) {
      return const [];
    }
    return [
      _SectionTitle(
        title: l10n.searchRecent,
        action: Semantics(
          button: true,
          label: l10n.searchClearHistoryLabel,
          excludeSemantics: true,
          onTap: () => unawaited(_controller.clearHistory()),
          child: TextButton(
            onPressed: () => unawaited(_controller.clearHistory()),
            style: TextButton.styleFrom(
              minimumSize: const Size(48, 48),
              foregroundColor: context.colors.accentText,
            ),
            child: Text(l10n.searchClearHistory),
          ),
        ),
      ),
      for (final term in state.history)
        _HistoryRow(
          term: term,
          onTap: () => _use(term),
          onRemove: () => unawaited(_controller.removeFromHistory(term)),
        ),
    ];
  }

  List<Widget> _popular(BuildContext context, SearchState state) {
    final l10n = context.l10n;
    return switch (state.popular) {
      Loaded(:final value) when value.isNotEmpty => [
        _SectionTitle(title: l10n.searchPopular),
        Padding(
          padding: const EdgeInsetsDirectional.symmetric(
            horizontal: Spacing.s4,
          ),
          child: Wrap(
            spacing: Spacing.s2,
            children: [
              for (final term in value)
                PillButton(label: term, onTap: () => _use(term)),
            ],
          ),
        ),
      ],
      _ => const [],
    };
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle({required this.title, this.action});

  final String title;
  final Widget? action;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsetsDirectional.fromSTEB(
      Spacing.s4,
      Spacing.s3,
      Spacing.s2,
      Spacing.s1,
    ),
    child: ConstrainedBox(
      constraints: const BoxConstraints(minHeight: 48),
      child: Row(
        children: [
          Expanded(
            child: Semantics(
              header: true,
              child: Text(
                title,
                style: context.type.labelLg.copyWith(
                  color: context.colors.contentSecondary,
                ),
              ),
            ),
          ),
          ?action,
        ],
      ),
    ),
  );
}

String _typeLabel(AppLocalizations l10n, String type) => switch (type) {
  'gaming_platform' || 'platform' => l10n.searchTypePlatform,
  'category' => l10n.searchTypeCategory,
  _ => l10n.searchTypeProduct,
};

/// A full-height row: the main tap target (one labelled button node) and a trailing 48dp action.
class _ActionRow extends StatelessWidget {
  const _ActionRow({
    required this.label,
    required this.onTap,
    required this.leading,
    required this.content,
    required this.trailing,
  });

  final String label;
  final VoidCallback onTap;
  final IconData leading;
  final Widget content;
  final Widget trailing;

  @override
  Widget build(BuildContext context) => Row(
    children: [
      Expanded(
        child: Semantics(
          button: true,
          label: label,
          excludeSemantics: true,
          onTap: onTap,
          child: InkWell(
            onTap: onTap,
            child: ConstrainedBox(
              constraints: const BoxConstraints(minHeight: 56),
              child: Padding(
                padding: const EdgeInsetsDirectional.only(start: Spacing.s4),
                child: Row(
                  children: [
                    Icon(leading, size: 20, color: context.colors.contentMuted),
                    const SizedBox(width: Spacing.s4),
                    Expanded(child: content),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
      Padding(
        padding: const EdgeInsetsDirectional.only(end: Spacing.s2),
        child: SizedBox.square(dimension: 48, child: trailing),
      ),
    ],
  );
}

class _SuggestionRow extends StatelessWidget {
  const _SuggestionRow({
    required this.suggestion,
    required this.onOpen,
    required this.onFill,
  });

  final SearchSuggestion suggestion;
  final VoidCallback onOpen;
  final VoidCallback onFill;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = _typeLabel(l10n, suggestion.type);
    return _ActionRow(
      label: '${suggestion.title}, $type',
      onTap: onOpen,
      leading: Icons.search,
      content: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(suggestion.title, style: context.type.labelMd),
          Text(
            type,
            style: context.type.bodyCaption.copyWith(
              color: colors.contentMuted,
            ),
          ),
        ],
      ),
      trailing: IconButton(
        // Arrow toward the field, mirrored in RTL.
        icon: Transform.flip(
          flipX: Directionality.of(context) == TextDirection.rtl,
          child: const Icon(Icons.north_west, size: 20),
        ),
        tooltip: l10n.searchFillTerm(suggestion.title),
        color: colors.contentMuted,
        onPressed: onFill,
      ),
    );
  }
}

class _HistoryRow extends StatelessWidget {
  const _HistoryRow({
    required this.term,
    required this.onTap,
    required this.onRemove,
  });

  final String term;
  final VoidCallback onTap;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) => _ActionRow(
    label: term,
    onTap: onTap,
    leading: Icons.history,
    content: Text(term, style: context.type.bodyMd),
    trailing: IconButton(
      icon: const Icon(Icons.close, size: 20),
      tooltip: context.l10n.searchRemoveTerm(term),
      color: context.colors.contentMuted,
      onPressed: onRemove,
    ),
  );
}
