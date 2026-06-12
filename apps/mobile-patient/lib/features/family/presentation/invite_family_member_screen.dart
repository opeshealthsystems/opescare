import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../providers/family_provider.dart';

class InviteFamilyMemberScreen extends ConsumerStatefulWidget {
  const InviteFamilyMemberScreen({super.key});

  @override
  ConsumerState<InviteFamilyMemberScreen> createState() =>
      _InviteFamilyMemberScreenState();
}

class _InviteFamilyMemberScreenState
    extends ConsumerState<InviteFamilyMemberScreen> {
  final _contactCtrl = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  int _methodIndex = 0;        // 0=Phone 1=Email
  String _relationship = 'Parent';
  int _accessLevel = 0;         // 0=View Only 1=Guardian 2=Full
  bool _sending = false;

  static const _methods = ['Phone', 'Email'];
  static const _relationships = ['Spouse', 'Child', 'Parent', 'Sibling', 'Other'];
  static const _accessLabels   = ['View Only', 'Guardian', 'Full'];
  static const _accessSubs     = ['See health data', 'Manage + book', 'All access'];

  @override
  void dispose() {
    _contactCtrl.dispose();
    super.dispose();
  }

  Future<void> _send() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _sending = true);
    try {
      await ref.read(familyRepositoryProvider).sendInvitation({
        'contact':      _contactCtrl.text.trim(),
        'method':       _methods[_methodIndex].toLowerCase(),
        'relationship': _relationship.toLowerCase(),
        'access_level': _accessLabels[_accessLevel].toLowerCase().replaceAll(' ', '_'),
      });
      ref.invalidate(familyInvitationsProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Invitation sent successfully')),
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
      if (mounted) setState(() => _sending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final invitesAsync = ref.watch(familyInvitationsProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(title: const Text('Invite Family Member')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // ── Method toggle ────────────────────────────────────────────
            Container(
              decoration: BoxDecoration(
                color: AppColors.surfaceMuted,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.divider),
              ),
              padding: const EdgeInsets.all(4),
              child: Row(
                children: List.generate(_methods.length, (i) {
                  final active = i == _methodIndex;
                  return Expanded(
                    child: GestureDetector(
                      onTap: () => setState(() => _methodIndex = i),
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 150),
                        padding: const EdgeInsets.symmetric(vertical: 9),
                        decoration: BoxDecoration(
                          color: active ? AppColors.surface : Colors.transparent,
                          borderRadius: BorderRadius.circular(9),
                          boxShadow: active
                              ? [BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.06),
                                  blurRadius: 4,
                                  offset: const Offset(0, 1))]
                              : null,
                        ),
                        child: Text(_methods[i],
                            textAlign: TextAlign.center,
                            style: AppTextStyles.bodySm.copyWith(
                              fontWeight: FontWeight.w700,
                              color: active
                                  ? AppColors.textPrimary
                                  : AppColors.textSecondary,
                            )),
                      ),
                    ),
                  );
                }),
              ),
            ),
            const SizedBox(height: 16),

            // ── Contact input ─────────────────────────────────────────────
            _label(_methodIndex == 0 ? 'Phone Number' : 'Email Address'),
            const SizedBox(height: 5),
            TextFormField(
              controller: _contactCtrl,
              keyboardType: _methodIndex == 0
                  ? TextInputType.phone
                  : TextInputType.emailAddress,
              decoration: InputDecoration(
                hintText: _methodIndex == 0
                    ? '+237 6XX XXX XXX'
                    : 'name@example.com',
                hintStyle: AppTextStyles.body.copyWith(
                    color: AppColors.textMuted, fontSize: 13),
                contentPadding: const EdgeInsets.symmetric(
                    horizontal: 14, vertical: 12),
                border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                    borderSide: const BorderSide(color: AppColors.divider)),
                enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                    borderSide: const BorderSide(color: AppColors.divider)),
                focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                    borderSide: const BorderSide(
                        color: AppColors.primary500, width: 1.5)),
                filled: true,
                fillColor: AppColors.surface,
              ),
              validator: (v) => (v == null || v.trim().isEmpty)
                  ? 'Contact is required'
                  : null,
            ),
            const SizedBox(height: 14),

            // ── Relationship ──────────────────────────────────────────────
            _label('Your Relationship to Them'),
            const SizedBox(height: 5),
            DropdownButtonFormField<String>(
              value: _relationship,
              onChanged: (v) => setState(() => _relationship = v!),
              decoration: InputDecoration(
                contentPadding: const EdgeInsets.symmetric(
                    horizontal: 14, vertical: 12),
                border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                    borderSide: const BorderSide(color: AppColors.divider)),
                enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                    borderSide: const BorderSide(color: AppColors.divider)),
                focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                    borderSide: const BorderSide(
                        color: AppColors.primary500, width: 1.5)),
                filled: true,
                fillColor: AppColors.surface,
              ),
              style: AppTextStyles.body.copyWith(fontSize: 13),
              items: _relationships
                  .map((r) => DropdownMenuItem(value: r, child: Text(r)))
                  .toList(),
            ),
            const SizedBox(height: 16),

            // ── Access level ──────────────────────────────────────────────
            _label('Access Level'),
            const SizedBox(height: 8),
            Row(
              children: List.generate(3, (i) {
                final active = i == _accessLevel;
                return Expanded(
                  child: GestureDetector(
                    onTap: () => setState(() => _accessLevel = i),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 150),
                      margin: EdgeInsets.only(right: i < 2 ? 8 : 0),
                      padding: const EdgeInsets.symmetric(
                          vertical: 10, horizontal: 8),
                      decoration: BoxDecoration(
                        color: active
                            ? AppColors.primary50
                            : AppColors.surface,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(
                          color: active
                              ? AppColors.primary500
                              : AppColors.divider,
                          width: active ? 1.5 : 1,
                        ),
                      ),
                      child: Column(mainAxisSize: MainAxisSize.min, children: [
                        Text(_accessLabels[i],
                            textAlign: TextAlign.center,
                            style: AppTextStyles.bodySm.copyWith(
                              fontWeight: FontWeight.w700,
                              color: active
                                  ? AppColors.primary500
                                  : AppColors.textSecondary,
                            )),
                        const SizedBox(height: 2),
                        Text(_accessSubs[i],
                            textAlign: TextAlign.center,
                            style: AppTextStyles.caption.copyWith(
                              fontSize: 9,
                              color: active
                                  ? AppColors.primary400
                                  : AppColors.textMuted,
                            )),
                      ]),
                    ),
                  ),
                );
              }),
            ),
            const SizedBox(height: 24),

            // ── Send ──────────────────────────────────────────────────────
            ElevatedButton.icon(
              onPressed: _sending ? null : _send,
              icon: _sending
                  ? const SizedBox(
                      width: 16, height: 16,
                      child: CircularProgressIndicator(
                          strokeWidth: 2, color: Colors.white))
                  : const Icon(LucideIcons.send, size: 16),
              label: const Text('Send Invite'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary500,
                foregroundColor: Colors.white,
                minimumSize: const Size(double.infinity, 52),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                elevation: 0,
              ),
            ),
            const SizedBox(height: 24),

            // ── Pending invites ───────────────────────────────────────────
            invitesAsync.when(
              loading: () => const SizedBox.shrink(),
              error: (_, __) => const SizedBox.shrink(),
              data: (invites) {
                if (invites.isEmpty) return const SizedBox.shrink();
                return Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Pending Invites',
                        style: AppTextStyles.body
                            .copyWith(fontWeight: FontWeight.w700)),
                    const SizedBox(height: 10),
                    ...invites.map((inv) => Container(
                          margin: const EdgeInsets.only(bottom: 8),
                          padding: const EdgeInsets.symmetric(
                              horizontal: 14, vertical: 12),
                          decoration: BoxDecoration(
                            color: AppColors.surface,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: AppColors.divider),
                          ),
                          child: Row(children: [
                            Container(
                              width: 36, height: 36,
                              decoration: BoxDecoration(
                                color: AppColors.warningLight,
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Icon(LucideIcons.user,
                                  size: 16, color: AppColors.warning),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                Text(inv.contact,
                                    style: AppTextStyles.body.copyWith(
                                        fontWeight: FontWeight.w600)),
                                Text('Sent ${inv.sentAt} · expires ${inv.expiresAt}',
                                    style: AppTextStyles.bodySm),
                              ]),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 9, vertical: 3),
                              decoration: BoxDecoration(
                                color: AppColors.warningLight,
                                borderRadius: BorderRadius.circular(999),
                              ),
                              child: Text('Pending',
                                  style: AppTextStyles.caption.copyWith(
                                    fontSize: 10, fontWeight: FontWeight.w700,
                                    color: const Color(0xFF92400E))),
                            ),
                          ]),
                        )),
                  ],
                );
              },
            ),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  Widget _label(String text) => Text(text,
      style: AppTextStyles.label.copyWith(
          color: AppColors.textSecondary, letterSpacing: 0.6));
}
