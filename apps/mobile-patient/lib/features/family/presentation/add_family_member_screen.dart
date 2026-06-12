import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../providers/family_provider.dart';

class AddFamilyMemberScreen extends ConsumerStatefulWidget {
  const AddFamilyMemberScreen({super.key});

  @override
  ConsumerState<AddFamilyMemberScreen> createState() =>
      _AddFamilyMemberScreenState();
}

class _AddFamilyMemberScreenState
    extends ConsumerState<AddFamilyMemberScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _dobCtrl  = TextEditingController();
  final _phoneCtrl = TextEditingController();

  String _relationship = 'Child';
  String _sex = 'Male';
  String _bloodGroup = 'O Rh+';
  bool _linkExisting = false;
  bool _saving = false;

  static const _relationships = ['Spouse', 'Child', 'Parent', 'Sibling', 'Other'];
  static const _sexes = ['Male', 'Female'];
  static const _bloodGroups = ['A Rh+', 'A Rh-', 'B Rh+', 'B Rh-',
    'AB Rh+', 'AB Rh-', 'O Rh+', 'O Rh-'];

  @override
  void dispose() {
    _nameCtrl.dispose();
    _dobCtrl.dispose();
    _phoneCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);
    try {
      await ref.read(familyRepositoryProvider).addMember({
        'full_name':    _nameCtrl.text.trim(),
        'date_of_birth': _dobCtrl.text.trim(),
        'relationship': _relationship.toLowerCase(),
        'sex':          _sex.toLowerCase(),
        'blood_group':  _bloodGroup,
        'phone':        _phoneCtrl.text.trim(),
      });
      ref.invalidate(familyMembersProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Family member added successfully')),
        );
        Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString())),
        );
      }
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(title: const Text('Add Family Member')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // ── Toggle ──────────────────────────────────────────────────
            Container(
              decoration: BoxDecoration(
                color: AppColors.surfaceMuted,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.divider),
              ),
              padding: const EdgeInsets.all(4),
              child: Row(children: [
                _Tab('New Member', !_linkExisting,
                    () => setState(() => _linkExisting = false)),
                _Tab('Link Account', _linkExisting,
                    () => setState(() => _linkExisting = true)),
              ]),
            ),
            const SizedBox(height: 20),

            if (!_linkExisting) ...[
              // ── Full Name ──────────────────────────────────────────────
              _Field(label: 'Full Name', child: TextFormField(
                controller: _nameCtrl,
                decoration: _dec('e.g. Sophie Mbarga'),
                validator: (v) => (v == null || v.trim().isEmpty)
                    ? 'Name is required' : null,
              )),
              const SizedBox(height: 14),

              // ── DOB + Sex ──────────────────────────────────────────────
              Row(children: [
                Expanded(
                  child: _Field(label: 'Date of Birth', child: TextFormField(
                    controller: _dobCtrl,
                    keyboardType: TextInputType.datetime,
                    decoration: _dec('YYYY-MM-DD'),
                    validator: (v) => (v == null || v.trim().isEmpty)
                        ? 'Required' : null,
                  )),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _Field(label: 'Sex', child: _Dropdown(
                    value: _sex,
                    items: _sexes,
                    onChanged: (v) => setState(() => _sex = v!),
                  )),
                ),
              ]),
              const SizedBox(height: 14),

              // ── Relationship + Blood Group ─────────────────────────────
              Row(children: [
                Expanded(
                  child: _Field(label: 'Relationship', child: _Dropdown(
                    value: _relationship,
                    items: _relationships,
                    onChanged: (v) => setState(() => _relationship = v!),
                  )),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _Field(label: 'Blood Group', child: _Dropdown(
                    value: _bloodGroup,
                    items: _bloodGroups,
                    onChanged: (v) => setState(() => _bloodGroup = v!),
                  )),
                ),
              ]),
              const SizedBox(height: 14),

              // ── Emergency Phone ────────────────────────────────────────
              _Field(label: 'Emergency Contact Phone', child: TextFormField(
                controller: _phoneCtrl,
                keyboardType: TextInputType.phone,
                decoration: _dec('+237 6XX XXX XXX'),
              )),
              const SizedBox(height: 16),

              // ── Info notice ────────────────────────────────────────────
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.infoLight,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: AppColors.primary200),
                ),
                child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  const Icon(LucideIcons.info, size: 14,
                      color: AppColors.primary500),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'A Health ID will be generated for this member. You can share access with their providers.',
                      style: AppTextStyles.bodySm.copyWith(
                          color: const Color(0xFF1E40AF)),
                    ),
                  ),
                ]),
              ),
            ] else ...[
              // ── Link existing account ──────────────────────────────────
              _Field(label: 'Their Phone or Health ID', child: TextFormField(
                decoration: _dec('OPC·XXXX·XXXX·CM or +237 6XX XXX XXX'),
                validator: (v) => (v == null || v.trim().isEmpty)
                    ? 'Required' : null,
              )),
              const SizedBox(height: 14),
              _Field(label: 'Your Relationship to Them', child: _Dropdown(
                value: _relationship,
                items: _relationships,
                onChanged: (v) => setState(() => _relationship = v!),
              )),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.infoLight,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: AppColors.primary200),
                ),
                child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  const Icon(LucideIcons.info, size: 14, color: AppColors.primary500),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'They will receive a notification to accept the family link. Their data is only shared with your consent.',
                      style: AppTextStyles.bodySm.copyWith(
                          color: const Color(0xFF1E40AF)),
                    ),
                  ),
                ]),
              ),
            ],

            const SizedBox(height: 24),

            // ── Submit ──────────────────────────────────────────────────
            ElevatedButton.icon(
              onPressed: _saving ? null : _submit,
              icon: _saving
                  ? const SizedBox(
                      width: 16, height: 16,
                      child: CircularProgressIndicator(
                          strokeWidth: 2, color: Colors.white))
                  : const Icon(LucideIcons.userPlus, size: 16),
              label: Text(_linkExisting
                  ? 'Send Link Request'
                  : 'Add to My Family'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary500,
                foregroundColor: Colors.white,
                minimumSize: const Size(double.infinity, 52),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                elevation: 0,
              ),
            ),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  InputDecoration _dec(String hint) => InputDecoration(
        hintText: hint,
        hintStyle: AppTextStyles.body
            .copyWith(color: AppColors.textMuted, fontSize: 13),
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: AppColors.divider),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: AppColors.divider),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide:
              const BorderSide(color: AppColors.primary500, width: 1.5),
        ),
        filled: true,
        fillColor: AppColors.surface,
      );
}

