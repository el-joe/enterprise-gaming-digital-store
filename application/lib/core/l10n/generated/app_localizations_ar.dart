// ignore: unused_import
import 'package:intl/intl.dart' as intl;

import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for Arabic (`ar`).
class AppLocalizationsAr extends AppLocalizations {
  AppLocalizationsAr([String locale = 'ar']) : super(locale);

  @override
  String get appTitle => 'boxcards.shop';

  @override
  String get brandLogoLabel => 'boxcards.shop';

  @override
  String environmentBadge(String flavor) {
    String _temp0 = intl.Intl.selectLogic(flavor, {
      'dev': 'التطوير',
      'staging': 'التجريب',
      'other': 'الإنتاج',
    });
    return '$_temp0';
  }

  @override
  String get switchLanguage => 'English';

  @override
  String get switchLanguageHint => 'تغيير لغة التطبيق إلى الإنجليزية';

  @override
  String get maintenanceTitle => 'سنعود قريبًا';

  @override
  String get maintenanceBody =>
      'المتجر قيد الصيانة. يُرجى المحاولة مجددًا بعد بضع دقائق.';

  @override
  String get updateRequiredTitle => 'التحديث مطلوب';

  @override
  String get updateRequiredBody =>
      'لم يعد هذا الإصدار من التطبيق مدعومًا. يُرجى تحديثه من متجر التطبيقات.';

  @override
  String get notFoundTitle => 'الصفحة غير موجودة';

  @override
  String get notFoundBody => 'الصفحة التي فتحتها غير موجودة.';

  @override
  String get backToHome => 'العودة إلى الرئيسية';

  @override
  String get actionTryAgain => 'حاول مرة أخرى';

  @override
  String get actionContinue => 'متابعة';

  @override
  String get actionBack => 'رجوع';

  @override
  String get actionClose => 'إغلاق';

  @override
  String get actionCancel => 'إلغاء';

  @override
  String get actionNotNow => 'ليس الآن';

  @override
  String get loadingLabel => 'جارٍ التحميل';

  @override
  String get showPassword => 'إظهار كلمة المرور';

  @override
  String get hidePassword => 'إخفاء كلمة المرور';

  @override
  String requestReference(String id) {
    return 'المرجع: $id';
  }

  @override
  String get failureNetwork => 'أنت غير متصل. تحقّق من اتصالك وحاول مرة أخرى.';

  @override
  String get failureTimeout => 'انتهت مهلة الاتصال. يرجى المحاولة مرة أخرى.';

  @override
  String get failureUnauthenticated =>
      'انتهت جلستك. يرجى تسجيل الدخول مرة أخرى.';

  @override
  String get failureForbidden => 'لا يمكنك القيام بذلك بهذا الحساب.';

  @override
  String get failureEmailUnverified => 'أكّد بريدك الإلكتروني للمتابعة.';

  @override
  String get failureNotFound => 'لم نتمكن من العثور على ما تبحث عنه.';

  @override
  String failureRateLimited(String time) {
    return 'محاولات كثيرة جدًا. حاول مرة أخرى بعد $time.';
  }

  @override
  String get failureRateLimitedNoTime =>
      'محاولات كثيرة جدًا. يرجى الانتظار ثم المحاولة مرة أخرى.';

  @override
  String get failureMaintenance =>
      'المتجر قيد الصيانة. يرجى المحاولة بعد قليل.';

  @override
  String get failureServer => 'حدث خطأ من جهتنا. يرجى المحاولة مرة أخرى.';

  @override
  String get failureValidation => 'تحقّق من الحقول المحددة وحاول مرة أخرى.';

  @override
  String get failureRecentAuth => 'أكّد كلمة المرور للمتابعة.';

  @override
  String get failureGeneric => 'حدث خطأ ما. يرجى المحاولة مرة أخرى.';

  @override
  String get splashChecking => 'جارٍ البحث عن تحديثات…';

  @override
  String get offlineTitle => 'أنت غير متصل';

