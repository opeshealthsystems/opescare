import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../models/patient_profile.dart';
import '../providers/profile_provider.dart';

class EditProfileScreen extends ConsumerStatefulWidget {
  const EditProfileScreen({super.key, required this.profile});
  final PatientProfile profile;

  @override
  ConsumerState<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends ConsumerState<EditProfileScreen> {
  late final TextEditingController _firstName;
  late final TextEditingController _lastName;
  String? _bloodGroup;
  String? _sex;
  bool _saving = false;
  String? _error;

  static const _bloodGroups = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
  static const _sexOptions = ['male','female','other'];

  @override
  void initState() {
    super.initState();
    _firstName  = TextEditingController(text: widget.profile.firstName);
    _lastName   = TextEditingController(text: widget.profile.lastName);
    _bloodGroup = widget.profile.bloodGroup;
    _sex        = widget.profile.sex;
  }

  @override
  void dispose() {
    _firstName.dispose();
    _lastName.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Edit Profile'),
        actions: [
          if (_saving)
            const Padding(
              padding: EdgeInsets.all(16),
              child: SizedBox(width: 20, height: 20,
                  child: CircularProgressIndicator(strokeWidth: 2)),
            )
          else
            TextButton(
              onPressed: _save,
              child: Text('Save',
                  style: AppTextStyles.body.copyWith(
                      color: AppColors.primary500, fontWeight: FontWeight.w600)),
            ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (_error != null) ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.dangerLight,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(_error!, style: AppTextStyles.bodySm.copyWith(color: AppColors.danger)),
            ),
            const SizedBox(height: 16),
          ],

          _field('First Name', _firstName),
          const SizedBox(height: 12),
          _field('Last Name', _lastName),
          const SizedBox(height: 20),

          Text('BLOOD GROUP', style: AppTextStyles.label),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8, runSpacing: 8,
            children: _bloodGroups.map((bg) {
              final selected = _bloodGroup == bg;
              return GestureDetector(
                onTap: () => setState(() => _bloodGroup = bg),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  decoration: BoxDecoration(
                    color: selected ? AppColors.primary500 : AppColors.surface,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: selected ? AppColors.primary500 : AppColors.divider,
                    ),
                  ),
                  child: Text(
                    bg,
                    style: AppTextStyles.body.copyWith(
                      color: selected ? Colors.white : AppColors.textPrimary,
                      fontWeight: selected ? FontWeight.w600 : FontWeight.w400,
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 20),

          Text('SEX', style: AppTextStyles.label),
          const SizedBox(height: 8),
          RadioGroup<String>(
            groupValue: _sex,
            onChanged: (v) => setState(() => _sex = v),
            child: Column(
              children: _sexOptions.map((s) => RadioListTile<String>(
                value: s,
                title: Text(s[0].toUpperCase() + s.substring(1),
                    style: AppTextStyles.body),
                fillColor: WidgetStateProperty.resolveWith(
                  (states) => states.contains(WidgetState.selected)
                      ? AppColors.primary500
                      : AppColors.neutral400,
                ),
                contentPadding: EdgeInsets.zero,
              )).toList(),
            ),
          ),
          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _field(String label, TextEditingController ctrl) {
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Text(label.toUpperCase(), style: AppTextStyles.label),
      const SizedBox(height: 6),
      TextFormField(
        controller: ctrl,
        style: AppTextStyles.body,
        decoration: const InputDecoration(),
      ),
    ]);
  }

  Future<void> _save() async {
    setState(() { _saving = true; _error = null; });
    try {
      await ref.read(profileRepositoryProvider).updateProfile({
        'first_name':  _firstName.text.trim(),
        'last_name':   _lastName.text.trim(),
        if (_bloodGroup != null) 'blood_group': _bloodGroup,
        if (_sex != null) 'sex': _sex,
      });
      ref.invalidate(profileProvider);
      if (mounted) Navigator.pop(context);
    } catch (e) {
      setState(() {
        _saving = false;
        _error = e.toString();
      });
    }
  }
}
