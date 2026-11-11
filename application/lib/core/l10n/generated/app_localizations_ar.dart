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
  String get languageSetting => 'اللغة';

  @override
  String reviewsRating(int rating) {
    return 'التقييم $rating من 5';
  }

  @override
  String get verifiedPurchase => 'شراء مؤكد';

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

  @override
  String get blockUnavailable => 'لم يعد متاحاً. أزله للمتابعة.';

  @override
  String get blockOutOfStock => 'نفد من المخزون. أزله للمتابعة.';

  @override
  String get blockCustomAmount => 'لا يمكن شراء هذا المبلغ. أزله للمتابعة.';

  @override
  String get blockQuantityLimit => 'الكمية أعلى من الحد المسموح. قلّل الكمية.';

  @override
  String get blockNotSoldInCountry => 'غير متاح للبيع في بلدك. أزله للمتابعة.';

  @override
  String get blockPricing => 'يجري تحديث السعر. حاول مرة أخرى بعد قليل.';

  @override
  String get blockOther => 'غير متاح حالياً. أزله للمتابعة.';

  @override
  String cartTitleCount(int count) {
    return 'السلة ($count)';
  }

  @override
  String get cartEmptyTitle => 'سلتك فارغة';

  @override
  String get cartEmptyBody =>
      'ستظهر هنا بطاقات الهدايا وعمليات الشحن التي تضيفها.';

  @override
  String get cartBrowseStore => 'تصفّح المتجر';

  @override
  String get cartLoadFailedTitle => 'تعذّر تحميل سلتك';

  @override
  String cartRemoveItem(String name) {
    return 'إزالة $name';
  }

  @override
  String cartLineSemantics(
    String name,
    String variant,
    String price,
    int quantity,
  ) {
    return '$name، $variant، $price، الكمية $quantity';
  }

  @override
  String get cartCouponLabel => 'رمز القسيمة';

  @override
  String get cartCouponApply => 'تطبيق';

  @override
  String get cartCouponRemove => 'إزالة';

  @override
  String cartCouponApplied(String code) {
    return 'تم تطبيق $code.';
  }

  @override
  String get cartCouponLimited => 'تم تقليل الخصم لهذا الطلب.';

  @override
  String get cartSubtotal => 'المجموع الفرعي';

  @override
  String get cartDiscount => 'الخصم';

  @override
  String cartDiscountCode(String code) {
    return 'الخصم ($code)';
  }

  @override
  String get cartTotal => 'الإجمالي';

  @override
  String get cartCheckout => 'إتمام الشراء';

  @override
  String get cartCheckoutWeb => 'أكمل الشراء على boxcards.shop';

  @override
  String get cartCheckoutWebHint => 'يفتح boxcards.shop في متصفحك.';

  @override
  String get cartCheckoutDisabled => 'إتمام الشراء غير متاح في التطبيق حالياً.';

  @override
  String get cartCheckoutBlocked => 'أزل العناصر المميزة أو عدّلها للمتابعة.';

  @override
  String get cartSignInHint => 'ستسجّل الدخول أولاً، وتبقى سلتك كما هي.';

  @override
  String get cartPricesUpdatedTitle => 'تم تحديث الأسعار';

  @override
  String get cartPricesUpdatedBody =>
      'تغيّرت أسعار الصرف منذ أضفت هذه العناصر. راجع الإجمالي الجديد.';

  @override
  String get cartPriceMayVaryTitle => 'قد تتغير الأسعار';

  @override
  String get cartPriceMayVaryBody => 'يُؤكَّد السعر النهائي عند إتمام الشراء.';

  @override
  String get checkoutTitle => 'إتمام الشراء';

  @override
  String get checkoutEmailNote => 'تصل رسائل الطلب إلى هذا البريد';

  @override
  String get checkoutCountry => 'البلد';

  @override
  String get checkoutChooseCountry => 'اختر بلدك';

  @override
  String get checkoutCountryHint => 'اختر بلدك لعرض خيارات الدفع.';

  @override
  String get checkoutSearchCountries => 'ابحث عن بلد';

  @override
  String get checkoutNoCountryMatch => 'لا يوجد بلد يطابق بحثك.';

  @override
  String get checkoutPayment => 'الدفع';

  @override
  String checkoutChargedIn(String currency) {
    return 'يُخصم بعملة $currency';
  }

  @override
  String checkoutChargeNoticeTitle(String gateway, String currency) {
    return 'يخصم $gateway بعملة $currency';
  }

  @override
  String checkoutChargeNoticeBody(
    String display,
    String gateway,
    String amount,
  ) {
    return 'تظهر لك الأسعار بعملة $display، لكن $gateway سيخصم $amount. قد يضيف بنكك رسوم تحويل.';
  }

  @override
  String get optionBelowMargin => 'غير متاح لهذا المبلغ';

  @override
  String get optionUnavailable => 'غير متاح حالياً';

  @override
  String get optionNotInApp => 'غير متاح في التطبيق بعد';

  @override
  String get checkoutSomeUnavailableTitle => 'بعض الخيارات غير متاحة';

  @override
  String checkoutSomeUnavailableBody(String country) {
    return 'الخيارات المعروضة هي التي يتيحها المتجر لـ $country.';
  }

  @override
  String get checkoutNoOptionsTitle => 'لا يوجد خيار دفع لهذا الطلب';

  @override
  String get checkoutNoOptionsBody =>
      'جرّب بلداً آخر، أو أكمل الشراء على boxcards.shop.';

  @override
  String get checkoutAcceptTerms => 'أوافق على شروط البيع';

  @override
  String get checkoutTermsRequired => 'وافق على شروط البيع للمتابعة.';

  @override
  String get checkoutReadTerms => 'اقرأ شروط البيع';

  @override
  String checkoutItems(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count عنصر',
      many: '$count عنصراً',
      few: '$count عناصر',
      two: 'عنصران',
      one: 'عنصر واحد',
      zero: 'لا عناصر',
    );
    return '$_temp0';
  }

  @override
  String checkoutChargedLine(String amount, String currency, String gateway) {
    return 'سيُخصم منك $amount بعملة $currency عبر $gateway.';
  }

  @override
  String get checkoutFeesNote =>
      'الأسعار شاملة جميع الرسوم. تُسلَّم الأكواد إلى حسابك.';

  @override
  String get checkoutPayButton => 'ادفع';

  @override
  String checkoutPay(String amount) {
    return 'ادفع $amount';
  }

  @override
  String checkoutPayWallet(String amount) {
    return 'ادفع $amount من المحفظة';
  }

  @override
  String checkoutContinueTo(String gateway) {
    return 'المتابعة إلى $gateway';
  }

  @override
  String get checkoutPriceChangedTitle => 'تغيّر السعر';

  @override
  String checkoutPriceChangedBody(String current, String previous) {
    return 'أصبح إجماليك $current (كان $previous). لم يُخصم أي مبلغ. راجع الإجمالي وحاول مرة أخرى.';
  }

  @override
  String checkoutPriceChangedBodyUnknown(String previous) {
    return 'تغيّر إجماليك (كان $previous). لم يُخصم أي مبلغ. راجع الإجمالي وحاول مرة أخرى.';
  }

  @override
  String get checkoutReviewNewTotal => 'راجع الإجمالي الجديد';

  @override
  String get checkoutBackToCart => 'العودة إلى السلة';

  @override
  String get checkoutInProgressTitle => 'ما زال طلبك قيد الإنشاء';

  @override
  String get checkoutInProgressBody =>
      'انتظر لحظة ثم اضغط ادفع مرة أخرى. لن يُخصم منك مرتين.';

  @override
  String get checkoutCartAttentionTitle => 'بعض العناصر تحتاج إلى مراجعة';

  @override
  String get checkoutRateStaleTitle => 'يجري تحديث الأسعار';

  @override
  String get checkoutRateStaleBody =>
      'يجري تحديث أسعار الصرف. حاول مرة أخرى بعد لحظة.';

  @override
  String get checkoutFailedTitle => 'تعذّر إنشاء طلبك';

  @override
  String get checkoutQuoteFailedTitle => 'تعذّر تحميل خيارات الدفع';

  @override
  String get checkoutWebTitle => 'أكمل الشراء على الموقع';

  @override
  String get checkoutWebBody =>
      'تتم عمليات الشراء على boxcards.shop في متصفحك. سجّل الدخول هناك بالحساب نفسه.';

  @override
  String get checkoutDisabledTitle => 'إتمام الشراء غير متاح';

  @override
  String get paymentProviderFallback => 'مزوّد الدفع';

  @override
  String get paymentInProgressTitle => 'الدفع قيد التنفيذ';

  @override
  String paymentInProgressBody(String gateway) {
    return 'أكمل الدفع في نافذة $gateway. نتحقق من الدفع مع $gateway قبل عرضه كمدفوع.';
  }

  @override
  String get paymentOpenFailed => 'تعذّر فتح صفحة الدفع. حاول مرة أخرى.';

  @override
  String get paymentDontPayTwiceTitle => 'لا تدفع مرتين';

  @override
  String get paymentDontPayTwiceBody =>
      'إذا أغلقت التطبيق، نعود إلى هنا ونتحقق من الدفع مرة أخرى.';

  @override
  String paymentReturnTo(String gateway) {
    return 'العودة إلى $gateway';
  }

  @override
  String get paymentCheckStatus => 'تحقق من حالة الدفع';

  @override
  String get paymentConfirmingTitle => 'نتحقق من دفعتك';

  @override
  String get paymentConfirmingBody =>
      'نتحقق من الدفع مع المزوّد. قد يستغرق ذلك دقيقة.';

  @override
  String get paymentPaidTitle => 'تم تأكيد الدفع';

  @override
  String get paymentPaidBody => 'يجري تسليم أكوادك. سنخبرك عندما تصبح جاهزة.';

  @override
  String get paymentReceiptTitle => 'أكوادك في الطلبات';

  @override
  String get paymentReceiptBody => 'افتح الطلب لعرض كل كود بعد تسليمه.';

  @override
  String get paymentViewOrder => 'عرض الطلب';

  @override
  String get paymentKeepShopping => 'متابعة التسوق';

  @override
  String get paymentProcessingTitle => 'ما زالت قيد المعالجة';

  @override
  String get paymentProcessingBody =>
      'لم يؤكد مزوّد الدفع بعد. لا حاجة للدفع مرة أخرى؛ يتحدّث الطلب فور التأكيد.';

  @override
  String paymentLastChecked(String time) {
    return 'آخر تحقق $time';
  }

  @override
  String get paymentCheckAgain => 'تحقق مرة أخرى';

  @override
  String get paymentGoToOrders => 'الذهاب إلى الطلبات';

  @override
  String get paymentFailedTitle => 'لم يكتمل الدفع';

  @override
  String get paymentFailedBody =>
      'لم نتمكن من تأكيد دفع لهذا الطلب. يمكنك المحاولة مرة أخرى بالطريقة نفسها.';

  @override
  String get paymentCancelledBody =>
      'غادرت صفحة الدفع قبل الدفع. يمكنك المحاولة مرة أخرى بالطريقة نفسها.';

  @override
  String get paymentFailedFinalBody =>
      'لم يعد بالإمكان دفع هذا الطلب. أنشئ الطلب مرة أخرى من سلتك.';

  @override
  String paymentHeldUntil(String time) {
    return 'طلبك محجوز حتى $time.';
  }

  @override
  String paymentAttempt(int number) {
    return 'المحاولة $number';
  }

  @override
  String get paymentAnotherMethodTitle => 'تريد طريقة أخرى؟';

  @override
  String get paymentAnotherMethodBody =>
      'لاستخدام طريقة دفع أخرى، أنشئ الطلب مرة أخرى من سلتك.';

  @override
  String get paymentAttemptsExhausted =>
      'استُنفدت كل محاولات الدفع لهذا الطلب. أنشئ الطلب مرة أخرى من سلتك.';

  @override
  String get paymentTryAgain => 'أعد محاولة الدفع';

  @override
  String get paymentExpiredTitle => 'انتهت صلاحية الطلب';

  @override
  String get paymentExpiredBody =>
      'لم يُدفع هذا الطلب في الوقت المحدد، لذا أفرجنا عن العناصر. لم يُخصم أي مبلغ.';

  @override
  String get paymentReturnToCart => 'العودة إلى السلة';

  @override
  String get paymentContactSupport => 'تواصل مع الدعم';

  @override
  String get paymentNotStartedTitle => 'مزوّد الدفع غير متاح';

  @override
  String paymentNotStartedBody(String gateway) {
    return 'تعذّر على $gateway بدء الدفع. حاول مرة أخرى؛ طلبك محفوظ.';
  }

  @override
  String get paymentCallFailedTitle => 'تعذّر التحقق من الدفع';

  @override
  String get paymentUnsupportedTitle => 'لا يمكن إكمال هذا الدفع في التطبيق';

  @override
  String get paymentUnsupportedBody =>
      'تحقق من حالة الدفع، أو أنشئ الطلب مرة أخرى بطريقة أخرى.';

  @override
  String get paymentIdleTitle => 'حالة الدفع';

  @override
  String get paymentIdleBody => 'تحقق من دفع هذا الطلب مع المتجر.';

  @override
  String get paymentSummaryOrder => 'الطلب';

  @override
  String get paymentSummaryAmount => 'المبلغ';

  @override
  String get paymentSummaryPaidWith => 'طريقة الدفع';

  @override
  String get paymentSummaryStatus => 'الحالة';

  @override
  String get paymentStatusAwaiting => 'بانتظار الدفع';

  @override
  String get paymentStatusProcessing => 'الدفع قيد المعالجة';

  @override
  String get paymentStatusPaid => 'مدفوع';

  @override
  String get paymentStatusFailed => 'فشل';

  @override
  String get paymentStatusExpired => 'منتهي';

  @override
  String get paymentStatusUnknown => 'لم يُتحقق بعد';

  @override
  String get ordersFilterAll => 'الكل';

  @override
  String get orderStatusCompleted => 'مكتمل';

  @override
  String get orderStatusProcessing => 'قيد التنفيذ';

  @override
  String get orderStatusPaymentProcessing => 'الدفع قيد المعالجة';

  @override
  String get orderStatusAwaitingPayment => 'بانتظار الدفع';

  @override
  String get orderStatusPartlyDelivered => 'تم التسليم جزئيًا';

  @override
  String get orderStatusFailed => 'فشل';

  @override
  String get orderStatusCancelled => 'ملغى';

  @override
  String get orderStatusRefundPending => 'الاسترداد قيد التنفيذ';

  @override
  String get orderStatusPartiallyRefunded => 'مسترد جزئيًا';

  @override
  String get orderStatusRefunded => 'مسترد';

  @override
  String ordersItemCount(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count منتجات',
      two: 'منتجان',
      one: 'منتج واحد',
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
    return 'الطلب $number، $status، $date، $total';
  }

  @override
  String get ordersEmptyTitle => 'لا توجد طلبات بعد';

  @override
  String get ordersEmptyBody => 'تظهر هنا الأكواد التي تشتريها.';

  @override
  String get ordersEmptyAction => 'تصفح المتجر';

  @override
  String get ordersEmptyFilteredTitle => 'لا توجد طلبات بهذه الحالة';

  @override
  String get ordersShowAll => 'عرض كل الطلبات';

  @override
  String get ordersLoadErrorTitle => 'تعذر تحميل طلباتك';

  @override
  String get ordersMoreFailed => 'تعذر تحميل المزيد من الطلبات.';

  @override
  String get ordersSignedOutTitle => 'سجّل الدخول لعرض طلباتك';

  @override
  String get ordersSignedOutBody => 'طلباتك وأكوادك محفوظة في حسابك.';

  @override
  String get ordersSignIn => 'تسجيل الدخول';

  @override
  String get guestAccessEntry => 'فتح طلب ضيف';

  @override
  String get guestAccessEntryBody =>
      'طلبت من الموقع دون حساب؟ استخدم الرابط في رسالة التسليم.';

  @override
  String orderDetailSemantics(String number) {
    return 'الطلب $number';
  }

  @override
  String orderQtyEach(int quantity, String price) {
    return 'الكمية $quantity · $price للواحدة';
  }

  @override
  String orderQty(int quantity) {
    return 'الكمية $quantity';
  }

  @override
  String get orderUnitCode => 'الكود';

  @override
  String get orderUnitDelivered => 'تم التسليم';

  @override
  String get orderUnitRevealed => 'تم الإظهار';

  @override
  String get orderUnitPreparing => 'قيد التجهيز';

  @override
  String get orderUnitFailed => 'لم يُسلَّم';

  @override
  String get orderUnitRefunded => 'مسترد';

  @override
  String get orderUnitRefundPending => 'الاسترداد قيد التنفيذ';

  @override
  String get orderUnitCancelled => 'ملغى';

  @override
  String orderUnitSemantics(int index, String name, String status) {
    return 'الكود $index من $name، $status';
  }

  @override
  String get orderUnitOpenHint => 'يفتح الكود';

  @override
  String get orderTotal => 'الإجمالي';

  @override
  String get orderCharged => 'المبلغ المدفوع';

  @override
  String get orderPayment => 'الدفع';

  @override
  String orderAttempt(int attempt) {
    return 'المحاولة $attempt';
  }

  @override
  String get orderPaymentStatus => 'حالة الدفع';

  @override
  String get orderPaymentUnpaid => 'غير مدفوع';

  @override
  String get orderPaymentProcessing => 'قيد المعالجة';

  @override
  String get orderPaymentPaid => 'مدفوع';

  @override
  String get orderPaymentPartiallyRefunded => 'مسترد جزئيًا';

  @override
  String get orderPaymentRefunded => 'مسترد';

  @override
  String get orderRetryPayment => 'إعادة محاولة الدفع';

  @override
  String get orderAwaitingPaymentNote =>
      'هذا الطلب بانتظار الدفع. لم تكتمل محاولتك الأخيرة.';

  @override
  String get orderGetHelp => 'الحصول على مساعدة';

  @override
  String get orderRequestRefund => 'طلب استرداد';

  @override
  String get orderTimeline => 'السجل الزمني';

  @override
  String get orderTimelinePlaced => 'تم الطلب';

  @override
  String get orderTimelinePaid => 'تم الدفع';

  @override
  String get orderRefundPendingTitle => 'الاسترداد قيد التنفيذ';

  @override
  String get orderRefundPartialTitle => 'تم استرداد جزء من هذا الطلب';

  @override
  String get orderRefundFullTitle => 'تم استرداد هذا الطلب';

  @override
  String orderRefundToWallet(String amount) {
    return 'تمت إعادة $amount إلى محفظتك.';
  }

  @override
  String orderRefundToOriginal(String amount) {
    return 'تمت إعادة $amount إلى وسيلة الدفع.';
  }

  @override
  String get orderNotFoundTitle => 'الطلب غير موجود';

  @override
  String get orderNotFoundBody => 'هذا الطلب غير موجود في حسابك.';

  @override
  String get orderLoadErrorTitle => 'تعذر تحميل هذا الطلب';

  @override
  String get refundRequestTitle => 'طلب استرداد';

  @override
  String get revealTitle => 'الكود الخاص بك';

  @override
  String revealCodeOf(int index, int count) {
    return 'الكود $index من $count';
  }

  @override
  String revealDeliveredAt(String date) {
    return 'سُلّم $date';
  }

  @override
  String get revealCodeLabel => 'الكود';

  @override
  String get revealPinLabel => 'الرقم السري';

  @override
  String revealMaskedSemantics(String last) {
    return 'الكود مخفي. ينتهي بـ $last.';
  }

  @override
  String get revealMaskedSemanticsNoHint => 'الكود مخفي.';

  @override
  String get revealButton => 'إظهار الكود';

  @override
  String get revealButtonHint => 'يطلب تأكيد هويتك ثم يعرض الكود لمدة 60 ثانية';

  @override
  String get revealCopy => 'نسخ الكود';

  @override
  String get revealCopySemantics => 'نسخ الكود. تُمسح الحافظة بعد 60 ثانية';

  @override
  String get revealHide => 'إخفاء';

  @override
  String get revealHideSemantics => 'إخفاء الكود الآن';

  @override
  String get revealReadAloud => 'قراءة الكود بصوت';

  @override
  String get revealShownSemantics =>
      'الكود ظاهر. استخدم قراءة الكود بصوت لسماعه.';

  @override
  String get revealCopied => 'تم نسخ الكود. تُمسح الحافظة خلال 60 ث.';

  @override
  String revealHidesIn(String time) {
    return 'يُخفى خلال $time';
  }

  @override
  String get revealOpenRedemption => 'فتح صفحة الاسترداد';

  @override
  String get revealSecureNoteAndroid =>
      'لقطات الشاشة محظورة هنا. يُسجَّل كل إظهار للكود.';

  @override
  String get revealSecureNoteIos =>
      'يُخفى الكود في مبدّل التطبيقات وأثناء تسجيل الشاشة. يُسجَّل كل إظهار للكود.';

  @override
  String get revealHowTo => 'طريقة الاستخدام';

  @override
  String get revealHowToAfterReveal => 'تظهر خطوات الاستخدام مع الكود.';

  @override
  String get revealHiddenTimeout => 'أُخفي الكود بعد 60 ثانية.';

  @override
  String get revealHidden => 'أُخفي الكود.';

  @override
  String get revealCaptureTitle => 'تم رصد تسجيل للشاشة';

  @override
  String get revealCaptureBody =>
      'لا يمكننا حظر لقطات الشاشة على آيفون، لذا يُخفى الكود أثناء التسجيل أو المشاركة.';

  @override
  String get revealCaptureHidden =>
      'الكود مخفي أثناء تسجيل الشاشة أو مشاركتها.';

  @override
  String get revealObscured => 'مخفي أثناء عدم ظهور التطبيق';

  @override
  String get revealDeniedTitle => 'لا يمكن عرض هذا الكود';

  @override
  String get revealDeniedBody =>
      'تأكد من توثيق بريدك الإلكتروني ومن دفع الطلب. تُسجَّل كل محاولة.';

  @override
  String get revealGuestDeniedBody =>
      'افتح الرابط في رسالة التسليم وأكّد الرمز المُرسل على هذا الجهاز أولًا.';

  @override
  String get revealUnverifiedTitle => 'وثّق بريدك الإلكتروني لإظهار الأكواد';

  @override
  String get revealUnverifiedBody => 'أرسلنا رابطًا إلى بريدك الإلكتروني.';

  @override
  String get revealVerifyAction => 'توثيق البريد';

  @override
  String get revealRateLimitedTitle => 'عدد كبير من مرات الإظهار';

  @override
  String revealRateLimitedBody(int minutes) {
    String _temp0 = intl.Intl.pluralLogic(
      minutes,
      locale: localeName,
      other: '$minutes دقيقة',
      two: 'دقيقتين',
      one: 'دقيقة واحدة',
    );
    return 'لأمانك يمكنك إظهار 10 أكواد في الساعة. حاول مجددًا بعد $_temp0.';
  }

  @override
  String get revealRateLimitedLater =>
      'لأمانك يمكنك إظهار 10 أكواد في الساعة. حاول مجددًا لاحقًا.';

  @override
  String revealTryAgainIn(String time) {
    return 'حاول مجددًا بعد $time';
  }

  @override
  String get revealIntegrityTitle => 'قد يكون هذا الجهاز معدّلًا';

  @override
  String get revealIntegrityBody =>
      'الأجهزة المعدّلة (روت أو جيلبريك) أقل أمانًا للأكواد. أظهر الكود على جهاز آخر إن أمكن.';

  @override
  String get revealNotFoundTitle => 'الكود غير موجود';

  @override
  String get revealNotFoundBody => 'هذا الكود غير موجود في طلباتك.';

  @override
  String get revealFailedTitle => 'تعذر عرض الكود';

  @override
  String get revealNotReady =>
      'هذا الكود غير جاهز بعد. سنرسل إليك بريدًا عندما يجهز.';

  @override
  String get revealBiometricPrompt => 'أكّد هويتك لإظهار الكود';

  @override
  String get reauthTitle => 'تأكيد الهوية';

  @override
  String get reauthBody =>
      'أدخل كلمة المرور لإظهار الأكواد. سنطلبها مجددًا بعد 15 دقيقة.';

  @override
  String get reauthConfirm => 'تأكيد وإظهار';

  @override
  String get reauthForgot => 'نسيت كلمة المرور؟';

  @override
  String get guestOrderTitle => 'طلب ضيف';

  @override
  String guestOrderNumberTitle(String number) {
    return 'الطلب $number';
  }

  @override
  String get guestVerifyTitle => 'تأكيد الهوية';

  @override
  String get guestVerifyBody =>
      'لفتح أكواد هذا الطلب سنرسل رمزًا من 6 أرقام إلى البريد الذي طلبت به.';

  @override
  String get guestEmailLabel => 'بريد الطلب';

  @override
  String get guestLinkVerifiedTitle => 'تم التحقق من الرابط';

  @override
  String get guestLinkVerifiedBody =>
      'فتحت هذا الطلب من رسالة التسليم. الرابط صالح لمدة 72 ساعة.';

  @override
  String get guestSendCode => 'إرسال الرمز';

  @override
  String get guestCodeTitle => 'أدخل الرمز';

  @override
  String guestCodeBody(String email) {
    return 'إذا كان $email مطابقًا للطلب فقد أرسلنا إليه رمزًا من 6 أرقام. تنتهي صلاحيته خلال 10 دقائق.';
  }

  @override
  String get guestCodeSemantics => 'رمز من 6 أرقام من البريد';

  @override
  String get guestVerify => 'تحقق';

  @override
  String get guestResend => 'إعادة إرسال الرمز';

  @override
  String guestResendIn(String time) {
    return 'إعادة الإرسال بعد $time';
  }

  @override
  String get guestChangeEmail => 'تغيير البريد';

  @override
  String get guestOrderLabel => 'طلب ضيف';

  @override
  String get guestAccountPromptTitle => 'احتفظ بأكوادك في مكان واحد';

  @override
  String get guestAccountPromptBody =>
      'أنشئ حسابًا ببريد الطلب لترى طلباتك القادمة هنا.';

  @override
  String get guestCreateAccount => 'إنشاء حساب';

  @override
  String get guestLinkExpiredTitle => 'انتهت صلاحية هذا الرابط';

  @override
  String get guestLinkExpiredBody =>
      'تعمل روابط الطلب لمدة 72 ساعة. تواصل مع الدعم للحصول على رابط جديد.';

  @override
  String get guestNeedLinkTitle => 'افتح رسالة التسليم';

  @override
  String get guestNeedLinkBody =>
      'لعرض أكواد طلب ضيف على هذا الجهاز، افتح الرابط في رسالة التسليم.';

  @override
  String get guestLinkProofMissing =>
      'افتح الرابط في رسالة التسليم مجددًا لإظهار الأكواد على هذا الجهاز.';

  @override
  String get guestEntryTitle => 'فتح طلب ضيف';

  @override
  String get guestEntryBody =>
      'الصق الرابط من رسالة التسليم. سنرسل إليك رمزًا بالبريد لتأكيد هويتك.';

  @override
  String get guestEntryLabel => 'رابط الطلب';

  @override
  String get guestEntryInvalid => 'هذا ليس رابط طلب من boxcards.shop.';

  @override
  String get guestEntryOpen => 'فتح الطلب';

  @override
  String get orderGatewayWallet => 'المحفظة';

  @override
  String get walletTitle => 'المحفظة';

  @override
  String get walletAccountSubtitle => 'رصيد المتجر وكشف الحساب';

  @override
  String get walletStoreCredit => 'رصيد المتجر';

  @override
  String get walletStatusActive => 'نشطة';

  @override
  String get walletStatusFrozen => 'مجمّدة';

  @override
  String get walletBalanceNote =>
      'من المبالغ المستردة وأرصدة الدعم. يمكن استخدامه للدفع، ولا يمكن شحنه.';

  @override
  String walletBalanceSemantics(String amount, String status) {
    return 'رصيد المتجر $amount، $status';
  }

  @override
  String get walletStatement => 'كشف الحساب';

  @override
  String get walletFrozenTitle => 'المحفظة مجمّدة';

  @override
  String get walletFrozenBody =>
      'لا يمكنك الدفع برصيد المتجر حاليًا. تستمر المبالغ المستردة بالوصول. تواصل مع الدعم لمعرفة المزيد.';

  @override
  String get walletContactSupport => 'تواصل مع الدعم';

  @override
  String get walletEmptyTitle => 'لا توجد معاملات بعد';

  @override
  String get walletEmptyBody => 'ستظهر المبالغ المستردة والأرصدة هنا.';

  @override
  String get walletLoadErrorTitle => 'تعذّر تحميل محفظتك';

  @override
  String get walletMoreFailed => 'تعذّر تحميل المزيد من المعاملات';

  @override
  String get walletUnavailableTitle => 'المحفظة غير متاحة';

  @override
  String get walletUnavailableBody =>
      'لا يمكن عرض رصيد المتجر في التطبيق حاليًا.';

  @override
  String get walletTypeCreditRefund => 'استرداد';

  @override
  String get walletTypeCreditAdmin => 'رصيد من الدعم';

  @override
  String get walletTypeDebitPurchase => 'دفع';

  @override
  String get walletTypeDebitAdmin => 'تسوية من الدعم';

  @override
  String get walletTypeReversal => 'عكس عملية';

  @override
  String get walletTypeOther => 'معاملة في المحفظة';

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
    return '$title، $amount، الرصيد $balance، $date';
  }

  @override
  String get walletLineOpenOrderHint => 'يفتح الطلب';

  @override
  String checkoutWalletBalance(String amount) {
    return 'رصيد المتجر · الرصيد $amount';
  }

  @override
  String get accountSignedOutTitle => 'حسابك';

  @override
  String get accountSignedOutBody =>
      'سجّل الدخول لرؤية طلباتك ورموزك ورصيد المتجر.';

  @override
  String accountHeaderSemantics(String name, String email) {
    return 'مسجّل الدخول باسم $name، $email';
  }

  @override
  String get accountVerifiedBadge => 'موثّق';

  @override
  String get accountUnverifiedBadge => 'غير موثّق';

  @override
  String get accountNotifications => 'الإشعارات';

  @override
  String get accountSupport => 'الدعم';

  @override
  String get accountProfile => 'الملف الشخصي';

  @override
  String get accountSecurity => 'الأمان';

  @override
  String get accountSettings => 'الإعدادات';

  @override
  String get accountTwoFactorOn => 'التحقق بخطوتين مفعّل';

  @override
  String get accountTwoFactorOff => 'التحقق بخطوتين غير مفعّل';

  @override
  String get accountGuestSection => 'طلبت دون حساب؟';

  @override
  String appVersionLabel(String version) {
    return 'الإصدار $version';
  }

  @override
  String get settingsPreferences => 'التفضيلات';

  @override
  String get settingsAbout => 'حول';

  @override
  String get settingsTerms => 'شروط البيع';

  @override
  String get settingsPrivacy => 'سياسة الخصوصية';

  @override
  String get settingsRefundPolicy => 'سياسة الاسترداد';

  @override
  String get settingsHelpCentre => 'مركز المساعدة';

  @override
  String get opensInBrowserHint => 'يُفتح في المتصفح';

  @override
  String get languageChanged => 'تم تغيير اللغة.';

  @override
  String get profileNameLabel => 'الاسم الكامل';

  @override
  String get profileEmailLabel => 'البريد الإلكتروني';

  @override
  String get profileEmailHelper => 'تواصل مع الدعم لتغيير بريدك الإلكتروني.';

  @override
  String get profilePhoneLabel => 'الهاتف (اختياري)';

  @override
  String get profilePhoneHelper => 'مع رمز الدولة، مثل ‎+20 100 123 4567.';

  @override
  String get profileCountryLabel => 'الدولة';

  @override
  String get profileRegionalSection => 'اللغة والعملة';

  @override
  String get profileSave => 'حفظ التغييرات';

  @override
  String get profileSaved => 'تم حفظ الملف الشخصي.';

  @override
  String get profileUnchanged => 'لا توجد تغييرات لحفظها.';

  @override
  String get profileNameTooLong => 'استخدم 120 حرفًا على الأكثر.';

  @override
  String get profileUnverifiedTitle => 'بريدك الإلكتروني غير موثّق';

  @override
  String get profileUnverifiedBody =>
      'وثّقه لتتمكن من عرض الرموز وكتابة التقييمات. ابحث عن الرابط في بريدك.';

  @override
  String get profileCountriesFailed => 'تعذّر تحميل الدول.';

  @override
  String get changePasswordTitle => 'تغيير كلمة المرور';

  @override
  String get changePasswordSubtitle => 'يسجّل خروج أجهزتك الأخرى';

  @override
  String get currentPasswordLabel => 'كلمة المرور الحالية';

  @override
  String get newPasswordLabel => 'كلمة المرور الجديدة';

  @override
  String get newPasswordHelper =>
      '10 أحرف على الأقل، ومختلفة عن كلمة المرور الحالية.';

  @override
  String get confirmNewPasswordLabel => 'تأكيد كلمة المرور الجديدة';

  @override
  String get changePasswordNoticeTitle => 'سيتم تسجيل خروج الأجهزة الأخرى';

  @override
  String get changePasswordNoticeBody => 'سيبقى هذا الجهاز مسجّل الدخول.';

  @override
  String get changePasswordAction => 'تحديث كلمة المرور';

  @override
  String get passwordChanged =>
      'تم تغيير كلمة المرور. تم تسجيل خروج أجهزتك الأخرى.';

  @override
  String get passwordChangedBiometricTitle => 'فعّل فتح القفل بالبصمة مرة أخرى';

  @override
  String get passwordChangedBiometricBody =>
      'أدى تغيير كلمة المرور إلى إيقاف فتح القفل بالبصمة على جميع الأجهزة. فعّله أدناه إن أردت.';

  @override
  String get securitySignInSection => 'تسجيل الدخول';

  @override
  String get securityDevicesSection => 'الأجهزة';

  @override
  String get twoFactorTitle => 'التحقق بخطوتين';

  @override
  String get twoFactorOffSubtitle => 'غير مفعّل · احمِ رموزك بتطبيق مصادقة';

  @override
  String get twoFactorOnSubtitle =>
      'مفعّل · رمز من تطبيق المصادقة عند تسجيل الدخول';

  @override
  String get twoFactorTurnOn => 'تفعيل';

  @override
  String get twoFactorTurnOff => 'إيقاف';

  @override
  String get devicesTitle => 'الأجهزة المسجّلة';

  @override
  String devicesCount(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count جهاز',
      many: '$count جهازًا',
      few: '$count أجهزة',
      two: 'جهازان',
      one: 'جهاز واحد',
      zero: 'لا أجهزة',
    );
    return '$_temp0';
  }

  @override
  String get securityRevealNoticeTitle => 'عرض الرموز يتطلب تأكيد هويتك';

  @override
  String get securityRevealNoticeBody =>
      'مرة كل 15 دقيقة على الأكثر على كل جهاز: كلمة المرور، أو البصمة حيث يكون فتح القفل بالبصمة مفعّلًا.';

  @override
  String get signOutEverywhere => 'تسجيل الخروج من كل مكان';

  @override
  String get signOutEverywhereTitle => 'تسجيل الخروج من كل مكان؟';

  @override
  String get signOutEverywhereBody =>
      'ستحتاج كل الأجهزة، ومنها هذا الجهاز، إلى كلمة المرور لتسجيل الدخول مرة أخرى.';

  @override
  String get signedOutEverywhere => 'تم تسجيل الخروج من كل الأجهزة.';

  @override
  String get twoFactorPasswordBody =>
      'أدخل كلمة المرور للبدء. ستحتاج إلى تطبيق مصادقة على هاتفك.';

  @override
  String get twoFactorScanStep => '1. امسح الرمز بتطبيق المصادقة';

  @override
  String get twoFactorQrSemantics => 'رمز QR لتطبيق المصادقة';

  @override
  String get twoFactorKeyLabel => 'أو أدخل هذا المفتاح';

  @override
  String get twoFactorCopyKey => 'نسخ المفتاح';

  @override
  String get twoFactorKeyCopied =>
      'تم نسخ المفتاح. سيتم مسح الحافظة بعد 60 ثانية.';

  @override
  String get twoFactorCodeStep => '2. أدخل الرمز المكوّن من 6 أرقام';

  @override
  String get twoFactorCodeSemantics =>
      'الرمز المكوّن من 6 أرقام من تطبيق المصادقة';

  @override
  String get twoFactorCodeInvalid => 'أدخل الأرقام الستة من التطبيق.';

  @override
  String get twoFactorSecureNote =>
      'احتفظ بهذا المفتاح لنفسك. لقطات الشاشة محظورة حيث يسمح هاتفك.';

  @override
  String get recoveryCodesTitle => 'رموز الاسترداد';

  @override
  String get twoFactorOnBannerTitle => 'التحقق بخطوتين مفعّل';

  @override
  String get twoFactorOnBannerBody =>
      'سنطلب منك رمزًا عند تسجيل الدخول في المرة القادمة.';

  @override
  String get recoveryCodesBody =>
      'احفظ هذه الرموز في مكان آمن. كل رمز يعمل مرة واحدة إذا فقدت هاتفك. لن نعرضها مرة أخرى.';

  @override
  String recoveryCodesListSemantics(String codes) {
    return 'رموز الاسترداد: $codes';
  }

  @override
  String get recoveryCodesCopy => 'نسخ الكل';

  @override
  String get recoveryCodesCopied =>
      'تم نسخ الرموز. سيتم مسح الحافظة بعد 60 ثانية.';

  @override
  String get recoveryCodesSaved => 'حفظت الرموز';

  @override
  String get recoveryCodesLeaveTitle => 'هل حفظت الرموز؟';

  @override
  String get recoveryCodesLeaveBody =>
      'لن ترى هذه الرموز مرة أخرى. بدونها قد تفقد الوصول إلى حسابك إذا فقدت هاتفك.';

  @override
  String get recoveryCodesShowAgain => 'العودة إلى الرموز';

  @override
  String get twoFactorAlreadyOnTitle => 'التحقق بخطوتين مفعّل بالفعل';

  @override
  String get twoFactorAlreadyOnBody => 'لإعداده من جديد، أوقفه أولًا.';

  @override
  String get twoFactorEnabled => 'تم تفعيل التحقق بخطوتين.';

  @override
  String get twoFactorDisableTitle => 'إيقاف التحقق بخطوتين؟';

  @override
  String get twoFactorDisableBody =>
      'سيُحمى حسابك بكلمة المرور فقط. أدخلها للمتابعة.';

  @override
  String get twoFactorKeepOn => 'إبقاؤه مفعّلًا';

  @override
  String get twoFactorDisabled => 'تم إيقاف التحقق بخطوتين.';

  @override
  String get devicesPageTitle => 'الأجهزة';

  @override
  String get deviceThisDevice => 'هذا الجهاز';

  @override
  String get deviceCurrentBadge => 'الحالي';

  @override
  String deviceLastUsed(String date) {
    return 'آخر استخدام $date';
  }

  @override
  String deviceIp(String ip) {
    return 'IP $ip';
  }

  @override
  String get deviceUnnamed => 'جهاز بلا اسم';

  @override
  String get deviceWebBrowser => 'متصفح ويب';

  @override
  String get devicesWebNotListed => 'جلسات الويب غير مدرجة هنا.';

  @override
  String get deviceSignOut => 'تسجيل الخروج';

  @override
  String deviceSignOutSemantics(String name) {
    return 'تسجيل خروج $name';
  }

  @override
  String deviceRevokeTitle(String name) {
    return 'تسجيل خروج «$name»؟';
  }

  @override
  String get deviceRevokeBody =>
      'سيحتاج ذلك الجهاز إلى كلمة المرور لتسجيل الدخول مرة أخرى.';

  @override
  String get deviceRevokeAction => 'تسجيل خروج الجهاز';

  @override
  String deviceSignedOut(String name) {
    return 'تم تسجيل خروج $name.';
  }

  @override
  String get devicesOnlyThis => 'لا يوجد جهاز آخر مسجّل الدخول.';

  @override
  String get signOutOthersAction => 'تسجيل الخروج من كل الأجهزة الأخرى';

  @override
  String get signOutOthersTitle => 'تسجيل الخروج من كل الأجهزة الأخرى؟';

  @override
  String get signOutOthersBody =>
      'أدخل كلمة المرور. سيبقى هذا الجهاز مسجّل الدخول.';

  @override
  String get signOutOthersDone => 'تم تسجيل خروج أجهزتك الأخرى.';

  @override
  String get displayCurrencyAutomatic => 'تلقائي';

  @override
  String get listMoreFailed => 'تعذّر تحميل المزيد';

  @override
  String get myReviewsTitle => 'مراجعاتي';

  @override
  String get myReviewsLoadError => 'تعذّر تحميل مراجعاتك';

  @override
  String get myReviewsEmptyTitle => 'لا توجد مراجعات بعد';

  @override
  String get myReviewsEmptyBody => 'بعد تسليم الكود يمكنك تقييمه هنا.';

  @override
  String get reviewsReadyTitle => 'جاهزة للمراجعة';

  @override
  String get reviewsYoursTitle => 'مراجعاتك';

  @override
  String reviewOrderNumber(String number) {
    return 'الطلب $number';
  }

  @override
  String get reviewWriteHint => 'يفتح نموذج المراجعة';

  @override
  String get reviewWriteAction => 'اكتب مراجعة';

  @override
  String reviewWriteSemantics(String product) {
    return 'اكتب مراجعة لـ $product';
  }

  @override
  String get reviewWriteTitle => 'اكتب مراجعة';

  @override
  String get reviewEditTitle => 'تعديل المراجعة';

  @override
  String get reviewEditAction => 'تعديل';

  @override
  String reviewEditSemantics(String product) {
    return 'عدّل مراجعتك لـ $product';
  }

  @override
  String get reviewEditNoticeTitle => 'تُراجع التعديلات من جديد';

  @override
  String get reviewEditNoticeBody =>
      'تعود مراجعتك إلى المراجعة وتبقى مخفية حتى تتم الموافقة عليها.';

  @override
  String get reviewStatusPending => 'قيد المراجعة';

  @override
  String get reviewStatusApproved => 'منشورة';

  @override
  String get reviewStatusRejected => 'غير منشورة';

  @override
  String get reviewRatingLabel => 'تقييمك';

  @override
  String reviewRateStars(int count) {
    return '$count من 5 نجوم';
  }

  @override
  String get reviewRatingRequired => 'اختر تقييمًا من 1 إلى 5 نجوم.';

  @override
  String get reviewTitleLabel => 'العنوان (اختياري)';

  @override
  String get reviewBodyLabel => 'المراجعة (اختياري)';

  @override
  String get reviewModerationNote => 'تُراجع المراجعات قبل نشرها.';

  @override
  String get reviewSubmitAction => 'إرسال المراجعة';

  @override
  String get reviewSaveAction => 'حفظ التغييرات';

  @override
  String get reviewSubmitted => 'شكرًا لك. ستظهر مراجعتك بعد الموافقة عليها.';

  @override
  String get reviewUpdated =>
      'تم الحفظ. ستظهر مراجعتك مجددًا بعد الموافقة عليها.';

  @override
  String get reviewLoadError => 'تعذّر تحميل هذه المراجعة';

  @override
  String get reviewNotFoundTitle => 'لم يتم العثور على المراجعة';

  @override
  String get reviewNotEligibleTitle => 'لا يمكن مراجعة هذا المنتج';

  @override
  String get reviewNotEligibleBody =>
      'يمكن مراجعة المنتجات التي اشتريتها واستلمتها فقط، مرة واحدة لكل منتج.';

  @override
  String get reviewEmailUnverifiedTitle =>
      'وثّق بريدك الإلكتروني لكتابة المراجعات';

  @override
  String get reviewEmailUnverifiedBody =>
      'افتح الرابط الذي أرسلناه إلى بريدك الإلكتروني ثم حاول مجددًا.';

  @override
  String get reviewExistsTitle => 'لقد راجعت هذا المنتج من قبل';

  @override
  String get reviewExistsBody =>
      'يمكن مراجعة كل منتج مشترى مرة واحدة. يمكنك تعديل مراجعتك من مراجعاتي.';

  @override
  String supportOrderNumber(String number) {
    return 'الطلب $number';
  }

  @override
  String get supportSignedOutTitle => 'سجّل الدخول للتواصل مع الدعم';

  @override
  String get supportSignedOutBody => 'ترتبط طلبات الدعم بحسابك وطلباتك.';

  @override
  String get supportUnavailableTitle => 'الدعم غير متاح في التطبيق';

  @override
  String get supportUnavailableBody =>
      'لا يزال بإمكانك التواصل معنا عبر مركز المساعدة.';

  @override
  String get ticketsLoadError => 'تعذّر تحميل طلباتك';

  @override
  String get ticketsEmptyTitle => 'لا توجد طلبات بعد';

  @override
  String get ticketsEmptyBody => 'لديك سؤال عن طلب أو دفع أو حسابك؟ نحن هنا.';

  @override
  String get ticketNewAction => 'طلب جديد';

  @override
  String get ticketNewTitle => 'طلب جديد';

  @override
  String get ticketStatusOpen => 'مفتوح';

  @override
  String get ticketStatusWaiting => 'بانتظار ردّنا';

  @override
  String get ticketStatusAnswered => 'تم الرد';

  @override
  String get ticketStatusResolved => 'تم الحل';

  @override
  String get ticketStatusClosed => 'مغلق';

  @override
  String get ticketCategoryOrder => 'طلب';

  @override
  String get ticketCategoryPayment => 'الدفع';

  @override
  String get ticketCategoryRefund => 'الاسترداد';

  @override
  String get ticketCategoryAccount => 'الحساب';

  @override
  String get ticketCategoryOther => 'أخرى';

  @override
  String ticketUpdated(String topic, String date) {
    return '$topic · آخر تحديث $date';
  }

  @override
  String get ticketTopicLabel => 'الموضوع';

  @override
  String get ticketOrderLabel => 'الطلب (اختياري)';

  @override
  String get ticketOrderNone => 'بدون طلب';

  @override
  String get ticketOrderLinked => 'طلب مرتبط';

  @override
  String get ticketOrdersFailed =>
      'تعذّر تحميل طلباتك. يمكنك إرسال الطلب بدون طلب مرتبط.';

  @override
  String get ticketSubjectLabel => 'العنوان';

  @override
  String ticketSubjectTooShort(int min) {
    return 'استخدم $min أحرف على الأقل.';
  }

  @override
  String get ticketMessageLabel => 'الرسالة';

  @override
  String get ticketMessageHelper =>
      'لا تشارك الأكواد أو كلمات المرور أبدًا. المرفقات غير مدعومة، لذا صف المشكلة هنا.';

  @override
  String get ticketSendAction => 'إرسال الطلب';

  @override
  String ticketThreadSemantics(String number) {
    return 'طلب الدعم $number';
  }

  @override
  String get ticketFromYou => 'أنت';

  @override
  String get ticketFromSupport => 'الدعم';

  @override
  String get ticketReplyHint => 'اكتب ردًا…';

  @override
  String get ticketSendReply => 'إرسال الرد';

  @override
  String get ticketReplyRequired => 'اكتب رسالة أولًا.';

  @override
  String get ticketLoadError => 'تعذّر تحميل هذا الطلب';

  @override
  String get ticketNotFoundTitle => 'لم يتم العثور على الطلب';

  @override
  String get ticketClosedTitle => 'هذا الطلب مغلق';

  @override
  String get ticketClosedBody =>
      'الردود متوقفة. ابدأ طلبًا جديدًا إذا كنت لا تزال بحاجة إلى المساعدة.';

  @override
  String get refundRequestsTitle => 'طلبات الاسترداد';

  @override
  String get refundRequestsLoadError => 'تعذّر تحميل طلبات الاسترداد';

  @override
  String get refundRequestsEmptyTitle => 'لا توجد طلبات استرداد';

  @override
  String get refundRequestsEmptyBody => 'يمكنك طلب استرداد من صفحة طلب مدفوع.';

  @override
  String get refundRequestUnknownOrder => 'طلب';

  @override
  String get refundStatusRequested => 'مُرسل';

  @override
  String get refundStatusApproved => 'تمت الموافقة';

  @override
  String get refundStatusRejected => 'لم تتم الموافقة';

  @override
  String get refundStatusCompleted => 'مكتمل';

  @override
  String refundOrderPaid(String amount, String date) {
    return 'مدفوع $amount · $date';
  }

  @override
  String get refundReasonLabel => 'ما المشكلة؟';

  @override
  String refundReasonHelper(int min, int max) {
    return 'من $min إلى $max حرف. قد لا تُسترد الأكواد التي تم كشفها.';
  }

  @override
  String refundReasonTooShort(int min) {
    return 'استخدم $min أحرف على الأقل.';
  }

  @override
  String get refundTicketLabel => 'ربط طلب دعم (اختياري)';

  @override
  String get refundTicketNone => 'لا شيء';

  @override
  String get refundHowTitle => 'كيف يتم الاسترداد';

  @override
  String get refundHowBody =>
      'نراجع كل طلب. تذهب المبالغ المستردة الموافق عليها إلى محفظتك أو وسيلة الدفع الأصلية.';

  @override
  String get refundSendAction => 'إرسال الطلب';

  @override
  String get refundSentTitle => 'تم إرسال الطلب';

  @override
  String get refundSentBody => 'يمكنك متابعة حالته هنا.';

  @override
  String get refundOpenTitle => 'يوجد طلب مفتوح لهذا الطلب بالفعل';

  @override
  String get refundOpenBody =>
      'يمكنك متابعته في طلبات الاسترداد. طلب مفتوح واحد لكل طلب.';

  @override
  String get refundNotAllowedTitle => 'لا يمكن استرداد هذا الطلب';

  @override
  String get refundOrderRefusedTitle =>
      'لا يمكن استخدام هذا الطلب لطلب استرداد';

  @override
  String orderRefundRequestStatus(String status) {
    return 'طلب الاسترداد: $status';
  }

  @override
  String orderRefundRequestSent(String date) {
    return 'أُرسل في $date. اضغط لعرض طلبات الاسترداد.';
  }

  @override
  String notificationBellUnread(int count) {
    return 'الإشعارات، $count غير مقروءة';
  }

  @override
  String notificationsNewCount(int count) {
    return '$count جديدة';
  }

  @override
  String notificationsUnreadCount(int count) {
    return '$count غير مقروءة';
  }

  @override
  String get notificationsAllRead => 'كلها مقروءة';

  @override
  String get notificationsMarkAllRead => 'تعليم الكل كمقروء';

  @override
  String get notificationsLoadError => 'تعذّر تحميل إشعاراتك';

  @override
  String get notificationsEmptyTitle => 'لا جديد لديك';

  @override
  String get notificationsEmptyBody => 'ستظهر هنا تحديثات الطلبات وردود الدعم.';

  @override
  String get notificationUnread => 'غير مقروء';

  @override
  String get notificationOpenHint => 'يفتح التفاصيل';

  @override
  String get notificationJustNow => 'الآن';

  @override
  String notificationMinutesAgo(int count) {
    return 'قبل $count دقيقة';
  }

  @override
  String notificationHoursAgo(int count) {
    return 'قبل $count ساعة';
  }

  @override
  String get notificationSettingsTitle => 'إعدادات الإشعارات';

  @override
  String get preferencesLoadError => 'تعذّر تحميل إعدادات الإشعارات';

  @override
  String get preferenceCategoryOrders => 'الطلبات';

  @override
  String get preferenceCategorySupport => 'الدعم';

  @override
  String get preferenceCategoryMarketing => 'العروض والأخبار';

  @override
  String get preferenceCategorySecurity => 'الأمان';

  @override
  String get preferenceChannelInbox => 'داخل التطبيق';

  @override
  String get preferenceChannelMail => 'البريد الإلكتروني';

  @override
  String get preferenceChannelSms => 'رسائل SMS';

  @override
  String get preferenceRequired => 'إلزامي';

  @override
  String get preferenceSmsNote => 'فقط إلى رقم الهاتف في ملفك الشخصي';

  @override
  String preferenceSemantics(String category, String channel) {
    return '$category، $channel';
  }

  @override
  String preferenceSaveFailed(String reason) {
    return 'لم يتم الحفظ. $reason';
  }

  @override
  String get preferencePushTitle => 'الإشعارات الفورية';

  @override
  String get preferencePushBody =>
      'قريبًا. حتى ذلك الحين تظهر التحديثات في صندوق الإشعارات وعبر البريد.';
}
