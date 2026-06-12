import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/family_repository.dart';
import '../models/family_member.dart';

final familyRepositoryProvider = Provider<FamilyRepository>(
  (ref) => FamilyRepository(ref.watch(apiClientProvider)),
);

final familyMembersProvider = FutureProvider<List<FamilyMember>>(
  (ref) => ref.watch(familyRepositoryProvider).fetchMembers(),
);

final familyInvitationsProvider = FutureProvider<List<FamilyInvitation>>(
  (ref) => ref.watch(familyRepositoryProvider).fetchInvitations(),
);
