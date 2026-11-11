import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:intl/intl.dart' as intl;

import 'app_localizations_ar.dart';
import 'app_localizations_en.dart';

// ignore_for_file: type=lint

/// Callers can lookup localized strings with an instance of AppLocalizations
/// returned by `AppLocalizations.of(context)`.
///
/// Applications need to include `AppLocalizations.delegate()` in their app's
/// `localizationDelegates` list, and the locales they support in the app's
/// `supportedLocales` list. For example:
///
/// ```dart
/// import 'generated/app_localizations.dart';
///
/// return MaterialApp(
///   localizationsDelegates: AppLocalizations.localizationsDelegates,
///   supportedLocales: AppLocalizations.supportedLocales,
///   home: MyApplicationHome(),
/// );
/// ```
///
/// ## Update pubspec.yaml
///
/// Please make sure to update your pubspec.yaml to include the following
/// packages:
///
/// ```yaml
/// dependencies:
///   # Internationalization support.
///   flutter_localizations:
///     sdk: flutter
///   intl: any # Use the pinned version from flutter_localizations
///
///   # Rest of dependencies
/// ```
///
/// ## iOS Applications
///
/// iOS applications define key application metadata, including supported
/// locales, in an Info.plist file that is built into the application bundle.
/// To configure the locales supported by your app, you’ll need to edit this
/// file.
///
/// First, open your project’s ios/Runner.xcworkspace Xcode workspace file.
/// Then, in the Project Navigator, open the Info.plist file under the Runner
/// project’s Runner folder.
///
/// Next, select the Information Property List item, select Add Item from the
/// Editor menu, then select Localizations from the pop-up menu.
///
/// Select and expand the newly-created Localizations item then, for each
/// locale your application supports, add a new item and select the locale
/// you wish to add from the pop-up menu in the Value field. This list should
/// be consistent with the languages listed in the AppLocalizations.supportedLocales
/// property.
abstract class AppLocalizations {
  AppLocalizations(String locale)
    : localeName = intl.Intl.canonicalizedLocale(locale.toString());

  final String localeName;

