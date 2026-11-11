package shop.boxcards.app

import android.app.Activity
import android.content.ClipData
import android.content.ClipDescription
import android.content.ClipboardManager
import android.content.ActivityNotFoundException
import android.content.Context
import android.content.Intent
import android.net.Uri
import android.os.Build
import android.os.PersistableBundle
import android.view.WindowManager
import java.io.File
import io.flutter.plugin.common.MethodCall
import io.flutter.plugin.common.MethodChannel

/**
 * Screen and clipboard protection for sensitive screens (MOBILE_APP_ROADMAP.md section 6.3).
 *
 * Kept as a small in-app channel instead of a third-party plugin so the behaviour is auditable:
 * - `setSecure(enabled)` toggles FLAG_SECURE (blocks screenshots, recording and the recents thumbnail);
 * - `copySensitive(text)` writes a clip marked sensitive (Android 13+ hides it from the clipboard preview);
 * - `clearSensitive()` clears the clipboard only while it still holds the clip this app wrote;
 * - `deviceIntegrity()` returns `ok` or `compromised` from a minimal root check (M-A10, owner
 *   decision 5: a warning signal, never a block; it is bypassable by design);
 * - `deviceName()` names the sign-in (`device_name` of the mobile auth API);
 * - `openExternalUrl(url)` opens an https URL in the user's browser (forgot password, support);
 * - `openStoreListing()` opens this app's Play Store listing (force update).
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
            "deviceIntegrity" -> result.success(if (looksRooted()) "compromised" else "ok")
            "deviceName" -> result.success(deviceName())
            "openExternalUrl" -> {
                val url = call.argument<String>("url")
                val uri = url?.let(Uri::parse)
                if (uri == null || uri.scheme != "https") {
                    result.error("invalid_argument", "an https url is required", null)
                    return
                }
                result.success(startView(uri))
            }
            "openStoreListing" -> {
                val market = Uri.parse("market://details?id=${activity.packageName}")
                val web = Uri.parse("https://play.google.com/store/apps/details?id=${activity.packageName}")
                result.success(startView(market) || startView(web))
            }
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

    private fun startView(uri: Uri): Boolean = try {
        activity.startActivity(Intent(Intent.ACTION_VIEW, uri).addFlags(Intent.FLAG_ACTIVITY_NEW_TASK))
        true
    } catch (_: ActivityNotFoundException) {
        false
    }

    private fun deviceName(): String {
        val manufacturer = Build.MANUFACTURER.orEmpty().trim()
        val model = Build.MODEL.orEmpty().trim()
        val name = if (model.startsWith(manufacturer, ignoreCase = true)) model else "$manufacturer $model"
        return name.trim().ifEmpty { "Android" }.take(100)
    }

    /** Test-keys builds, an `su` binary or a Magisk install. A signal only: root hiding defeats it. */
    private fun looksRooted(): Boolean {
        if (Build.TAGS?.contains("test-keys") == true) {
            return true
        }
        return ROOT_PATHS.any { path ->
            try {
                File(path).exists()
            } catch (_: SecurityException) {
                false
            }
        }
    }

    private fun clipboard(): ClipboardManager =
        activity.getSystemService(Context.CLIPBOARD_SERVICE) as ClipboardManager

    companion object {
        const val NAME = "shop.boxcards.app/security"
        private const val CLIP_LABEL = "boxcards.sensitive"
        private const val LEGACY_SENSITIVE_EXTRA = "android.content.extra.IS_SENSITIVE"
        private val ROOT_PATHS = listOf(
            "/system/bin/su",
            "/system/xbin/su",
            "/sbin/su",
            "/su/bin/su",
            "/system/app/Superuser.apk",
            "/data/local/xbin/su",
            "/data/local/bin/su",
            "/system/sd/xbin/su",
            "/data/adb/magisk",
            "/sbin/.magisk",
        )
    }
}
