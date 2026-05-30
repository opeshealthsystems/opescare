import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_icons/lucide_icons.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/api/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../data/medical_export_repository.dart';

final _medicalExportRepositoryProvider = Provider<MedicalExportRepository>(
  (ref) => MedicalExportRepository(ref.watch(apiClientProvider)),
);

class MedicalExportScreen extends ConsumerStatefulWidget {
  const MedicalExportScreen({super.key});

  @override
  ConsumerState<MedicalExportScreen> createState() =>
      _MedicalExportScreenState();
}

class _MedicalExportScreenState extends ConsumerState<MedicalExportScreen> {
  bool _vitals        = true;
  bool _diagnoses     = true;
  bool _medications   = true;
  bool _labs          = true;
  bool _immunizations = true;

  bool _pdfLoading  = false;
  bool _fhirLoading = false;
  String? _error;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Export Medical Records')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Info banner
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: AppColors.infoLight,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(children: [
              const Icon(LucideIcons.info, size: 16, color: AppColors.info),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Generate a copy of your health records for personal use or to share with another provider.',
                  style: AppTextStyles.bodySm,
                ),
              ),
            ]),
          ),
          const SizedBox(height: 20),

          Text('INCLUDE IN EXPORT', style: AppTextStyles.label),
          const SizedBox(height: 8),

          Container(
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.divider),
            ),
            child: Column(children: [
              _SectionSwitch('Diagnoses & Conditions', _diagnoses,
                  (v) => setState(() => _diagnoses = v)),
              const Divider(height: 1),
              _SectionSwitch('Medications & Prescriptions', _medications,
                  (v) => setState(() => _medications = v)),
              const Divider(height: 1),
              _SectionSwitch('Lab Results', _labs,
                  (v) => setState(() => _labs = v)),
              const Divider(height: 1),
              _SectionSwitch('Immunizations', _immunizations,
                  (v) => setState(() => _immunizations = v)),
              const Divider(height: 1),
              _SectionSwitch('Vitals', _vitals,
                  (v) => setState(() => _vitals = v)),
            ]),
          ),
          const SizedBox(height: 24),

          if (_error != null) ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.dangerLight,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(_error!,
                  style: AppTextStyles.bodySm
                      .copyWith(color: AppColors.danger)),
            ),
            const SizedBox(height: 16),
          ],

          ElevatedButton.icon(
            onPressed: _pdfLoading ? null : _exportPdf,
            icon: _pdfLoading
                ? const SizedBox(
                    width: 18, height: 18,
                    child: CircularProgressIndicator(
                        color: Colors.white, strokeWidth: 2))
                : const Icon(LucideIcons.fileDown, size: 18),
            label: const Text('Export as PDF'),
          ),
          const SizedBox(height: 12),

          OutlinedButton.icon(
            onPressed: _fhirLoading ? null : _exportFhir,
            icon: _fhirLoading
                ? const SizedBox(
                    width: 18, height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2))
                : const Icon(LucideIcons.code2, size: 18),
            label: const Text('Export as FHIR R4'),
          ),
          const SizedBox(height: 8),
          Text('FHIR R4 format — compatible with most health record systems.',
              style: AppTextStyles.caption,
              textAlign: TextAlign.center),
          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Future<void> _exportPdf() async {
    setState(() { _pdfLoading = true; _error = null; });
    try {
      final result = await ref.read(_medicalExportRepositoryProvider).exportPdf(
        includeVitals: _vitals,
        includeDiagnoses: _diagnoses,
        includeMedications: _medications,
        includeLabs: _labs,
        includeImmunizations: _immunizations,
      );
      final filePath = result['file_path']!;
      final uri = Uri.tryParse(filePath);
      if (uri != null && await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        setState(() => _error = 'Could not open PDF. File path: $filePath');
      }
    } catch (e) {
      setState(() => _error = 'PDF export failed. Please try again.');
    } finally {
      if (mounted) setState(() => _pdfLoading = false);
    }
  }

  Future<void> _exportFhir() async {
    setState(() { _fhirLoading = true; _error = null; });
    try {
      final json = await ref.read(_medicalExportRepositoryProvider).exportFhir();
      await Clipboard.setData(ClipboardData(text: json));
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('FHIR bundle copied to clipboard.')),
        );
      }
    } catch (e) {
      setState(() => _error = 'FHIR export failed. Please try again.');
    } finally {
      if (mounted) setState(() => _fhirLoading = false);
    }
  }
}

class _SectionSwitch extends StatelessWidget {
  const _SectionSwitch(this.label, this.value, this.onChanged);
  final String label;
  final bool value;
  final ValueChanged<bool> onChanged;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
      child: Row(children: [
        Expanded(child: Text(label, style: AppTextStyles.body)),
        Switch(value: value, onChanged: onChanged,
            activeThumbColor: AppColors.primary500),
      ]),
    );
  }
}
