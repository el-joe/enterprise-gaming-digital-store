package shop.boxcards.app

import io.flutter.embedding.android.FlutterFragmentActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel

// FlutterFragmentActivity (not FlutterActivity): biometric_signature shows the androidx
// BiometricPrompt, which needs a FragmentActivity (M-A10).
class MainActivity : FlutterFragmentActivity() {
    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)
        MethodChannel(flutterEngine.dartExecutor.binaryMessenger, SecurityChannel.NAME)
            .setMethodCallHandler(SecurityChannel(this))
    }
}
