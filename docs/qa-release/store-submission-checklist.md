# OpesCare Patient App — Store Submission Checklist

## Pre-flight (all platforms)
- [ ] Privacy policy URL is live at a publicly accessible URL
- [ ] `pubspec.yaml` version / build number is correct for this release
- [ ] No hardcoded dev credentials in source
- [ ] `debugShowCheckedModeBanner: false` (already set in app.dart)
- [ ] `flutter test` passes with no failures
- [ ] Crashlytics test crash verified in Firebase console
- [ ] Push notification received end-to-end on a physical device
- [ ] App tested on physical Android device (real device, not just emulator)
- [ ] App tested on physical iPhone (or TestFlight)
- [ ] Icon PNG placed at `assets/icon/icon.png` and icons generated (`dart run flutter_launcher_icons`)
- [ ] Splash generated (`dart run flutter_native_splash:create`)

---

## Android — Play Store

### Build
```powershell
cd apps/mobile-patient
.\scripts\build_prod.ps1
# AAB output: build/app/outputs/bundle/release/app-release.aab
```

### Play Console — Required assets
- [ ] App icon: 512×512 PNG (Store listing → Graphics)
- [ ] Feature graphic: 1024×500 PNG
- [ ] Phone screenshots: minimum 2 (1080×1920 or similar)
- [ ] Short description: ≤ 80 characters
- [ ] Full description: ≤ 4000 characters
- [ ] Content rating: complete IARC questionnaire
- [ ] Data safety section:
  - Health and fitness data (health records) — shared with healthcare providers
  - Personal info (name, email) — collected, not sold
  - App activity — crash logs sent to Firebase
  - Device or other IDs — FCM token for push notifications

### Play Console — Release
- [ ] Upload AAB to Internal Testing → promote to Production
- [ ] `versionCode` incremented for each upload

---

## iOS — App Store

### Build (requires macOS + Xcode)
1. Open `apps/mobile-patient/ios/Runner.xcworkspace` in Xcode
2. Change Bundle Identifier to `com.opescare.patient` (Runner target → General → Bundle Identifier)
3. Select scheme: Runner → Any iOS Device (arm64)
4. Product → Archive → Distribute App → App Store Connect → Upload

### App Store Connect — Required assets
- [ ] App icon: 1024×1024 PNG (set in Xcode Assets.xcassets, auto-resized)
- [ ] iPhone 6.5" screenshots: 1284×2778 (minimum 1)
- [ ] iPhone 5.5" screenshots: 1242×2208 (minimum 1)
- [ ] Privacy policy URL
- [ ] App description + keywords

### App Store Connect — Review notes
- [ ] Provide reviewer test account credentials (create a test patient in the backend)
  Example: "Login requires an existing patient account. Use: test@opescare.com / TestPass123"
- [ ] Note: Health data displayed is read-only from provider-managed backend

---

## Web — Hosted

### Build
```powershell
cd apps/mobile-patient
flutter build web --release --dart-define=API_BASE_URL=https://api.opescare.com/api
# Output: build/web/
```

### Deploy
- [ ] Copy `build/web/` to web root of `app.opescare.com`
- [ ] Verify `<base href="/">` in `web/index.html` matches deployment path
- [ ] Confirm Laravel CORS config allows `https://app.opescare.com` (already configured)
- [ ] Test on HTTPS — service workers require secure context

---

## Android APK — Sideload

### Build
```powershell
cd apps/mobile-patient
flutter build apk --release `
  --dart-define=API_BASE_URL=https://api.opescare.com/api `
  --split-per-abi
# arm64-v8a (modern): build/app/outputs/flutter-apk/app-arm64-v8a-release.apk
# armeabi-v7a (older): build/app/outputs/flutter-apk/app-armeabi-v7a-release.apk
```

- [ ] Distribute `app-arm64-v8a-release.apk` for most users
- [ ] Recipients need to enable "Install unknown apps" on their device

---

## Manual Steps Required Before Any Release (Task 8)

The following require human action and cannot be automated:

1. **Firebase project setup**
   - Go to https://console.firebase.google.com → create project `opescare-patient`
   - Enable Crashlytics, Analytics, Cloud Messaging
   - Run from `apps/mobile-patient/`:
     ```
     dart pub global activate flutterfire_cli
     flutterfire configure --project=opescare-patient
     ```
   - This generates `lib/firebase_options.dart` (replacing the stub) and places `google-services.json` + `GoogleService-Info.plist`
   - Add Google Services plugin to `android/app/build.gradle.kts`:
     ```kotlin
     id("com.google.gms.google-services")
     id("com.google.firebase.crashlytics")
     ```

2. **Android keystore** (`android/local.properties` already wired):
   ```powershell
   keytool -genkeypair -v -keystore opescare-release.jks -keyalg RSA -keysize 2048 -validity 10000 -alias opescare
   ```
   Store the JKS outside the repo. Add to `android/local.properties`:
   ```
   storeFile=<absolute-path>/opescare-release.jks
   storePassword=<password>
   keyAlias=opescare
   keyPassword=<password>
   ```

3. **App icon PNG** — Place a 1024×1024 PNG at `assets/icon/icon.png`, then:
   ```
   dart run flutter_launcher_icons
   dart run flutter_native_splash:create
   ```

4. **iOS Bundle Identifier** — Change in Xcode (Runner target → General → Bundle Identifier → `com.opescare.patient`)