  @override
  String get offlineBody =>
      'اتصل بشبكة Wi-Fi أو بيانات الجوال لفتح المتجر. لا تُخزَّن رموزك على هذا الجهاز أبدًا.';

  @override
  String get startupErrorTitle => 'تعذّر فتح المتجر';

  @override
  String updateRequiredVersions(String installed, String minimum) {
    return 'المثبّت $installed · الحد الأدنى $minimum';
  }

  @override
  String get updateNow => 'حدّث الآن';

  @override
  String get updateStoreUnavailable =>
      'افتح متجر التطبيقات وحدّث boxcards.shop.';

  @override
  String get maintenanceOrdersSafeTitle => 'طلباتك في أمان';

  @override
  String get maintenanceOrdersSafeBody => 'الرموز التي اشتريتها تبقى في حسابك.';

  @override
  String get contactSupport => 'تواصل مع الدعم';

  @override
  String get linkOpenFailed => 'تعذّر فتح الصفحة. حاول لاحقًا.';

  @override
  String onboardingStep(int current, int total) {
    return 'الخطوة $current من $total';
  }

  @override
  String get onboardingLanguageTitle => 'اختر لغتك';

  @override
  String get onboardingLanguageBody => 'يمكنك تغييرها لاحقًا من الإعدادات.';

  @override
  String get languageEnglish => 'English';

  @override
  String get languageEnglishDetail => 'الإنجليزية (من اليسار إلى اليمين)';

  @override
  String get languageArabic => 'العربية';

  @override
  String get languageArabicDetail => 'العربية (من اليمين إلى اليسار)';

  @override
  String get onboardingCurrencyTitle => 'اختر عملة العرض';

  @override
  String get onboardingCurrencyBody =>
      'تظهر الأسعار بهذه العملة. إذا تخطّيت هذه الخطوة نستخدم عملة بلدك.';

  @override
  String get displayCurrencyLabel => 'عملة العرض';

  @override
  String get chargeNoticeTitle => 'قد يتم التحصيل بالدولار الأمريكي';

  @override
  String get chargeNoticeBody =>
      'بعض طرق الدفع تحصّل بعملة أخرى. سترى المبلغ دائمًا قبل الدفع.';

  @override
  String get startShopping => 'ابدأ التسوق';

  @override
  String get skipForNow => 'تخطَّ الآن';

  @override
  String get loginTitle => 'تسجيل الدخول';

  @override
  String get loginSubtitle => 'رموزك وطلباتك على أي جهاز.';

  @override
  String get emailLabel => 'البريد الإلكتروني';

  @override
  String get passwordLabel => 'كلمة المرور';

  @override
  String get forgotPasswordLink => 'نسيت كلمة المرور؟';

  @override
  String get signInAction => 'تسجيل الدخول';

  @override
  String get newHere => 'جديد هنا؟';

  @override
  String get createAccountLink => 'إنشاء حساب';

  @override
  String get rateLimitedTitle => 'محاولات كثيرة جدًا';

  @override
  String rateLimitedBody(int seconds) {
    String _temp0 = intl.Intl.pluralLogic(
      seconds,
      locale: localeName,
      other: 'حاول مرة أخرى بعد $seconds ثانية.',
      many: 'حاول مرة أخرى بعد $seconds ثانية.',
      few: 'حاول مرة أخرى بعد $seconds ثوانٍ.',
      two: 'حاول مرة أخرى بعد ثانيتين.',
      one: 'حاول مرة أخرى بعد ثانية واحدة.',
    );
    return '$_temp0';
  }

  @override
  String tryAgainIn(String time) {
    return 'حاول مرة أخرى بعد $time';
  }

  @override
  String get fieldRequired => 'هذا الحقل مطلوب.';

  @override
  String get emailInvalid => 'أدخل بريدًا إلكترونيًا صحيحًا.';

  @override
  String get signInFailedTitle => 'تعذّر تسجيل الدخول';

  @override
  String get twoFactorAppBar => 'التحقق بخطوتين';

