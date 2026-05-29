import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/settings_repository.dart';
import '../models/app_settings.dart';

final settingsRepositoryProvider = Provider<SettingsRepository>(
  (ref) => SettingsRepository(ref.watch(apiClientProvider)),
);

class SettingsNotifier extends StateNotifier<AsyncValue<AppSettings>> {
  SettingsNotifier(this._repo) : super(const AsyncValue.loading()) {
    _load();
  }

  final SettingsRepository _repo;

  Future<void> _load() async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(_repo.fetch);
  }

  Future<void> load() => _load();

  Future<void> update(AppSettings settings) async {
    state = await AsyncValue.guard(() => _repo.update(settings));
  }
}

final settingsProvider =
    StateNotifierProvider<SettingsNotifier, AsyncValue<AppSettings>>((ref) {
  return SettingsNotifier(ref.watch(settingsRepositoryProvider));
});
