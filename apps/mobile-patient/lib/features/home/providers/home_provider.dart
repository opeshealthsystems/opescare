import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/home_repository.dart';
import '../models/dashboard_summary.dart';

final homeRepositoryProvider = Provider<HomeRepository>(
  (ref) => HomeRepository(ref.watch(apiClientProvider)),
);

final dashboardSummaryProvider = FutureProvider<DashboardSummary>((ref) {
  return ref.watch(homeRepositoryProvider).fetchSummary();
});
