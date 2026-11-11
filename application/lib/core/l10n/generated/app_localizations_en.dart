// ignore: unused_import
import 'package:intl/intl.dart' as intl;

import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for English (`en`).
class AppLocalizationsEn extends AppLocalizations {
  AppLocalizationsEn([String locale = 'en']) : super(locale);

  @override
  String get appTitle => 'boxcards.shop';

  @override
  String get brandLogoLabel => 'boxcards.shop';

  @override
  String environmentBadge(String flavor) {
    String _temp0 = intl.Intl.selectLogic(flavor, {
      'dev': 'Development',
      'staging': 'Staging',
      'other': 'Production',
    });
    return '$_temp0';
  }

  @override
  String get switchLanguage => 'العربية';

  @override
  String get switchLanguageHint => 'Switch the app language to Arabic';

  @override
  String get maintenanceTitle => 'We\'ll be right back';

  @override
  String get maintenanceBody =>
      'The store is under maintenance. Please try again in a few minutes.';

  @override
  String get updateRequiredTitle => 'Update required';

  @override
  String get updateRequiredBody =>
      'This version of the app is no longer supported. Please update it from your app store.';

  @override
  String get notFoundTitle => 'Page not found';

  @override
  String get notFoundBody => 'The page you opened does not exist.';

  @override
  String get backToHome => 'Back to home';

  @override
  String get actionTryAgain => 'Try again';

  @override
  String get actionContinue => 'Continue';

  @override
  String get actionBack => 'Back';

  @override
  String get actionClose => 'Close';

  @override
  String get actionCancel => 'Cancel';

  @override
  String get actionNotNow => 'Not now';

  @override
  String get loadingLabel => 'Loading';

  @override
  String get showPassword => 'Show password';

  @override
  String get hidePassword => 'Hide password';

  @override
  String requestReference(String id) {
    return 'Reference: $id';
  }

  @override
  String get failureNetwork =>
      'You are offline. Check your connection and try again.';

  @override
  String get failureTimeout => 'The connection timed out. Please try again.';

  @override
  String get failureUnauthenticated =>
      'Your session has ended. Please sign in again.';

  @override
  String get failureForbidden => 'You can\'t do this with this account.';

  @override
  String get failureEmailUnverified => 'Verify your email to continue.';

  @override
  String get failureNotFound => 'We couldn\'t find what you were looking for.';

  @override
  String failureRateLimited(String time) {
    return 'Too many attempts. Try again in $time.';
  }

  @override
  String get failureRateLimitedNoTime =>
      'Too many attempts. Please wait and try again.';

  @override
  String get failureMaintenance =>
      'The store is under maintenance. Please try again shortly.';

  @override
  String get failureServer =>
      'Something went wrong on our side. Please try again.';

  @override
  String get failureValidation => 'Check the highlighted fields and try again.';

  @override
  String get failureRecentAuth => 'Confirm your password to continue.';

  @override
  String get failureGeneric => 'Something went wrong. Please try again.';

  @override
  String get splashChecking => 'Checking for updates…';

  @override
  String get offlineTitle => 'You are offline';

  @override
  String get offlineBody =>
      'Connect to Wi-Fi or mobile data to open the store. Your codes are never stored on this device.';

  @override
  String get startupErrorTitle => 'We couldn\'t open the store';

  @override
  String updateRequiredVersions(String installed, String minimum) {
    return 'Installed $installed · Minimum $minimum';
  }

  @override
  String get updateNow => 'Update now';

  @override
  String get updateStoreUnavailable =>
      'Open your app store and update boxcards.shop.';

  @override
  String get maintenanceOrdersSafeTitle => 'Your orders are safe';

  @override
  String get maintenanceOrdersSafeBody =>
      'Codes you already bought stay in your account.';

  @override
  String get contactSupport => 'Contact support';

  @override
  String get linkOpenFailed => 'We couldn\'t open the page. Try again later.';

  @override
  String onboardingStep(int current, int total) {
    return 'Step $current of $total';
  }

  @override
  String get onboardingLanguageTitle => 'Choose your language';

  @override
  String get onboardingLanguageBody => 'You can change it later in Settings.';

  @override
  String get languageEnglish => 'English';

  @override
  String get languageEnglishDetail => 'English (LTR)';

  @override
  String get languageArabic => 'العربية';

  @override
  String get languageArabicDetail => 'Arabic (RTL)';

  @override
  String get onboardingCurrencyTitle => 'Choose your display currency';

