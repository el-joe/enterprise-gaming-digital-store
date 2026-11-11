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
/// answers `deviceIntegrity`, `deviceName` and `openExternalUrl` (M-A10). Screen and clipboard
/// protection on iOS arrive with M-A14; those methods are not implemented here, and the Dart side
/// never calls them on iOS. Kept in this file so no Xcode project change is needed.
final class SecurityChannel {
  static let name = "shop.boxcards.app/security"

  static func register(messenger: FlutterBinaryMessenger) {
    let channel = FlutterMethodChannel(name: name, binaryMessenger: messenger)
    channel.setMethodCallHandler { call, result in
      switch call.method {
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
