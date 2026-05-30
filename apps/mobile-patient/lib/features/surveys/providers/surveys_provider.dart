import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/api/api_client.dart';
import '../data/surveys_repository.dart';
import '../models/survey.dart';

final surveysRepositoryProvider = Provider<SurveysRepository>(
  (ref) => SurveysRepository(ref.watch(apiClientProvider)),
);

final surveysListProvider = FutureProvider<List<Survey>>((ref) =>
    ref.watch(surveysRepositoryProvider).fetchAll());

final surveyDetailProvider =
    FutureProvider.family<Survey, String>((ref, id) =>
        ref.watch(surveysRepositoryProvider).fetchOne(id));