  @override
  String get onboardingCurrencyBody =>
      'Prices show in this currency. If you skip, we use your country\'s currency.';

  @override
  String get displayCurrencyLabel => 'Display currency';

  @override
  String get chargeNoticeTitle => 'Prices may be charged in USD';

  @override
  String get chargeNoticeBody =>
      'Some payment methods charge another currency. You always see the amount before paying.';

  @override
  String get startShopping => 'Start shopping';

  @override
  String get skipForNow => 'Skip for now';

  @override
  String get loginTitle => 'Sign in';

  @override
  String get loginSubtitle => 'Your codes and orders, on any device.';

  @override
  String get emailLabel => 'Email address';

  @override
  String get passwordLabel => 'Password';

  @override
  String get forgotPasswordLink => 'Forgot password?';

  @override
  String get signInAction => 'Sign in';

  @override
  String get newHere => 'New here?';

  @override
  String get createAccountLink => 'Create account';

  @override
  String get rateLimitedTitle => 'Too many attempts';

  @override
  String rateLimitedBody(int seconds) {
    return 'Try again in $seconds seconds.';
  }

  @override
  String tryAgainIn(String time) {
    return 'Try again in $time';
  }

  @override
  String get fieldRequired => 'This field is required.';

  @override
  String get emailInvalid => 'Enter a valid email address.';

  @override
  String get signInFailedTitle => 'Couldn\'t sign in';

  @override
  String get twoFactorAppBar => 'Two-step verification';

  @override
  String get twoFactorCodeTitle => 'Enter the 6-digit code';

  @override
  String get twoFactorCodeBody =>
      'Open your authenticator app. The code changes every 30 seconds.';

  @override
  String get otpSemantics => '6-digit code';

  @override
  String twoFactorExpiresIn(String time) {
    return 'This sign-in request expires in $time';
  }

  @override
  String get useRecoveryCode => 'Use a recovery code instead';

  @override
  String get recoveryTitle => 'Use a recovery code';

  @override
  String get recoveryBody =>
      'Enter one of the recovery codes you saved when you turned on two-step verification. Each code works once.';

  @override
  String get recoveryCodeLabel => 'Recovery code';

  @override
  String get useAuthenticatorCode => 'Use the authenticator code';

  @override
  String get verifyAction => 'Verify';

  @override
  String get twoFactorInvalid =>
      'That code is not valid. Check it and try again.';

  @override
  String get twoFactorExpiredTitle => 'Sign-in request expired';

  @override
  String get twoFactorExpiredBody =>
      'For your security, start again from the sign-in screen.';

  @override
  String get backToSignIn => 'Back to sign in';

  @override
  String get otpIncomplete => 'Enter all 6 digits.';

  @override
  String get registerTitle => 'Create account';

  @override
  String get fullNameLabel => 'Full name';

  @override
  String get passwordHelper =>
      'At least 8 characters with letters and numbers.';

  @override
  String get confirmPasswordLabel => 'Confirm password';

  @override
  String get acceptTerms => 'I accept the Terms and Privacy Policy';

  @override
  String get readTerms => 'Read the terms';

  @override
  String get termsRequired => 'Accept the terms to create an account.';

  @override
  String get passwordMismatch => 'The passwords do not match.';

