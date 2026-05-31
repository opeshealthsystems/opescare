import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/referrals_repository.dart';
import '../models/referral.dart';

final referralsRepositoryProvider = Provider<ReferralsRepository>(
  (ref) => ReferralsRepository(ref.watch(apiClientProvider)),
);

final referralsListProvider = FutureProvider<List<Referral>>(
  (ref) => ref.watch(referralsRepositoryProvider).fetchAll(),
);
