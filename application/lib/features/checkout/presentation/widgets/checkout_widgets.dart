import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/money.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:application/features/checkout/domain/entities/checkout.dart';
import 'package:application/features/checkout/presentation/controllers/checkout_controller.dart';
import 'package:application/shared/widgets/fit_amount.dart';
import 'package:application/shared/widgets/option_tile.dart';
import 'package:application/shared/widgets/sheet_header.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:flutter/material.dart';

/// Text for an option that cannot be chosen (07.3).
String optionBlockText(AppLocalizations l10n, OptionBlock block) =>
    switch (block) {
      OptionBlock.belowMargin => l10n.optionBelowMargin,
      OptionBlock.unavailable => l10n.optionUnavailable,
      OptionBlock.notInApp => l10n.optionNotInApp,
    };

/// A payment option (library `Payment Option`, 07.1 to 07.3): radio, the gateway's text label
/// (never a logo), its methods or charge currency, and the exact amount it charges. Options that
/// cannot be chosen stay visible, dimmed, with their reason, and are announced as disabled.
class PaymentOptionTile extends StatelessWidget {
  const PaymentOptionTile({
    required this.option,
    required this.selected,
    required this.onTap,
    this.walletBalance,
    super.key,
  });

  final PaymentOption option;
  final bool selected;
  final VoidCallback onTap;