  @override
  String get twoFactorCodeTitle => 'أدخل الرمز المكوّن من 6 أرقام';

  @override
  String get twoFactorCodeBody =>
      'افتح تطبيق المصادقة. يتغيّر الرمز كل 30 ثانية.';

  @override
  String get otpSemantics => 'رمز من 6 أرقام';

  @override
  String twoFactorExpiresIn(String time) {
    return 'تنتهي صلاحية طلب تسجيل الدخول خلال $time';
  }

  @override
  String get useRecoveryCode => 'استخدم رمز استرداد بدلًا من ذلك';

  @override
  String get recoveryTitle => 'استخدم رمز استرداد';

  @override
  String get recoveryBody =>
      'أدخل أحد رموز الاسترداد التي حفظتها عند تفعيل التحقق بخطوتين. يعمل كل رمز مرة واحدة.';

  @override
  String get recoveryCodeLabel => 'رمز الاسترداد';

  @override
  String get useAuthenticatorCode => 'استخدم رمز تطبيق المصادقة';

  @override
  String get verifyAction => 'تحقّق';

  @override
  String get twoFactorInvalid =>
      'هذا الرمز غير صالح. تحقّق منه وحاول مرة أخرى.';

  @override
  String get twoFactorExpiredTitle => 'انتهت صلاحية طلب تسجيل الدخول';

  @override
  String get twoFactorExpiredBody =>
      'حفاظًا على أمانك، ابدأ من جديد من شاشة تسجيل الدخول.';

  @override
  String get backToSignIn => 'العودة إلى تسجيل الدخول';

  @override
  String get otpIncomplete => 'أدخل الأرقام الستة كلها.';

  @override
  String get registerTitle => 'إنشاء حساب';

  @override
  String get fullNameLabel => 'الاسم الكامل';

  @override
  String get passwordHelper => '8 أحرف على الأقل تتضمن حروفًا وأرقامًا.';

  @override
  String get confirmPasswordLabel => 'تأكيد كلمة المرور';

  @override
  String get acceptTerms => 'أوافق على الشروط وسياسة الخصوصية';

  @override
  String get readTerms => 'اقرأ الشروط';

  @override
  String get termsRequired => 'وافق على الشروط لإنشاء حساب.';

  @override
  String get passwordMismatch => 'كلمتا المرور غير متطابقتين.';