class _Tab extends StatelessWidget {
  const _Tab(this.label, this.active, this.onTap);
  final String label;
  final bool active;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 150),
          padding: const EdgeInsets.symmetric(vertical: 9),
          decoration: BoxDecoration(
            color: active ? AppColors.surface : Colors.transparent,
            borderRadius: BorderRadius.circular(9),
            boxShadow: active
                ? [BoxShadow(
                    color: Colors.black.withValues(alpha: 0.06),
                    blurRadius: 4, offset: const Offset(0, 1))]
                : null,
          ),
          child: Text(label,
              textAlign: TextAlign.center,
              style: AppTextStyles.bodySm.copyWith(
                fontWeight: FontWeight.w700,
                color: active ? AppColors.textPrimary : AppColors.textSecondary,
              )),
        ),
      ),
    );
  }
}

class _Field extends StatelessWidget {
  const _Field({required this.label, required this.child});
  final String label;
  final Widget child;
  @override
  Widget build(BuildContext context) {
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Text(label,
          style: AppTextStyles.label.copyWith(
            color: AppColors.textSecondary, letterSpacing: 0.6)),
      const SizedBox(height: 5),
      child,
    ]);
  }
}

class _Dropdown extends StatelessWidget {
  const _Dropdown({
    required this.value,
    required this.items,
    required this.onChanged,
  });
  final String value;
  final List<String> items;
  final void Function(String?) onChanged;

  @override
  Widget build(BuildContext context) {
    return DropdownButtonFormField<String>(
      value: value,
      onChanged: onChanged,
      decoration: InputDecoration(
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: AppColors.divider),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: AppColors.divider),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide:
              const BorderSide(color: AppColors.primary500, width: 1.5),
        ),
        filled: true,
        fillColor: AppColors.surface,
      ),
      style: AppTextStyles.body.copyWith(fontSize: 13),
      items: items
          .map((s) => DropdownMenuItem(value: s, child: Text(s)))
          .toList(),
    );
  }
}
