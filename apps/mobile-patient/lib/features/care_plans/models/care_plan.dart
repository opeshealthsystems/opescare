class CarePlanGoal {
  const CarePlanGoal({
    required this.id,
    required this.description,
    required this.status,
    this.achievedAt,
  });
  final String id, description, status;
  final String? achievedAt;

  factory CarePlanGoal.fromJson(Map<String, dynamic> json) => CarePlanGoal(
        id:          json['id']?.toString()          ?? '',
        description: json['description']?.toString() ?? '',
        status:      json['status']?.toString()      ?? 'pending',
        achievedAt:  json['achieved_at']?.toString(),
      );
}

class CarePlanIntervention {
  const CarePlanIntervention({
    required this.id,
    required this.description,
    required this.type,
  });
  final String id, description, type;

  factory CarePlanIntervention.fromJson(Map<String, dynamic> json) =>
      CarePlanIntervention(
        id:          json['id']?.toString()          ?? '',
        description: json['description']?.toString() ?? '',
        type:        json['type']?.toString()        ?? 'general',
      );
}

class CarePlan {
  const CarePlan({
    required this.id,
    required this.title,
    required this.status,
    required this.goals,
    required this.interventions,
    this.startedAt,
    this.progressPct,
  });
  final String id, title, status;
  final String? startedAt;
  final List<CarePlanGoal> goals;
  final List<CarePlanIntervention> interventions;
  final int? progressPct;

  factory CarePlan.fromJson(Map<String, dynamic> json) {
    final planJson = json.containsKey('plan')
        ? (json['plan'] as Map<String, dynamic>)
        : json;
    final goalsList = json['goals'] as List? ??
        planJson['goals'] as List? ?? [];
    final interventionsList = json['interventions'] as List? ??
        planJson['interventions'] as List? ?? [];

    return CarePlan(
      id:            planJson['id']?.toString()        ?? '',
      title:         planJson['title']?.toString()     ?? 'Care Plan',
      status:        planJson['status']?.toString()    ?? 'active',
      startedAt:     planJson['started_at']?.toString(),
      goals:         goalsList
          .map((g) => CarePlanGoal.fromJson(g as Map<String, dynamic>))
          .toList(),
      interventions: interventionsList
          .map((i) => CarePlanIntervention.fromJson(i as Map<String, dynamic>))
          .toList(),
      progressPct: (json['progress_pct'] as num?)?.toInt(),
    );
  }
}
