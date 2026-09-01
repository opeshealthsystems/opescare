# OpesCare Patient App — Store Submission Checklist

**App:** `apps/mobile-expo` — Expo / React Native, built and submitted through **EAS**.
Release targets are **Android (Play Store)** and **iOS (App Store)**; web is not in scope.

> Config lives in two files: `apps/mobile-expo/app.json` (identity, version, icons,
> permissions, plugins) and `apps/mobile-expo/eas.json` (build profiles + env).

## Pre-flight (all platforms)
- [ ] Privacy policy URL is live at a publicly accessible URL
- [ ] `app.json` → `expo.version` is correct for this release, and `ios.buildNumber` /
      `android.versionCode` are right (the `production` EAS profile has
      `autoIncrement: true`, so it bumps the build number for you)
- [ ] `production` profile points at the right API — `EXPO_PUBLIC_API_BASE_URL`
      in `eas.json` (currently `https://opescare.cloud/api`)
- [ ] No hardcoded dev credentials in source; no `local-api` LAN address left in a
      shipping profile
- [ ] `npx expo-doctor` and `npm audit` are clean
- [ ] `npx tsc --noEmit` passes
- [ ] Push notification received end-to-end on a physical device
- [ ] App tested on a physical Android device (real device, not just emulator)
- [ ] App tested on a physical iPhone (or TestFlight)
- [ ] Icons present and correct in `app.json`: `icon`, `android.adaptiveIcon`
      (foreground / background / monochrome)
- [ ] EN/FR strings complete — no untranslated screens

---

## Android — Play Store

### Build
```powershell
cd apps/mobile-expo
eas build --platform android --profile production
# AAB is produced by EAS; download it from the build page or use `eas submit`
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
  - Location (coarse + fine) — used by the Care Map to find nearby facilities
  - App activity — crash logs
  - Device or other IDs — push token for notifications

### Play Console — Release
- [ ] `eas submit --platform android --profile production`, or upload the AAB to
      Internal Testing → promote to Production
- [ ] `versionCode` incremented for each upload

---

## iOS — App Store

### Build
```powershell
cd apps/mobile-expo
eas build --platform ios --profile production
eas submit --platform ios --profile production
```
Bundle identifier is `com.opescare.patient` (`app.json` → `ios.bundleIdentifier`).
EAS handles signing; no local macOS/Xcode toolchain is required for the build.

### App Store Connect — Required assets
- [ ] App icon: 1024×1024 PNG
- [ ] iPhone 6.5" screenshots: 1284×2778 (minimum 1)
- [ ] iPhone 5.5" screenshots: 1242×2208 (minimum 1)
- [ ] Privacy policy URL
- [ ] App description + keywords
- [ ] Privacy nutrition labels match the Play data-safety answers above

### App Store Connect — Review notes
- [ ] Provide reviewer test account credentials (create a test patient in the backend)
      Example: "Login requires an existing patient account. Use: test@opescare.com / TestPass123"
- [ ] Note: health data displayed is read-only from the provider-managed backend
- [ ] Justify the location permission (Care Map: nearby pharmacies and facilities) —
      the prompt string is in `app.json` under the `expo-location` plugin

---

## Android APK — Sideload / internal testing

```powershell
cd apps/mobile-expo
eas build --platform android --profile preview   # internal-distribution APK
```

- [ ] Recipients need to enable "Install unknown apps" on their device
- [ ] Use the `local-api` profile only for testing against a dev machine — never
      distribute a build made with it

---

## Manual steps required before any release

These need human action and cannot be automated:

1. **Push credentials** — create the Firebase project (Android FCM) and the APNs key
   (iOS), then register them with EAS (`eas credentials`). Verify a real push arrives
   on a production-profile build on both platforms. *(Tracked as GAP-002.)*

2. **Store accounts** — Play Console developer account and Apple Developer Program
   membership, both with the OpesCare organisation, plus App Store Connect API key
   for `eas submit`.

3. **Signing** — let EAS manage the Android keystore and iOS certificates, or upload
   existing ones with `eas credentials`. Never commit a keystore to the repo.

4. **Store listing copy** — EN and FR descriptions, screenshots taken from a real
   device against production data-safe content (no real patient data in screenshots).

5. **Privacy policy + terms** — published and linked from both stores and from the
   app's Help / Settings screens.
