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
  String get languageSetting => 'Language';

  @override
  String reviewsRating(int rating) {
    return 'Rated $rating out of 5';
  }

  @override
  String get verifiedPurchase => 'Verified purchase';

  @override
  String get productLoadErrorTitle => 'We couldn\'t load this product';

  @override
  String get productNotFoundTitle => 'Product not available';

  @override
  String get productNotFoundBody =>
      'It may have been removed or is not sold in your region.';

  @override
  String get backToStore => 'Back to store';

  @override
  String get productRegion => 'Region';

  @override
  String get productAnyRegion => 'All regions';

  @override
  String get productAmount => 'Amount';

  @override
  String productPay(String price) {
    return 'Pay $price';
  }

  @override
  String get variantSoldOut => 'Sold out';

  @override
  String get variantCustomAmount => 'Not available yet';

  @override
  String get variantUnavailable => 'Not available';

  @override
  String get variantLimited => 'Only a few left';

  @override
  String variantSemantics(String title, String detail) {
    return '$title, $detail';
  }

  @override
  String get priceMayVaryTitle => 'Price may vary';

  @override
  String get priceMayVaryBody => 'The final price is confirmed at checkout.';

  @override
  String get productUnavailableTitle => 'Not available right now';

  @override
  String get productUnavailableBody =>
      'None of this product\'s options can be bought at the moment. Check back later.';

  @override
  String get addFailedTitle => 'Couldn\'t add to cart';

  @override
  String productReviewsCount(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count reviews',
      one: '1 review',
      zero: 'No reviews',
    );
    return '$_temp0';
  }

  @override
  String productAverageRating(String rating) {
    return 'Average rating $rating out of 5';
  }

  @override
  String deliveryTime(int minutes) {
    String _temp0 = intl.Intl.pluralLogic(
      minutes,
      locale: localeName,
      other: 'About $minutes min',
      one: 'About 1 min',
      zero: 'Instant delivery',
    );
    return '$_temp0';
  }

  @override
  String get aboutProduct => 'About this product';

  @override
  String get howToRedeem => 'How to redeem';

  @override
  String get termsTitle => 'Terms';

  @override
  String get reviewsTitle => 'Reviews';

  @override
  String get reviewsEmptyTitle => 'No reviews yet';

  @override
  String get reviewsEmptyBody => 'Reviews from buyers appear here.';

  @override
  String get seeAllReviews => 'See all reviews';

  @override
  String get reviewsLoadFailed => 'Couldn\'t load reviews.';

  @override
  String get loadMoreReviewsFailed => 'Couldn\'t load more reviews.';

  @override
  String quantitySemantics(int count) {
    return 'Quantity $count';
  }

  @override
  String get increaseQuantity => 'Increase quantity';

  @override
  String get decreaseQuantity => 'Decrease quantity';

  @override
  String quantityLimit(int max) {
    return 'Up to $max per order';
  }

  @override
  String get priceLabel => 'Price';

  @override
  String priceTimesQuantity(String price, int count) {
    return '$price × $count';
  }

  @override
  String get addToCart => 'Add to cart';

  @override
  String get addedToCart => 'Added to cart.';

  @override
  String get viewCart => 'View cart';

  @override
  String cartBadgeSemantics(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count items',
      one: '1 item',
    );
    return '$_temp0';
  }

  @override
  String deliveryFieldRequired(String label) {
    return '$label is required.';
  }

  @override
  String deliveryFieldTooLong(String label, int max) {
    return '$label is too long (at most $max characters).';
  }

  @override
  String deliveryFieldDigits(String label) {
    return '$label must contain digits only.';
  }

  @override
  String get deliveryFieldEmail => 'Enter a valid email address.';

  @override
  String get deliveryFieldOption => 'Choose one of the options.';

  @override
  String deliveryFieldFormat(String label) {
    return '$label has an invalid format.';
  }

  @override
  String get selectPlaceholder => 'Choose';

  @override
  String galleryImage(int index, int count) {
    return 'Image $index of $count';
  }

  @override
  String get blockUnavailable => 'No longer available. Remove it to continue.';

  @override
  String get blockOutOfStock => 'Out of stock. Remove it to continue.';

  @override
  String get blockCustomAmount =>
      'This amount can\'t be bought. Remove it to continue.';

  @override
  String get blockQuantityLimit =>
      'Above the quantity limit. Lower the quantity.';

  @override
  String get blockNotSoldInCountry =>
      'Not sold in your country. Remove it to continue.';

  @override
  String get blockPricing => 'The price is being updated. Try again shortly.';

  @override
  String get blockOther => 'Not available right now. Remove it to continue.';

  @override
  String cartTitleCount(int count) {
    return 'Cart ($count)';
  }

  @override
  String get cartEmptyTitle => 'Your cart is empty';

  @override
  String get cartEmptyBody => 'Gift cards and top-ups you add will show here.';

  @override
  String get cartBrowseStore => 'Browse the store';

  @override
  String get cartLoadFailedTitle => 'We couldn\'t load your cart';

  @override
  String cartRemoveItem(String name) {
    return 'Remove $name';
  }

  @override
  String cartLineSemantics(
    String name,
    String variant,
    String price,
    int quantity,
  ) {
    return '$name, $variant, $price, quantity $quantity';
  }

  @override
  String get cartCouponLabel => 'Coupon code';

  @override
  String get cartCouponApply => 'Apply';

  @override
  String get cartCouponRemove => 'Remove';

  @override
  String cartCouponApplied(String code) {
    return '$code applied.';
  }

  @override
  String get cartCouponLimited => 'The discount was limited for this order.';

  @override
  String get cartSubtotal => 'Subtotal';

  @override
  String get cartDiscount => 'Discount';

  @override
  String cartDiscountCode(String code) {
    return 'Discount ($code)';
  }

  @override
  String get cartTotal => 'Total';

  @override
  String get cartCheckout => 'Checkout';

  @override
  String get cartCheckoutWeb => 'Complete purchase on boxcards.shop';

  @override
  String get cartCheckoutWebHint => 'Opens boxcards.shop in your browser.';

  @override
  String get cartCheckoutDisabled =>
      'Checkout isn\'t available in the app right now.';

  @override
  String get cartCheckoutBlocked =>
      'Remove or fix the marked items to continue.';

  @override
  String get cartSignInHint => 'You\'ll sign in first. Your cart is kept.';

  @override
  String get cartPricesUpdatedTitle => 'Prices were updated';

  @override
  String get cartPricesUpdatedBody =>
      'Exchange rates changed since you added these items. Review the new total.';

  @override
  String get cartPriceMayVaryTitle => 'Prices may change';

  @override
  String get cartPriceMayVaryBody =>
      'The final price is confirmed at checkout.';

  @override
  String get checkoutTitle => 'Checkout';

  @override
  String get checkoutEmailNote => 'Order emails go to this address';

  @override
  String get checkoutCountry => 'Country';

  @override
  String get checkoutChooseCountry => 'Choose your country';

  @override
  String get checkoutCountryHint =>
      'Choose your country to see the payment options.';

  @override
  String get checkoutSearchCountries => 'Search countries';

  @override
  String get checkoutNoCountryMatch => 'No country matches your search.';

  @override
  String get checkoutPayment => 'Payment';

  @override
  String checkoutChargedIn(String currency) {
    return 'Charged in $currency';
  }

  @override
  String checkoutChargeNoticeTitle(String gateway, String currency) {
    return '$gateway charges in $currency';
  }

  @override
  String checkoutChargeNoticeBody(
    String display,
    String gateway,
    String amount,
  ) {
    return 'You see prices in $display, but $gateway will charge $amount. Your bank may add a conversion fee.';
  }

  @override
  String get optionBelowMargin => 'Not available for this amount';

  @override
  String get optionUnavailable => 'Not available right now';

  @override
  String get optionNotInApp => 'Not available in the app yet';

  @override
  String get checkoutSomeUnavailableTitle => 'Some options are unavailable';

  @override
  String checkoutSomeUnavailableBody(String country) {
    return 'Options shown are the ones the store offers for $country.';
  }

  @override
  String get checkoutNoOptionsTitle => 'No payment option for this order';

  @override
  String get checkoutNoOptionsBody =>
      'Try another country, or complete the purchase on boxcards.shop.';

  @override
  String get checkoutAcceptTerms => 'I accept the Terms of Sale';

  @override
  String get checkoutTermsRequired => 'Accept the Terms of Sale to continue.';

  @override
  String get checkoutReadTerms => 'Read the Terms of Sale';

  @override
  String checkoutItems(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count items',
      one: '1 item',
    );
    return '$_temp0';
  }

  @override
  String checkoutChargedLine(String amount, String currency, String gateway) {
    return 'You will be charged $amount in $currency by $gateway.';
  }

  @override
  String get checkoutFeesNote =>
      'Prices include all fees. Codes are delivered to your account.';

  @override
  String get checkoutPayButton => 'Pay';

  @override
  String checkoutPay(String amount) {
    return 'Pay $amount';
  }

  @override
  String checkoutPayWallet(String amount) {
    return 'Pay $amount with wallet';
  }

  @override
  String checkoutContinueTo(String gateway) {
    return 'Continue to $gateway';
  }

  @override
  String get checkoutPriceChangedTitle => 'The price changed';

  @override
  String checkoutPriceChangedBody(String current, String previous) {
    return 'Your total is now $current (was $previous). Nothing was charged. Review the total and try again.';
  }

  @override
  String checkoutPriceChangedBodyUnknown(String previous) {
    return 'Your total changed (it was $previous). Nothing was charged. Review the total and try again.';
  }

  @override
  String get checkoutReviewNewTotal => 'Review new total';

  @override
  String get checkoutBackToCart => 'Back to cart';

  @override
  String get checkoutInProgressTitle => 'Your order is still being placed';

  @override
  String get checkoutInProgressBody =>
      'Wait a moment, then tap Pay again. You won\'t be charged twice.';

  @override
  String get checkoutCartAttentionTitle => 'Some items need attention';

  @override
  String get checkoutRateStaleTitle => 'Prices are being updated';

  @override
  String get checkoutRateStaleBody =>
      'Exchange rates are being refreshed. Try again in a moment.';

  @override
  String get checkoutFailedTitle => 'We couldn\'t place your order';

  @override
  String get checkoutQuoteFailedTitle =>
      'We couldn\'t load the payment options';

  @override
  String get checkoutWebTitle => 'Complete your purchase on the web';

  @override
  String get checkoutWebBody =>
      'Purchases are completed on boxcards.shop in your browser. Sign in there with the same account.';

  @override
  String get checkoutDisabledTitle => 'Checkout is unavailable';

  @override
  String get paymentProviderFallback => 'the payment provider';

  @override
  String get paymentInProgressTitle => 'Payment in progress';

  @override
  String paymentInProgressBody(String gateway) {
    return 'Finish paying in the $gateway window. We confirm the payment with $gateway before showing it as paid.';
  }

  @override
  String get paymentOpenFailed =>
      'We couldn\'t open the payment page. Try again.';

  @override
  String get paymentDontPayTwiceTitle => 'Don\'t pay twice';

  @override
  String get paymentDontPayTwiceBody =>
      'If you close the app, we resume here and check the payment again.';

  @override
  String paymentReturnTo(String gateway) {
    return 'Return to $gateway';
  }

  @override
  String get paymentCheckStatus => 'Check payment status';

  @override
  String get paymentConfirmingTitle => 'Checking your payment';

  @override
  String get paymentConfirmingBody =>
      'We\'re confirming the payment with the provider. This can take a minute.';

  @override
  String get paymentPaidTitle => 'Payment confirmed';

  @override
  String get paymentPaidBody =>
      'Your codes are being delivered. We\'ll let you know when they\'re ready.';

  @override
  String get paymentReceiptTitle => 'Your codes are in Orders';

  @override
  String get paymentReceiptBody =>
      'Open the order to see each code once it\'s delivered.';

  @override
  String get paymentViewOrder => 'View order';

  @override
  String get paymentKeepShopping => 'Keep shopping';

  @override
  String get paymentProcessingTitle => 'Still processing';

  @override
  String get paymentProcessingBody =>
      'Your payment provider hasn\'t confirmed yet. You don\'t need to pay again; the order updates as soon as it\'s confirmed.';

  @override
  String paymentLastChecked(String time) {
    return 'Last checked $time';
  }

  @override
  String get paymentCheckAgain => 'Check again';

  @override
  String get paymentGoToOrders => 'Go to orders';

  @override
  String get paymentFailedTitle => 'Payment not completed';

  @override
  String get paymentFailedBody =>
      'We couldn\'t confirm a payment for this order. You can try again with the same method.';

  @override
  String get paymentCancelledBody =>
      'You left the payment page before paying. You can try again with the same method.';

  @override
  String get paymentFailedFinalBody =>
      'This order can\'t be paid any more. Place the order again from your cart.';

  @override
  String paymentHeldUntil(String time) {
    return 'Your order is held until $time.';
  }

  @override
  String paymentAttempt(int number) {
    return 'Attempt $number';
  }

  @override
  String get paymentAnotherMethodTitle => 'Want another method?';

  @override
  String get paymentAnotherMethodBody =>
      'To use another payment method, place the order again from your cart.';

  @override
  String get paymentAttemptsExhausted =>
      'All payment attempts for this order were used. Place the order again from your cart.';

  @override
  String get paymentTryAgain => 'Try payment again';

  @override
  String get paymentExpiredTitle => 'Order expired';

  @override
  String get paymentExpiredBody =>
      'This order wasn\'t paid in time, so we released the items. Nothing was charged.';

  @override
  String get paymentReturnToCart => 'Return to cart';

  @override
  String get paymentContactSupport => 'Contact support';

  @override
  String get paymentNotStartedTitle => 'Payment provider unavailable';

  @override
  String paymentNotStartedBody(String gateway) {
    return '$gateway could not start the payment. Try again; your order is kept.';
  }

  @override
  String get paymentCallFailedTitle => 'We couldn\'t check the payment';

  @override
  String get paymentUnsupportedTitle =>
      'This payment can\'t be completed in the app';

  @override
  String get paymentUnsupportedBody =>
      'Check the payment status, or place the order again with another method.';

  @override
  String get paymentIdleTitle => 'Payment status';

  @override
  String get paymentIdleBody => 'Check this order\'s payment with the store.';

  @override
  String get paymentSummaryOrder => 'Order';

  @override
  String get paymentSummaryAmount => 'Amount';

  @override
  String get paymentSummaryPaidWith => 'Paid with';

  @override
  String get paymentSummaryStatus => 'Status';

  @override
  String get paymentStatusAwaiting => 'Awaiting payment';

  @override
  String get paymentStatusProcessing => 'Payment processing';

  @override
  String get paymentStatusPaid => 'Paid';

  @override
  String get paymentStatusFailed => 'Failed';

  @override
  String get paymentStatusExpired => 'Expired';

  @override
  String get paymentStatusUnknown => 'Not checked yet';

  @override
  String get ordersFilterAll => 'All';

  @override
  String get orderStatusCompleted => 'Completed';

  @override
  String get orderStatusProcessing => 'Processing';

  @override
  String get orderStatusPaymentProcessing => 'Payment processing';

  @override
  String get orderStatusAwaitingPayment => 'Awaiting payment';

  @override
  String get orderStatusPartlyDelivered => 'Partly delivered';

  @override
  String get orderStatusFailed => 'Failed';

  @override
  String get orderStatusCancelled => 'Cancelled';

  @override
  String get orderStatusRefundPending => 'Refund pending';

  @override
  String get orderStatusPartiallyRefunded => 'Partially refunded';

  @override
  String get orderStatusRefunded => 'Refunded';

  @override
  String ordersItemCount(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count items',
      one: '1 item',
    );
    return '$_temp0';
  }

  @override
  String orderTileSemantics(
    String number,
    String status,
    String date,
    String total,
  ) {
    return 'Order $number, $status, $date, $total';
  }

  @override
  String get ordersEmptyTitle => 'No orders yet';

  @override
  String get ordersEmptyBody => 'Codes you buy show up here.';

  @override
  String get ordersEmptyAction => 'Browse the store';

  @override
  String get ordersEmptyFilteredTitle => 'No orders with this status';

  @override
  String get ordersShowAll => 'Show all orders';

  @override
  String get ordersLoadErrorTitle => 'We couldn\'t load your orders';

  @override
  String get ordersMoreFailed => 'More orders couldn\'t load.';

  @override
  String get ordersSignedOutTitle => 'Sign in to see your orders';

  @override
  String get ordersSignedOutBody =>
      'Your orders and codes are kept in your account.';

  @override
  String get ordersSignIn => 'Sign in';

  @override
  String get guestAccessEntry => 'Open a guest order';

  @override
  String get guestAccessEntryBody =>
      'Ordered on the web without an account? Use the link in your delivery email.';

  @override
  String orderDetailSemantics(String number) {
    return 'Order $number';
  }

  @override
  String orderQtyEach(int quantity, String price) {
    return 'Qty $quantity · $price each';
  }

  @override
  String orderQty(int quantity) {
    return 'Qty $quantity';
  }

  @override
  String get orderUnitCode => 'Code';

  @override
  String get orderUnitDelivered => 'Delivered';

  @override
  String get orderUnitRevealed => 'Revealed';

  @override
  String get orderUnitPreparing => 'Preparing';

  @override
  String get orderUnitFailed => 'Not delivered';

  @override
  String get orderUnitRefunded => 'Refunded';

  @override
  String get orderUnitRefundPending => 'Refund pending';

  @override
  String get orderUnitCancelled => 'Cancelled';

  @override
  String orderUnitSemantics(int index, String name, String status) {
    return 'Code $index of $name, $status';
  }

  @override
  String get orderUnitOpenHint => 'Opens the code';

  @override
  String get orderTotal => 'Total';

  @override
  String get orderCharged => 'Charged';

  @override
  String get orderPayment => 'Payment';

  @override
  String orderAttempt(int attempt) {
    return 'attempt $attempt';
  }

  @override
  String get orderPaymentStatus => 'Payment status';

  @override
  String get orderPaymentUnpaid => 'Unpaid';

  @override
  String get orderPaymentProcessing => 'Processing';

  @override
  String get orderPaymentPaid => 'Paid';

  @override
  String get orderPaymentPartiallyRefunded => 'Partially refunded';

  @override
  String get orderPaymentRefunded => 'Refunded';

  @override
  String get orderRetryPayment => 'Retry payment';

  @override
  String get orderAwaitingPaymentNote =>
      'This order is waiting for payment. Your last attempt did not go through.';

  @override
  String get orderGetHelp => 'Get help';

  @override
  String get orderRequestRefund => 'Request refund';

  @override
  String get orderTimeline => 'Timeline';

  @override
  String get orderTimelinePlaced => 'Placed';

  @override
  String get orderTimelinePaid => 'Paid';

  @override
  String get orderRefundPendingTitle => 'Refund in progress';

  @override
  String get orderRefundPartialTitle => 'Part of this order was refunded';

  @override
  String get orderRefundFullTitle => 'This order was refunded';

  @override
  String orderRefundToWallet(String amount) {
    return '$amount was returned to your wallet.';
  }

  @override
  String orderRefundToOriginal(String amount) {
    return '$amount was returned to your payment method.';
  }

  @override
  String get orderNotFoundTitle => 'Order not found';

  @override
  String get orderNotFoundBody => 'This order isn\'t in your account.';

  @override
  String get orderLoadErrorTitle => 'We couldn\'t load this order';

  @override
  String get refundRequestTitle => 'Request a refund';

  @override
  String get revealTitle => 'Your code';

  @override
  String revealCodeOf(int index, int count) {
    return 'Code $index of $count';
  }

  @override
  String revealDeliveredAt(String date) {
    return 'Delivered $date';
  }

  @override
  String get revealCodeLabel => 'Code';

  @override
  String get revealPinLabel => 'PIN';

  @override
  String revealMaskedSemantics(String last) {
    return 'Code hidden. Ends in $last.';
  }

  @override
  String get revealMaskedSemanticsNoHint => 'Code hidden.';

  @override
  String get revealButton => 'Reveal code';

  @override
  String get revealButtonHint =>
      'Asks you to confirm it\'s you, then shows the code for 60 seconds';

  @override
  String get revealCopy => 'Copy code';

  @override
  String get revealCopySemantics =>
      'Copy the code. The clipboard clears after 60 seconds';

  @override
  String get revealHide => 'Hide';

  @override
  String get revealHideSemantics => 'Hide the code now';

  @override
  String get revealReadAloud => 'Read code aloud';

  @override
  String get revealShownSemantics =>
      'Code shown. Use Read code aloud to hear it.';

  @override
  String get revealCopied => 'Code copied. Clipboard clears in 60 s.';

  @override
  String revealHidesIn(String time) {
    return 'Hides in $time';
  }

  @override
  String get revealOpenRedemption => 'Open redemption page';

  @override
  String get revealSecureNoteAndroid =>
      'Screenshots are blocked here. Every reveal is logged.';

  @override
  String get revealSecureNoteIos =>
      'The code hides in the app switcher and while the screen is recorded. Every reveal is logged.';

  @override
  String get revealHowTo => 'How to redeem';

  @override
  String get revealHowToAfterReveal => 'Redemption steps appear with the code.';

  @override
  String get revealHiddenTimeout => 'Code hidden after 60 seconds.';

  @override
  String get revealHidden => 'Code hidden.';

  @override
  String get revealCaptureTitle => 'Screen recording detected';

  @override
  String get revealCaptureBody =>
      'On iPhone we can\'t block screenshots, so the code is hidden while recording or sharing.';

  @override
  String get revealCaptureHidden =>
      'Code hidden while the screen is being recorded or shared.';

  @override
  String get revealObscured => 'Hidden while the app is not in front';

  @override
  String get revealDeniedTitle => 'This code can\'t be shown';

  @override
  String get revealDeniedBody =>
      'Check that your email is verified and the order is paid. Every attempt is logged.';

  @override
  String get revealGuestDeniedBody =>
      'Open the link in your delivery email and confirm the emailed code on this device first.';

  @override
  String get revealUnverifiedTitle => 'Verify your email to reveal codes';

  @override
  String get revealUnverifiedBody => 'We sent a link to your email address.';

  @override
  String get revealVerifyAction => 'Verify email';

  @override
  String get revealRateLimitedTitle => 'Too many reveals';

  @override
  String revealRateLimitedBody(int minutes) {
    String _temp0 = intl.Intl.pluralLogic(
      minutes,
      locale: localeName,
      other: '$minutes minutes',
      one: '1 minute',
    );
    return 'For security you can reveal 10 codes per hour. Try again in $_temp0.';
  }

  @override
  String get revealRateLimitedLater =>
      'For security you can reveal 10 codes per hour. Try again later.';

  @override
  String revealTryAgainIn(String time) {
    return 'Try again in $time';
  }

  @override
  String get revealIntegrityTitle => 'This device may be modified';

  @override
  String get revealIntegrityBody =>
      'Rooted or jailbroken devices are less safe for codes. Reveal on another device if you can.';

  @override
  String get revealNotFoundTitle => 'Code not found';

  @override
  String get revealNotFoundBody => 'This code isn\'t in your orders.';

  @override
  String get revealFailedTitle => 'The code couldn\'t be shown';

  @override
  String get revealNotReady =>
      'This code isn\'t ready yet. We\'ll email you when it is.';

  @override
  String get revealBiometricPrompt => 'Confirm it\'s you to reveal the code';

  @override
  String get reauthTitle => 'Confirm it\'s you';

  @override
  String get reauthBody =>
      'Enter your password to reveal codes. We ask again after 15 minutes.';

  @override
  String get reauthConfirm => 'Confirm and reveal';

  @override
  String get reauthForgot => 'Forgot password?';

  @override
  String get guestOrderTitle => 'Guest order';

  @override
  String guestOrderNumberTitle(String number) {
    return 'Order $number';
  }

  @override
  String get guestVerifyTitle => 'Verify it\'s you';

  @override
  String get guestVerifyBody =>
      'To open the codes for this order we\'ll send a 6-digit code to the email you ordered with.';

  @override
  String get guestEmailLabel => 'Order email';

  @override
  String get guestLinkVerifiedTitle => 'Link verified';

  @override
  String get guestLinkVerifiedBody =>
      'You opened this order from the delivery email. The link is valid for 72 hours.';

  @override
  String get guestSendCode => 'Send code';

  @override
  String get guestCodeTitle => 'Enter the code';

  @override
  String guestCodeBody(String email) {
    return 'If $email matches the order, we sent it a 6-digit code. It expires in 10 minutes.';
  }

  @override
  String get guestCodeSemantics => '6-digit code from the email';

  @override
  String get guestVerify => 'Verify';

  @override
  String get guestResend => 'Resend code';

  @override
  String guestResendIn(String time) {
    return 'Resend in $time';
  }

  @override
  String get guestChangeEmail => 'Change email';

  @override
  String get guestOrderLabel => 'Guest order';

  @override
  String get guestAccountPromptTitle => 'Keep your codes in one place';

  @override
  String get guestAccountPromptBody =>
      'Create an account with your order email to see future orders here.';

  @override
  String get guestCreateAccount => 'Create account';

  @override
  String get guestLinkExpiredTitle => 'This link has expired';

  @override
  String get guestLinkExpiredBody =>
      'Order links work for 72 hours. Contact support to get a new one.';

  @override
  String get guestNeedLinkTitle => 'Open your delivery email';

  @override
  String get guestNeedLinkBody =>
      'To see the codes of a guest order on this device, open the link in the delivery email.';

  @override
  String get guestLinkProofMissing =>
      'Open the link in the delivery email again to reveal codes on this device.';

  @override
  String get guestEntryTitle => 'Open a guest order';

  @override
  String get guestEntryBody =>
      'Paste the link from your delivery email. We\'ll email you a code to confirm it\'s you.';

  @override
  String get guestEntryLabel => 'Order link';

  @override
  String get guestEntryInvalid => 'This isn\'t a boxcards.shop order link.';

  @override
  String get guestEntryOpen => 'Open order';

  @override
  String get orderGatewayWallet => 'Wallet';

  @override
  String get walletTitle => 'Wallet';

  @override
  String get walletAccountSubtitle => 'Store credit and statement';

  @override
  String get walletStoreCredit => 'Store credit';

  @override
  String get walletStatusActive => 'Active';

  @override
  String get walletStatusFrozen => 'Frozen';

  @override
  String get walletBalanceNote =>
      'From refunds and staff credits. It can pay for orders; there is no top-up.';

  @override
  String walletBalanceSemantics(String amount, String status) {
    return 'Store credit balance $amount, $status';
  }

  @override
  String get walletStatement => 'Statement';

  @override
  String get walletFrozenTitle => 'Wallet frozen';

  @override
  String get walletFrozenBody =>
      'You can’t pay with store credit right now. Refunds still arrive. Contact support to learn more.';

  @override
  String get walletContactSupport => 'Contact support';

  @override
  String get walletEmptyTitle => 'No transactions yet';

  @override
  String get walletEmptyBody => 'Refunds and credits will appear here.';

  @override
  String get walletLoadErrorTitle => 'We couldn\'t load your wallet';

  @override
  String get walletMoreFailed => 'We couldn\'t load more transactions';

  @override
  String get walletUnavailableTitle => 'Wallet isn\'t available';

  @override
  String get walletUnavailableBody =>
      'Store credit can\'t be viewed in the app right now.';

  @override
  String get walletTypeCreditRefund => 'Refund';

  @override
  String get walletTypeCreditAdmin => 'Credit from support';

  @override
  String get walletTypeDebitPurchase => 'Payment';

  @override
  String get walletTypeDebitAdmin => 'Adjustment by support';

  @override
  String get walletTypeReversal => 'Reversal';

  @override
  String get walletTypeOther => 'Wallet transaction';

  @override
  String walletLineWithOrder(String type, String order) {
    return '$type · $order';
  }

  @override
  String walletLineSemantics(
    String title,
    String amount,
    String balance,
    String date,
  ) {
    return '$title, $amount, balance $balance, $date';
  }

  @override
  String get walletLineOpenOrderHint => 'Opens the order';

  @override
  String checkoutWalletBalance(String amount) {
    return 'Store credit · balance $amount';
  }

  @override
  String get accountSignedOutTitle => 'Your account';

  @override
  String get accountSignedOutBody =>
      'Sign in to see your orders, codes and store credit.';

  @override
  String accountHeaderSemantics(String name, String email) {
    return 'Signed in as $name, $email';
  }

  @override
  String get accountVerifiedBadge => 'Verified';

  @override
  String get accountUnverifiedBadge => 'Not verified';

  @override
  String get accountNotifications => 'Notifications';

  @override
  String get accountSupport => 'Support';

  @override
  String get accountProfile => 'Profile';

  @override
  String get accountSecurity => 'Security';

  @override
  String get accountSettings => 'Settings';

  @override
  String get accountTwoFactorOn => 'Two-step verification on';

  @override
  String get accountTwoFactorOff => 'Two-step verification off';

  @override
  String get accountGuestSection => 'Ordered without an account?';

  @override
  String appVersionLabel(String version) {
    return 'Version $version';
  }

  @override
  String get settingsPreferences => 'Preferences';

  @override
  String get settingsAbout => 'About';

  @override
  String get settingsTerms => 'Terms of Sale';

  @override
  String get settingsPrivacy => 'Privacy policy';

  @override
  String get settingsRefundPolicy => 'Refund policy';

  @override
  String get settingsHelpCentre => 'Help centre';

  @override
  String get opensInBrowserHint => 'Opens in your browser';

  @override
  String get languageChanged => 'Language changed.';

  @override
  String get profileNameLabel => 'Full name';

  @override
  String get profileEmailLabel => 'Email';

  @override
  String get profileEmailHelper => 'Contact support to change your email.';

  @override
  String get profilePhoneLabel => 'Phone (optional)';

  @override
  String get profilePhoneHelper =>
      'With the country code, for example +20 100 123 4567.';

  @override
  String get profileCountryLabel => 'Country';

  @override
  String get profileRegionalSection => 'Language and currency';

  @override
  String get profileSave => 'Save changes';

  @override
  String get profileSaved => 'Profile saved.';

  @override
  String get profileUnchanged => 'Nothing to save.';

  @override
  String get profileNameTooLong => 'Use at most 120 characters.';

  @override
  String get profileUnverifiedTitle => 'Your email isn\'t verified';

  @override
  String get profileUnverifiedBody =>
      'Verify it to reveal codes and write reviews. Check your inbox for the link.';

  @override
  String get profileCountriesFailed => 'Countries couldn\'t be loaded.';

  @override
  String get changePasswordTitle => 'Change password';

  @override
  String get changePasswordSubtitle => 'Signs out your other devices';

  @override
  String get currentPasswordLabel => 'Current password';

  @override
  String get newPasswordLabel => 'New password';

  @override
  String get newPasswordHelper =>
      'At least 10 characters, different from your current password.';

  @override
  String get confirmNewPasswordLabel => 'Confirm new password';

  @override
  String get changePasswordNoticeTitle => 'Other devices will be signed out';

  @override
  String get changePasswordNoticeBody => 'This device stays signed in.';

  @override
  String get changePasswordAction => 'Update password';

  @override
  String get passwordChanged =>
      'Password changed. Your other devices were signed out.';

  @override
  String get passwordChangedBiometricTitle => 'Turn biometric unlock on again';

  @override
  String get passwordChangedBiometricBody =>
      'Changing your password turned biometric unlock off on every device. Turn it on below if you want it.';

  @override
  String get securitySignInSection => 'Sign-in';

  @override
  String get securityDevicesSection => 'Devices';

  @override
  String get twoFactorTitle => 'Two-step verification';

  @override
  String get twoFactorOffSubtitle =>
      'Off · protect codes with an authenticator app';

  @override
  String get twoFactorOnSubtitle =>
      'On · a code from your authenticator app at sign-in';

  @override
  String get twoFactorTurnOn => 'Turn on';

  @override
  String get twoFactorTurnOff => 'Turn off';

  @override
  String get devicesTitle => 'Signed-in devices';

  @override
  String devicesCount(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count devices',
      one: '1 device',
    );
    return '$_temp0';
  }

  @override
  String get securityRevealNoticeTitle =>
      'Revealing codes asks you to confirm it\'s you';

  @override
  String get securityRevealNoticeBody =>
      'At most every 15 minutes on each device: your password, or biometrics where biometric unlock is on.';

  @override
  String get signOutEverywhere => 'Sign out everywhere';

  @override
  String get signOutEverywhereTitle => 'Sign out everywhere?';

  @override
  String get signOutEverywhereBody =>
      'Every device, this one included, will need your password to sign in again.';

  @override
  String get signedOutEverywhere => 'Signed out on every device.';

  @override
  String get twoFactorPasswordBody =>
      'Enter your password to start. You\'ll need an authenticator app on your phone.';

  @override
  String get twoFactorScanStep => '1. Scan with your authenticator app';

  @override
  String get twoFactorQrSemantics => 'QR code for your authenticator app';

  @override
  String get twoFactorKeyLabel => 'Or enter this key';

  @override
  String get twoFactorCopyKey => 'Copy key';

  @override
  String get twoFactorKeyCopied =>
      'Key copied. The clipboard is cleared in 60 seconds.';

  @override
  String get twoFactorCodeStep => '2. Enter the 6-digit code';

  @override
  String get twoFactorCodeSemantics =>
      '6-digit code from your authenticator app';

  @override
  String get twoFactorCodeInvalid => 'Enter the 6 digits from your app.';

  @override
  String get twoFactorSecureNote =>
      'Keep this key private. Screenshots are blocked where your phone allows.';

  @override
  String get recoveryCodesTitle => 'Recovery codes';

  @override
  String get twoFactorOnBannerTitle => 'Two-step verification is on';

  @override
  String get twoFactorOnBannerBody =>
      'Next time you sign in, we\'ll ask for a code.';

  @override
  String get recoveryCodesBody =>
      'Save these codes somewhere safe. Each works once if you lose your phone. We will not show them again.';

  @override
  String recoveryCodesListSemantics(String codes) {
    return 'Recovery codes: $codes';
  }

  @override
  String get recoveryCodesCopy => 'Copy all';

  @override
  String get recoveryCodesCopied =>
      'Codes copied. The clipboard is cleared in 60 seconds.';

  @override
  String get recoveryCodesSaved => 'I saved my codes';

  @override
  String get recoveryCodesLeaveTitle => 'Saved your codes?';

  @override
  String get recoveryCodesLeaveBody =>
      'You won\'t see these codes again. Without them, losing your phone can lock you out.';

  @override
  String get recoveryCodesShowAgain => 'Back to codes';

  @override
  String get twoFactorAlreadyOnTitle => 'Two-step verification is already on';

  @override
  String get twoFactorAlreadyOnBody => 'To set it up again, turn it off first.';

  @override
  String get twoFactorEnabled => 'Two-step verification is on.';

  @override
  String get twoFactorDisableTitle => 'Turn off two-step verification?';

  @override
  String get twoFactorDisableBody =>
      'Your account will be protected by your password only. Enter it to continue.';

  @override
  String get twoFactorKeepOn => 'Keep it on';

  @override
  String get twoFactorDisabled => 'Two-step verification is off.';

  @override
  String get devicesPageTitle => 'Devices';

  @override
  String get deviceThisDevice => 'This device';

  @override
  String get deviceCurrentBadge => 'Current';

  @override
  String deviceLastUsed(String date) {
    return 'Last used $date';
  }

  @override
  String deviceIp(String ip) {
    return 'IP $ip';
  }

  @override
  String get deviceUnnamed => 'Unnamed device';

  @override
  String get deviceWebBrowser => 'Web browser';

  @override
  String get devicesWebNotListed => 'Web sessions are not listed here.';

  @override
  String get deviceSignOut => 'Sign out';

  @override
  String deviceSignOutSemantics(String name) {
    return 'Sign out $name';
  }

  @override
  String deviceRevokeTitle(String name) {
    return 'Sign out “$name”?';
  }

  @override
  String get deviceRevokeBody =>
      'That device will need your password to sign in again.';

  @override
  String get deviceRevokeAction => 'Sign out device';

  @override
  String deviceSignedOut(String name) {
    return '$name was signed out.';
  }

  @override
  String get devicesOnlyThis => 'No other device is signed in.';

  @override
  String get signOutOthersAction => 'Sign out of all other devices';

  @override
  String get signOutOthersTitle => 'Sign out of all other devices?';

  @override
  String get signOutOthersBody =>
      'Enter your password. This device stays signed in.';

  @override
  String get signOutOthersDone => 'Your other devices were signed out.';

  @override
  String get displayCurrencyAutomatic => 'Automatic';

  @override
  String get listMoreFailed => 'We couldn\'t load more';

  @override
  String get myReviewsTitle => 'My reviews';

  @override
  String get myReviewsLoadError => 'We couldn\'t load your reviews';

  @override
  String get myReviewsEmptyTitle => 'No reviews yet';

  @override
  String get myReviewsEmptyBody =>
      'After a code is delivered you can rate it here.';

  @override
  String get reviewsReadyTitle => 'Ready to review';

  @override
  String get reviewsYoursTitle => 'Your reviews';

  @override
  String reviewOrderNumber(String number) {
    return 'Order $number';
  }

  @override
  String get reviewWriteHint => 'Opens the review form';

  @override
  String get reviewWriteAction => 'Write a review';

  @override
  String reviewWriteSemantics(String product) {
    return 'Write a review of $product';
  }

  @override
  String get reviewWriteTitle => 'Write a review';

  @override
  String get reviewEditTitle => 'Edit review';

  @override
  String get reviewEditAction => 'Edit';

  @override
  String reviewEditSemantics(String product) {
    return 'Edit your review of $product';
  }

  @override
  String get reviewEditNoticeTitle => 'Edits are checked again';

  @override
  String get reviewEditNoticeBody =>
      'Your review goes back to moderation and is hidden until it is approved.';

  @override
  String get reviewStatusPending => 'Pending';

  @override
  String get reviewStatusApproved => 'Published';

  @override
  String get reviewStatusRejected => 'Not published';

  @override
  String get reviewRatingLabel => 'Your rating';

  @override
  String reviewRateStars(int count) {
    return '$count of 5 stars';
  }

  @override
  String get reviewRatingRequired => 'Choose a rating from 1 to 5 stars.';

  @override
  String get reviewTitleLabel => 'Title (optional)';

  @override
  String get reviewBodyLabel => 'Review (optional)';

  @override
  String get reviewModerationNote =>
      'Reviews are checked before they\'re published.';

  @override
  String get reviewSubmitAction => 'Submit review';

  @override
  String get reviewSaveAction => 'Save changes';

  @override
  String get reviewSubmitted =>
      'Thank you. Your review will appear once it\'s approved.';

  @override
  String get reviewUpdated =>
      'Saved. Your review will appear again once it\'s approved.';

  @override
  String get reviewLoadError => 'We couldn\'t load this review';

  @override
  String get reviewNotFoundTitle => 'Review not found';

  @override
  String get reviewNotEligibleTitle => 'This item can\'t be reviewed';

  @override
  String get reviewNotEligibleBody =>
      'Only items you bought and received can be reviewed, once each.';

  @override
  String get reviewEmailUnverifiedTitle => 'Verify your email to write reviews';

  @override
  String get reviewEmailUnverifiedBody =>
      'Open the link we sent to your email address, then try again.';

  @override
  String get reviewExistsTitle => 'You already reviewed this item';

  @override
  String get reviewExistsBody =>
      'Each purchased item can be reviewed once. You can edit your review in My reviews.';

  @override
  String supportOrderNumber(String number) {
    return 'Order $number';
  }

  @override
  String get supportSignedOutTitle => 'Sign in to contact support';

  @override
  String get supportSignedOutBody =>
      'Support requests are linked to your account and orders.';

  @override
  String get supportUnavailableTitle => 'Support isn\'t available in the app';

  @override
  String get supportUnavailableBody =>
      'You can still reach us through our help centre.';

  @override
  String get ticketsLoadError => 'We couldn\'t load your requests';

  @override
  String get ticketsEmptyTitle => 'No requests yet';

  @override
  String get ticketsEmptyBody =>
      'Questions about an order, payment or your account? We\'re here.';

  @override
  String get ticketNewAction => 'New request';

  @override
  String get ticketNewTitle => 'New request';

  @override
  String get ticketStatusOpen => 'Open';

  @override
  String get ticketStatusWaiting => 'Waiting for us';

  @override
  String get ticketStatusAnswered => 'Answered';

  @override
  String get ticketStatusResolved => 'Resolved';

  @override
  String get ticketStatusClosed => 'Closed';

  @override
  String get ticketCategoryOrder => 'Order';

  @override
  String get ticketCategoryPayment => 'Payment';

  @override
  String get ticketCategoryRefund => 'Refund';

  @override
  String get ticketCategoryAccount => 'Account';

  @override
  String get ticketCategoryOther => 'Other';

  @override
  String ticketUpdated(String topic, String date) {
    return '$topic · updated $date';
  }

  @override
  String get ticketTopicLabel => 'Topic';

  @override
  String get ticketOrderLabel => 'Order (optional)';

  @override
  String get ticketOrderNone => 'No order';

  @override
  String get ticketOrderLinked => 'Linked order';

  @override
  String get ticketOrdersFailed =>
      'Your orders couldn\'t be loaded. You can send the request without one.';

  @override
  String get ticketSubjectLabel => 'Subject';

  @override
  String ticketSubjectTooShort(int min) {
    return 'Use at least $min characters.';
  }

  @override
  String get ticketMessageLabel => 'Message';

  @override
  String get ticketMessageHelper =>
      'Never share codes or passwords. Attachments aren\'t supported, so describe the problem here.';

  @override
  String get ticketSendAction => 'Send request';

  @override
  String ticketThreadSemantics(String number) {
    return 'Support request $number';
  }

  @override
  String get ticketFromYou => 'You';

  @override
  String get ticketFromSupport => 'Support';

  @override
  String get ticketReplyHint => 'Write a reply…';

  @override
  String get ticketSendReply => 'Send reply';

  @override
  String get ticketReplyRequired => 'Write a message first.';

  @override
  String get ticketLoadError => 'We couldn\'t load this request';

  @override
  String get ticketNotFoundTitle => 'Request not found';

  @override
  String get ticketClosedTitle => 'This request is closed';

  @override
  String get ticketClosedBody =>
      'Replies are turned off. Start a new request if you still need help.';

  @override
  String get refundRequestsTitle => 'Refund requests';

  @override
  String get refundRequestsLoadError =>
      'We couldn\'t load your refund requests';

  @override
  String get refundRequestsEmptyTitle => 'No refund requests';

  @override
  String get refundRequestsEmptyBody =>
      'You can ask for a refund from a paid order\'s page.';

  @override
  String get refundRequestUnknownOrder => 'Order';

  @override
  String get refundStatusRequested => 'Requested';

  @override
  String get refundStatusApproved => 'Approved';

  @override
  String get refundStatusRejected => 'Not approved';

  @override
  String get refundStatusCompleted => 'Completed';

  @override
  String refundOrderPaid(String amount, String date) {
    return 'Paid $amount · $date';
  }

  @override
  String get refundReasonLabel => 'What went wrong?';

  @override
  String refundReasonHelper(int min, int max) {
    return '$min to $max characters. Revealed codes may not be refundable.';
  }

  @override
  String refundReasonTooShort(int min) {
    return 'Use at least $min characters.';
  }

  @override
  String get refundTicketLabel => 'Link a support request (optional)';

  @override
  String get refundTicketNone => 'None';

  @override
  String get refundHowTitle => 'How refunds work';

  @override
  String get refundHowBody =>
      'We review each request. Approved refunds go to your wallet or the original payment method.';

  @override
  String get refundSendAction => 'Send request';

  @override
  String get refundSentTitle => 'Request sent';

  @override
  String get refundSentBody => 'You can follow its status here.';

  @override
  String get refundOpenTitle => 'A request is already open for this order';

  @override
  String get refundOpenBody =>
      'You can follow it in Refund requests. One open request per order.';

  @override
  String get refundNotAllowedTitle => 'This order can\'t be refunded';

  @override
  String get refundOrderRefusedTitle =>
      'This order can\'t be used for a refund request';

  @override
  String orderRefundRequestStatus(String status) {
    return 'Refund request: $status';
  }

  @override
  String orderRefundRequestSent(String date) {
    return 'Sent $date. Tap to see your refund requests.';
  }

  @override
  String notificationBellUnread(int count) {
    return 'Notifications, $count unread';
  }

  @override
  String notificationsNewCount(int count) {
    return '$count new';
  }

  @override
  String notificationsUnreadCount(int count) {
    return '$count unread';
  }

  @override
  String get notificationsAllRead => 'All read';

  @override
  String get notificationsMarkAllRead => 'Mark all read';

  @override
  String get notificationsLoadError => 'We couldn\'t load your notifications';

  @override
  String get notificationsEmptyTitle => 'You\'re all caught up';

  @override
  String get notificationsEmptyBody =>
      'Order updates and support replies will appear here.';

  @override
  String get notificationUnread => 'Unread';

  @override
  String get notificationOpenHint => 'Opens the details';

  @override
  String get notificationJustNow => 'Just now';

  @override
  String notificationMinutesAgo(int count) {
    return '$count min ago';
  }

  @override
  String notificationHoursAgo(int count) {
    return '$count h ago';
  }

  @override
  String get notificationSettingsTitle => 'Notification settings';

  @override
  String get preferencesLoadError =>
      'We couldn\'t load your notification settings';

  @override
  String get preferenceCategoryOrders => 'Orders';

  @override
  String get preferenceCategorySupport => 'Support';

  @override
  String get preferenceCategoryMarketing => 'Offers and news';

  @override
  String get preferenceCategorySecurity => 'Security';

  @override
  String get preferenceChannelInbox => 'In the app';

  @override
  String get preferenceChannelMail => 'Email';

  @override
  String get preferenceChannelSms => 'SMS';

  @override
  String get preferenceRequired => 'Required';

  @override
  String get preferenceSmsNote => 'Only to a phone number on your profile';

  @override
  String preferenceSemantics(String category, String channel) {
    return '$category, $channel';
  }

  @override
  String preferenceSaveFailed(String reason) {
    return 'Not saved. $reason';
  }

  @override
  String get preferencePushTitle => 'Push notifications';

  @override
  String get preferenceChannelPush => 'Push notifications';

  @override
  String get preferencePushNote =>
      'On every phone where you are signed in and allow notifications';

  @override
  String get preferencePushUnavailable =>
      'Not available in this version of the app. Updates appear in the inbox and by email.';

  @override
  String get preferencePushOffTitle => 'Notifications are off on this phone';

  @override
  String get preferencePushOffBody =>
      'Allow notifications to get these updates on this phone.';

  @override
  String get preferencePushDeniedBody =>
      'If nothing happens, turn notifications on for boxcards.shop in your phone\'s settings.';

  @override
  String get pushExplainerTitle => 'Get order updates';

  @override
  String get pushExplainerBody =>
      'We can let you know right away when something happens with your orders.';

  @override
  String get pushExplainerOrders => 'When your order is ready';

  @override
  String get pushExplainerPayments => 'When a payment does not go through';

  @override
  String get pushExplainerSupport => 'When our support team replies';

  @override
  String get pushExplainerNoCodes =>
      'Codes are never sent in notifications. You open them in the app.';

  @override
  String get pushExplainerSettingsNote =>
      'You can change this any time in Settings > Notifications.';

  @override
  String get pushExplainerAllow => 'Turn on notifications';

  @override
  String get pushExplainerNotNow => 'Not now';

  @override
  String get pushOpen => 'Open';

  @override
  String get pushNewNotification => 'You have a new notification';
}