  /// The wallet option's balance (M-A15): "Store credit · balance $40.00".
  final Money? walletBalance;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final block = blockOfOption(option);
    final enabled = block == null;
    final charge = moneyText(option.paymentAmount);
    final balance = walletBalance;
    final detail = block != null
        ? optionBlockText(l10n, block)
        : balance != null
        ? l10n.checkoutWalletBalance(ltrIsolate(moneyText(balance)))
        : option.notice == 'charged_in_payment_currency'
        ? l10n.checkoutChargedIn(option.paymentCurrency)
        : option.methods.isNotEmpty
        ? option.methods.map((m) => m.label).join(', ')
        : null;
    final active = selected && enabled;
    return Semantics(
      inMutuallyExclusiveGroup: true,
      checked: active,
      enabled: enabled,
      button: true,
      label: [option.label, ?detail, charge].join(', '),
      excludeSemantics: true,
      onTap: enabled ? onTap : null,
      child: Opacity(
        opacity: enabled ? 1 : 0.55,
        child: Material(
          color: active ? colors.accentPrimaryTint : colors.surfaceRaised,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(Radii.md),
            side: BorderSide(
              color: active ? colors.accentPrimary : colors.lineSubtle,
              width: active ? 1.5 : 1,
            ),
          ),
          clipBehavior: Clip.antiAlias,
          child: InkWell(
            onTap: enabled ? onTap : null,
            child: ConstrainedBox(
              constraints: const BoxConstraints(minHeight: 56),
              child: Padding(
                padding: const EdgeInsetsDirectional.symmetric(
                  horizontal: Spacing.s3,
                  vertical: Spacing.s3,
                ),
                child: Row(
                  children: [
                    Icon(
                      active
                          ? Icons.radio_button_checked
                          : Icons.radio_button_unchecked,
                      color: active
                          ? colors.accentPrimary
                          : colors.contentMuted,
                    ),
                    const SizedBox(width: Spacing.s3),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(option.label, style: type.labelLg),
                          if (detail != null)
                            Text(
                              detail,
                              style: type.bodySm.copyWith(
                                color: colors.contentSecondary,
                              ),
                            ),
                        ],
                      ),
                    ),
                    const SizedBox(width: Spacing.s2),
                    // At large text the amount scales down instead of squeezing the name out.
                    FitAmount(
                      maxWidth: 160,
                      child: DecoratedBox(
                        decoration: BoxDecoration(
                          color: colors.surfaceSunken,
                          borderRadius: BorderRadius.circular(Radii.xs),
                        ),
                        child: Padding(
                          padding: const EdgeInsetsDirectional.symmetric(
                            horizontal: Spacing.s2,
                            vertical: Spacing.s1,
                          ),
                          child: Text(
                            enabled ? charge : '–',
                            textDirection: TextDirection.ltr,
                            style: type.monoSm,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// The country field (07.1, 07.2): a select box that opens the searchable country sheet (07.4).
class CountryField extends StatelessWidget {
  const CountryField({
    required this.label,
    required this.countries,
    required this.selected,
    required this.onSelected,
    this.enabled = true,
    super.key,
  });

  final String label;
  final List<Country> countries;
  final Country? selected;
  final ValueChanged<String> onSelected;
  final bool enabled;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    Future<void> open() async {
      final code = await showCountrySheet(
        context,
        countries: countries,
        selected: selected?.code,
      );
      if (code != null) {
        onSelected(code);
      }
    }

    final value = selected?.name ?? l10n.checkoutChooseCountry;
    return Semantics(
      button: true,
      enabled: enabled,
      label: '$label, $value',
      excludeSemantics: true,
      onTap: enabled ? () => unawaited(open()) : null,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            label,
            style: type.labelSm.copyWith(color: colors.contentSecondary),
          ),
          const SizedBox(height: Spacing.s1_5),
          Material(
            color: colors.surfaceSunken,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(Radii.sm),
              side: BorderSide(color: colors.lineStrong),
            ),
            child: InkWell(
              onTap: enabled ? () => unawaited(open()) : null,
              child: ConstrainedBox(
                constraints: const BoxConstraints(minHeight: 48),
                child: Padding(
                  padding: const EdgeInsetsDirectional.symmetric(
                    horizontal: Spacing.s3,
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: Text(
                          value,
                          style: type.bodyMd.copyWith(
                            color: selected == null
                                ? colors.contentMuted
                                : colors.contentPrimary,
                          ),
                        ),
                      ),
                      Icon(Icons.expand_more, color: colors.contentSecondary),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// The country sheet (07.4): search and the checkout countries; returns the chosen code.
Future<String?> showCountrySheet(
  BuildContext context, {
  required List<Country> countries,
  String? selected,
}) => showModalBottomSheet<String>(
  context: context,
  isScrollControlled: true,
  backgroundColor: context.colors.surfaceRaised,
  builder: (sheet) => _CountrySheet(countries: countries, selected: selected),
);

class _CountrySheet extends StatefulWidget {
  const _CountrySheet({required this.countries, this.selected});

  final List<Country> countries;
  final String? selected;

  @override
  State<_CountrySheet> createState() => _CountrySheetState();
}

class _CountrySheetState extends State<_CountrySheet> {
  String _query = '';

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final query = _query.trim().toLowerCase();
    final shown = query.isEmpty
        ? widget.countries
        : widget.countries
              .where(
                (c) =>
                    c.name.toLowerCase().contains(query) ||
                    c.code.toLowerCase() == query,
              )
              .toList();
    return SafeArea(
      child: Padding(
        padding: EdgeInsetsDirectional.only(
          bottom: MediaQuery.viewInsetsOf(context).bottom,
        ),
        child: ConstrainedBox(
          constraints: BoxConstraints(
            maxHeight: MediaQuery.sizeOf(context).height * 0.8,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              SheetHeader(
                title: l10n.checkoutCountry,
                onClose: () => Navigator.of(context).pop(),
              ),
              Padding(
                padding: const EdgeInsetsDirectional.symmetric(
                  horizontal: Spacing.s4,
                ),
                child: TextField(
                  onChanged: (value) => setState(() => _query = value),
                  style: context.type.bodyMd,
                  decoration: InputDecoration(
                    hintText: l10n.checkoutSearchCountries,
                    prefixIcon: Icon(Icons.search, color: colors.contentMuted),
                  ),
                ),
              ),
              const SizedBox(height: Spacing.s2),
              if (shown.isEmpty)
                Padding(
                  padding: const EdgeInsetsDirectional.all(Spacing.s6),
                  child: Text(
                    l10n.checkoutNoCountryMatch,
                    textAlign: TextAlign.center,
                    style: context.type.bodyMd.copyWith(
                      color: colors.contentSecondary,
                    ),
                  ),
                )
              else
                Flexible(
                  child: ListView.separated(
                    shrinkWrap: true,
                    padding: const EdgeInsetsDirectional.all(Spacing.s4),
                    itemCount: shown.length,
                    separatorBuilder: (_, _) =>
                        const SizedBox(height: Spacing.s2),
                    itemBuilder: (context, index) => OptionTile(
                      title: shown[index].name,
                      selected: shown[index].code == widget.selected,
                      onTap: () => Navigator.of(context).pop(shown[index].code),
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Payment options loading (07.7).
class OptionsSkeleton extends StatelessWidget {
  const OptionsSkeleton({super.key});

  @override
  Widget build(BuildContext context) => const SkeletonRegion(
    child: Column(
      children: [
        SkeletonBlock(height: 56, radius: Radii.md),
        SizedBox(height: Spacing.s3),
        SkeletonBlock(height: 56, radius: Radii.md),
      ],
    ),
  );
}
