package shop.boxcards.app

import android.app.Activity
import android.content.ClipData
import android.content.ClipDescription
import android.content.ClipboardManager
import android.content.Context
import android.os.Build
import android.os.PersistableBundle
import android.view.WindowManager
import io.flutter.plugin.common.MethodCall
import io.flutter.plugin.common.MethodChannel

/**
 * Screen and clipboard protection for sensitive screens (MOBILE_APP_ROADMAP.md section 6.3).
 *
 * Kept as a small in-app channel instead of a third-party plugin so the behaviour is auditable:
 * - `setSecure(enabled)` toggles FLAG_SECURE (blocks screenshots, recording and the recents thumbnail);
 * - `copySensitive(text)` writes a clip marked sensitive (Android 13+ hides it from the clipboard preview);
 * - `clearSensitive()` clears the clipboard only while it still holds the clip this app wrote.
 */
class SecurityChannel(private val activity: Activity) : MethodChannel.MethodCallHandler {
    override fun onMethodCall(call: MethodCall, result: MethodChannel.Result) {
        when (call.method) {
            "setSecure" -> {
                val enabled = call.argument<Boolean>("enabled") ?: false
                if (enabled) {
                    activity.window.addFlags(WindowManager.LayoutParams.FLAG_SECURE)
                } else {
                    activity.window.clearFlags(WindowManager.LayoutParams.FLAG_SECURE)
                }
                result.success(true)
            }
            "copySensitive" -> {
                val text = call.argument<String>("text")
                if (text == null) {
                    result.error("invalid_argument", "text is required", null)
                    return
                }
                val clip = ClipData.newPlainText(CLIP_LABEL, text)
                val extras = PersistableBundle()
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
                    extras.putBoolean(ClipDescription.EXTRA_IS_SENSITIVE, true)
                } else {
                    extras.putBoolean(LEGACY_SENSITIVE_EXTRA, true)
                }
                clip.description.extras = extras
                clipboard().setPrimaryClip(clip)
                result.success(true)
            }
            "clearSensitive" -> result.success(clearIfOwned())
            else -> result.notImplemented()
        }
    }

    private fun clearIfOwned(): Boolean {
        val manager = clipboard()
        // The description can be unreadable while the app is in the background (Android 10+);
        // in that case the clipboard is left alone rather than wiping something the user copied.
        val label = try {
            manager.primaryClipDescription?.label
        } catch (_: SecurityException) {
            null
        }
        if (label != CLIP_LABEL) {
            return false
        }
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.P) {
            manager.clearPrimaryClip()
        } else {
            manager.setPrimaryClip(ClipData.newPlainText("", ""))
        }
        return true
    }

    private fun clipboard(): ClipboardManager =
        activity.getSystemService(Context.CLIPBOARD_SERVICE) as ClipboardManager

    companion object {
        const val NAME = "shop.boxcards.app/security"
        private const val CLIP_LABEL = "boxcards.sensitive"
        private const val LEGACY_SENSITIVE_EXTRA = "android.content.extra.IS_SENSITIVE"
    }
}
