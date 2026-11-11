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

  /// Order status filter chip.
  ///
  /// In en, this message translates to:
  /// **'All'**
  String get ordersFilterAll;

  /// Order status.
  ///
  /// In en, this message translates to:
  /// **'Completed'**
  String get orderStatusCompleted;

  /// Order status (paid, processing, supplier or review steps).
  ///
  /// In en, this message translates to:
  /// **'Processing'**
  String get orderStatusProcessing;

  /// Order status.
  ///
  /// In en, this message translates to:
  /// **'Payment processing'**
  String get orderStatusPaymentProcessing;

  /// Order status pending_payment.
  ///
  /// In en, this message translates to:
  /// **'Awaiting payment'**
  String get orderStatusAwaitingPayment;

  /// Order status partially_fulfilled.
  ///
  /// In en, this message translates to:
  /// **'Partly delivered'**
  String get orderStatusPartlyDelivered;

  /// Order status.
  ///
  /// In en, this message translates to:
  /// **'Failed'**
  String get orderStatusFailed;

  /// Order status.
  ///
  /// In en, this message translates to:
  /// **'Cancelled'**
  String get orderStatusCancelled;

  /// Order status.
  ///
  /// In en, this message translates to:
  /// **'Refund pending'**
  String get orderStatusRefundPending;

  /// Order status.
  ///
  /// In en, this message translates to:
  /// **'Partially refunded'**
  String get orderStatusPartiallyRefunded;

  /// Order status.
  ///
  /// In en, this message translates to:
  /// **'Refunded'**
  String get orderStatusRefunded;

  /// Number of items in an order.
  ///
  /// In en, this message translates to:
  /// **'{count, plural, =1{1 item} other{{count} items}}'**
  String ordersItemCount(int count);

  /// Screen reader label of an order row.
  ///
  /// In en, this message translates to:
  /// **'Order {number}, {status}, {date}, {total}'**
  String orderTileSemantics(
    String number,
    String status,
    String date,
    String total,
  );

  /// Empty orders title.
  ///
  /// In en, this message translates to:
  /// **'No orders yet'**
  String get ordersEmptyTitle;

  /// Empty orders body.
  ///
  /// In en, this message translates to:
  /// **'Codes you buy show up here.'**
  String get ordersEmptyBody;

  /// Empty orders action.
  ///
  /// In en, this message translates to:
  /// **'Browse the store'**
  String get ordersEmptyAction;

  /// Empty state with a status filter.
  ///
  /// In en, this message translates to:
  /// **'No orders with this status'**
  String get ordersEmptyFilteredTitle;

  /// Clears the status filter.
  ///
  /// In en, this message translates to:
  /// **'Show all orders'**
  String get ordersShowAll;

  /// Orders error title.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load your orders'**
  String get ordersLoadErrorTitle;

  /// Next page failed bar.
  ///
  /// In en, this message translates to:
  /// **'More orders couldn\'t load.'**
  String get ordersMoreFailed;

  /// Signed-out orders tab title.
  ///
  /// In en, this message translates to:
  /// **'Sign in to see your orders'**
  String get ordersSignedOutTitle;

  /// Signed-out orders tab body.
  ///
  /// In en, this message translates to:
  /// **'Your orders and codes are kept in your account.'**
  String get ordersSignedOutBody;

  /// Signed-out orders tab action.
  ///
  /// In en, this message translates to:
  /// **'Sign in'**
  String get ordersSignIn;

  /// Link to guest order access.
  ///
  /// In en, this message translates to:
  /// **'Open a guest order'**
  String get guestAccessEntry;

  /// Guest order access hint.
  ///
  /// In en, this message translates to:
  /// **'Ordered on the web without an account? Use the link in your delivery email.'**
  String get guestAccessEntryBody;

  /// Order detail title for screen readers.
  ///
  /// In en, this message translates to:
  /// **'Order {number}'**
  String orderDetailSemantics(String number);

  /// Line quantity and unit price.
  ///
  /// In en, this message translates to:
  /// **'Qty {quantity} · {price} each'**
  String orderQtyEach(int quantity, String price);

  /// Line quantity.
  ///
  /// In en, this message translates to:
  /// **'Qty {quantity}'**
  String orderQty(int quantity);

  /// Unit row label.
  ///
  /// In en, this message translates to:
  /// **'Code'**
  String get orderUnitCode;

  /// Unit status.
  ///
  /// In en, this message translates to:
  /// **'Delivered'**
  String get orderUnitDelivered;

  /// Unit status: delivered and viewed before.
  ///
  /// In en, this message translates to:
  /// **'Revealed'**
  String get orderUnitRevealed;

  /// Unit status before delivery.
  ///
  /// In en, this message translates to:
  /// **'Preparing'**
  String get orderUnitPreparing;

  /// Unit status failed.
  ///
  /// In en, this message translates to:
  /// **'Not delivered'**
  String get orderUnitFailed;

  /// Unit status.
  ///
  /// In en, this message translates to:
  /// **'Refunded'**
  String get orderUnitRefunded;

  /// Unit status.
  ///
  /// In en, this message translates to:
  /// **'Refund pending'**
  String get orderUnitRefundPending;

  /// Unit status.
  ///
  /// In en, this message translates to:
  /// **'Cancelled'**
  String get orderUnitCancelled;

  /// Screen reader label of a unit row.
  ///
  /// In en, this message translates to:
  /// **'Code {index} of {name}, {status}'**
  String orderUnitSemantics(int index, String name, String status);

  /// Screen reader hint of a unit row.
  ///
  /// In en, this message translates to:
  /// **'Opens the code'**
  String get orderUnitOpenHint;

  /// Order total (display currency).
  ///
  /// In en, this message translates to:
  /// **'Total'**
  String get orderTotal;

  /// Amount charged in the payment currency.
  ///
  /// In en, this message translates to:
  /// **'Charged'**
  String get orderCharged;

  /// Payment row label.
  ///
  /// In en, this message translates to:
  /// **'Payment'**
  String get orderPayment;

  /// Payment attempt number.
  ///
  /// In en, this message translates to:
  /// **'attempt {attempt}'**
  String orderAttempt(int attempt);

  /// Payment status row.
  ///
  /// In en, this message translates to:
  /// **'Payment status'**
  String get orderPaymentStatus;

  /// Payment status.
  ///
  /// In en, this message translates to:
  /// **'Unpaid'**
  String get orderPaymentUnpaid;

  /// Payment status.
  ///
  /// In en, this message translates to:
  /// **'Processing'**
  String get orderPaymentProcessing;

  /// Payment status.
  ///
  /// In en, this message translates to:
  /// **'Paid'**
  String get orderPaymentPaid;

  /// Payment status.
  ///
  /// In en, this message translates to:
  /// **'Partially refunded'**
  String get orderPaymentPartiallyRefunded;

  /// Payment status.
  ///
  /// In en, this message translates to:
  /// **'Refunded'**
  String get orderPaymentRefunded;

  /// Order detail action.
  ///
  /// In en, this message translates to:
  /// **'Retry payment'**
  String get orderRetryPayment;

  /// Order detail notice.
  ///
  /// In en, this message translates to:
  /// **'This order is waiting for payment. Your last attempt did not go through.'**
  String get orderAwaitingPaymentNote;

  /// Order detail action.
  ///
  /// In en, this message translates to:
  /// **'Get help'**
  String get orderGetHelp;

  /// Order detail action.
  ///
  /// In en, this message translates to:
  /// **'Request refund'**
  String get orderRequestRefund;

  /// Order timeline heading.
  ///
  /// In en, this message translates to:
  /// **'Timeline'**
  String get orderTimeline;

  /// Timeline entry (pending payment).
  ///
  /// In en, this message translates to:
  /// **'Placed'**
  String get orderTimelinePlaced;

  /// Timeline entry.
  ///
  /// In en, this message translates to:
  /// **'Paid'**
  String get orderTimelinePaid;

  /// Refund banner.
  ///
  /// In en, this message translates to:
  /// **'Refund in progress'**
  String get orderRefundPendingTitle;

  /// Refund banner.
  ///
  /// In en, this message translates to:
  /// **'Part of this order was refunded'**
  String get orderRefundPartialTitle;

  /// Refund banner.
  ///
  /// In en, this message translates to:
  /// **'This order was refunded'**
  String get orderRefundFullTitle;

  /// Refund banner body, wallet destination.
  ///
  /// In en, this message translates to:
  /// **'{amount} was returned to your wallet.'**
  String orderRefundToWallet(String amount);

  /// Refund banner body, original destination.
  ///
  /// In en, this message translates to:
  /// **'{amount} was returned to your payment method.'**
  String orderRefundToOriginal(String amount);

  /// 404 order.
  ///
  /// In en, this message translates to:
  /// **'Order not found'**
  String get orderNotFoundTitle;

  /// 404 order body.
  ///
  /// In en, this message translates to:
  /// **'This order isn\'t in your account.'**
  String get orderNotFoundBody;

  /// Order error title.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load this order'**
  String get orderLoadErrorTitle;

  /// Refund request placeholder title.
  ///
  /// In en, this message translates to:
  /// **'Request a refund'**
  String get refundRequestTitle;

  /// Code screen title.
  ///
  /// In en, this message translates to:
  /// **'Your code'**
  String get revealTitle;

  /// Unit position.
  ///
  /// In en, this message translates to:
  /// **'Code {index} of {count}'**
  String revealCodeOf(int index, int count);

  /// Delivery time.
  ///
  /// In en, this message translates to:
  /// **'Delivered {date}'**
  String revealDeliveredAt(String date);

  /// Code box label.
  ///
  /// In en, this message translates to:
  /// **'Code'**
  String get revealCodeLabel;

  /// PIN row label.
  ///
  /// In en, this message translates to:
  /// **'PIN'**
  String get revealPinLabel;

  /// Masked code for screen readers.
  ///
  /// In en, this message translates to:
  /// **'Code hidden. Ends in {last}.'**
  String revealMaskedSemantics(String last);

  /// Masked code without a hint.
  ///
  /// In en, this message translates to:
  /// **'Code hidden.'**
  String get revealMaskedSemanticsNoHint;

  /// Reveal button.
  ///
  /// In en, this message translates to:
  /// **'Reveal code'**
  String get revealButton;

  /// Reveal button hint.
  ///
  /// In en, this message translates to:
  /// **'Asks you to confirm it\'s you, then shows the code for 60 seconds'**
  String get revealButtonHint;

  /// Copy button.
  ///
  /// In en, this message translates to:
  /// **'Copy code'**
  String get revealCopy;

  /// Copy button label for screen readers.
  ///
  /// In en, this message translates to:
  /// **'Copy the code. The clipboard clears after 60 seconds'**
  String get revealCopySemantics;

  /// Hide button.
  ///
  /// In en, this message translates to:
  /// **'Hide'**
  String get revealHide;

  /// Hide button label for screen readers.
  ///
  /// In en, this message translates to:
  /// **'Hide the code now'**
  String get revealHideSemantics;

  /// Button that announces the code to the screen reader.
  ///
  /// In en, this message translates to:
  /// **'Read code aloud'**
  String get revealReadAloud;

  /// Revealed code box for screen readers (the code is not read automatically).
  ///
  /// In en, this message translates to:
  /// **'Code shown. Use Read code aloud to hear it.'**
  String get revealShownSemantics;

  /// Snackbar after copy.
  ///
  /// In en, this message translates to:
  /// **'Code copied. Clipboard clears in 60 s.'**
  String get revealCopied;

  /// Countdown before auto-hide.
  ///
  /// In en, this message translates to:
  /// **'Hides in {time}'**
  String revealHidesIn(String time);

  /// Redemption link.
  ///
  /// In en, this message translates to:
  /// **'Open redemption page'**
  String get revealOpenRedemption;

  /// Security note on Android.
  ///
  /// In en, this message translates to:
  /// **'Screenshots are blocked here. Every reveal is logged.'**
  String get revealSecureNoteAndroid;

  /// Security note on iOS (screenshots cannot be blocked).
  ///
  /// In en, this message translates to:
  /// **'The code hides in the app switcher and while the screen is recorded. Every reveal is logged.'**
  String get revealSecureNoteIos;

  /// Instructions heading.
  ///
  /// In en, this message translates to:
  /// **'How to redeem'**
  String get revealHowTo;

  /// Before the first reveal the API has no instructions.
  ///
  /// In en, this message translates to:
  /// **'Redemption steps appear with the code.'**
  String get revealHowToAfterReveal;

  /// Announcement after auto-hide.
  ///
  /// In en, this message translates to:
  /// **'Code hidden after 60 seconds.'**
  String get revealHiddenTimeout;

  /// Announcement after a hide.
  ///
  /// In en, this message translates to:
  /// **'Code hidden.'**
  String get revealHidden;

  /// iOS capture banner.
  ///
  /// In en, this message translates to:
  /// **'Screen recording detected'**
  String get revealCaptureTitle;

  /// iOS capture banner body.
  ///
  /// In en, this message translates to:
  /// **'On iPhone we can\'t block screenshots, so the code is hidden while recording or sharing.'**
  String get revealCaptureBody;

  /// Code box while captured.
  ///
  /// In en, this message translates to:
  /// **'Code hidden while the screen is being recorded or shared.'**
  String get revealCaptureHidden;

  /// Privacy cover label.
  ///
  /// In en, this message translates to:
  /// **'Hidden while the app is not in front'**
  String get revealObscured;

  /// 403 reveal_denied.
  ///
  /// In en, this message translates to:
  /// **'This code can\'t be shown'**
  String get revealDeniedTitle;

  /// 403 body.
  ///
  /// In en, this message translates to:
  /// **'Check that your email is verified and the order is paid. Every attempt is logged.'**
  String get revealDeniedBody;

  /// Guest 403 body.
  ///
  /// In en, this message translates to:
  /// **'Open the link in your delivery email and confirm the emailed code on this device first.'**
  String get revealGuestDeniedBody;

  /// Unverified email banner.
  ///
  /// In en, this message translates to:
  /// **'Verify your email to reveal codes'**
  String get revealUnverifiedTitle;

  /// Unverified email banner body.
  ///
  /// In en, this message translates to:
  /// **'We sent a link to your email address.'**
  String get revealUnverifiedBody;

  /// Opens the verify email screen.
  ///
  /// In en, this message translates to:
  /// **'Verify email'**
  String get revealVerifyAction;

  /// 429 banner.
  ///
  /// In en, this message translates to:
  /// **'Too many reveals'**
  String get revealRateLimitedTitle;

  /// 429 body with the wait.
  ///
  /// In en, this message translates to:
  /// **'For security you can reveal 10 codes per hour. Try again in {minutes, plural, =1{1 minute} other{{minutes} minutes}}.'**
  String revealRateLimitedBody(int minutes);

  /// 429 body without a wait.
  ///
  /// In en, this message translates to:
  /// **'For security you can reveal 10 codes per hour. Try again later.'**
  String get revealRateLimitedLater;

  /// Disabled reveal button while rate limited.
  ///
  /// In en, this message translates to:
  /// **'Try again in {time}'**
  String revealTryAgainIn(String time);

  /// Rooted device banner.
  ///
  /// In en, this message translates to:
  /// **'This device may be modified'**
  String get revealIntegrityTitle;

  /// Rooted device banner body.
  ///
  /// In en, this message translates to:
  /// **'Rooted or jailbroken devices are less safe for codes. Reveal on another device if you can.'**
  String get revealIntegrityBody;

  /// 404 unit.
  ///
  /// In en, this message translates to:
  /// **'Code not found'**
  String get revealNotFoundTitle;

  /// 404 unit body.
  ///
  /// In en, this message translates to:
  /// **'This code isn\'t in your orders.'**
  String get revealNotFoundBody;

  /// Other reveal failure.
  ///
  /// In en, this message translates to:
  /// **'The code couldn\'t be shown'**
  String get revealFailedTitle;

  /// Unit not revealable.
  ///
  /// In en, this message translates to:
  /// **'This code isn\'t ready yet. We\'ll email you when it is.'**
  String get revealNotReady;

  /// System biometric prompt title.
  ///
  /// In en, this message translates to:
  /// **'Confirm it\'s you to reveal the code'**
  String get revealBiometricPrompt;

  /// Re-auth sheet title.
  ///
  /// In en, this message translates to:
  /// **'Confirm it\'s you'**
  String get reauthTitle;

  /// Re-auth sheet body.
  ///
  /// In en, this message translates to:
  /// **'Enter your password to reveal codes. We ask again after 15 minutes.'**
  String get reauthBody;

  /// Re-auth sheet button.
  ///
  /// In en, this message translates to:
  /// **'Confirm and reveal'**
  String get reauthConfirm;

  /// Re-auth sheet link.
  ///
  /// In en, this message translates to:
  /// **'Forgot password?'**
  String get reauthForgot;

  /// Guest order screen title.
  ///
  /// In en, this message translates to:
  /// **'Guest order'**
  String get guestOrderTitle;

  /// Guest order screen title with number.
  ///
  /// In en, this message translates to:
  /// **'Order {number}'**
  String guestOrderNumberTitle(String number);

  /// Guest step 11.1.
  ///
  /// In en, this message translates to:
  /// **'Verify it\'s you'**
  String get guestVerifyTitle;

  /// Guest step 11.1 body.
  ///
  /// In en, this message translates to:
  /// **'To open the codes for this order we\'ll send a 6-digit code to the email you ordered with.'**
  String get guestVerifyBody;

  /// Guest email field.
  ///
  /// In en, this message translates to:
  /// **'Order email'**
  String get guestEmailLabel;

  /// Guest info banner.
  ///
  /// In en, this message translates to:
  /// **'Link verified'**
  String get guestLinkVerifiedTitle;

  /// Guest info banner body.
  ///
  /// In en, this message translates to:
  /// **'You opened this order from the delivery email. The link is valid for 72 hours.'**
  String get guestLinkVerifiedBody;

  /// Guest send OTP button.
  ///
  /// In en, this message translates to:
  /// **'Send code'**
  String get guestSendCode;

  /// Guest step 11.2.
  ///
  /// In en, this message translates to:
  /// **'Enter the code'**
  String get guestCodeTitle;

  /// Guest step 11.2 body.
  ///
  /// In en, this message translates to:
  /// **'If {email} matches the order, we sent it a 6-digit code. It expires in 10 minutes.'**
  String guestCodeBody(String email);

  /// OTP input label.
  ///
  /// In en, this message translates to:
  /// **'6-digit code from the email'**
  String get guestCodeSemantics;

  /// Guest verify button.
  ///
  /// In en, this message translates to:
  /// **'Verify'**
  String get guestVerify;

  /// Guest resend.
  ///
  /// In en, this message translates to:
  /// **'Resend code'**
  String get guestResend;

  /// Guest resend cooldown.
  ///
  /// In en, this message translates to:
  /// **'Resend in {time}'**
  String guestResendIn(String time);

  /// Back to the email step.
  ///
  /// In en, this message translates to:
  /// **'Change email'**
  String get guestChangeEmail;

  /// Tag on the guest order.
  ///
  /// In en, this message translates to:
  /// **'Guest order'**
  String get guestOrderLabel;

  /// Create account prompt.
  ///
  /// In en, this message translates to:
  /// **'Keep your codes in one place'**
  String get guestAccountPromptTitle;

  /// Create account prompt body.
  ///
  /// In en, this message translates to:
  /// **'Create an account with your order email to see future orders here.'**
  String get guestAccountPromptBody;

  /// Create account button.
  ///
  /// In en, this message translates to:
  /// **'Create account'**
  String get guestCreateAccount;

  /// Guest 11.5.
  ///
  /// In en, this message translates to:
  /// **'This link has expired'**
  String get guestLinkExpiredTitle;

  /// Guest 11.5 body.
  ///
  /// In en, this message translates to:
  /// **'Order links work for 72 hours. Contact support to get a new one.'**
  String get guestLinkExpiredBody;

  /// No guest proof on this device.
  ///
  /// In en, this message translates to:
  /// **'Open your delivery email'**
  String get guestNeedLinkTitle;

  /// No guest proof body.
  ///
  /// In en, this message translates to:
  /// **'To see the codes of a guest order on this device, open the link in the delivery email.'**
  String get guestNeedLinkBody;

  /// Guest order without the link proof.
  ///
  /// In en, this message translates to:
  /// **'Open the link in the delivery email again to reveal codes on this device.'**
  String get guestLinkProofMissing;

  /// Paste link page title.
  ///
  /// In en, this message translates to:
  /// **'Open a guest order'**
  String get guestEntryTitle;

  /// Paste link page body.
  ///
  /// In en, this message translates to:
  /// **'Paste the link from your delivery email. We\'ll email you a code to confirm it\'s you.'**
  String get guestEntryBody;

  /// Paste link field.
  ///
  /// In en, this message translates to:
  /// **'Order link'**
  String get guestEntryLabel;

  /// Invalid pasted link.
  ///
  /// In en, this message translates to:
  /// **'This isn\'t a boxcards.shop order link.'**
  String get guestEntryInvalid;

  /// Paste link button.
  ///
  /// In en, this message translates to:
  /// **'Open order'**
  String get guestEntryOpen;

  /// Payment provider name for the store wallet.
  ///
  /// In en, this message translates to:
  /// **'Wallet'**
  String get orderGatewayWallet;

  /// Wallet screen title and Account tab entry.
  ///
  /// In en, this message translates to:
  /// **'Wallet'**
  String get walletTitle;

  /// Account tab wallet entry subtitle.
  ///
  /// In en, this message translates to:
  /// **'Store credit and statement'**
  String get walletAccountSubtitle;

  /// Balance card label.
  ///
  /// In en, this message translates to:
  /// **'Store credit'**
  String get walletStoreCredit;

  /// Wallet status badge, active.
  ///
  /// In en, this message translates to:
  /// **'Active'**
  String get walletStatusActive;

  /// Wallet status badge, frozen.
  ///
  /// In en, this message translates to:
  /// **'Frozen'**
  String get walletStatusFrozen;

  /// Balance card note (no top-up, BD3).
  ///
  /// In en, this message translates to:
  /// **'From refunds and staff credits. It can pay for orders; there is no top-up.'**
  String get walletBalanceNote;

  /// Screen reader label of the balance card.
  ///
  /// In en, this message translates to:
  /// **'Store credit balance {amount}, {status}'**
  String walletBalanceSemantics(String amount, String status);

  /// Statement section title.
  ///
  /// In en, this message translates to:
  /// **'Statement'**
  String get walletStatement;

  /// Frozen wallet banner title.
  ///
  /// In en, this message translates to:
  /// **'Wallet frozen'**
  String get walletFrozenTitle;

  /// Frozen wallet banner body.
  ///
  /// In en, this message translates to:
  /// **'You can’t pay with store credit right now. Refunds still arrive. Contact support to learn more.'**
  String get walletFrozenBody;

  /// Frozen wallet footer button.
  ///
  /// In en, this message translates to:
  /// **'Contact support'**
  String get walletContactSupport;

  /// Empty statement title.
  ///
  /// In en, this message translates to:
  /// **'No transactions yet'**
  String get walletEmptyTitle;

  /// Empty statement body.
  ///
  /// In en, this message translates to:
  /// **'Refunds and credits will appear here.'**
  String get walletEmptyBody;

  /// Wallet load error title.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load your wallet'**
  String get walletLoadErrorTitle;

  /// Next statement page failed.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load more transactions'**
  String get walletMoreFailed;

  /// Wallet turned off in the app configuration.
  ///
  /// In en, this message translates to:
  /// **'Wallet isn\'t available'**
  String get walletUnavailableTitle;

  /// Wallet turned off, body.
  ///
  /// In en, this message translates to:
  /// **'Store credit can\'t be viewed in the app right now.'**
  String get walletUnavailableBody;

  /// Statement line type: refund credit.
  ///
  /// In en, this message translates to:
  /// **'Refund'**
  String get walletTypeCreditRefund;

  /// Statement line type: staff credit.
  ///
  /// In en, this message translates to:
  /// **'Credit from support'**
  String get walletTypeCreditAdmin;

  /// Statement line type: paid an order.
  ///
  /// In en, this message translates to:
  /// **'Payment'**
  String get walletTypeDebitPurchase;

  /// Statement line type: staff debit.
  ///
  /// In en, this message translates to:
  /// **'Adjustment by support'**
  String get walletTypeDebitAdmin;

  /// Statement line type: reversal.
  ///
  /// In en, this message translates to:
  /// **'Reversal'**
  String get walletTypeReversal;

  /// Statement line type not known to this version.
  ///
  /// In en, this message translates to:
  /// **'Wallet transaction'**
  String get walletTypeOther;

  /// Statement line title with the order number.
  ///
  /// In en, this message translates to:
  /// **'{type} · {order}'**
  String walletLineWithOrder(String type, String order);

  /// Screen reader label of a statement line.
  ///
  /// In en, this message translates to:
  /// **'{title}, {amount}, balance {balance}, {date}'**
  String walletLineSemantics(
    String title,
    String amount,
    String balance,
    String date,
  );

  /// Screen reader hint of a statement line linked to an order.
  ///
  /// In en, this message translates to:
  /// **'Opens the order'**
  String get walletLineOpenOrderHint;

  /// Wallet payment option detail with the wallet balance.
  ///
  /// In en, this message translates to:
  /// **'Store credit · balance {amount}'**
  String checkoutWalletBalance(String amount);

  /// Account hub header when signed out.
  ///
  /// In en, this message translates to:
  /// **'Your account'**
  String get accountSignedOutTitle;

  /// Account hub text when signed out.
  ///
  /// In en, this message translates to:
  /// **'Sign in to see your orders, codes and store credit.'**
  String get accountSignedOutBody;

  /// Account hub header for screen readers.
  ///
  /// In en, this message translates to:
  /// **'Signed in as {name}, {email}'**
  String accountHeaderSemantics(String name, String email);

  /// Badge: email verified.
  ///
  /// In en, this message translates to:
  /// **'Verified'**
  String get accountVerifiedBadge;

  /// Badge: email not verified.
  ///
  /// In en, this message translates to:
  /// **'Not verified'**
  String get accountUnverifiedBadge;

  /// Account hub row.
  ///
  /// In en, this message translates to:
  /// **'Notifications'**
  String get accountNotifications;

  /// Account hub row.
  ///
  /// In en, this message translates to:
  /// **'Support'**
  String get accountSupport;

  /// Account hub row and Profile title.
  ///
  /// In en, this message translates to:
  /// **'Profile'**
  String get accountProfile;

  /// Account hub row and Security title.
  ///
  /// In en, this message translates to:
  /// **'Security'**
  String get accountSecurity;

  /// Account hub row and Settings title.
  ///
  /// In en, this message translates to:
  /// **'Settings'**
  String get accountSettings;

  /// Security row subtitle.
  ///
  /// In en, this message translates to:
  /// **'Two-step verification on'**
  String get accountTwoFactorOn;

  /// Security row subtitle.
  ///
  /// In en, this message translates to:
  /// **'Two-step verification off'**
  String get accountTwoFactorOff;

  /// Signed-out hub section title.
  ///
  /// In en, this message translates to:
  /// **'Ordered without an account?'**
  String get accountGuestSection;

  /// Installed app version in Settings.
  ///
  /// In en, this message translates to:
  /// **'Version {version}'**
  String appVersionLabel(String version);

  /// Settings section.
  ///
  /// In en, this message translates to:
  /// **'Preferences'**
  String get settingsPreferences;

  /// Settings section.
  ///
  /// In en, this message translates to:
  /// **'About'**
  String get settingsAbout;

  /// Settings link.
  ///
  /// In en, this message translates to:
  /// **'Terms of Sale'**
  String get settingsTerms;

  /// Settings link.
  ///
  /// In en, this message translates to:
  /// **'Privacy policy'**
  String get settingsPrivacy;

  /// Settings link.
  ///
  /// In en, this message translates to:
  /// **'Refund policy'**
  String get settingsRefundPolicy;

  /// Settings link.
  ///
  /// In en, this message translates to:
  /// **'Help centre'**
  String get settingsHelpCentre;

  /// Screen reader hint of a link row.
  ///
  /// In en, this message translates to:
  /// **'Opens in your browser'**
  String get opensInBrowserHint;

  /// Snackbar after a language change.
  ///
  /// In en, this message translates to:
  /// **'Language changed.'**
  String get languageChanged;

  /// Profile field.
  ///
  /// In en, this message translates to:
  /// **'Full name'**
  String get profileNameLabel;

  /// Profile field.
  ///
  /// In en, this message translates to:
  /// **'Email'**
  String get profileEmailLabel;

  /// Profile email helper.
  ///
  /// In en, this message translates to:
  /// **'Contact support to change your email.'**
  String get profileEmailHelper;

  /// Profile field.
  ///
  /// In en, this message translates to:
  /// **'Phone (optional)'**
  String get profilePhoneLabel;

  /// Profile phone helper.
  ///
  /// In en, this message translates to:
  /// **'With the country code, for example +20 100 123 4567.'**
  String get profilePhoneHelper;

  /// Profile field.
  ///
  /// In en, this message translates to:
  /// **'Country'**
  String get profileCountryLabel;

  /// Profile section.
  ///
  /// In en, this message translates to:
  /// **'Language and currency'**
  String get profileRegionalSection;

  /// Profile save button.
  ///
  /// In en, this message translates to:
  /// **'Save changes'**
  String get profileSave;

  /// Snackbar.
  ///
  /// In en, this message translates to:
  /// **'Profile saved.'**
  String get profileSaved;

  /// Snackbar.
  ///
  /// In en, this message translates to:
  /// **'Nothing to save.'**
  String get profileUnchanged;

  /// Local check.
  ///
  /// In en, this message translates to:
  /// **'Use at most 120 characters.'**
  String get profileNameTooLong;

  /// Profile banner title.
  ///
  /// In en, this message translates to:
  /// **'Your email isn\'t verified'**
  String get profileUnverifiedTitle;

  /// Profile banner body.
  ///
  /// In en, this message translates to:
  /// **'Verify it to reveal codes and write reviews. Check your inbox for the link.'**
  String get profileUnverifiedBody;

  /// Profile country field error.
  ///
  /// In en, this message translates to:
  /// **'Countries couldn\'t be loaded.'**
  String get profileCountriesFailed;

  /// Title and Security row.
  ///
  /// In en, this message translates to:
  /// **'Change password'**
  String get changePasswordTitle;

  /// Security row subtitle.
  ///
  /// In en, this message translates to:
  /// **'Signs out your other devices'**
  String get changePasswordSubtitle;

  /// Field.
  ///
  /// In en, this message translates to:
  /// **'Current password'**
  String get currentPasswordLabel;

  /// Field.
  ///
  /// In en, this message translates to:
  /// **'New password'**
  String get newPasswordLabel;

  /// New password helper (customer policy).
  ///
  /// In en, this message translates to:
  /// **'At least 10 characters, different from your current password.'**
  String get newPasswordHelper;

  /// Field.
  ///
  /// In en, this message translates to:
  /// **'Confirm new password'**
  String get confirmNewPasswordLabel;

  /// Info banner.
  ///
  /// In en, this message translates to:
  /// **'Other devices will be signed out'**
  String get changePasswordNoticeTitle;

  /// Info banner.
  ///
  /// In en, this message translates to:
  /// **'This device stays signed in.'**
  String get changePasswordNoticeBody;

  /// Button.
  ///
  /// In en, this message translates to:
  /// **'Update password'**
  String get changePasswordAction;

  /// Snackbar.
  ///
  /// In en, this message translates to:
  /// **'Password changed. Your other devices were signed out.'**
  String get passwordChanged;

  /// Banner after a password change when biometric unlock was on.
  ///
  /// In en, this message translates to:
  /// **'Turn biometric unlock on again'**
  String get passwordChangedBiometricTitle;

  /// Banner body.
  ///
  /// In en, this message translates to:
  /// **'Changing your password turned biometric unlock off on every device. Turn it on below if you want it.'**
  String get passwordChangedBiometricBody;

  /// Security section.
  ///
  /// In en, this message translates to:
  /// **'Sign-in'**
  String get securitySignInSection;

  /// Security section.
  ///
  /// In en, this message translates to:
  /// **'Devices'**
  String get securityDevicesSection;

  /// Title and row.
  ///
  /// In en, this message translates to:
  /// **'Two-step verification'**
  String get twoFactorTitle;

  /// Security row subtitle.
  ///
  /// In en, this message translates to:
  /// **'Off · protect codes with an authenticator app'**
  String get twoFactorOffSubtitle;

  /// Security row subtitle.
  ///
  /// In en, this message translates to:
  /// **'On · a code from your authenticator app at sign-in'**
  String get twoFactorOnSubtitle;

  /// Row action.
  ///
  /// In en, this message translates to:
  /// **'Turn on'**
  String get twoFactorTurnOn;

  /// Row action.
  ///
  /// In en, this message translates to:
  /// **'Turn off'**
  String get twoFactorTurnOff;

  /// Security row and Devices title.
  ///
  /// In en, this message translates to:
  /// **'Signed-in devices'**
  String get devicesTitle;

  /// Security row subtitle.
  ///
  /// In en, this message translates to:
  /// **'{count, plural, =1{1 device} other{{count} devices}}'**
  String devicesCount(int count);

  /// Info banner.
  ///
  /// In en, this message translates to:
  /// **'Revealing codes asks you to confirm it\'s you'**
  String get securityRevealNoticeTitle;

  /// Info banner.
  ///
  /// In en, this message translates to:
  /// **'At most every 15 minutes on each device: your password, or biometrics where biometric unlock is on.'**
  String get securityRevealNoticeBody;

  /// Security button.
  ///
  /// In en, this message translates to:
  /// **'Sign out everywhere'**
  String get signOutEverywhere;

  /// Dialog title.
  ///
  /// In en, this message translates to:
  /// **'Sign out everywhere?'**
  String get signOutEverywhereTitle;

  /// Dialog body.
  ///
  /// In en, this message translates to:
  /// **'Every device, this one included, will need your password to sign in again.'**
  String get signOutEverywhereBody;

  /// Snackbar.
  ///
  /// In en, this message translates to:
  /// **'Signed out on every device.'**
  String get signedOutEverywhere;

  /// Enrolment step 0.
  ///
  /// In en, this message translates to:
  /// **'Enter your password to start. You\'ll need an authenticator app on your phone.'**
  String get twoFactorPasswordBody;

  /// Enrolment step.
  ///
  /// In en, this message translates to:
  /// **'1. Scan with your authenticator app'**
  String get twoFactorScanStep;

  /// QR for screen readers.
  ///
  /// In en, this message translates to:
  /// **'QR code for your authenticator app'**
  String get twoFactorQrSemantics;

  /// Enrolment key label.
  ///
  /// In en, this message translates to:
  /// **'Or enter this key'**
  String get twoFactorKeyLabel;

  /// Copy button label.
  ///
  /// In en, this message translates to:
  /// **'Copy key'**
  String get twoFactorCopyKey;

  /// Snackbar.
  ///
  /// In en, this message translates to:
  /// **'Key copied. The clipboard is cleared in 60 seconds.'**
  String get twoFactorKeyCopied;

  /// Enrolment step.
  ///
  /// In en, this message translates to:
  /// **'2. Enter the 6-digit code'**
  String get twoFactorCodeStep;

  /// OTP field label.
  ///
  /// In en, this message translates to:
  /// **'6-digit code from your authenticator app'**
  String get twoFactorCodeSemantics;

  /// Local check.
  ///
  /// In en, this message translates to:
  /// **'Enter the 6 digits from your app.'**
  String get twoFactorCodeInvalid;

  /// Note under the key.
  ///
  /// In en, this message translates to:
  /// **'Keep this key private. Screenshots are blocked where your phone allows.'**
  String get twoFactorSecureNote;

  /// Title.
  ///
  /// In en, this message translates to:
  /// **'Recovery codes'**
  String get recoveryCodesTitle;

  /// Success banner.
  ///
  /// In en, this message translates to:
  /// **'Two-step verification is on'**
  String get twoFactorOnBannerTitle;

  /// Success banner.
  ///
  /// In en, this message translates to:
  /// **'Next time you sign in, we\'ll ask for a code.'**
  String get twoFactorOnBannerBody;

  /// Recovery codes text.
  ///
  /// In en, this message translates to:
  /// **'Save these codes somewhere safe. Each works once if you lose your phone. We will not show them again.'**
  String get recoveryCodesBody;

  /// Codes for screen readers.
  ///
  /// In en, this message translates to:
  /// **'Recovery codes: {codes}'**
  String recoveryCodesListSemantics(String codes);

  /// Button.
  ///
  /// In en, this message translates to:
  /// **'Copy all'**
  String get recoveryCodesCopy;

  /// Snackbar.
  ///
  /// In en, this message translates to:
  /// **'Codes copied. The clipboard is cleared in 60 seconds.'**
  String get recoveryCodesCopied;

  /// Button.
  ///
  /// In en, this message translates to:
  /// **'I saved my codes'**
  String get recoveryCodesSaved;

  /// Dialog when leaving the codes.
  ///
  /// In en, this message translates to:
  /// **'Saved your codes?'**
  String get recoveryCodesLeaveTitle;

  /// Dialog body.
  ///
  /// In en, this message translates to:
  /// **'You won\'t see these codes again. Without them, losing your phone can lock you out.'**
  String get recoveryCodesLeaveBody;

  /// Dialog: stay.
  ///
  /// In en, this message translates to:
  /// **'Back to codes'**
  String get recoveryCodesShowAgain;

  /// Already enabled state.
  ///
  /// In en, this message translates to:
  /// **'Two-step verification is already on'**
  String get twoFactorAlreadyOnTitle;

  /// Already enabled state.
  ///
  /// In en, this message translates to:
  /// **'To set it up again, turn it off first.'**
  String get twoFactorAlreadyOnBody;

  /// Snackbar.
  ///
  /// In en, this message translates to:
  /// **'Two-step verification is on.'**
  String get twoFactorEnabled;

  /// Sheet title.
  ///
  /// In en, this message translates to:
  /// **'Turn off two-step verification?'**
  String get twoFactorDisableTitle;

  /// Sheet body.
  ///
  /// In en, this message translates to:
  /// **'Your account will be protected by your password only. Enter it to continue.'**
  String get twoFactorDisableBody;

  /// Sheet cancel.
  ///
  /// In en, this message translates to:
  /// **'Keep it on'**
  String get twoFactorKeepOn;

  /// Snackbar.
  ///
  /// In en, this message translates to:
  /// **'Two-step verification is off.'**
  String get twoFactorDisabled;

  /// Devices title.
  ///
  /// In en, this message translates to:
  /// **'Devices'**
  String get devicesPageTitle;

  /// Device row.
  ///
  /// In en, this message translates to:
  /// **'This device'**
  String get deviceThisDevice;

  /// Device row trailing.
  ///
  /// In en, this message translates to:
  /// **'Current'**
  String get deviceCurrentBadge;

  /// Device row.
  ///
  /// In en, this message translates to:
  /// **'Last used {date}'**
  String deviceLastUsed(String date);

  /// Device row.
  ///
  /// In en, this message translates to:
  /// **'IP {ip}'**
  String deviceIp(String ip);

  /// Device without a name.
  ///
  /// In en, this message translates to:
  /// **'Unnamed device'**
  String get deviceUnnamed;

  /// Browser session.
  ///
  /// In en, this message translates to:
  /// **'Web browser'**
  String get deviceWebBrowser;

  /// Devices note.
  ///
  /// In en, this message translates to:
  /// **'Web sessions are not listed here.'**
  String get devicesWebNotListed;

  /// Device action.
  ///
  /// In en, this message translates to:
  /// **'Sign out'**
  String get deviceSignOut;

  /// Device action for screen readers.
  ///
  /// In en, this message translates to:
  /// **'Sign out {name}'**
  String deviceSignOutSemantics(String name);

  /// Dialog title.
  ///
  /// In en, this message translates to:
  /// **'Sign out “{name}”?'**
  String deviceRevokeTitle(String name);

  /// Dialog body.
  ///
  /// In en, this message translates to:
  /// **'That device will need your password to sign in again.'**
  String get deviceRevokeBody;

  /// Dialog action.
  ///
  /// In en, this message translates to:
  /// **'Sign out device'**
  String get deviceRevokeAction;

  /// Snackbar.
  ///
  /// In en, this message translates to:
  /// **'{name} was signed out.'**
  String deviceSignedOut(String name);

  /// Devices note.
  ///
  /// In en, this message translates to:
  /// **'No other device is signed in.'**
  String get devicesOnlyThis;

  /// Button.
  ///
  /// In en, this message translates to:
  /// **'Sign out of all other devices'**
  String get signOutOthersAction;

  /// Sheet title.
  ///
  /// In en, this message translates to:
  /// **'Sign out of all other devices?'**
  String get signOutOthersTitle;

  /// Sheet body.
  ///
  /// In en, this message translates to:
  /// **'Enter your password. This device stays signed in.'**
  String get signOutOthersBody;

  /// Snackbar.
  ///
  /// In en, this message translates to:
  /// **'Your other devices were signed out.'**
  String get signOutOthersDone;

  /// Display currency row value when the server picks the currency (owner decision 1: the country's currency, else USD).
  ///
  /// In en, this message translates to:
  /// **'Automatic'**
  String get displayCurrencyAutomatic;

  /// Banner when a next page of a list failed.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load more'**
  String get listMoreFailed;

  /// My reviews screen title and Account row.
  ///
  /// In en, this message translates to:
  /// **'My reviews'**
  String get myReviewsTitle;

  /// Error title.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load your reviews'**
  String get myReviewsLoadError;

  /// Empty state (13.3).
  ///
  /// In en, this message translates to:
  /// **'No reviews yet'**
  String get myReviewsEmptyTitle;

  /// Empty state body.
  ///
  /// In en, this message translates to:
  /// **'After a code is delivered you can rate it here.'**
  String get myReviewsEmptyBody;

  /// Section of eligible items (13.1).
  ///
  /// In en, this message translates to:
  /// **'Ready to review'**
  String get reviewsReadyTitle;

  /// Section of the customer's reviews (13.1).
  ///
  /// In en, this message translates to:
  /// **'Your reviews'**
  String get reviewsYoursTitle;

  /// Order line under a reviewable item; number is an LTR island.
  ///
  /// In en, this message translates to:
  /// **'Order {number}'**
  String reviewOrderNumber(String number);

  /// Screen-reader hint of a reviewable item.
  ///
  /// In en, this message translates to:
  /// **'Opens the review form'**
  String get reviewWriteHint;

  /// Button on the product page and delivered order lines.
  ///
  /// In en, this message translates to:
  /// **'Write a review'**
  String get reviewWriteAction;

  /// Screen-reader label of the order line button.
  ///
  /// In en, this message translates to:
  /// **'Write a review of {product}'**
  String reviewWriteSemantics(String product);

  /// Review form title (13.2).
  ///
  /// In en, this message translates to:
  /// **'Write a review'**
  String get reviewWriteTitle;

  /// Review form title when editing.
  ///
  /// In en, this message translates to:
  /// **'Edit review'**
  String get reviewEditTitle;

  /// Edit button on a review card.
  ///
  /// In en, this message translates to:
  /// **'Edit'**
  String get reviewEditAction;

  /// Screen-reader label of Edit.
  ///
  /// In en, this message translates to:
  /// **'Edit your review of {product}'**
  String reviewEditSemantics(String product);

  /// Info banner on the edit form.
  ///
  /// In en, this message translates to:
  /// **'Edits are checked again'**
  String get reviewEditNoticeTitle;

  /// Info banner body on the edit form.
  ///
  /// In en, this message translates to:
  /// **'Your review goes back to moderation and is hidden until it is approved.'**
  String get reviewEditNoticeBody;

  /// Review status pending.
  ///
  /// In en, this message translates to:
  /// **'Pending'**
  String get reviewStatusPending;

  /// Review status approved.
  ///
  /// In en, this message translates to:
  /// **'Published'**
  String get reviewStatusApproved;

  /// Review status rejected.
  ///
  /// In en, this message translates to:
  /// **'Not published'**
  String get reviewStatusRejected;

  /// Label above the star input.
  ///
  /// In en, this message translates to:
  /// **'Your rating'**
  String get reviewRatingLabel;

  /// Screen-reader label of one star button.
  ///
  /// In en, this message translates to:
  /// **'{count} of 5 stars'**
  String reviewRateStars(int count);

  /// Local check: no rating.
  ///
  /// In en, this message translates to:
  /// **'Choose a rating from 1 to 5 stars.'**
  String get reviewRatingRequired;

  /// Review title field.
  ///
  /// In en, this message translates to:
  /// **'Title (optional)'**
  String get reviewTitleLabel;

  /// Review text field.
  ///
  /// In en, this message translates to:
  /// **'Review (optional)'**
  String get reviewBodyLabel;

  /// Helper under the review text.
  ///
  /// In en, this message translates to:
  /// **'Reviews are checked before they\'re published.'**
  String get reviewModerationNote;

  /// Footer button.
  ///
  /// In en, this message translates to:
  /// **'Submit review'**
  String get reviewSubmitAction;

  /// Footer button when editing.
  ///
  /// In en, this message translates to:
  /// **'Save changes'**
  String get reviewSaveAction;

  /// Snackbar after submitting.
  ///
  /// In en, this message translates to:
  /// **'Thank you. Your review will appear once it\'s approved.'**
  String get reviewSubmitted;

  /// Snackbar after editing.
  ///
  /// In en, this message translates to:
  /// **'Saved. Your review will appear again once it\'s approved.'**
  String get reviewUpdated;

  /// Error title of the form.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load this review'**
  String get reviewLoadError;

  /// Edit form for an unknown review.
  ///
  /// In en, this message translates to:
  /// **'Review not found'**
  String get reviewNotFoundTitle;

  /// Not eligible (13.3).
  ///
  /// In en, this message translates to:
  /// **'This item can\'t be reviewed'**
  String get reviewNotEligibleTitle;

  /// Not eligible body.
  ///
  /// In en, this message translates to:
  /// **'Only items you bought and received can be reviewed, once each.'**
  String get reviewNotEligibleBody;

  /// Banner for 403 email_unverified (13.3).
  ///
  /// In en, this message translates to:
  /// **'Verify your email to write reviews'**
  String get reviewEmailUnverifiedTitle;

  /// Banner body.
  ///
  /// In en, this message translates to:
  /// **'Open the link we sent to your email address, then try again.'**
  String get reviewEmailUnverifiedBody;

  /// Banner for 409 review_exists (13.3).
  ///
  /// In en, this message translates to:
  /// **'You already reviewed this item'**
  String get reviewExistsTitle;

  /// Banner body.
  ///
  /// In en, this message translates to:
  /// **'Each purchased item can be reviewed once. You can edit your review in My reviews.'**
  String get reviewExistsBody;

  /// Order label in support screens; number is an LTR island.
  ///
  /// In en, this message translates to:
  /// **'Order {number}'**
  String supportOrderNumber(String number);

  /// Support signed out.
  ///
  /// In en, this message translates to:
  /// **'Sign in to contact support'**
  String get supportSignedOutTitle;

  /// Support signed out body.
  ///
  /// In en, this message translates to:
  /// **'Support requests are linked to your account and orders.'**
  String get supportSignedOutBody;

  /// support_enabled off.
  ///
  /// In en, this message translates to:
  /// **'Support isn\'t available in the app'**
  String get supportUnavailableTitle;

  /// support_enabled off body.
  ///
  /// In en, this message translates to:
  /// **'You can still reach us through our help centre.'**
  String get supportUnavailableBody;

  /// Error title.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load your requests'**
  String get ticketsLoadError;

  /// Empty tickets (14.4).
  ///
  /// In en, this message translates to:
  /// **'No requests yet'**
  String get ticketsEmptyTitle;

  /// Empty tickets body.
  ///
  /// In en, this message translates to:
  /// **'Questions about an order, payment or your account? We\'re here.'**
  String get ticketsEmptyBody;

  /// Button.
  ///
  /// In en, this message translates to:
  /// **'New request'**
  String get ticketNewAction;

  /// New ticket title (14.2).
  ///
  /// In en, this message translates to:
  /// **'New request'**
  String get ticketNewTitle;

  /// Ticket status open.
  ///
  /// In en, this message translates to:
  /// **'Open'**
  String get ticketStatusOpen;

  /// Ticket status pending_staff.
  ///
  /// In en, this message translates to:
  /// **'Waiting for us'**
  String get ticketStatusWaiting;

  /// Ticket status pending_customer.
  ///
  /// In en, this message translates to:
  /// **'Answered'**
  String get ticketStatusAnswered;

  /// Ticket status resolved.
  ///
  /// In en, this message translates to:
  /// **'Resolved'**
  String get ticketStatusResolved;

  /// Ticket status closed.
  ///
  /// In en, this message translates to:
  /// **'Closed'**
  String get ticketStatusClosed;

  /// Ticket topic.
  ///
  /// In en, this message translates to:
  /// **'Order'**
  String get ticketCategoryOrder;

  /// Ticket topic.
  ///
  /// In en, this message translates to:
  /// **'Payment'**
  String get ticketCategoryPayment;

  /// Ticket topic.
  ///
  /// In en, this message translates to:
  /// **'Refund'**
  String get ticketCategoryRefund;

  /// Ticket topic.
  ///
  /// In en, this message translates to:
  /// **'Account'**
  String get ticketCategoryAccount;

  /// Ticket topic.
  ///
  /// In en, this message translates to:
  /// **'Other'**
  String get ticketCategoryOther;

  /// Ticket row meta.
  ///
  /// In en, this message translates to:
  /// **'{topic} · updated {date}'**
  String ticketUpdated(String topic, String date);

  /// Label above the topic chips.
  ///
  /// In en, this message translates to:
  /// **'Topic'**
  String get ticketTopicLabel;

  /// Order picker label.
  ///
  /// In en, this message translates to:
  /// **'Order (optional)'**
  String get ticketOrderLabel;

  /// Order picker: none.
  ///
  /// In en, this message translates to:
  /// **'No order'**
  String get ticketOrderNone;

  /// Order picker value when the linked order is not in the list.
  ///
  /// In en, this message translates to:
  /// **'Linked order'**
  String get ticketOrderLinked;

  /// Order picker helper when the list failed.
  ///
  /// In en, this message translates to:
  /// **'Your orders couldn\'t be loaded. You can send the request without one.'**
  String get ticketOrdersFailed;

  /// Subject field.
  ///
  /// In en, this message translates to:
  /// **'Subject'**
  String get ticketSubjectLabel;

  /// Local check.
  ///
  /// In en, this message translates to:
  /// **'Use at least {min} characters.'**
  String ticketSubjectTooShort(int min);

  /// Message field.
  ///
  /// In en, this message translates to:
  /// **'Message'**
  String get ticketMessageLabel;

  /// Helper under the message.
  ///
  /// In en, this message translates to:
  /// **'Never share codes or passwords. Attachments aren\'t supported, so describe the problem here.'**
  String get ticketMessageHelper;

  /// Footer button.
  ///
  /// In en, this message translates to:
  /// **'Send request'**
  String get ticketSendAction;

  /// Thread title for screen readers.
  ///
  /// In en, this message translates to:
  /// **'Support request {number}'**
  String ticketThreadSemantics(String number);

  /// Author of a customer message.
  ///
  /// In en, this message translates to:
  /// **'You'**
  String get ticketFromYou;

  /// Author of a staff message.
  ///
  /// In en, this message translates to:
  /// **'Support'**
  String get ticketFromSupport;

  /// Composer hint.
  ///
  /// In en, this message translates to:
  /// **'Write a reply…'**
  String get ticketReplyHint;

  /// Send button tooltip.
  ///
  /// In en, this message translates to:
  /// **'Send reply'**
  String get ticketSendReply;

  /// Local check of the composer.
  ///
  /// In en, this message translates to:
  /// **'Write a message first.'**
  String get ticketReplyRequired;

  /// Error title.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load this request'**
  String get ticketLoadError;

  /// Unknown ticket.
  ///
  /// In en, this message translates to:
  /// **'Request not found'**
  String get ticketNotFoundTitle;

  /// Closed banner (14.4).
  ///
  /// In en, this message translates to:
  /// **'This request is closed'**
  String get ticketClosedTitle;

  /// Closed banner body.
  ///
  /// In en, this message translates to:
  /// **'Replies are turned off. Start a new request if you still need help.'**
  String get ticketClosedBody;

  /// Refund requests title and Account row.
  ///
  /// In en, this message translates to:
  /// **'Refund requests'**
  String get refundRequestsTitle;

  /// Error title.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load your refund requests'**
  String get refundRequestsLoadError;

  /// Empty state.
  ///
  /// In en, this message translates to:
  /// **'No refund requests'**
  String get refundRequestsEmptyTitle;

  /// Empty body.
  ///
  /// In en, this message translates to:
  /// **'You can ask for a refund from a paid order\'s page.'**
  String get refundRequestsEmptyBody;

  /// Row title when the order is unknown.
  ///
  /// In en, this message translates to:
  /// **'Order'**
  String get refundRequestUnknownOrder;

  /// Refund request status.
  ///
  /// In en, this message translates to:
  /// **'Requested'**
  String get refundStatusRequested;

  /// Refund request status.
  ///
  /// In en, this message translates to:
  /// **'Approved'**
  String get refundStatusApproved;

  /// Refund request status.
  ///
  /// In en, this message translates to:
  /// **'Not approved'**
  String get refundStatusRejected;

  /// Refund request status.
  ///
  /// In en, this message translates to:
  /// **'Completed'**
  String get refundStatusCompleted;

  /// Order card of the refund form; amount is an LTR island.
  ///
  /// In en, this message translates to:
  /// **'Paid {amount} · {date}'**
  String refundOrderPaid(String amount, String date);

  /// Reason field.
  ///
  /// In en, this message translates to:
  /// **'What went wrong?'**
  String get refundReasonLabel;

  /// Helper under the reason.
  ///
  /// In en, this message translates to:
  /// **'{min} to {max} characters. Revealed codes may not be refundable.'**
  String refundReasonHelper(int min, int max);

  /// Local check.
  ///
  /// In en, this message translates to:
  /// **'Use at least {min} characters.'**
  String refundReasonTooShort(int min);

  /// Ticket picker label.
  ///
  /// In en, this message translates to:
  /// **'Link a support request (optional)'**
  String get refundTicketLabel;

  /// Ticket picker: none.
  ///
  /// In en, this message translates to:
  /// **'None'**
  String get refundTicketNone;

  /// Info banner.
  ///
  /// In en, this message translates to:
  /// **'How refunds work'**
  String get refundHowTitle;

  /// Info banner body.
  ///
  /// In en, this message translates to:
  /// **'We review each request. Approved refunds go to your wallet or the original payment method.'**
  String get refundHowBody;

  /// Footer button.
  ///
  /// In en, this message translates to:
  /// **'Send request'**
  String get refundSendAction;

  /// Success banner (15.2).
  ///
  /// In en, this message translates to:
  /// **'Request sent'**
  String get refundSentTitle;

  /// Success banner body.
  ///
  /// In en, this message translates to:
  /// **'You can follow its status here.'**
  String get refundSentBody;

  /// 409 refund_request_open.
  ///
  /// In en, this message translates to:
  /// **'A request is already open for this order'**
  String get refundOpenTitle;

  /// 409 body.
  ///
  /// In en, this message translates to:
  /// **'You can follow it in Refund requests. One open request per order.'**
  String get refundOpenBody;

  /// 409 refund_not_allowed.
  ///
  /// In en, this message translates to:
  /// **'This order can\'t be refunded'**
  String get refundNotAllowedTitle;

  /// 422 for an order that is not the customer's.
  ///
  /// In en, this message translates to:
  /// **'This order can\'t be used for a refund request'**
  String get refundOrderRefusedTitle;

  /// Order detail note.
  ///
  /// In en, this message translates to:
  /// **'Refund request: {status}'**
  String orderRefundRequestStatus(String status);

  /// Order detail note body.
  ///
  /// In en, this message translates to:
  /// **'Sent {date}. Tap to see your refund requests.'**
  String orderRefundRequestSent(String date);

  /// Bell and Account row for screen readers.
  ///
  /// In en, this message translates to:
  /// **'Notifications, {count} unread'**
  String notificationBellUnread(int count);

  /// Account row value.
  ///
  /// In en, this message translates to:
  /// **'{count} new'**
  String notificationsNewCount(int count);

  /// Inbox header.
  ///
  /// In en, this message translates to:
  /// **'{count} unread'**
  String notificationsUnreadCount(int count);

  /// Inbox header with nothing unread.
  ///
  /// In en, this message translates to:
  /// **'All read'**
  String get notificationsAllRead;

  /// Inbox action.
  ///
  /// In en, this message translates to:
  /// **'Mark all read'**
  String get notificationsMarkAllRead;

  /// Error title.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load your notifications'**
  String get notificationsLoadError;

  /// Empty inbox (16.2).
  ///
  /// In en, this message translates to:
  /// **'You\'re all caught up'**
  String get notificationsEmptyTitle;

  /// Empty inbox body.
  ///
  /// In en, this message translates to:
  /// **'Order updates and support replies will appear here.'**
  String get notificationsEmptyBody;

  /// Screen-reader prefix of an unread notification.
  ///
  /// In en, this message translates to:
  /// **'Unread'**
  String get notificationUnread;

  /// Screen-reader hint.
  ///
  /// In en, this message translates to:
  /// **'Opens the details'**
  String get notificationOpenHint;

  /// Age.
  ///
  /// In en, this message translates to:
  /// **'Just now'**
  String get notificationJustNow;

  /// Age in minutes.
  ///
  /// In en, this message translates to:
  /// **'{count} min ago'**
  String notificationMinutesAgo(int count);

  /// Age in hours.
  ///
  /// In en, this message translates to:
  /// **'{count} h ago'**
  String notificationHoursAgo(int count);

  /// Preferences title (16.3).
  ///
  /// In en, this message translates to:
  /// **'Notification settings'**
  String get notificationSettingsTitle;

  /// Error title.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t load your notification settings'**
  String get preferencesLoadError;

  /// Preference section.
  ///
  /// In en, this message translates to:
  /// **'Orders'**
  String get preferenceCategoryOrders;

  /// Preference section.
  ///
  /// In en, this message translates to:
  /// **'Support'**
  String get preferenceCategorySupport;

  /// Preference section.
  ///
  /// In en, this message translates to:
  /// **'Offers and news'**
  String get preferenceCategoryMarketing;

  /// Preference section.
  ///
  /// In en, this message translates to:
  /// **'Security'**
  String get preferenceCategorySecurity;

  /// Channel database (the inbox).
  ///
  /// In en, this message translates to:
  /// **'In the app'**
  String get preferenceChannelInbox;

  /// Channel mail.
  ///
  /// In en, this message translates to:
  /// **'Email'**
  String get preferenceChannelMail;

  /// Channel sms.
  ///
  /// In en, this message translates to:
  /// **'SMS'**
  String get preferenceChannelSms;

  /// Subtitle of a mandatory cell.
  ///
  /// In en, this message translates to:
  /// **'Required'**
  String get preferenceRequired;

  /// Subtitle of SMS rows.
  ///
  /// In en, this message translates to:
  /// **'Only to a phone number on your profile'**
  String get preferenceSmsNote;

  /// Switch label for screen readers.
  ///
  /// In en, this message translates to:
  /// **'{category}, {channel}'**
  String preferenceSemantics(String category, String channel);

  /// Snackbar when a switch could not be saved.
  ///
  /// In en, this message translates to:
  /// **'Not saved. {reason}'**
  String preferenceSaveFailed(String reason);

  /// Push banner title in the notification settings when push is not available in this build.
  ///
  /// In en, this message translates to:
  /// **'Push notifications'**
  String get preferencePushTitle;

  /// Channel name in the notification settings (M-A18).
  ///
  /// In en, this message translates to:
  /// **'Push notifications'**
  String get preferenceChannelPush;

  /// Subtitle of a push switch: the setting is the account's.
  ///
  /// In en, this message translates to:
  /// **'On every phone where you are signed in and allow notifications'**
  String get preferencePushNote;

  /// Banner body when push is not configured in this build.
  ///
  /// In en, this message translates to:
  /// **'Not available in this version of the app. Updates appear in the inbox and by email.'**
  String get preferencePushUnavailable;

  /// Banner title when the system permission is not granted.
  ///
  /// In en, this message translates to:
  /// **'Notifications are off on this phone'**
  String get preferencePushOffTitle;

  /// Banner body when the system has not been asked yet.
  ///
  /// In en, this message translates to:
  /// **'Allow notifications to get these updates on this phone.'**
  String get preferencePushOffBody;

  /// Banner body when the system permission was refused.
  ///
  /// In en, this message translates to:
  /// **'If nothing happens, turn notifications on for boxcards.shop in your phone\'s settings.'**
  String get preferencePushDeniedBody;

  /// Push explainer sheet title, shown once after the first paid order.
  ///
  /// In en, this message translates to:
  /// **'Get order updates'**
  String get pushExplainerTitle;

  /// Push explainer intro.
  ///
  /// In en, this message translates to:
  /// **'We can let you know right away when something happens with your orders.'**
  String get pushExplainerBody;

  /// Push explainer point.
  ///
  /// In en, this message translates to:
  /// **'When your order is ready'**
  String get pushExplainerOrders;

  /// Push explainer point.
  ///
  /// In en, this message translates to:
  /// **'When a payment does not go through'**
  String get pushExplainerPayments;

  /// Push explainer point.
  ///
  /// In en, this message translates to:
  /// **'When our support team replies'**
  String get pushExplainerSupport;

  /// Push explainer point.
  ///
  /// In en, this message translates to:
  /// **'Codes are never sent in notifications. You open them in the app.'**
  String get pushExplainerNoCodes;

  /// Push explainer footnote.
  ///
  /// In en, this message translates to:
  /// **'You can change this any time in Settings > Notifications.'**
  String get pushExplainerSettingsNote;

  /// Button that shows the system permission prompt.
  ///
  /// In en, this message translates to:
  /// **'Turn on notifications'**
  String get pushExplainerAllow;

  /// Button that closes the push explainer.
  ///
  /// In en, this message translates to:
  /// **'Not now'**
  String get pushExplainerNotNow;

  /// Snackbar action that opens the order or ticket of a push.
  ///
  /// In en, this message translates to:
  /// **'Open'**
  String get pushOpen;

  /// Snackbar text for a push without text.
  ///
  /// In en, this message translates to:
  /// **'You have a new notification'**
  String get pushNewNotification;
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
