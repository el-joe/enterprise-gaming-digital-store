import groovy.json.JsonSlurper

plugins {
    id("com.android.application")
    // The Flutter Gradle Plugin must be applied after the Android and Kotlin Gradle plugins.
    id("dev.flutter.flutter-gradle-plugin")
}

android {
    // Base id: proposal `shop.boxcards.app` (MOBILE_APP_ROADMAP.md open decision 2). It may change
    // before the first store upload; the namespace and the Kotlin package move with it.
    namespace = "shop.boxcards.app"
    compileSdk = flutter.compileSdkVersion
    ndkVersion = flutter.ndkVersion

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    defaultConfig {
        applicationId = "shop.boxcards.app"
        minSdk = flutter.minSdkVersion
        targetSdk = flutter.targetSdkVersion
        versionCode = flutter.versionCode
        versionName = flutter.versionName
    }

    flavorDimensions += "environment"

    // The verified app-link host of each flavor comes from its env file (APP_LINK_HOST in
    // lib/core/config/env_<flavor>.json), the same value the Dart side parses links with, so the
    // intent filters are never hard-coded to production.
    fun appLinkHost(flavor: String): String {
        val env = file("../../lib/core/config/env_$flavor.json")
        @Suppress("UNCHECKED_CAST")
        val values = JsonSlurper().parse(env) as Map<String, Any?>
        return values["APP_LINK_HOST"] as String
    }

    productFlavors {
        create("dev") {
            dimension = "environment"
            manifestPlaceholders["appLinkHost"] = appLinkHost("dev")
            applicationIdSuffix = ".dev"
            versionNameSuffix = "-dev"
            manifestPlaceholders["appLabel"] = "boxcards.shop Dev"
            // The dev backend runs on the developer machine over plain HTTP (10.0.2.2 from the emulator).
            manifestPlaceholders["usesCleartextTraffic"] = "true"
        }
        create("staging") {
            dimension = "environment"
            manifestPlaceholders["appLinkHost"] = appLinkHost("staging")
            applicationIdSuffix = ".staging"
            versionNameSuffix = "-staging"
            manifestPlaceholders["appLabel"] = "boxcards.shop Staging"
            manifestPlaceholders["usesCleartextTraffic"] = "false"
        }
        create("prod") {
            dimension = "environment"
            manifestPlaceholders["appLinkHost"] = appLinkHost("prod")
            manifestPlaceholders["appLabel"] = "boxcards.shop"
            manifestPlaceholders["usesCleartextTraffic"] = "false"
        }
    }

    buildTypes {
        release {
            // Release signing, R8 and symbol upload are configured in M-A20 from an untracked
            // android/key.properties. Until then a local `--release` run is signed with the debug key.
            signingConfig = signingConfigs.getByName("debug")
        }
    }
}

kotlin {
    compilerOptions {
        jvmTarget = org.jetbrains.kotlin.gradle.dsl.JvmTarget.JVM_17
    }
}

flutter {
    source = "../.."
}

// M-A18 push (owner decision 9): Firebase is configured per flavor by the owner's
// google-services.json, never committed (.gitignore). Put one file per flavor in
// app/src/<flavor>/google-services.json, or one file listing every flavor's application id in
// app/google-services.json. Without any file the plugin is not applied, the build succeeds and the
// app runs with push off (Firebase.initializeApp fails and push stays disabled). With per-flavor
// files, a flavor whose file is missing fails at processGoogleServices: build only the flavors
// that have one, or add the combined file.
val googleServicesFiles = listOf("google-services.json") +
    listOf("dev", "staging", "prod").flatMap { flavor ->
        listOf("src/$flavor/google-services.json", "src/${flavor}Debug/google-services.json", "src/${flavor}Release/google-services.json")
    }
if (googleServicesFiles.any { file(it).exists() }) {
    apply(plugin = "com.google.gms.google-services")
} else {
    logger.lifecycle("google-services.json not found: building without Firebase (push off).")
}
