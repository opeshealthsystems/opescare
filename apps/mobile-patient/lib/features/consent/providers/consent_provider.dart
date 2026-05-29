import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/consent_repository.dart';
import '../models/consent_request.dart';

final consentRepositoryProvider = Provider<ConsentRepository>(
  (ref) => ConsentRepository(ref.watch(apiClientProvider)),
);

final consentRequestsProvider =
    FutureProvider.family<List<ConsentRequest>, String?>((ref, status) {
  return ref.watch(consentRepositoryProvider).fetchRequests(status: status);
});

class ConsentActionNotifier
    extends StateNotifier<AsyncValue<void>> {
  ConsentActionNotifier(this._repo) : super(const AsyncValue.data(null));

  final ConsentRepository _repo;

  Future<void> approve(String id, WidgetRef ref) async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(() async {
      await _repo.approve(id);
      ref.invalidate(consentRequestsProvider);
    });
  }

  Future<void> deny(String id, WidgetRef ref) async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(() async {
      await _repo.deny(id);
      ref.invalidate(consentRequestsProvider);
    });
  }

  Future<void> revoke(String id, WidgetRef ref) async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(() async {
      await _repo.revoke(id);
      ref.invalidate(consentRequestsProvider);
    });
  }
}

final consentActionProvider =
    StateNotifierProvider<ConsentActionNotifier, AsyncValue<void>>((ref) {
  return ConsentActionNotifier(ref.watch(consentRepositoryProvider));
});
