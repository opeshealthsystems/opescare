# Build release APK targeting staging server
# Replace the URL below with your actual staging host when available
Set-Location $PSScriptRoot/..
flutter build apk --release `
  --dart-define=API_BASE_URL=https://staging.opescare.com/api
Write-Host "Staging APK: build/app/outputs/flutter-apk/app-release.apk"
