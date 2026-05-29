import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/documents_repository.dart';
import '../models/document.dart';

final documentsRepositoryProvider = Provider<DocumentsRepository>(
  (ref) => DocumentsRepository(ref.watch(apiClientProvider)),
);

final documentsListProvider = FutureProvider<List<PatientDocument>>((ref) {
  return ref.watch(documentsRepositoryProvider).fetchAll();
});
