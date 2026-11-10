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

    productFlavors {
        create("dev") {
            dimension = "environment"
            applicationIdSuffix = ".dev"
            versionNameSuffix = "-dev"
            manifestPlaceholders["appLabel"] = "boxcards.shop Dev"
            // The dev backend runs on the developer machine over plain HTTP (10.0.2.2 from the emulator).
            manifestPlaceholders["usesCleartextTraffic"] = "true"
        }
        create("staging") {
            dimension = "environment"
            applicationIdSuffix = ".staging"
            versionNameSuffix = "-staging"
            manifestPlaceholders["appLabel"] = "boxcards.shop Staging"
            manifestPlaceholders["usesCleartextTraffic"] = "false"
        }
        create("prod") {
            dimension = "environment"
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
