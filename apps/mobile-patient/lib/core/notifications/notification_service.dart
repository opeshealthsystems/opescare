import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

/// Singleton that owns all FCM + local notification setup.
/// Call [init] once from main(), before runApp.
class NotificationService {
  NotificationService._();
  static final instance = NotificationService._();

  final _fcm = FirebaseMessaging.instance;
  final _localNotifications = FlutterLocalNotificationsPlugin();

  static const _androidChannel = AndroidNotificationChannel(
    'opescare_default',
    'OpesCare Alerts',
    description: 'Lab results, consent requests, appointment reminders',
    importance: Importance.high,
  );

  /// Pending deep-link route set when a notification is tapped while the app
  /// is in background/terminated. Consumed once in app.dart after router builds.
  String? pendingRoute;

  void Function(String)? _onRoute;

  /// Call after router is ready to route live notification taps.
  void setRouteHandler(void Function(String route) handler) {
    _onRoute = handler;
  }

  Future<void> init() async {
    // Request iOS/Android 13+ permission
    await _fcm.requestPermission(
      alert: true, badge: true, sound: true,
    );

    // Create Android notification channel (required Android 8+)
    await _localNotifications
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(_androidChannel);

    // Initialize flutter_local_notifications
    const initSettings = InitializationSettings(
      android: AndroidInitializationSettings('@mipmap/ic_launcher'),
      iOS: DarwinInitializationSettings(),
    );
    await _localNotifications.initialize(initSettings);

    // Foreground: show a local notification
    FirebaseMessaging.onMessage.listen(_showForegroundNotification);

    // Background tap: route or store pending
    FirebaseMessaging.onMessageOpenedApp.listen((msg) {
      final route = _routeFromMessage(msg);
      if (route == null) return;
      if (_onRoute != null) {
        _onRoute!(route);
      } else {
        pendingRoute = route;
      }
    });

    // Terminated tap: store pending route
    final initial = await _fcm.getInitialMessage();
    if (initial != null) {
      pendingRoute = _routeFromMessage(initial);
    }
  }

  /// Returns the FCM token, or null if unavailable (simulator / no permission).
  Future<String?> getToken() async {
    try {
      // iOS: need APNS token first
      final apns = await _fcm.getAPNSToken().timeout(
        const Duration(seconds: 5),
        onTimeout: () => null,
      );
      if (apns == null) {
        // On iOS simulator APNS is unavailable — skip gracefully
        return null;
      }
    } catch (_) {
      // Non-iOS platforms don't have APNS — continue
    }
    return _fcm.getToken();
  }

  void _showForegroundNotification(RemoteMessage message) {
    final notification = message.notification;
    if (notification == null) return;
    _localNotifications.show(
      notification.hashCode,
      notification.title,
      notification.body,
      NotificationDetails(
        android: AndroidNotificationDetails(
          _androidChannel.id,
          _androidChannel.name,
          channelDescription: _androidChannel.description,
          importance: Importance.high,
          priority: Priority.high,
          icon: '@mipmap/ic_launcher',
        ),
        iOS: const DarwinNotificationDetails(),
      ),
    );
  }

  String? _routeFromMessage(RemoteMessage message) {
    final data = message.data;
    final type = data['type'] as String?;
    final id   = data['id']   as String?;
    return switch (type) {
      'lab_result'  => id != null ? '/labs/$id' : '/labs',
      'consent'     => '/consent',
      'appointment' => id != null ? '/appointments/$id' : '/appointments',
      'document'    => '/documents',
      _             => null,
    };
  }
}
