# Production builds for all targets
Set-Location $PSScriptRoot/..
$apiUrl = "https://api.opescare.com/api"

Write-Host "Building Android App Bundle (Play Store)..."
flutter build appbundle --release --dart-define=API_BASE_URL=$apiUrl

Write-Host "Building Android APKs (sideload, split by ABI)..."
flutter build apk --release --dart-define=API_BASE_URL=$apiUrl --split-per-abi

Write-Host "Building Web..."
flutter build web --release --dart-define=API_BASE_URL=$apiUrl

Write-Host ""
Write-Host "Outputs:"
Write-Host "  AAB:  build/app/outputs/bundle/release/app-release.aab"
Write-Host "  APKs: build/app/outputs/flutter-apk/app-*-release.apk"
Write-Host "  Web:  build/web/"
