# Build debug APK targeting local Laragon dev server
Set-Location $PSScriptRoot/..
flutter build apk --debug `
  --dart-define=API_BASE_URL=http://opescare.test/api
Write-Host "Dev APK: build/app/outputs/flutter-apk/app-debug.apk"