  static AppLocalizations of(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations)!;
  }

  static const LocalizationsDelegate<AppLocalizations> delegate =
      _AppLocalizationsDelegate();

  /// A list of this localizations delegate along with the default localizations
  /// delegates.
  ///
  /// Returns a list of localizations delegates containing this delegate along with
  /// GlobalMaterialLocalizations.delegate, GlobalCupertinoLocalizations.delegate,
  /// and GlobalWidgetsLocalizations.delegate.
  ///
  /// Additional delegates can be added by appending to this list in
  /// MaterialApp. This list does not have to be used at all if a custom list
  /// of delegates is preferred or required.
  static const List<LocalizationsDelegate<dynamic>> localizationsDelegates =
      <LocalizationsDelegate<dynamic>>[
        delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
      ];

  /// A list of this localizations delegate's supported locales.
  static const List<Locale> supportedLocales = <Locale>[
    Locale('ar'),
    Locale('en'),
  ];

  /// App name shown by the OS task switcher and in the header. Brand name, not translated.
  ///
  /// In en, this message translates to:
  /// **'boxcards.shop'**
  String get appTitle;

  /// Screen reader label of the brand logo.
  ///
  /// In en, this message translates to:
  /// **'boxcards.shop'**
  String get brandLogoLabel;

  /// Badge naming the build environment; hidden in production.
  ///
  /// In en, this message translates to:
  /// **'{flavor, select, dev{Development} staging{Staging} other{Production}}'**
  String environmentBadge(String flavor);

  /// Button that switches the app to the other language, written in that language.
  ///
  /// In en, this message translates to:
  /// **'العربية'**
  String get switchLanguage;

  /// Screen reader hint of the language switch button.
  ///
  /// In en, this message translates to:
  /// **'Switch the app language to Arabic'**
  String get switchLanguageHint;

  /// Title shown while the store is in maintenance.
  ///
  /// In en, this message translates to:
  /// **'We\'ll be right back'**
  String get maintenanceTitle;

  /// Body shown while the store is in maintenance.
  ///
  /// In en, this message translates to:
  /// **'The store is under maintenance. Please try again in a few minutes.'**
  String get maintenanceBody;

  /// Title shown when this app version is no longer supported.
  ///
  /// In en, this message translates to:
  /// **'Update required'**
  String get updateRequiredTitle;

  /// Body shown when this app version is no longer supported.
  ///
  /// In en, this message translates to:
  /// **'This version of the app is no longer supported. Please update it from your app store.'**
  String get updateRequiredBody;

  /// Title for an unknown route.
  ///
  /// In en, this message translates to:
  /// **'Page not found'**
  String get notFoundTitle;

  /// Body for an unknown route.
  ///
  /// In en, this message translates to:
  /// **'The page you opened does not exist.'**
  String get notFoundBody;

  /// Button returning to the home page.
  ///
  /// In en, this message translates to:
  /// **'Back to home'**
  String get backToHome;

  /// Button that repeats a failed action.
  ///
  /// In en, this message translates to:
  /// **'Try again'**
  String get actionTryAgain;

  /// Primary button moving to the next step.
  ///
  /// In en, this message translates to:
  /// **'Continue'**
  String get actionContinue;

  /// Screen reader label of the app bar back button.
  ///
  /// In en, this message translates to:
  /// **'Back'**
  String get actionBack;

  /// Screen reader label of a close button.
  ///
  /// In en, this message translates to:
  /// **'Close'**
  String get actionClose;

  /// Cancels a dialog or a system prompt.
  ///
  /// In en, this message translates to:
  /// **'Cancel'**
  String get actionCancel;

  /// Declines an optional offer.
  ///
  /// In en, this message translates to:
  /// **'Not now'**
  String get actionNotNow;

  /// Screen reader label of a progress indicator.
  ///
  /// In en, this message translates to:
  /// **'Loading'**
  String get loadingLabel;

  /// Tooltip of the button that reveals the typed password.
  ///
  /// In en, this message translates to:
  /// **'Show password'**
  String get showPassword;

  /// Tooltip of the button that hides the typed password.
  ///
  /// In en, this message translates to:
  /// **'Hide password'**
  String get hidePassword;

  /// Request id shown under a server error so support can find it.
  ///
  /// In en, this message translates to:
  /// **'Reference: {id}'**
  String requestReference(String id);

  /// No connection.
  ///
  /// In en, this message translates to:
  /// **'You are offline. Check your connection and try again.'**
  String get failureNetwork;

  /// Request timed out.
  ///
  /// In en, this message translates to:
  /// **'The connection timed out. Please try again.'**
  String get failureTimeout;

  /// 401 after refresh failed.
  ///
  /// In en, this message translates to:
  /// **'Your session has ended. Please sign in again.'**
  String get failureUnauthenticated;

  /// Generic 403.
  ///
  /// In en, this message translates to:
  /// **'You can\'t do this with this account.'**
  String get failureForbidden;

  /// 403 email_unverified.
  ///
  /// In en, this message translates to:
  /// **'Verify your email to continue.'**
  String get failureEmailUnverified;

  /// 404.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t find what you were looking for.'**
  String get failureNotFound;

  /// 429 with a wait time such as 0:58.
  ///
  /// In en, this message translates to:
  /// **'Too many attempts. Try again in {time}.'**
  String failureRateLimited(String time);

  /// 429 without a wait time.
  ///
  /// In en, this message translates to:
  /// **'Too many attempts. Please wait and try again.'**
  String get failureRateLimitedNoTime;

  /// 503 maintenance.
  ///
  /// In en, this message translates to:
  /// **'The store is under maintenance. Please try again shortly.'**
  String get failureMaintenance;

  /// 5xx or an unexpected response.
  ///
  /// In en, this message translates to:
  /// **'Something went wrong on our side. Please try again.'**
  String get failureServer;

  /// 422 validation without a field on screen.
  ///
  /// In en, this message translates to:
  /// **'Check the highlighted fields and try again.'**
  String get failureValidation;

  /// 423 recent authentication required.
  ///
  /// In en, this message translates to:
  /// **'Confirm your password to continue.'**
  String get failureRecentAuth;

  /// Any other failure.
  ///
  /// In en, this message translates to:
  /// **'Something went wrong. Please try again.'**
  String get failureGeneric;

  /// Shown under the logo while the app config is loading.
  ///
  /// In en, this message translates to:
  /// **'Checking for updates…'**
  String get splashChecking;

  /// Title of the offline banner on the splash screen.
  ///
  /// In en, this message translates to:
  /// **'You are offline'**
  String get offlineTitle;

  /// Body of the offline banner on the splash screen.
  ///
  /// In en, this message translates to:
  /// **'Connect to Wi-Fi or mobile data to open the store. Your codes are never stored on this device.'**
  String get offlineBody;

  /// Title of the banner when the app config request failed.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t open the store'**
  String get startupErrorTitle;

  /// Installed and minimum app versions on the update screen.
  ///
  /// In en, this message translates to:
  /// **'Installed {installed} · Minimum {minimum}'**
  String updateRequiredVersions(String installed, String minimum);

  /// Opens the store listing to update the app.
  ///
  /// In en, this message translates to:
  /// **'Update now'**
  String get updateNow;

  /// Shown when the store listing cannot be opened.
  ///
  /// In en, this message translates to:
  /// **'Open your app store and update boxcards.shop.'**
  String get updateStoreUnavailable;

  /// Info banner title on the maintenance screen.
  ///
  /// In en, this message translates to:
  /// **'Your orders are safe'**
  String get maintenanceOrdersSafeTitle;

  /// Info banner body on the maintenance screen.
  ///
  /// In en, this message translates to:
  /// **'Codes you already bought stay in your account.'**
  String get maintenanceOrdersSafeBody;

  /// Opens the support page.
  ///
  /// In en, this message translates to:
  /// **'Contact support'**
  String get contactSupport;

  /// A web page could not be opened.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t open the page. Try again later.'**
  String get linkOpenFailed;

  /// Onboarding progress label.
  ///
  /// In en, this message translates to:
  /// **'Step {current} of {total}'**
  String onboardingStep(int current, int total);

  /// Onboarding step 1 title.
  ///
  /// In en, this message translates to:
  /// **'Choose your language'**
  String get onboardingLanguageTitle;

  /// Onboarding step 1 body.
  ///
  /// In en, this message translates to:
  /// **'You can change it later in Settings.'**
  String get onboardingLanguageBody;

  /// Name of English, always written in English.
  ///
  /// In en, this message translates to:
  /// **'English'**
  String get languageEnglish;

  /// Detail line of the English option.
  ///
  /// In en, this message translates to:
  /// **'English (LTR)'**
  String get languageEnglishDetail;

  /// Name of Arabic, always written in Arabic.
  ///
  /// In en, this message translates to:
  /// **'العربية'**
  String get languageArabic;

  /// Detail line of the Arabic option.
  ///
  /// In en, this message translates to:
  /// **'Arabic (RTL)'**
  String get languageArabicDetail;

  /// Onboarding step 2 title.
  ///
  /// In en, this message translates to:
  /// **'Choose your display currency'**
  String get onboardingCurrencyTitle;

  /// Onboarding step 2 body.
  ///
  /// In en, this message translates to:
  /// **'Prices show in this currency. If you skip, we use your country\'s currency.'**
  String get onboardingCurrencyBody;

  /// Label above the currency chips.
  ///
  /// In en, this message translates to:
  /// **'Display currency'**
  String get displayCurrencyLabel;

  /// Info banner title about the charge currency.
  ///
  /// In en, this message translates to:
  /// **'Prices may be charged in USD'**
  String get chargeNoticeTitle;

  /// Info banner body about the charge currency.
  ///
  /// In en, this message translates to:
  /// **'Some payment methods charge another currency. You always see the amount before paying.'**
  String get chargeNoticeBody;

  /// Finishes onboarding.
  ///
  /// In en, this message translates to:
  /// **'Start shopping'**
  String get startShopping;

  /// Finishes onboarding without choosing.
  ///
  /// In en, this message translates to:
  /// **'Skip for now'**
  String get skipForNow;

  /// Sign-in page title.
  ///
  /// In en, this message translates to:
  /// **'Sign in'**
  String get loginTitle;

  /// Sign-in page subtitle.
  ///
  /// In en, this message translates to:
  /// **'Your codes and orders, on any device.'**
  String get loginSubtitle;

  /// Email field label.
  ///
  /// In en, this message translates to:
  /// **'Email address'**
  String get emailLabel;

  /// Password field label.
  ///
  /// In en, this message translates to:
  /// **'Password'**
  String get passwordLabel;

  /// Link to the forgot-password page.
  ///
  /// In en, this message translates to:
  /// **'Forgot password?'**
  String get forgotPasswordLink;

  /// Sign-in button.
  ///
  /// In en, this message translates to:
  /// **'Sign in'**
  String get signInAction;

  /// Text before the create-account link.
  ///
  /// In en, this message translates to:
  /// **'New here?'**
  String get newHere;

  /// Link to the register page.
  ///
  /// In en, this message translates to:
  /// **'Create account'**
  String get createAccountLink;

  /// Rate-limit banner title.
  ///
  /// In en, this message translates to:
  /// **'Too many attempts'**
  String get rateLimitedTitle;

  /// Rate-limit banner body with the seconds left.
  ///
  /// In en, this message translates to:
  /// **'Try again in {seconds} seconds.'**
  String rateLimitedBody(int seconds);

  /// Disabled button label during a rate limit, with m:ss.
  ///
  /// In en, this message translates to:
  /// **'Try again in {time}'**
  String tryAgainIn(String time);

  /// Local check: empty field.
  ///
  /// In en, this message translates to:
  /// **'This field is required.'**
  String get fieldRequired;

  /// Local check: malformed email.
  ///
  /// In en, this message translates to:
  /// **'Enter a valid email address.'**
  String get emailInvalid;

  /// Banner title when sign-in failed for a reason other than fields.
  ///
  /// In en, this message translates to:
  /// **'Couldn\'t sign in'**
  String get signInFailedTitle;

  /// Two-factor page app bar title.
  ///
  /// In en, this message translates to:
  /// **'Two-step verification'**
  String get twoFactorAppBar;

  /// Two-factor code title.
  ///
  /// In en, this message translates to:
  /// **'Enter the 6-digit code'**
  String get twoFactorCodeTitle;

  /// Two-factor code body.
  ///
  /// In en, this message translates to:
  /// **'Open your authenticator app. The code changes every 30 seconds.'**
  String get twoFactorCodeBody;

  /// Screen reader label of the code boxes.
  ///
  /// In en, this message translates to:
  /// **'6-digit code'**
  String get otpSemantics;

  /// Challenge expiry countdown, m:ss.
  ///
  /// In en, this message translates to:
  /// **'This sign-in request expires in {time}'**
  String twoFactorExpiresIn(String time);

  /// Switches to recovery code mode.
  ///
  /// In en, this message translates to:
  /// **'Use a recovery code instead'**
  String get useRecoveryCode;

  /// Recovery code title.
  ///
  /// In en, this message translates to:
  /// **'Use a recovery code'**
  String get recoveryTitle;

  /// Recovery code body.
  ///
  /// In en, this message translates to:
  /// **'Enter one of the recovery codes you saved when you turned on two-step verification. Each code works once.'**
  String get recoveryBody;

  /// Recovery code field label.
  ///
  /// In en, this message translates to:
  /// **'Recovery code'**
  String get recoveryCodeLabel;

  /// Switches back to the 6-digit code.
  ///
  /// In en, this message translates to:
  /// **'Use the authenticator code'**
  String get useAuthenticatorCode;

  /// Submits the two-factor code.
  ///
  /// In en, this message translates to:
  /// **'Verify'**
  String get verifyAction;

  /// two_factor_invalid.
  ///
  /// In en, this message translates to:
  /// **'That code is not valid. Check it and try again.'**
  String get twoFactorInvalid;

  /// two_factor_expired banner title.
  ///
  /// In en, this message translates to:
  /// **'Sign-in request expired'**
  String get twoFactorExpiredTitle;

  /// two_factor_expired banner body.
  ///
  /// In en, this message translates to:
  /// **'For your security, start again from the sign-in screen.'**
  String get twoFactorExpiredBody;

  /// Returns to the sign-in page.
  ///
  /// In en, this message translates to:
  /// **'Back to sign in'**
  String get backToSignIn;

  /// Local check: incomplete code.
  ///
  /// In en, this message translates to:
  /// **'Enter all 6 digits.'**
  String get otpIncomplete;

  /// Register page title.
  ///
  /// In en, this message translates to:
  /// **'Create account'**
  String get registerTitle;

  /// Name field label.
  ///
  /// In en, this message translates to:
  /// **'Full name'**
  String get fullNameLabel;

  /// Password rules helper.
  ///
  /// In en, this message translates to:
  /// **'At least 8 characters with letters and numbers.'**
  String get passwordHelper;

  /// Password confirmation field label.
  ///
  /// In en, this message translates to:
  /// **'Confirm password'**
  String get confirmPasswordLabel;

  /// Terms checkbox label.
  ///
  /// In en, this message translates to:
  /// **'I accept the Terms and Privacy Policy'**
  String get acceptTerms;

  /// Opens the terms page.
  ///
  /// In en, this message translates to:
  /// **'Read the terms'**
  String get readTerms;

  /// Local check: terms not accepted.
  ///
  /// In en, this message translates to:
  /// **'Accept the terms to create an account.'**
  String get termsRequired;

  /// Local check: confirmation differs.
  ///
  /// In en, this message translates to:
  /// **'The passwords do not match.'**
  String get passwordMismatch;

  /// Summary banner title with the number of fields in error.
  ///
  /// In en, this message translates to:
  /// **'{count, plural, =1{Check 1 field} other{Check {count} fields}}'**
  String formErrorsTitle(int count);

  /// Summary banner body.
  ///
  /// In en, this message translates to:
  /// **'Fix the highlighted fields and try again.'**
  String get formErrorsBody;

  /// Text before the sign-in link on register.
  ///
  /// In en, this message translates to:
  /// **'Already have an account?'**
  String get haveAccount;

  /// Verify-email title.
  ///
  /// In en, this message translates to:
  /// **'Check your inbox'**
  String get verifyEmailTitle;

  /// Verify-email body with the address.
  ///
  /// In en, this message translates to:
  /// **'We sent a link to {email}. Open it on this phone to verify your email. You need a verified email to reveal codes and write reviews.'**
  String verifyEmailBody(String email);

  /// Verify-email body before the address is known.
  ///
  /// In en, this message translates to:
  /// **'We sent a link to your email address. Open it on this phone to verify your email. You need a verified email to reveal codes and write reviews.'**
  String get verifyEmailBodyNoEmail;

  /// Snackbar after a resend.
  ///
  /// In en, this message translates to:
  /// **'Verification email sent.'**
  String get verificationSent;

  /// Resend cooldown, m:ss.
  ///
  /// In en, this message translates to:
  /// **'You can resend in {time}'**
  String resendIn(String time);

  /// Resend button.
  ///
  /// In en, this message translates to:
  /// **'Resend email'**
  String get resendEmail;

  /// Re-checks the verification status.
  ///
  /// In en, this message translates to:
  /// **'I\'ve verified my email'**
  String get iVerified;

  /// Snackbar when the email is still unverified.
  ///
  /// In en, this message translates to:
  /// **'Your email is not verified yet. Open the link we sent, then try again.'**
  String get stillUnverified;

  /// Snackbar when the email is verified.
  ///
  /// In en, this message translates to:
  /// **'Email verified. Thank you!'**
  String get emailVerifiedDone;

  /// Forgot-password title.
  ///
  /// In en, this message translates to:
  /// **'Reset your password'**
  String get forgotTitle;

  /// Forgot-password body.
  ///
  /// In en, this message translates to:
  /// **'Password resets happen on boxcards.shop. We\'ll open the reset page in your browser: enter your account email there and we\'ll send a reset link.'**
  String get forgotBody;

  /// Opens the web reset page.
  ///
  /// In en, this message translates to:
  /// **'Open reset page'**
  String get openResetPage;

  /// Banner after the reset page opened.
  ///
  /// In en, this message translates to:
  /// **'Check your browser'**
  String get resetPageOpenedTitle;

  /// Banner body after the reset page opened.
  ///
  /// In en, this message translates to:
  /// **'Finish on boxcards.shop, then come back and sign in with your new password.'**
  String get resetPageOpenedBody;

  /// No reset URL in the app config.
  ///
  /// In en, this message translates to:
  /// **'The reset page is not available right now. Try again later.'**
  String get resetPageUnavailable;

  /// Sign-out button.
  ///
  /// In en, this message translates to:
  /// **'Sign out'**
  String get signOutAction;

  /// Sign-out dialog title.
  ///
  /// In en, this message translates to:
  /// **'Sign out of boxcards.shop?'**
  String get signOutConfirmTitle;

  /// Sign-out dialog body.
  ///
  /// In en, this message translates to:
  /// **'You\'ll need your password to sign in again. Biometric unlock is turned off on this device.'**
  String get signOutConfirmBody;

  /// Snackbar after signing out.
  ///
  /// In en, this message translates to:
  /// **'You\'re signed out.'**
  String get signedOutMessage;

  /// Account card line on home.
  ///
  /// In en, this message translates to:
  /// **'Signed in as {email}'**
  String homeSignedInAs(String email);

  /// Account card line before the email is known.
  ///
  /// In en, this message translates to:
  /// **'You\'re signed in.'**
  String get homeSignedIn;

  /// Account card line when signed out.
  ///
  /// In en, this message translates to:
  /// **'Sign in to see your orders and codes.'**
  String get homeSignedOut;

  /// Account card title on home.
  ///
  /// In en, this message translates to:
  /// **'Account'**
  String get homeAccountTitle;

  /// Biometric offer title.
  ///
  /// In en, this message translates to:
  /// **'Unlock with biometrics?'**
  String get biometricOfferTitle;

  /// Biometric offer body.
  ///
  /// In en, this message translates to:
  /// **'Use your fingerprint or face to open boxcards.shop after 5 minutes in the background, and to confirm it\'s you before showing codes. You can turn it off any time.'**
  String get biometricOfferBody;

  /// Turns biometric unlock on.
  ///
  /// In en, this message translates to:
  /// **'Turn on'**
  String get biometricTurnOn;

  /// System biometric prompt when enrolling.
  ///
  /// In en, this message translates to:
  /// **'Confirm to turn on biometric unlock'**
  String get biometricPromptEnrol;

  /// System biometric prompt when unlocking.
  ///
  /// In en, this message translates to:
  /// **'Unlock boxcards.shop'**
  String get biometricPromptUnlock;

  /// System biometric prompt for re-authentication.
  ///
  /// In en, this message translates to:
  /// **'Confirm it\'s you'**
  String get biometricPromptConfirm;

  /// Snackbar after enrolling.
  ///
  /// In en, this message translates to:
  /// **'Biometric unlock is on.'**
  String get biometricEnabled;

  /// Snackbar after turning off.
  ///
  /// In en, this message translates to:
  /// **'Biometric unlock is off.'**
  String get biometricDisabled;

  /// Biometrics unavailable.
  ///
  /// In en, this message translates to:
  /// **'This device has no fingerprint or face unlock set up.'**
  String get biometricUnavailable;

  /// Enrolment failed.
  ///
  /// In en, this message translates to:
  /// **'Biometric unlock could not be turned on. Try again later.'**
  String get biometricFailed;

  /// Biometric switch title.
  ///
  /// In en, this message translates to:
  /// **'Biometric unlock'**
  String get biometricSettingTitle;

  /// Biometric switch subtitle.
  ///
  /// In en, this message translates to:
  /// **'After 5 minutes in the background'**
  String get biometricSettingSubtitle;

  /// Password confirmation sheet title.
  ///
  /// In en, this message translates to:
  /// **'Confirm your password'**
  String get passwordSheetTitle;

  /// Password confirmation sheet body.
  ///
  /// In en, this message translates to:
  /// **'For your security, enter your password to continue.'**
  String get passwordSheetBody;

  /// Confirms the password.
  ///
  /// In en, this message translates to:
  /// **'Confirm'**
  String get confirmAction;

  /// App lock title.
  ///
  /// In en, this message translates to:
  /// **'boxcards.shop is locked'**
  String get lockTitle;

  /// App lock body.
  ///
  /// In en, this message translates to:
  /// **'Unlock with your fingerprint or face, or use your password.'**
  String get lockBody;

  /// App lock biometric button.
  ///
  /// In en, this message translates to:
  /// **'Unlock with biometrics'**
  String get lockUseBiometric;

  /// App lock password option.
  ///
  /// In en, this message translates to:
  /// **'Use password'**
  String get lockUsePassword;

  /// Shown when the key was invalidated.
  ///
  /// In en, this message translates to:
  /// **'Your biometrics changed, so biometric unlock was turned off. Use your password.'**
  String get lockBiometricChanged;

  /// App lock password submit.
  ///
  /// In en, this message translates to:
  /// **'Unlock'**
  String get unlockAction;

  /// Device integrity sheet title.
  ///
  /// In en, this message translates to:
  /// **'This device may be rooted or jailbroken'**
  String get integrityTitle;

  /// Device integrity sheet body.
  ///
  /// In en, this message translates to:
  /// **'Apps and codes are less protected on modified devices. You can keep using boxcards.shop; we\'ll ask you to confirm it\'s you before showing codes.'**
  String get integrityBody;

  /// Closes the device integrity sheet.
  ///
  /// In en, this message translates to:
  /// **'I understand'**
  String get integrityAcknowledge;

  /// Bottom navigation tab.
  ///
  /// In en, this message translates to:
  /// **'Home'**
  String get navHome;

  /// Bottom navigation tab.
  ///
  /// In en, this message translates to:
  /// **'Browse'**
  String get navBrowse;

  /// Bottom navigation tab.
  ///
  /// In en, this message translates to:
  /// **'Cart'**
  String get navCart;

  /// Bottom navigation tab.
  ///
  /// In en, this message translates to:
  /// **'Orders'**
  String get navOrders;

  /// Bottom navigation tab.
  ///
  /// In en, this message translates to:
  /// **'Account'**
  String get navAccount;

  /// Search field placeholder.
  ///
  /// In en, this message translates to:
  /// **'Search gift cards and top-ups'**
  String get searchFieldHint;

  /// Search button tooltip.
  ///
  /// In en, this message translates to:
  /// **'Search'**
  String get searchAction;

  /// Semantics of the currency button in the home header.
  ///
  /// In en, this message translates to:
  /// **'Display currency: {code}'**
  String currencyButtonLabel(String code);

  /// Section link to the full list.
  ///
  /// In en, this message translates to:
  /// **'See all'**
  String get seeAll;

  /// Screen reader label of See all.
  ///
  /// In en, this message translates to:
  /// **'See all: {section}'**
  String seeAllSemantics(String section);

  /// Generic load error title.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load this page'**
  String get loadErrorTitle;

  /// Home error state title (03.3).
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load the store'**
  String get homeErrorTitle;

  /// Server error with the request id.
  ///
  /// In en, this message translates to:
  /// **'Something went wrong on our side. Request ID {requestId}'**
  String errorServerBody(String requestId);

  /// Server error without a request id.
  ///
  /// In en, this message translates to:
  /// **'Something went wrong on our side.'**
  String get errorServerBodyNoId;

  /// Retry button.
  ///
  /// In en, this message translates to:
  /// **'Retry'**
  String get actionRetry;

  /// Offline banner title (03.4).
  ///
  /// In en, this message translates to:
  /// **'You are offline'**
  String get offlineBannerTitle;

  /// Offline banner text.
  ///
  /// In en, this message translates to:
  /// **'Prices and stock need a connection. We will refresh when you are back online.'**
  String get offlineBannerBody;

  /// Offline state title.
  ///
  /// In en, this message translates to:
  /// **'No connection'**
  String get noConnectionTitle;

  /// Offline state text.
  ///
  /// In en, this message translates to:
  /// **'Check Wi-Fi or mobile data, then try again.'**
  String get noConnectionBody;

  /// Screen reader label of a hero slide.
  ///
  /// In en, this message translates to:
  /// **'Banner {index} of {count}: {title}'**
  String heroSlideLabel(int index, int count, String title);

  /// Screen reader label of the hero carousel.
  ///
  /// In en, this message translates to:
  /// **'Offers'**
  String get heroCarouselLabel;

  /// Platforms title.
  ///
  /// In en, this message translates to:
  /// **'Platforms'**
  String get platformsTitle;

  /// Platforms page intro (04.1).
  ///
  /// In en, this message translates to:
  /// **'Choose a platform to see its gift cards, wallet codes and top-ups.'**
  String get platformsIntro;

  /// No platforms.
  ///
  /// In en, this message translates to:
  /// **'No platforms yet'**
  String get platformsEmpty;

  /// Categories title.
  ///
  /// In en, this message translates to:
  /// **'Categories'**
  String get categoriesTitle;

  /// Chip that removes the category filter.
  ///
  /// In en, this message translates to:
  /// **'All'**
  String get allCategory;

  /// Number of products.
  ///
  /// In en, this message translates to:
  /// **'{count, plural, =0{No products} =1{1 product} other{{count} products}}'**
  String productCount(int count);

  /// Listing title without category or platform.
  ///
  /// In en, this message translates to:
  /// **'All products'**
  String get allProductsTitle;

  /// Filters chip without active filters.
  ///
  /// In en, this message translates to:
  /// **'Filters'**
  String get filtersChip;

  /// Filters chip with the number of active filters.
  ///
  /// In en, this message translates to:
  /// **'Filters · {count}'**
  String filtersChipCount(int count);

  /// Sort chip.
  ///
  /// In en, this message translates to:
  /// **'Sort: {sort}'**
  String sortChip(String sort);

  /// Sort option.
  ///
  /// In en, this message translates to:
  /// **'Popular'**
  String get sortPopular;

  /// Sort option.
  ///
  /// In en, this message translates to:
  /// **'Price low to high'**
  String get sortPriceAsc;

  /// Sort option.
  ///
  /// In en, this message translates to:
  /// **'Price high to low'**
  String get sortPriceDesc;

  /// Sort option.
  ///
  /// In en, this message translates to:
  /// **'Newest'**
  String get sortNewest;

  /// Sort option.
  ///
  /// In en, this message translates to:
  /// **'Name'**
  String get sortName;

  /// Sort sheet and filter group title.
  ///
  /// In en, this message translates to:
  /// **'Sort'**
  String get sortTitle;

  /// Filters sheet title.
  ///
  /// In en, this message translates to:
  /// **'Filters'**
  String get filtersTitle;

  /// Filter group.
  ///
  /// In en, this message translates to:
  /// **'Delivery'**
  String get filterDelivery;

  /// Delivery filter option.
  ///
  /// In en, this message translates to:
  /// **'Instant code'**
  String get deliveryInstantCode;

  /// Delivery filter option.
  ///
  /// In en, this message translates to:
  /// **'Direct top-up'**
  String get deliveryDirectTopup;

  /// Price filter field.
  ///
  /// In en, this message translates to:
  /// **'Min price'**
  String get filterMinPrice;

  /// Price filter field.
  ///
  /// In en, this message translates to:
  /// **'Max price'**
  String get filterMaxPrice;

  /// Currency of the price filter.
  ///
  /// In en, this message translates to:
  /// **'In {currency}'**
  String filterPriceHelper(String currency);

  /// Invalid price bound.
  ///
  /// In en, this message translates to:
  /// **'Enter an amount like 5 or 5.50'**
  String get filterPriceInvalid;

  /// Min above max.
  ///
  /// In en, this message translates to:
  /// **'Min price must not be above max price'**
  String get filterPriceRange;

  /// Clears the filters in the sheet.
  ///
  /// In en, this message translates to:
  /// **'Clear'**
  String get filtersClear;

  /// Applies the filters.
  ///
  /// In en, this message translates to:
  /// **'Show results'**
  String get filtersApply;

  /// Listing empty with filters (04.4).
  ///
  /// In en, this message translates to:
  /// **'No products match'**
  String get listingEmptyTitle;

  /// Listing empty text.
  ///
  /// In en, this message translates to:
  /// **'Try another price or remove a filter.'**
  String get listingEmptyBody;

  /// Listing empty without filters.
  ///
  /// In en, this message translates to:
  /// **'No products here yet'**
  String get catalogEmptyTitle;

  /// Listing empty text without filters.
  ///
  /// In en, this message translates to:
  /// **'New gift cards and top-ups are added often.'**
  String get catalogEmptyBody;

  /// Clears the listing filters.
  ///
  /// In en, this message translates to:
  /// **'Clear filters'**
  String get clearFilters;

  /// Next page failed (04.5).
  ///
  /// In en, this message translates to:
  /// **'Couldn\'t load more products.'**
  String get loadMoreFailed;

  /// Delivery badge.
  ///
  /// In en, this message translates to:
  /// **'Instant'**
  String get badgeInstant;

  /// Delivery badge.
  ///
  /// In en, this message translates to:
  /// **'Top-up'**
  String get badgeTopup;

  /// Delivery badge.
  ///
  /// In en, this message translates to:
  /// **'Subscription'**
  String get badgeSubscription;

  /// Product without a price.
  ///
  /// In en, this message translates to:
  /// **'Unavailable'**
  String get productUnavailable;

  /// Search section.
  ///
  /// In en, this message translates to:
  /// **'Suggestions'**
  String get searchSuggestions;

  /// Search results section after submit.
  ///
  /// In en, this message translates to:
  /// **'Results'**
  String get searchResults;

  /// Search history section.
  ///
  /// In en, this message translates to:
  /// **'Recent searches'**
  String get searchRecent;

  /// Clears the search history.
  ///
  /// In en, this message translates to:
  /// **'Clear'**
  String get searchClearHistory;

  /// Screen reader label of Clear.
  ///
  /// In en, this message translates to:
  /// **'Clear recent searches'**
  String get searchClearHistoryLabel;

  /// Removes one history term.
  ///
  /// In en, this message translates to:
  /// **'Remove {term}'**
  String searchRemoveTerm(String term);

  /// Puts a suggestion in the field.
  ///
  /// In en, this message translates to:
  /// **'Search for {term}'**
  String searchFillTerm(String term);

  /// Popular searches section.
  ///
  /// In en, this message translates to:
  /// **'Popular'**
  String get searchPopular;

  /// Search no results (04.7).
  ///
  /// In en, this message translates to:
  /// **'No results for “{query}”'**
  String searchNoResultsTitle(String query);

  /// Search no results text.
  ///
  /// In en, this message translates to:
  /// **'Check the spelling or try a platform name.'**
  String get searchNoResultsBody;

  /// Suggestion type.
  ///
  /// In en, this message translates to:
  /// **'Platform'**
  String get searchTypePlatform;

  /// Suggestion type.
  ///
  /// In en, this message translates to:
  /// **'Category'**
  String get searchTypeCategory;

  /// Suggestion type.
  ///
  /// In en, this message translates to:
  /// **'Product'**
  String get searchTypeProduct;

  /// Suggestions failed.
  ///
  /// In en, this message translates to:
  /// **'Couldn\'t load suggestions.'**
  String get searchFailed;

  /// Currency sheet text (03.5).
  ///
  /// In en, this message translates to:
  /// **'Prices refresh in the new currency. Your cart is re-priced before checkout.'**
  String get currencySheetBody;

  /// Applies the currency.
  ///
  /// In en, this message translates to:
  /// **'Apply'**
  String get currencyApply;

  /// Currency changed snackbar.
  ///
  /// In en, this message translates to:
  /// **'Prices are now shown in {code}.'**
  String currencyChanged(String code);

  /// Cart tab title.
  ///
  /// In en, this message translates to:
  /// **'Cart'**
  String get cartTitle;

  /// Orders tab title.
  ///
  /// In en, this message translates to:
  /// **'Orders'**
  String get ordersTitle;

  /// Account tab title.
  ///
  /// In en, this message translates to:
  /// **'Account'**
  String get accountTitle;

  /// Placeholder of a screen not built yet.
  ///
  /// In en, this message translates to:
  /// **'Coming in the next update'**
  String get comingSoonTitle;

  /// Placeholder text.
  ///
  /// In en, this message translates to:
  /// **'This part of the app is on its way. Meanwhile you can use boxcards.shop on the web.'**
  String get comingSoonBody;

  /// Account settings row.
  ///
  /// In en, this message translates to:
  /// **'Language'**
  String get languageSetting;

  /// Review rating for screen readers.
  ///
  /// In en, this message translates to:
  /// **'Rated {rating} out of 5'**
  String reviewsRating(int rating);

  /// Review badge.
  ///
  /// In en, this message translates to:
  /// **'Verified purchase'**
  String get verifiedPurchase;

  /// Notifications button tooltip.
  ///
  /// In en, this message translates to:
  /// **'Notifications'**
  String get notificationsComingSoon;

  /// Product page error title.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load this product'**
  String get productLoadErrorTitle;

  /// Product 404 title (05.6).
  ///
  /// In en, this message translates to:
  /// **'Product not available'**
  String get productNotFoundTitle;

  /// Product 404 text.
  ///
  /// In en, this message translates to:
  /// **'It may have been removed or is not sold in your region.'**
  String get productNotFoundBody;

  /// Button from the product 404 to home.
  ///
  /// In en, this message translates to:
  /// **'Back to store'**
  String get backToStore;

  /// Region selector label.
  ///
  /// In en, this message translates to:
  /// **'Region'**
  String get productRegion;

  /// Region chip for variants without a region.
  ///
  /// In en, this message translates to:
  /// **'All regions'**
  String get productAnyRegion;

  /// Denomination selector label.
  ///
  /// In en, this message translates to:
  /// **'Amount'**
  String get productAmount;

  /// Price under a denomination; price is a server amount.
  ///
  /// In en, this message translates to:
  /// **'Pay {price}'**
  String productPay(String price);

  /// Out-of-stock denomination.
  ///
  /// In en, this message translates to:
  /// **'Sold out'**
  String get variantSoldOut;

  /// Custom-amount denomination (cannot be bought yet).
  ///
  /// In en, this message translates to:
  /// **'Not available yet'**
  String get variantCustomAmount;

  /// Denomination that cannot be bought.
  ///
  /// In en, this message translates to:
  /// **'Not available'**
  String get variantUnavailable;

  /// Limited availability of the selected denomination.
  ///
  /// In en, this message translates to:
  /// **'Only a few left'**
  String get variantLimited;

  /// Denomination for screen readers.
  ///
  /// In en, this message translates to:
  /// **'{title}, {detail}'**
  String variantSemantics(String title, String detail);

  /// price_may_vary notice title.
  ///
  /// In en, this message translates to:
  /// **'Price may vary'**
  String get priceMayVaryTitle;

  /// price_may_vary notice text.
  ///
  /// In en, this message translates to:
  /// **'The final price is confirmed at checkout.'**
  String get priceMayVaryBody;

  /// Notice when no option can be bought.
  ///
  /// In en, this message translates to:
  /// **'Not available right now'**
  String get productUnavailableTitle;

  /// Notice text when no option can be bought.
  ///
  /// In en, this message translates to:
  /// **'None of this product\'s options can be bought at the moment. Check back later.'**
  String get productUnavailableBody;

  /// Banner title when add to cart fails.
  ///
  /// In en, this message translates to:
  /// **'Couldn\'t add to cart'**
  String get addFailedTitle;

  /// Number of reviews.
  ///
  /// In en, this message translates to:
  /// **'{count, plural, =0{No reviews} =1{1 review} other{{count} reviews}}'**
  String productReviewsCount(int count);

  /// Rating summary for screen readers.
  ///
  /// In en, this message translates to:
  /// **'Average rating {rating} out of 5'**
  String productAverageRating(String rating);

  /// Delivery time of a product.
  ///
  /// In en, this message translates to:
  /// **'{minutes, plural, =0{Instant delivery} =1{About 1 min} other{About {minutes} min}}'**
  String deliveryTime(int minutes);

  /// Description heading.
  ///
  /// In en, this message translates to:
  /// **'About this product'**
  String get aboutProduct;

  /// Instructions heading.
  ///
  /// In en, this message translates to:
  /// **'How to redeem'**
  String get howToRedeem;

  /// Terms heading.
  ///
  /// In en, this message translates to:
  /// **'Terms'**
  String get termsTitle;

  /// Reviews heading and page title.
  ///
  /// In en, this message translates to:
  /// **'Reviews'**
  String get reviewsTitle;

  /// No reviews.
  ///
  /// In en, this message translates to:
  /// **'No reviews yet'**
  String get reviewsEmptyTitle;

  /// No reviews text.
  ///
  /// In en, this message translates to:
  /// **'Reviews from buyers appear here.'**
  String get reviewsEmptyBody;

  /// Button to the full reviews list.
  ///
  /// In en, this message translates to:
  /// **'See all reviews'**
  String get seeAllReviews;

  /// Reviews failed to load.
  ///
  /// In en, this message translates to:
  /// **'Couldn\'t load reviews.'**
  String get reviewsLoadFailed;

  /// Next reviews page failed.
  ///
  /// In en, this message translates to:
  /// **'Couldn\'t load more reviews.'**
  String get loadMoreReviewsFailed;

  /// Quantity stepper for screen readers.
  ///
  /// In en, this message translates to:
  /// **'Quantity {count}'**
  String quantitySemantics(int count);

  /// Quantity plus button.
  ///
  /// In en, this message translates to:
  /// **'Increase quantity'**
  String get increaseQuantity;

  /// Quantity minus button.
  ///
  /// In en, this message translates to:
  /// **'Decrease quantity'**
  String get decreaseQuantity;

  /// Per-order quantity limit.
  ///
  /// In en, this message translates to:
  /// **'Up to {max} per order'**
  String quantityLimit(int max);

  /// Footer price label.
  ///
  /// In en, this message translates to:
  /// **'Price'**
  String get priceLabel;

  /// Unit price and quantity in the footer.
  ///
  /// In en, this message translates to:
  /// **'{price} × {count}'**
  String priceTimesQuantity(String price, int count);

  /// Add to cart button.
  ///
  /// In en, this message translates to:
  /// **'Add to cart'**
  String get addToCart;

  /// Success snackbar.
  ///
  /// In en, this message translates to:
  /// **'Added to cart.'**
  String get addedToCart;

  /// Snackbar action to the cart.
  ///
  /// In en, this message translates to:
  /// **'View cart'**
  String get viewCart;

  /// Cart tab badge for screen readers.
  ///
  /// In en, this message translates to:
  /// **'{count, plural, =1{1 item} other{{count} items}}'**
  String cartBadgeSemantics(int count);

  /// Required delivery field is empty.
  ///
  /// In en, this message translates to:
  /// **'{label} is required.'**
  String deliveryFieldRequired(String label);

  /// Delivery value too long.
  ///
  /// In en, this message translates to:
  /// **'{label} is too long (at most {max} characters).'**
  String deliveryFieldTooLong(String label, int max);

  /// Number delivery field with other characters.
  ///
  /// In en, this message translates to:
  /// **'{label} must contain digits only.'**
  String deliveryFieldDigits(String label);

  /// Email delivery field invalid.
  ///
  /// In en, this message translates to:
  /// **'Enter a valid email address.'**
  String get deliveryFieldEmail;

  /// Select delivery field invalid.
  ///
  /// In en, this message translates to:
  /// **'Choose one of the options.'**
  String get deliveryFieldOption;

  /// Delivery value does not match the pattern.
  ///
  /// In en, this message translates to:
  /// **'{label} has an invalid format.'**
  String deliveryFieldFormat(String label);

  /// Empty select field.
  ///
  /// In en, this message translates to:
  /// **'Choose'**
  String get selectPlaceholder;

  /// Gallery page for screen readers.
  ///
  /// In en, this message translates to:
  /// **'Image {index} of {count}'**
  String galleryImage(int index, int count);

  /// Cart line block_reason unavailable.
  ///
  /// In en, this message translates to:
  /// **'No longer available. Remove it to continue.'**
  String get blockUnavailable;

  /// Cart line block_reason out_of_stock.
  ///
  /// In en, this message translates to:
  /// **'Out of stock. Remove it to continue.'**
  String get blockOutOfStock;

  /// Cart line block_reason custom_amount_unsupported.
  ///
  /// In en, this message translates to:
  /// **'This amount can\'t be bought. Remove it to continue.'**
  String get blockCustomAmount;

  /// Cart line block_reason quantity_limit.
  ///
  /// In en, this message translates to:
  /// **'Above the quantity limit. Lower the quantity.'**
  String get blockQuantityLimit;

  /// Cart line block_reason not_sold_in_country.
  ///
  /// In en, this message translates to:
  /// **'Not sold in your country. Remove it to continue.'**
  String get blockNotSoldInCountry;

  /// Cart line pricing block reason (rate_stale, rate_missing).
  ///
  /// In en, this message translates to:
  /// **'The price is being updated. Try again shortly.'**
  String get blockPricing;

  /// Cart line with another block reason.
  ///
  /// In en, this message translates to:
  /// **'Not available right now. Remove it to continue.'**
  String get blockOther;

  /// Cart app bar title with the number of units.
  ///
  /// In en, this message translates to:
  /// **'Cart ({count})'**
  String cartTitleCount(int count);

  /// Empty cart title (06.3).
  ///
  /// In en, this message translates to:
  /// **'Your cart is empty'**
  String get cartEmptyTitle;

  /// Empty cart body.
  ///
  /// In en, this message translates to:
  /// **'Gift cards and top-ups you add will show here.'**
  String get cartEmptyBody;

  /// Empty cart action.
  ///
  /// In en, this message translates to:
  /// **'Browse the store'**
  String get cartBrowseStore;

  /// Cart error title (06.5).
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load your cart'**
  String get cartLoadFailedTitle;

  /// Remove button of a cart line.
  ///
  /// In en, this message translates to:
  /// **'Remove {name}'**
  String cartRemoveItem(String name);

  /// A cart line for screen readers.
  ///
  /// In en, this message translates to:
  /// **'{name}, {variant}, {price}, quantity {quantity}'**
  String cartLineSemantics(
    String name,
    String variant,
    String price,
    int quantity,
  );

  /// Coupon field label.
  ///
  /// In en, this message translates to:
  /// **'Coupon code'**
  String get cartCouponLabel;

  /// Apply the coupon.
  ///
  /// In en, this message translates to:
  /// **'Apply'**
  String get cartCouponApply;

  /// Remove the applied coupon.
  ///
  /// In en, this message translates to:
  /// **'Remove'**
  String get cartCouponRemove;

  /// Coupon applied note.
  ///
  /// In en, this message translates to:
  /// **'{code} applied.'**
  String cartCouponApplied(String code);

  /// Coupon limited_by_margin note.
  ///
  /// In en, this message translates to:
  /// **'The discount was limited for this order.'**
  String get cartCouponLimited;

  /// Cart subtotal label.
  ///
  /// In en, this message translates to:
  /// **'Subtotal'**
  String get cartSubtotal;

  /// Cart discount label.
  ///
  /// In en, this message translates to:
  /// **'Discount'**
  String get cartDiscount;

  /// Cart discount label with the coupon code.
  ///
  /// In en, this message translates to:
  /// **'Discount ({code})'**
  String cartDiscountCode(String code);

  /// Cart total label.
  ///
  /// In en, this message translates to:
  /// **'Total'**
  String get cartTotal;

  /// Cart checkout button.
  ///
  /// In en, this message translates to:
  /// **'Checkout'**
  String get cartCheckout;

  /// Checkout button in web checkout mode (owner decision 3).
  ///
  /// In en, this message translates to:
  /// **'Complete purchase on boxcards.shop'**
  String get cartCheckoutWeb;

  /// Hint above the web checkout button.
  ///
  /// In en, this message translates to:
  /// **'Opens boxcards.shop in your browser.'**
  String get cartCheckoutWebHint;

  /// Checkout disabled by the app config.
  ///
  /// In en, this message translates to:
  /// **'Checkout isn\'t available in the app right now.'**
  String get cartCheckoutDisabled;

  /// Checkout disabled because a line is blocked.
  ///
  /// In en, this message translates to:
  /// **'Remove or fix the marked items to continue.'**
  String get cartCheckoutBlocked;

  /// Hint for guests above the checkout button (owner decision 4).
  ///
  /// In en, this message translates to:
  /// **'You\'ll sign in first. Your cart is kept.'**
  String get cartSignInHint;

  /// Cart notice after price_changed or a stale rate.
  ///
  /// In en, this message translates to:
  /// **'Prices were updated'**
  String get cartPricesUpdatedTitle;

  /// Cart notice body after price_changed.
  ///
  /// In en, this message translates to:
  /// **'Exchange rates changed since you added these items. Review the new total.'**
  String get cartPricesUpdatedBody;

  /// Cart price_may_vary notice title.
  ///
  /// In en, this message translates to:
  /// **'Prices may change'**
  String get cartPriceMayVaryTitle;

  /// Cart price_may_vary notice body.
  ///
  /// In en, this message translates to:
  /// **'The final price is confirmed at checkout.'**
  String get cartPriceMayVaryBody;

  /// Checkout app bar title.
  ///
  /// In en, this message translates to:
  /// **'Checkout'**
  String get checkoutTitle;

  /// Note under the account email on checkout.
  ///
  /// In en, this message translates to:
  /// **'Order emails go to this address'**
  String get checkoutEmailNote;

  /// Country field label and sheet title.
  ///
  /// In en, this message translates to:
  /// **'Country'**
  String get checkoutCountry;

  /// Country field placeholder.
  ///
  /// In en, this message translates to:
  /// **'Choose your country'**
  String get checkoutChooseCountry;

  /// Shown before a country is chosen.
  ///
  /// In en, this message translates to:
  /// **'Choose your country to see the payment options.'**
  String get checkoutCountryHint;

  /// Country sheet search hint.
  ///
  /// In en, this message translates to:
  /// **'Search countries'**
  String get checkoutSearchCountries;

  /// Country sheet without results.
  ///
  /// In en, this message translates to:
  /// **'No country matches your search.'**
  String get checkoutNoCountryMatch;

  /// Payment section heading.
  ///
  /// In en, this message translates to:
  /// **'Payment'**
  String get checkoutPayment;

  /// Payment option detail when it charges another currency.
  ///
  /// In en, this message translates to:
  /// **'Charged in {currency}'**
  String checkoutChargedIn(String currency);

  /// charged_in_payment_currency notice title.
  ///
  /// In en, this message translates to:
  /// **'{gateway} charges in {currency}'**
  String checkoutChargeNoticeTitle(String gateway, String currency);

  /// charged_in_payment_currency notice body.
  ///
  /// In en, this message translates to:
  /// **'You see prices in {display}, but {gateway} will charge {amount}. Your bank may add a conversion fee.'**
  String checkoutChargeNoticeBody(
    String display,
    String gateway,
    String amount,
  );

  /// Payment option disabled (payment_currency_below_margin).
  ///
  /// In en, this message translates to:
  /// **'Not available for this amount'**
  String get optionBelowMargin;

  /// Payment option disabled (available false).
  ///
  /// In en, this message translates to:
  /// **'Not available right now'**
  String get optionUnavailable;

  /// Stripe option: the app has no card payment SDK.
  ///
  /// In en, this message translates to:
  /// **'Not available in the app yet'**
  String get optionNotInApp;

  /// Banner when an option is disabled.
  ///
  /// In en, this message translates to:
  /// **'Some options are unavailable'**
  String get checkoutSomeUnavailableTitle;

  /// Banner body when an option is disabled.
  ///
  /// In en, this message translates to:
  /// **'Options shown are the ones the store offers for {country}.'**
  String checkoutSomeUnavailableBody(String country);

  /// No option can be chosen.
  ///
  /// In en, this message translates to:
  /// **'No payment option for this order'**
  String get checkoutNoOptionsTitle;

  /// No option can be chosen, body.
  ///
  /// In en, this message translates to:
  /// **'Try another country, or complete the purchase on boxcards.shop.'**
  String get checkoutNoOptionsBody;

  /// Terms checkbox.
  ///
  /// In en, this message translates to:
  /// **'I accept the Terms of Sale'**
  String get checkoutAcceptTerms;

  /// Terms not accepted.
  ///
  /// In en, this message translates to:
  /// **'Accept the Terms of Sale to continue.'**
  String get checkoutTermsRequired;

  /// Opens the terms link.
  ///
  /// In en, this message translates to:
  /// **'Read the Terms of Sale'**
  String get checkoutReadTerms;

  /// Units in the checkout summary.
  ///
  /// In en, this message translates to:
  /// **'{count, plural, =1{1 item} other{{count} items}}'**
  String checkoutItems(int count);

  /// The exact amount and currency charged (owner decision 1).
  ///
  /// In en, this message translates to:
  /// **'You will be charged {amount} in {currency} by {gateway}.'**
  String checkoutChargedLine(String amount, String currency, String gateway);

  /// Checkout summary note.
  ///
  /// In en, this message translates to:
  /// **'Prices include all fees. Codes are delivered to your account.'**
  String get checkoutFeesNote;

  /// Pay button before an option is chosen.
  ///
  /// In en, this message translates to:
  /// **'Pay'**
  String get checkoutPayButton;

  /// Pay button with the charged amount.
  ///
  /// In en, this message translates to:
  /// **'Pay {amount}'**
  String checkoutPay(String amount);

  /// Pay button for the wallet.
  ///
  /// In en, this message translates to:
  /// **'Pay {amount} with wallet'**
  String checkoutPayWallet(String amount);

  /// Pay button for PayPal.
  ///
  /// In en, this message translates to:
  /// **'Continue to {gateway}'**
  String checkoutContinueTo(String gateway);

  /// price_changed dialog title (07.5).
  ///
  /// In en, this message translates to:
  /// **'The price changed'**
  String get checkoutPriceChangedTitle;

  /// price_changed dialog body.
  ///
  /// In en, this message translates to:
  /// **'Your total is now {current} (was {previous}). Nothing was charged. Review the total and try again.'**
  String checkoutPriceChangedBody(String current, String previous);

  /// price_changed dialog body when the new total could not be loaded.
  ///
  /// In en, this message translates to:
  /// **'Your total changed (it was {previous}). Nothing was charged. Review the total and try again.'**
  String checkoutPriceChangedBodyUnknown(String previous);

  /// price_changed dialog primary action.
  ///
  /// In en, this message translates to:
  /// **'Review new total'**
  String get checkoutReviewNewTotal;

  /// Back to the cart.
  ///
  /// In en, this message translates to:
  /// **'Back to cart'**
  String get checkoutBackToCart;

  /// idempotency_in_progress banner title.
  ///
  /// In en, this message translates to:
  /// **'Your order is still being placed'**
  String get checkoutInProgressTitle;

  /// idempotency_in_progress banner body.
  ///
  /// In en, this message translates to:
  /// **'Wait a moment, then tap Pay again. You won\'t be charged twice.'**
  String get checkoutInProgressBody;

  /// An item in the cart blocks the order.
  ///
  /// In en, this message translates to:
  /// **'Some items need attention'**
  String get checkoutCartAttentionTitle;

  /// rate_stale banner title.
  ///
  /// In en, this message translates to:
  /// **'Prices are being updated'**
  String get checkoutRateStaleTitle;

  /// rate_stale banner body.
  ///
  /// In en, this message translates to:
  /// **'Exchange rates are being refreshed. Try again in a moment.'**
  String get checkoutRateStaleBody;

  /// Place-order failure banner title.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t place your order'**
  String get checkoutFailedTitle;

  /// Checkout load failure title.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load the payment options'**
  String get checkoutQuoteFailedTitle;

  /// Checkout in web checkout mode (owner decision 3).
  ///
  /// In en, this message translates to:
  /// **'Complete your purchase on the web'**
  String get checkoutWebTitle;

  /// Checkout in web checkout mode, body.
  ///
  /// In en, this message translates to:
  /// **'Purchases are completed on boxcards.shop in your browser. Sign in there with the same account.'**
  String get checkoutWebBody;

  /// Checkout disabled by the app config.
  ///
  /// In en, this message translates to:
  /// **'Checkout is unavailable'**
  String get checkoutDisabledTitle;

  /// Gateway name when unknown.
  ///
  /// In en, this message translates to:
  /// **'the payment provider'**
  String get paymentProviderFallback;

  /// Payment in progress title (07.6).
  ///
  /// In en, this message translates to:
  /// **'Payment in progress'**
  String get paymentInProgressTitle;

  /// Payment in progress body.
  ///
  /// In en, this message translates to:
  /// **'Finish paying in the {gateway} window. We confirm the payment with {gateway} before showing it as paid.'**
  String paymentInProgressBody(String gateway);

  /// The browser could not open the provider page.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t open the payment page. Try again.'**
  String get paymentOpenFailed;

  /// In-progress notice title.
  ///
  /// In en, this message translates to:
  /// **'Don\'t pay twice'**
  String get paymentDontPayTwiceTitle;

  /// In-progress notice body.
  ///
  /// In en, this message translates to:
  /// **'If you close the app, we resume here and check the payment again.'**
  String get paymentDontPayTwiceBody;

  /// Reopen the provider page.
  ///
  /// In en, this message translates to:
  /// **'Return to {gateway}'**
  String paymentReturnTo(String gateway);

  /// Ask the server for the payment status.
  ///
  /// In en, this message translates to:
  /// **'Check payment status'**
  String get paymentCheckStatus;

  /// While verifying and polling.
  ///
  /// In en, this message translates to:
  /// **'Checking your payment'**
  String get paymentConfirmingTitle;

  /// While verifying and polling, body.
  ///
  /// In en, this message translates to:
  /// **'We\'re confirming the payment with the provider. This can take a minute.'**
  String get paymentConfirmingBody;

  /// Result paid (08.1).
  ///
  /// In en, this message translates to:
  /// **'Payment confirmed'**
  String get paymentPaidTitle;

  /// Result paid body.
  ///
  /// In en, this message translates to:
  /// **'Your codes are being delivered. We\'ll let you know when they\'re ready.'**
  String get paymentPaidBody;

  /// Paid notice title.
  ///
  /// In en, this message translates to:
  /// **'Your codes are in Orders'**
  String get paymentReceiptTitle;

  /// Paid notice body.
  ///
  /// In en, this message translates to:
  /// **'Open the order to see each code once it\'s delivered.'**
  String get paymentReceiptBody;

  /// Paid primary action.
  ///
  /// In en, this message translates to:
  /// **'View order'**
  String get paymentViewOrder;

  /// Paid secondary action.
  ///
  /// In en, this message translates to:
  /// **'Keep shopping'**
  String get paymentKeepShopping;

  /// Result processing (08.2).
  ///
  /// In en, this message translates to:
  /// **'Still processing'**
  String get paymentProcessingTitle;

  /// Result processing body.
  ///
  /// In en, this message translates to:
  /// **'Your payment provider hasn\'t confirmed yet. You don\'t need to pay again; the order updates as soon as it\'s confirmed.'**
  String get paymentProcessingBody;

  /// Time of the last status check.
  ///
  /// In en, this message translates to:
  /// **'Last checked {time}'**
  String paymentLastChecked(String time);

  /// Processing primary action.
  ///
  /// In en, this message translates to:
  /// **'Check again'**
  String get paymentCheckAgain;

  /// Processing secondary action.
  ///
  /// In en, this message translates to:
  /// **'Go to orders'**
  String get paymentGoToOrders;

  /// Result failed (08.3).
  ///
  /// In en, this message translates to:
  /// **'Payment not completed'**
  String get paymentFailedTitle;

  /// Result failed body (retryable).
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t confirm a payment for this order. You can try again with the same method.'**
  String get paymentFailedBody;

  /// Result failed after the cancel link.
  ///
  /// In en, this message translates to:
  /// **'You left the payment page before paying. You can try again with the same method.'**
  String get paymentCancelledBody;

  /// Result failed, order closed.
  ///
  /// In en, this message translates to:
  /// **'This order can\'t be paid any more. Place the order again from your cart.'**
  String get paymentFailedFinalBody;

  /// End of the payment window.
  ///
  /// In en, this message translates to:
  /// **'Your order is held until {time}.'**
  String paymentHeldUntil(String time);

  /// Payment attempt number.
  ///
  /// In en, this message translates to:
  /// **'Attempt {number}'**
  String paymentAttempt(int number);

  /// Failed notice title without an attempt number.
  ///
  /// In en, this message translates to:
  /// **'Want another method?'**
  String get paymentAnotherMethodTitle;

  /// Failed notice body.
  ///
  /// In en, this message translates to:
  /// **'To use another payment method, place the order again from your cart.'**
  String get paymentAnotherMethodBody;

  /// payment_attempts_exhausted.
  ///
  /// In en, this message translates to:
  /// **'All payment attempts for this order were used. Place the order again from your cart.'**
  String get paymentAttemptsExhausted;

  /// Retry payment action.
  ///
  /// In en, this message translates to:
  /// **'Try payment again'**
  String get paymentTryAgain;

  /// Result expired (08.4).
  ///
  /// In en, this message translates to:
  /// **'Order expired'**
  String get paymentExpiredTitle;

  /// Result expired body.
  ///
  /// In en, this message translates to:
  /// **'This order wasn\'t paid in time, so we released the items. Nothing was charged.'**
  String get paymentExpiredBody;

  /// Expired primary action.
  ///
  /// In en, this message translates to:
  /// **'Return to cart'**
  String get paymentReturnToCart;

  /// Expired secondary action.
  ///
  /// In en, this message translates to:
  /// **'Contact support'**
  String get paymentContactSupport;

  /// No attempt could be started (07.7).
  ///
  /// In en, this message translates to:
  /// **'Payment provider unavailable'**
  String get paymentNotStartedTitle;

  /// No attempt could be started, body.
  ///
  /// In en, this message translates to:
  /// **'{gateway} could not start the payment. Try again; your order is kept.'**
  String paymentNotStartedBody(String gateway);

  /// A payment call failed.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t check the payment'**
  String get paymentCallFailedTitle;

  /// A step the app cannot launch (Stripe, unknown).
  ///
  /// In en, this message translates to:
  /// **'This payment can\'t be completed in the app'**
  String get paymentUnsupportedTitle;

  /// Unsupported step body.
  ///
  /// In en, this message translates to:
  /// **'Check the payment status, or place the order again with another method.'**
  String get paymentUnsupportedBody;

  /// Payment screen before any check.
  ///
  /// In en, this message translates to:
  /// **'Payment status'**
  String get paymentIdleTitle;

  /// Payment screen before any check, body.
  ///
  /// In en, this message translates to:
  /// **'Check this order\'s payment with the store.'**
  String get paymentIdleBody;

  /// Summary row.
  ///
  /// In en, this message translates to:
  /// **'Order'**
  String get paymentSummaryOrder;

  /// Summary row.
  ///
  /// In en, this message translates to:
  /// **'Amount'**
  String get paymentSummaryAmount;

  /// Summary row.
  ///
  /// In en, this message translates to:
  /// **'Paid with'**
  String get paymentSummaryPaidWith;

  /// Summary row.
  ///
  /// In en, this message translates to:
  /// **'Status'**
  String get paymentSummaryStatus;

  /// Status chip.
  ///
  /// In en, this message translates to:
  /// **'Awaiting payment'**
  String get paymentStatusAwaiting;

  /// Status chip.
  ///
  /// In en, this message translates to:
  /// **'Payment processing'**
  String get paymentStatusProcessing;

  /// Status chip.
  ///
  /// In en, this message translates to:
  /// **'Paid'**
  String get paymentStatusPaid;

  /// Status chip.
  ///
  /// In en, this message translates to:
  /// **'Failed'**
  String get paymentStatusFailed;

  /// Status chip.
  ///
  /// In en, this message translates to:
  /// **'Expired'**
  String get paymentStatusExpired;

  /// Status chip before a check.
  ///
  /// In en, this message translates to:
  /// **'Not checked yet'**
  String get paymentStatusUnknown;
}

class _AppLocalizationsDelegate
    extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  @override
  Future<AppLocalizations> load(Locale locale) {
    return SynchronousFuture<AppLocalizations>(lookupAppLocalizations(locale));
  }

  @override
  bool isSupported(Locale locale) =>
      <String>['ar', 'en'].contains(locale.languageCode);

  @override
  bool shouldReload(_AppLocalizationsDelegate old) => false;
}

AppLocalizations lookupAppLocalizations(Locale locale) {
  // Lookup logic when only language code is specified.
  switch (locale.languageCode) {
    case 'ar':
      return AppLocalizationsAr();
    case 'en':
      return AppLocalizationsEn();
  }

  throw FlutterError(
    'AppLocalizations.delegate failed to load unsupported locale "$locale". This is likely '
    'an issue with the localizations generation tool. Please file an issue '
    'on GitHub with a reproducible sample app and the gen-l10n configuration '
    'that was used.',
  );
}
