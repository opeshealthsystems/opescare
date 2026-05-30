class SurveyQuestion {
  const SurveyQuestion({
    required this.key,
    required this.text,
    required this.type,
    this.options = const [],
  });
  final String key, text, type; // type: single_choice | multi_choice | text
  final List<String> options;

  factory SurveyQuestion.fromJson(Map<String, dynamic> json) => SurveyQuestion(
        key:     json['key']?.toString()  ?? '',
        text:    json['text']?.toString() ?? '',
        type:    json['type']?.toString() ?? 'text',
        options: (json['options'] as List? ?? [])
            .map((o) => o.toString())
            .toList(),
      );
}

class Survey {
  const Survey({
    required this.id,
    required this.title,
    required this.status,
    required this.templateKey,
    this.questions = const [],
  });
  final String id, title, status, templateKey;
  final List<SurveyQuestion> questions; // populated only in detail

  factory Survey.fromJson(Map<String, dynamic> json) {
    final template = json['template'];
    final questions = (template != null && template['questions'] is List)
        ? (template['questions'] as List)
            .map((q) => SurveyQuestion.fromJson(q as Map<String, dynamic>))
            .toList()
        : <SurveyQuestion>[];
    return Survey(
      id:          json['id']?.toString()           ?? '',
      title:       json['title']?.toString()        ?? 'Health Survey',
      status:      json['status']?.toString()       ?? 'sent',
      templateKey: json['template_key']?.toString() ?? '',
      questions:   questions,
    );
  }
}
