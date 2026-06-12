import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

/// Forced-update gate for the OpesCare patient app.
///
/// WHY: a health app holding PHI must be able to push users off a build that has
/// a critical bug or security fix. This client asks the backend for the minimum
/// supported build number and, if the running build is older, shows a blocking
/// screen that the user cannot dismiss until they update.
///
/// BACKEND CONTRACT (must be implemented server-side before this does anything):
///   GET {baseUrl}/mobile/app-config
///   200 -> { "min_supported_build": 7, "latest_version": "1.2.0", "store_url": "https://..." }
///
/// FAIL-OPEN: any network/parse error returns [ForceUpdateStatus.ok] so a backend
/// outage can never lock users out of the app.
///
/// WIRING (do this after `flutter pub get` + a successful build):
///   1. Bump [currentBuildNumber] to match `version: x.y.z+<build>` in pubspec on each release.
///   2. In app.dart, wrap the app body with [ForceUpdateGate], e.g.
///        ForceUpdateGate(baseUrl: ApiConfig.baseUrl, child: MaterialApp.router(...));
///      or run [ForceUpdateService.check] at startup and route to [ForceUpdateScreen]
///      when it returns [ForceUpdateStatus.required].
class ForceUpdateService {
  /// The build number of THIS release. Keep in sync with pubspec `version: +<build>`.
  static const int currentBuildNumber = 1;

  final Dio _dio;
  ForceUpdateService({Dio? dio}) : _dio = dio ?? Dio();

  Future<ForceUpdateResult> check(String baseUrl) async {
    try {
      final res = await _dio.get(
        '$baseUrl/mobile/app-config',
        options: Options(receiveTimeout: const Duration(seconds: 6)),
      );
      final data = res.data;
      if (data is! Map) return const ForceUpdateResult(ForceUpdateStatus.ok);
      final minBuild = (data['min_supported_build'] as num?)?.toInt() ?? 0;
      final storeUrl = data['store_url']?.toString();
      if (currentBuildNumber < minBuild) {
        return ForceUpdateResult(ForceUpdateStatus.required, storeUrl: storeUrl);
      }
      return const ForceUpdateResult(ForceUpdateStatus.ok);
    } catch (_) {
      // Fail-open: never lock users out due to a backend/network problem.
      return const ForceUpdateResult(ForceUpdateStatus.ok);
    }
  }
}

enum ForceUpdateStatus { ok, required }

class ForceUpdateResult {
  final ForceUpdateStatus status;
  final String? storeUrl;
  const ForceUpdateResult(this.status, {this.storeUrl});
}

/// Blocking, non-dismissible screen shown when an update is mandatory.
class ForceUpdateScreen extends StatelessWidget {
  final String? storeUrl;
  const ForceUpdateScreen({super.key, this.storeUrl});

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      child: Scaffold(
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.system_update, size: 64),
                const SizedBox(height: 16),
                Text(
                  'Update required',
                  style: Theme.of(context).textTheme.headlineSmall,
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 12),
                const Text(
                  'A newer version of OpesCare is required to continue. '
                  'Please update to keep your data secure.',
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 24),
                FilledButton(
                  onPressed: storeUrl == null
                      ? null
                      : () => launchUrl(
                            Uri.parse(storeUrl!),
                            mode: LaunchMode.externalApplication,
                          ),
                  child: const Text('Update now'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// Optional convenience wrapper. Place around the app body once wired:
///   ForceUpdateGate(baseUrl: ApiConfig.baseUrl, child: <app>)
class ForceUpdateGate extends StatefulWidget {
  final String baseUrl;
  final Widget child;
  const ForceUpdateGate({super.key, required this.baseUrl, required this.child});

  @override
  State<ForceUpdateGate> createState() => _ForceUpdateGateState();
}

class _ForceUpdateGateState extends State<ForceUpdateGate> {
  late final Future<ForceUpdateResult> _future =
      ForceUpdateService().check(widget.baseUrl);

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<ForceUpdateResult>(
      future: _future,
      builder: (context, snap) {
        if (snap.hasData && snap.data!.status == ForceUpdateStatus.required) {
          return ForceUpdateScreen(storeUrl: snap.data!.storeUrl);
        }
        // While loading or on fail-open, show the app normally.
        return widget.child;
      },
    );
  }
}