  @override
  String formErrorsTitle(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: 'تحقّق من $count حقل',
      many: 'تحقّق من $count حقلًا',
      few: 'تحقّق من $count حقول',
      two: 'تحقّق من حقلين',
      one: 'تحقّق من حقل واحد',
    );
    return '$_temp0';
  }

  @override
  String get formErrorsBody => 'صحّح الحقول المحددة وحاول مرة أخرى.';

  @override
  String get haveAccount => 'لديك حساب بالفعل؟';

  @override
  String get verifyEmailTitle => 'تحقّق من بريدك الوارد';

  @override
  String verifyEmailBody(String email) {
    return 'أرسلنا رابطًا إلى $email. افتحه على هذا الهاتف لتأكيد بريدك. تحتاج إلى بريد مؤكَّد لكشف الرموز وكتابة التقييمات.';
  }

  @override
  String get verifyEmailBodyNoEmail =>
      'أرسلنا رابطًا إلى بريدك الإلكتروني. افتحه على هذا الهاتف لتأكيد بريدك. تحتاج إلى بريد مؤكَّد لكشف الرموز وكتابة التقييمات.';

  @override
  String get verificationSent => 'تم إرسال رسالة التأكيد.';

  @override
  String resendIn(String time) {
    return 'يمكنك إعادة الإرسال بعد $time';
  }

  @override
  String get resendEmail => 'إعادة إرسال الرسالة';

  @override
  String get iVerified => 'أكّدت بريدي الإلكتروني';

  @override
  String get stillUnverified =>
      'لم يتم تأكيد بريدك بعد. افتح الرابط الذي أرسلناه ثم حاول مرة أخرى.';

  @override
  String get emailVerifiedDone => 'تم تأكيد البريد. شكرًا لك!';

  @override
  String get forgotTitle => 'إعادة تعيين كلمة المرور';

  @override
  String get forgotBody =>
      'تتم إعادة تعيين كلمة المرور على boxcards.shop. سنفتح صفحة إعادة التعيين في متصفحك: أدخل بريد حسابك هناك وسنرسل لك رابطًا.';

  @override
  String get openResetPage => 'افتح صفحة إعادة التعيين';

  @override
  String get resetPageOpenedTitle => 'تحقّق من متصفحك';

  @override
  String get resetPageOpenedBody =>
      'أكمل على boxcards.shop ثم عد وسجّل الدخول بكلمة المرور الجديدة.';

  @override
  String get resetPageUnavailable =>
      'صفحة إعادة التعيين غير متاحة حاليًا. حاول لاحقًا.';

  @override
  String get signOutAction => 'تسجيل الخروج';

  @override
  String get signOutConfirmTitle => 'تسجيل الخروج من boxcards.shop؟';

  @override
  String get signOutConfirmBody =>
      'ستحتاج إلى كلمة المرور لتسجيل الدخول مرة أخرى. سيتم إيقاف فتح القفل بالبصمة على هذا الجهاز.';

  @override
  String get signedOutMessage => 'تم تسجيل خروجك.';

  @override
  String homeSignedInAs(String email) {
    return 'مسجّل الدخول باسم $email';
  }

  @override
  String get homeSignedIn => 'أنت مسجّل الدخول.';

  @override
  String get homeSignedOut => 'سجّل الدخول لرؤية طلباتك ورموزك.';

  @override
  String get homeAccountTitle => 'الحساب';

  @override
  String get biometricOfferTitle => 'فتح القفل بالبصمة؟';

  @override
  String get biometricOfferBody =>
      'استخدم بصمتك أو وجهك لفتح boxcards.shop بعد 5 دقائق في الخلفية، ولتأكيد هويتك قبل عرض الرموز. يمكنك إيقافه في أي وقت.';

  @override
  String get biometricTurnOn => 'تفعيل';

  @override
  String get biometricPromptEnrol => 'أكّد لتفعيل فتح القفل بالبصمة';

  @override
  String get biometricPromptUnlock => 'افتح قفل boxcards.shop';

  @override
  String get biometricPromptConfirm => 'أكّد هويتك';

  @override
  String get biometricEnabled => 'تم تفعيل فتح القفل بالبصمة.';

  @override
  String get biometricDisabled => 'تم إيقاف فتح القفل بالبصمة.';

  @override
  String get biometricUnavailable =>
      'لا توجد بصمة أو ميزة التعرّف على الوجه مُعدّة على هذا الجهاز.';

  @override
  String get biometricFailed => 'تعذّر تفعيل فتح القفل بالبصمة. حاول لاحقًا.';

  @override
  String get biometricSettingTitle => 'فتح القفل بالبصمة';

  @override
  String get biometricSettingSubtitle => 'بعد 5 دقائق في الخلفية';

  @override
  String get passwordSheetTitle => 'أكّد كلمة المرور';

  @override
  String get passwordSheetBody =>
      'حفاظًا على أمانك، أدخل كلمة المرور للمتابعة.';

  @override
  String get confirmAction => 'تأكيد';

  @override
  String get lockTitle => 'boxcards.shop مقفل';

  @override
  String get lockBody => 'افتح القفل ببصمتك أو وجهك، أو استخدم كلمة المرور.';

  @override
  String get lockUseBiometric => 'افتح بالبصمة';

  @override
  String get lockUsePassword => 'استخدم كلمة المرور';

  @override
  String get lockBiometricChanged =>
      'تغيّرت بياناتك الحيوية، لذا أُوقف فتح القفل بالبصمة. استخدم كلمة المرور.';

  @override
  String get unlockAction => 'فتح القفل';

  @override
  String get integrityTitle => 'قد يكون هذا الجهاز مكسور الحماية';

  @override
  String get integrityBody =>
      'التطبيقات والرموز أقل حماية على الأجهزة المعدّلة. يمكنك الاستمرار في استخدام boxcards.shop، وسنطلب منك تأكيد هويتك قبل عرض الرموز.';

  @override
  String get integrityAcknowledge => 'فهمت';

  @override
  String get navHome => 'الرئيسية';

  @override
  String get navBrowse => 'تصفّح';

  @override
  String get navCart => 'السلة';

  @override
  String get navOrders => 'الطلبات';

  @override
  String get navAccount => 'حسابي';

  @override
  String get searchFieldHint => 'ابحث عن بطاقات الهدايا وشحن الألعاب';

  @override
  String get searchAction => 'بحث';

  @override
  String currencyButtonLabel(String code) {
    return 'عملة العرض: $code';
  }

  @override
  String get seeAll => 'عرض الكل';

  @override
  String seeAllSemantics(String section) {
    return 'عرض الكل: $section';
  }

  @override
  String get loadErrorTitle => 'تعذّر تحميل هذه الصفحة';

  @override
  String get homeErrorTitle => 'تعذّر تحميل المتجر';

  @override
  String errorServerBody(String requestId) {
    return 'حدث خطأ من جهتنا. رقم الطلب $requestId';
  }

  @override
  String get errorServerBodyNoId => 'حدث خطأ من جهتنا.';

  @override
  String get actionRetry => 'إعادة المحاولة';

  @override
  String get offlineBannerTitle => 'أنت غير متصل';

  @override
  String get offlineBannerBody =>
      'الأسعار والمخزون تحتاج إلى اتصال. سنحدّث الصفحة عند عودتك إلى الاتصال.';

  @override
  String get noConnectionTitle => 'لا يوجد اتصال';

  @override
  String get noConnectionBody =>
      'تحقّق من الواي فاي أو بيانات الجوال، ثم حاول مرة أخرى.';

  @override
  String heroSlideLabel(int index, int count, String title) {
    return 'الإعلان $index من $count: $title';
  }

  @override
  String get heroCarouselLabel => 'العروض';

  @override
  String get platformsTitle => 'المنصات';

  @override
  String get platformsIntro =>
      'اختر منصة لعرض بطاقات الهدايا وأكواد المحافظ وعمليات الشحن.';

  @override
  String get platformsEmpty => 'لا توجد منصات بعد';

  @override
  String get categoriesTitle => 'الفئات';

  @override
  String get allCategory => 'الكل';

  @override
  String productCount(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count منتج',
      many: '$count منتجًا',
      few: '$count منتجات',
      two: 'منتجان',
      one: 'منتج واحد',
      zero: 'لا توجد منتجات',
    );
    return '$_temp0';
  }

  @override
  String get allProductsTitle => 'كل المنتجات';

  @override
  String get filtersChip => 'الفلاتر';

  @override
  String filtersChipCount(int count) {
    return 'الفلاتر · $count';
  }

  @override
  String sortChip(String sort) {
    return 'الترتيب: $sort';
  }

  @override
  String get sortPopular => 'الأكثر رواجًا';

  @override
  String get sortPriceAsc => 'السعر من الأقل إلى الأعلى';

  @override
  String get sortPriceDesc => 'السعر من الأعلى إلى الأقل';

  @override
  String get sortNewest => 'الأحدث';

  @override
  String get sortName => 'الاسم';

  @override
  String get sortTitle => 'الترتيب';

  @override
  String get filtersTitle => 'الفلاتر';

  @override
  String get filterDelivery => 'طريقة التسليم';

  @override
  String get deliveryInstantCode => 'كود فوري';

  @override
  String get deliveryDirectTopup => 'شحن مباشر';

  @override
  String get filterMinPrice => 'أقل سعر';

  @override
  String get filterMaxPrice => 'أعلى سعر';

  @override
  String filterPriceHelper(String currency) {
    return 'بعملة $currency';
  }

  @override
  String get filterPriceInvalid => 'أدخل مبلغًا مثل 5 أو 5.50';

  @override
  String get filterPriceRange => 'يجب ألّا يزيد أقل سعر عن أعلى سعر';

  @override
  String get filtersClear => 'مسح';

  @override
  String get filtersApply => 'عرض النتائج';

  @override
  String get listingEmptyTitle => 'لا توجد منتجات مطابقة';

  @override
  String get listingEmptyBody => 'جرّب سعرًا آخر أو أزل أحد الفلاتر.';

  @override
  String get catalogEmptyTitle => 'لا توجد منتجات هنا بعد';

  @override
  String get catalogEmptyBody =>
      'نضيف بطاقات هدايا وعمليات شحن جديدة باستمرار.';

  @override
  String get clearFilters => 'مسح الفلاتر';

  @override
  String get loadMoreFailed => 'تعذّر تحميل المزيد من المنتجات.';

  @override
  String get badgeInstant => 'فوري';

  @override
  String get badgeTopup => 'شحن';

  @override
  String get badgeSubscription => 'اشتراك';

  @override
  String get productUnavailable => 'غير متاح';

  @override
  String get searchSuggestions => 'اقتراحات';

  @override
  String get searchResults => 'النتائج';

  @override
  String get searchRecent => 'عمليات البحث الأخيرة';

  @override
  String get searchClearHistory => 'مسح';

  @override
  String get searchClearHistoryLabel => 'مسح عمليات البحث الأخيرة';

  @override
  String searchRemoveTerm(String term) {
    return 'إزالة $term';
  }

  @override
  String searchFillTerm(String term) {
    return 'البحث عن $term';
  }

  @override
  String get searchPopular => 'الأكثر بحثًا';

  @override
  String searchNoResultsTitle(String query) {
    return 'لا توجد نتائج لـ «$query»';
  }

  @override
  String get searchNoResultsBody => 'تحقّق من الإملاء أو جرّب اسم منصة.';

  @override
  String get searchTypePlatform => 'منصة';

  @override
  String get searchTypeCategory => 'فئة';

  @override
  String get searchTypeProduct => 'منتج';

  @override
  String get searchFailed => 'تعذّر تحميل الاقتراحات.';

  @override
  String get currencySheetBody =>
      'تتحدّث الأسعار بالعملة الجديدة، ويُعاد تسعير سلتك قبل الدفع.';

  @override
  String get currencyApply => 'تطبيق';

  @override
  String currencyChanged(String code) {
    return 'تظهر الأسعار الآن بعملة $code.';
  }

  @override
  String get cartTitle => 'السلة';

  @override
  String get ordersTitle => 'الطلبات';

  @override
  String get accountTitle => 'حسابي';

  @override
  String get comingSoonTitle => 'قادم في التحديث القادم';

  @override
  String get comingSoonBody =>
      'هذا القسم قيد الإعداد. يمكنك حتى ذلك الحين استخدام boxcards.shop على الويب.';

  @override
  String get languageSetting => 'اللغة';

  @override
  String reviewsRating(int rating) {
    return 'التقييم $rating من 5';
  }

  @override
  String get verifiedPurchase => 'شراء مؤكد';

  @override
  String get notificationsComingSoon => 'الإشعارات';

  @override
  String get productLoadErrorTitle => 'تعذّر تحميل هذا المنتج';

  @override
  String get productNotFoundTitle => 'المنتج غير متاح';

  @override
  String get productNotFoundBody => 'ربما أُزيل أو غير متاح في منطقتك.';

  @override
  String get backToStore => 'العودة إلى المتجر';

  @override
  String get productRegion => 'المنطقة';

  @override
  String get productAnyRegion => 'كل المناطق';

  @override
  String get productAmount => 'القيمة';

  @override
  String productPay(String price) {
    return 'ادفع $price';
  }

  @override
  String get variantSoldOut => 'نفدت الكمية';

  @override
  String get variantCustomAmount => 'غير متاح بعد';

  @override
  String get variantUnavailable => 'غير متاح';

  @override
  String get variantLimited => 'الكمية محدودة';

  @override
  String variantSemantics(String title, String detail) {
    return '$title، $detail';
  }

  @override
  String get priceMayVaryTitle => 'قد يتغيّر السعر';

  @override
  String get priceMayVaryBody => 'يُؤكَّد السعر النهائي عند الدفع.';

  @override
  String get productUnavailableTitle => 'غير متاح حاليًا';

  @override
  String get productUnavailableBody =>
      'لا يمكن شراء أي من خيارات هذا المنتج الآن. عُد لاحقًا.';

  @override
  String get addFailedTitle => 'تعذّرت الإضافة إلى السلة';

  @override
  String productReviewsCount(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count تقييم',
      many: '$count تقييمًا',
      few: '$count تقييمات',
      two: 'تقييمان',
      one: 'تقييم واحد',
      zero: 'لا توجد تقييمات',
    );
    return '$_temp0';
  }

  @override
  String productAverageRating(String rating) {
    return 'متوسط التقييم $rating من 5';
  }

  @override
  String deliveryTime(int minutes) {
    String _temp0 = intl.Intl.pluralLogic(
      minutes,
      locale: localeName,
      other: 'حوالي $minutes دقيقة',
      many: 'حوالي $minutes دقيقة',
      few: 'حوالي $minutes دقائق',
      two: 'حوالي دقيقتين',
      one: 'حوالي دقيقة',
      zero: 'تسليم فوري',
    );
    return '$_temp0';
  }

  @override
  String get aboutProduct => 'عن هذا المنتج';

  @override
  String get howToRedeem => 'طريقة الاستخدام';

  @override
  String get termsTitle => 'الشروط';

  @override
  String get reviewsTitle => 'التقييمات';

  @override
  String get reviewsEmptyTitle => 'لا توجد تقييمات بعد';

  @override
  String get reviewsEmptyBody => 'تظهر هنا تقييمات المشترين.';

  @override
  String get seeAllReviews => 'عرض كل التقييمات';

  @override
  String get reviewsLoadFailed => 'تعذّر تحميل التقييمات.';

  @override
  String get loadMoreReviewsFailed => 'تعذّر تحميل المزيد من التقييمات.';

  @override
  String quantitySemantics(int count) {
    return 'الكمية $count';
  }

  @override
  String get increaseQuantity => 'زيادة الكمية';

  @override
  String get decreaseQuantity => 'تقليل الكمية';

  @override
  String quantityLimit(int max) {
    return 'حتى $max في الطلب الواحد';
  }

  @override
  String get priceLabel => 'السعر';

  @override
  String priceTimesQuantity(String price, int count) {
    return '$price × $count';
  }

  @override
  String get addToCart => 'أضف إلى السلة';

  @override
  String get addedToCart => 'أُضيفت إلى السلة.';

  @override
  String get viewCart => 'عرض السلة';

  @override
  String cartBadgeSemantics(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count منتج',
      many: '$count منتجًا',
      few: '$count منتجات',
      two: 'منتجان',
      one: 'منتج واحد',
    );
    return '$_temp0';
  }

  @override
  String deliveryFieldRequired(String label) {
    return '$label مطلوب.';
  }

  @override
  String deliveryFieldTooLong(String label, int max) {
    return '$label طويل جدًا (حتى $max حرفًا).';
  }

  @override
  String deliveryFieldDigits(String label) {
    return 'يجب أن يحتوي $label على أرقام فقط.';
  }

  @override
  String get deliveryFieldEmail => 'أدخل بريدًا إلكترونيًا صحيحًا.';

  @override
  String get deliveryFieldOption => 'اختر أحد الخيارات.';

  @override
  String deliveryFieldFormat(String label) {
    return 'تنسيق $label غير صحيح.';
  }

  @override
  String get selectPlaceholder => 'اختر';

  @override
  String galleryImage(int index, int count) {
    return 'الصورة $index من $count';
  }
}
