// Notification service — initialized by main.dart before runApp.
// Full implementation added in Task 10.
class NotificationService {
  NotificationService._();
  static final instance = NotificationService._();

  String? pendingRoute;
  void Function(String)? _onRoute;

  Future<void> init() async {}

  Future<String?> getToken() async => null;

  void setRouteHandler(void Function(String route) handler) {
    _onRoute = handler;
  }
}
