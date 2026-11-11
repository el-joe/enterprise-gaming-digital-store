import Flutter
import UIKit

@main
@objc class AppDelegate: FlutterAppDelegate, FlutterImplicitEngineDelegate {
  override func application(
    _ application: UIApplication,
    didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
  ) -> Bool {
    return super.application(application, didFinishLaunchingWithOptions: launchOptions)
  }

  func didInitializeImplicitFlutterEngine(_ engineBridge: FlutterImplicitEngineBridge) {
    GeneratedPluginRegistrant.register(with: engineBridge.pluginRegistry)
    if let registrar = engineBridge.pluginRegistry.registrar(forPlugin: "BoxcardsSecurityChannel") {
      SecurityChannel.register(messenger: registrar.messenger())
    }
  }
}

/// The app's own channel `shop.boxcards.app/security` (Android: SecurityChannel.kt). On iOS it
/// answers `deviceIntegrity`, `deviceName` and `openExternalUrl` (M-A10) and, for code screens
/// (M-A14, owner decision 7):
/// - `setSecure(enabled)`: while on, a privacy overlay covers every window when the app resigns
///   active, so the app-switcher snapshot never shows a code (iOS cannot block screenshots);
/// - `isCaptured()` and the event channel `shop.boxcards.app/security/capture`: screen recording
///   and mirroring (`UIScreen.capturedDidChangeNotification`), so Dart hides the code meanwhile.
/// The clipboard on iOS is plain `Clipboard.setData` from Dart (no method here).
/// Kept in this file so no Xcode project change is needed.
final class SecurityChannel {
  static let name = "shop.boxcards.app/security"
  static let captureName = "shop.boxcards.app/security/capture"

  static func register(messenger: FlutterBinaryMessenger) {
    let channel = FlutterMethodChannel(name: name, binaryMessenger: messenger)
    let capture = FlutterEventChannel(name: captureName, binaryMessenger: messenger)
    capture.setStreamHandler(CaptureStreamHandler.shared)
    PrivacyOverlay.shared.observe()
    channel.setMethodCallHandler { call, result in
      switch call.method {
      case "setSecure":
        let enabled = (call.arguments as? [String: Any])?["enabled"] as? Bool ?? false
        PrivacyOverlay.shared.enabled = enabled
        if !enabled {
          PrivacyOverlay.shared.hide()
        }
        result(true)
      case "isCaptured":
        result(CaptureStreamHandler.isCaptured)
      case "deviceIntegrity":
        result(looksJailbroken() ? "compromised" : "ok")
      case "deviceName":
        let model = UIDevice.current.model
        result(model.isEmpty ? "iPhone" : String(model.prefix(100)))
      case "openExternalUrl":
        guard
          let arguments = call.arguments as? [String: Any],
          let raw = arguments["url"] as? String,
          let url = URL(string: raw),
          url.scheme == "https"
        else {
          result(FlutterError(code: "invalid_argument", message: "an https url is required", details: nil))
          return
        }
        UIApplication.shared.open(url, options: [:]) { opened in result(opened) }
      case "openStoreListing":
        // No App Store id exists yet (roadmap open decision 2); the Dart side shows the notice only.
        result(false)
      default:
        result(FlutterMethodNotImplemented)
      }
    }
  }

  /// Minimal jailbreak signal (owner decision 5: warn, never block). Bypassable by design.
  private static func looksJailbroken() -> Bool {
    #if targetEnvironment(simulator)
      return false
    #else
      let paths = [
        "/Applications/Cydia.app",
        "/Applications/Sileo.app",
        "/Library/MobileSubstrate/MobileSubstrate.dylib",
        "/bin/bash",
        "/usr/sbin/sshd",
        "/etc/apt",
        "/private/var/lib/apt/",
        "/var/jb",
      ]
      if paths.contains(where: { FileManager.default.fileExists(atPath: $0) }) {
        return true
      }
      let probe = "/private/boxcards_jb_probe.txt"
      do {
        try "probe".write(toFile: probe, atomically: true, encoding: .utf8)
        try? FileManager.default.removeItem(atPath: probe)
        return true
      } catch {
        return false
      }
    #endif
  }
}

/// Streams `true` / `false` when screen recording or mirroring starts or stops.
final class CaptureStreamHandler: NSObject, FlutterStreamHandler {
  static let shared = CaptureStreamHandler()
  private var observer: NSObjectProtocol?

  static var isCaptured: Bool {
    let scenes = UIApplication.shared.connectedScenes.compactMap { $0 as? UIWindowScene }
    if scenes.isEmpty {
      return UIScreen.main.isCaptured
    }
    return scenes.contains { $0.screen.isCaptured }
  }

  func onListen(withArguments arguments: Any?, eventSink events: @escaping FlutterEventSink)
    -> FlutterError?
  {
    events(CaptureStreamHandler.isCaptured)
    observer = NotificationCenter.default.addObserver(
      forName: UIScreen.capturedDidChangeNotification, object: nil, queue: .main
    ) { _ in
      events(CaptureStreamHandler.isCaptured)
    }
    return nil
  }

  func onCancel(withArguments arguments: Any?) -> FlutterError? {
    if let observer = observer {
      NotificationCenter.default.removeObserver(observer)
    }
    observer = nil
    return nil
  }
}

/// Covers the windows with a blur while the app is inactive or in the background, but only while
/// a code screen has asked for it (`setSecure(true)`). The system takes the app-switcher snapshot
/// after `willResignActive`, before Flutter could draw a frame, so this is native.
final class PrivacyOverlay {
  static let shared = PrivacyOverlay()
  var enabled = false
  private var views: [UIView] = []
  private var observing = false

  func observe() {
    if observing {
      return
    }
    observing = true
    let center = NotificationCenter.default
    for name in [UIApplication.willResignActiveNotification, UIScene.willDeactivateNotification] {
      center.addObserver(forName: name, object: nil, queue: .main) { [weak self] _ in
        self?.show()
      }
    }
    for name in [UIApplication.didBecomeActiveNotification, UIScene.didActivateNotification] {
      center.addObserver(forName: name, object: nil, queue: .main) { [weak self] _ in
        self?.hide()
      }
    }
  }

  func show() {
    guard enabled, views.isEmpty else {
      return
    }
    let windows = UIApplication.shared.connectedScenes
      .compactMap { $0 as? UIWindowScene }
      .flatMap { $0.windows }
    for window in windows {
      let blur = UIVisualEffectView(effect: UIBlurEffect(style: .dark))
      blur.frame = window.bounds
      blur.autoresizingMask = [.flexibleWidth, .flexibleHeight]
      blur.isAccessibilityElement = false
      window.addSubview(blur)
      views.append(blur)
    }
  }

  func hide() {
    views.forEach { $0.removeFromSuperview() }
    views.removeAll()
  }
}