  @override
  String formErrorsTitle(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: 'Check $count fields',
      one: 'Check 1 field',
    );
    return '$_temp0';
  }

  @override
  String get formErrorsBody => 'Fix the highlighted fields and try again.';

  @override
  String get haveAccount => 'Already have an account?';

  @override
  String get verifyEmailTitle => 'Check your inbox';

  @override
  String verifyEmailBody(String email) {
    return 'We sent a link to $email. Open it on this phone to verify your email. You need a verified email to reveal codes and write reviews.';
  }

  @override
  String get verifyEmailBodyNoEmail =>
      'We sent a link to your email address. Open it on this phone to verify your email. You need a verified email to reveal codes and write reviews.';

  @override
  String get verificationSent => 'Verification email sent.';

  @override
  String resendIn(String time) {
    return 'You can resend in $time';
  }

  @override
  String get resendEmail => 'Resend email';

  @override
  String get iVerified => 'I\'ve verified my email';

  @override
  String get stillUnverified =>
      'Your email is not verified yet. Open the link we sent, then try again.';

  @override
  String get emailVerifiedDone => 'Email verified. Thank you!';

  @override
  String get forgotTitle => 'Reset your password';

  @override
  String get forgotBody =>
      'Password resets happen on boxcards.shop. We\'ll open the reset page in your browser: enter your account email there and we\'ll send a reset link.';

  @override
  String get openResetPage => 'Open reset page';

  @override
  String get resetPageOpenedTitle => 'Check your browser';

  @override
  String get resetPageOpenedBody =>
      'Finish on boxcards.shop, then come back and sign in with your new password.';

  @override
  String get resetPageUnavailable =>
      'The reset page is not available right now. Try again later.';

  @override
  String get signOutAction => 'Sign out';

  @override
  String get signOutConfirmTitle => 'Sign out of boxcards.shop?';

  @override
  String get signOutConfirmBody =>
      'You\'ll need your password to sign in again. Biometric unlock is turned off on this device.';

  @override
  String get signedOutMessage => 'You\'re signed out.';

  @override
  String homeSignedInAs(String email) {
    return 'Signed in as $email';
  }

  @override
  String get homeSignedIn => 'You\'re signed in.';

  @override
  String get homeSignedOut => 'Sign in to see your orders and codes.';

  @override
  String get homeAccountTitle => 'Account';

  @override
  String get biometricOfferTitle => 'Unlock with biometrics?';

  @override
  String get biometricOfferBody =>
      'Use your fingerprint or face to open boxcards.shop after 5 minutes in the background, and to confirm it\'s you before showing codes. You can turn it off any time.';

  @override
  String get biometricTurnOn => 'Turn on';

  @override
  String get biometricPromptEnrol => 'Confirm to turn on biometric unlock';

  @override
  String get biometricPromptUnlock => 'Unlock boxcards.shop';

  @override
  String get biometricPromptConfirm => 'Confirm it\'s you';

  @override
  String get biometricEnabled => 'Biometric unlock is on.';

  @override
  String get biometricDisabled => 'Biometric unlock is off.';

  @override
  String get biometricUnavailable =>
      'This device has no fingerprint or face unlock set up.';

  @override
  String get biometricFailed =>
      'Biometric unlock could not be turned on. Try again later.';

  @override
  String get biometricSettingTitle => 'Biometric unlock';

  @override
  String get biometricSettingSubtitle => 'After 5 minutes in the background';

  @override
  String get passwordSheetTitle => 'Confirm your password';

  @override
  String get passwordSheetBody =>
      'For your security, enter your password to continue.';

  @override
  String get confirmAction => 'Confirm';

  @override
  String get lockTitle => 'boxcards.shop is locked';

  @override
  String get lockBody =>
      'Unlock with your fingerprint or face, or use your password.';

  @override
  String get lockUseBiometric => 'Unlock with biometrics';

  @override
  String get lockUsePassword => 'Use password';

  @override
  String get lockBiometricChanged =>
      'Your biometrics changed, so biometric unlock was turned off. Use your password.';

  @override
  String get unlockAction => 'Unlock';

  @override
  String get integrityTitle => 'This device may be rooted or jailbroken';

  @override
  String get integrityBody =>
      'Apps and codes are less protected on modified devices. You can keep using boxcards.shop; we\'ll ask you to confirm it\'s you before showing codes.';

  @override
  String get integrityAcknowledge => 'I understand';

  @override
  String get navHome => 'Home';

  @override
  String get navBrowse => 'Browse';

  @override
  String get navCart => 'Cart';

  @override
  String get navOrders => 'Orders';

  @override
  String get navAccount => 'Account';

  @override
  String get searchFieldHint => 'Search gift cards and top-ups';

  @override
  String get searchAction => 'Search';

  @override
  String currencyButtonLabel(String code) {
    return 'Display currency: $code';
  }

  @override
  String get seeAll => 'See all';

  @override
  String seeAllSemantics(String section) {
    return 'See all: $section';
  }

  @override
  String get loadErrorTitle => 'We couldn\'t load this page';

  @override
  String get homeErrorTitle => 'We couldn\'t load the store';

  @override
  String errorServerBody(String requestId) {
    return 'Something went wrong on our side. Request ID $requestId';
  }

  @override
  String get errorServerBodyNoId => 'Something went wrong on our side.';

  @override
  String get actionRetry => 'Retry';

  @override
  String get offlineBannerTitle => 'You are offline';

  @override
  String get offlineBannerBody =>
      'Prices and stock need a connection. We will refresh when you are back online.';

  @override
  String get noConnectionTitle => 'No connection';

  @override
  String get noConnectionBody => 'Check Wi-Fi or mobile data, then try again.';

  @override
  String heroSlideLabel(int index, int count, String title) {
    return 'Banner $index of $count: $title';
  }

  @override
  String get heroCarouselLabel => 'Offers';

  @override
  String get platformsTitle => 'Platforms';

  @override
  String get platformsIntro =>
      'Choose a platform to see its gift cards, wallet codes and top-ups.';

  @override
  String get platformsEmpty => 'No platforms yet';

  @override
  String get categoriesTitle => 'Categories';

  @override
  String get allCategory => 'All';

  @override
  String productCount(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count products',
      one: '1 product',
      zero: 'No products',
    );
    return '$_temp0';
  }

  @override
  String get allProductsTitle => 'All products';

  @override
  String get filtersChip => 'Filters';

  @override
  String filtersChipCount(int count) {
    return 'Filters · $count';
  }

  @override
  String sortChip(String sort) {
    return 'Sort: $sort';
  }

  @override
  String get sortPopular => 'Popular';

  @override
  String get sortPriceAsc => 'Price low to high';

  @override
  String get sortPriceDesc => 'Price high to low';

  @override
  String get sortNewest => 'Newest';

  @override
  String get sortName => 'Name';

  @override
  String get sortTitle => 'Sort';

  @override
  String get filtersTitle => 'Filters';

  @override
  String get filterDelivery => 'Delivery';

  @override
  String get deliveryInstantCode => 'Instant code';

  @override
  String get deliveryDirectTopup => 'Direct top-up';

  @override
  String get filterMinPrice => 'Min price';

  @override
  String get filterMaxPrice => 'Max price';

  @override
  String filterPriceHelper(String currency) {
    return 'In $currency';
  }

  @override
  String get filterPriceInvalid => 'Enter an amount like 5 or 5.50';

  @override
  String get filterPriceRange => 'Min price must not be above max price';

  @override
  String get filtersClear => 'Clear';

  @override
  String get filtersApply => 'Show results';

  @override
  String get listingEmptyTitle => 'No products match';

  @override
  String get listingEmptyBody => 'Try another price or remove a filter.';

  @override
  String get catalogEmptyTitle => 'No products here yet';

  @override
  String get catalogEmptyBody => 'New gift cards and top-ups are added often.';

  @override
  String get clearFilters => 'Clear filters';

  @override
  String get loadMoreFailed => 'Couldn\'t load more products.';

  @override
  String get badgeInstant => 'Instant';

  @override
  String get badgeTopup => 'Top-up';

  @override
  String get badgeSubscription => 'Subscription';

  @override
  String get productUnavailable => 'Unavailable';

  @override
  String get searchSuggestions => 'Suggestions';

  @override
  String get searchResults => 'Results';

  @override
  String get searchRecent => 'Recent searches';

  @override
  String get searchClearHistory => 'Clear';

  @override
  String get searchClearHistoryLabel => 'Clear recent searches';

  @override
  String searchRemoveTerm(String term) {
    return 'Remove $term';
  }

  @override
  String searchFillTerm(String term) {
    return 'Search for $term';
  }

  @override
  String get searchPopular => 'Popular';

  @override
  String searchNoResultsTitle(String query) {
    return 'No results for “$query”';
  }

  @override
  String get searchNoResultsBody =>
      'Check the spelling or try a platform name.';

  @override
  String get searchTypePlatform => 'Platform';

  @override
  String get searchTypeCategory => 'Category';

  @override
  String get searchTypeProduct => 'Product';

  @override
  String get searchFailed => 'Couldn\'t load suggestions.';

  @override
  String get currencySheetBody =>
      'Prices refresh in the new currency. Your cart is re-priced before checkout.';

  @override
  String get currencyApply => 'Apply';

  @override
  String currencyChanged(String code) {
    return 'Prices are now shown in $code.';
  }

  @override
  String get cartTitle => 'Cart';

  @override
  String get ordersTitle => 'Orders';

  @override
  String get accountTitle => 'Account';

  @override
  String get comingSoonTitle => 'Coming in the next update';

  @override
  String get comingSoonBody =>
      'This part of the app is on its way. Meanwhile you can use boxcards.shop on the web.';

  @override
  String get productTitle => 'Product';

  @override
  String get languageSetting => 'Language';

  @override
  String reviewsRating(int rating) {
    return 'Rated $rating out of 5';
  }

  @override
  String get verifiedPurchase => 'Verified purchase';

  @override
  String get notificationsComingSoon => 'Notifications';
}
