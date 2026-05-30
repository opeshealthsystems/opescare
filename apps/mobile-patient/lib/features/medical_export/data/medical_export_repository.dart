import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';

class MedicalExportRepository {
  const MedicalExportRepository(this._client);
  final ApiClient _client;

  /// Returns the file_path and filename for the generated PDF.
  Future<Map<String, String>> exportPdf({
    bool includeVitals        = true,
    bool includeDiagnoses     = true,
    bool includeMedications   = true,
    bool includeLabs          = true,
    bool includeImmunizations = true,
  }) async {
    final res = await _client.post(ApiEndpoints.exportRecordsPdf, body: {
      'include_vitals':        includeVitals,
      'include_diagnoses':     includeDiagnoses,
      'include_medications':   includeMedications,
      'include_labs':          includeLabs,
      'include_immunizations': includeImmunizations,
    });
    return {
      'file_path': res['file_path']?.toString() ?? '',
      'filename':  res['filename']?.toString()  ?? 'medical_records.pdf',
    };
  }

  /// Returns the raw FHIR R4 Bundle as a JSON string.
  Future<String> exportFhir() async {
    final res = await _client.post(ApiEndpoints.exportRecordsFhir);
    return res.toString();
  }
}
