import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../../health_id/providers/health_id_provider.dart';
import '../models/family_member.dart';
import '../providers/family_provider.dart';
import 'add_family_member_screen.dart';
import 'invite_family_member_screen.dart';

class FamilyScreen extends ConsumerWidget {
  const FamilyScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final membersAsync = ref.watch(familyMembersProvider);
    final invitesAsync = ref.watch(familyInvitationsProvider);
    final cardAsync    = ref.watch(healthIdCardProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('My Family', style: AppTextStyles.h4),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 12),
            child: GestureDetector(
              onTap: () => _showAddSheet(context),
              child: Container(
                width: 36, height: 36,
                decoration: BoxDecoration(
                  color: AppColors.primary50,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(LucideIcons.userPlus,
                    size: 18, color: AppColors.primary500),
              ),
            ),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(familyMembersProvider);
          ref.invalidate(familyInvitationsProvider);
        },
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // ── Primary Patient (self) ────────────────────────────────────
            Text('PRIMARY ACCOUNT',
                style: AppTextStyles.label.copyWith(
                  color: AppColors.textMuted,
                  letterSpacing: 0.8,
                )),
            const SizedBox(height: 8),
            cardAsync.when(
              loading: () => const LoadingSkeleton(height: 72, borderRadius: 14),
              error: (_, __) => const SizedBox.shrink(),
              data: (card) => Container(
                padding: const EdgeInsets.symmetric(
                    horizontal: 16, vertical: 14),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [AppColors.cardGradientStart, AppColors.cardGradientEnd],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(14),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.primary500.withValues(alpha: 0.3),
                      blurRadius: 14,
                      offset: const Offset(0, 5),
                    ),
                  ],
                ),
                child: Row(children: [
                  Container(
                    width: 40, height: 40,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      shape: BoxShape.circle,
                    ),
                    child: Center(
                      child: Text(
                        card.displayName.isNotEmpty
                            ? card.displayName[0].toUpperCase()
                            : 'P',
                        style: AppTextStyles.h3.copyWith(color: Colors.white),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                      Text(card.displayName,
                          style: AppTextStyles.body.copyWith(
                              color: Colors.white, fontWeight: FontWeight.w700)),
                      const SizedBox(height: 2),
                      Text(card.healthId,
                          style: AppTextStyles.monoXs.copyWith(
                              color: Colors.white60, letterSpacing: 0.08)),
                    ]),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 9, vertical: 3),
                    decoration: BoxDecoration(
                      color: const Color(0x3310B981),
                      borderRadius: BorderRadius.circular(999),
                      border: Border.all(
                          color: const Color(0x5910B981)),
                    ),
                    child: Text('You',
                        style: AppTextStyles.caption.copyWith(
                          color: const Color(0xFF6EE7B7),
                          fontWeight: FontWeight.w700,
                        )),
                  ),
                ]),
              ),
            ),
            const SizedBox(height: 20),

            // ── Family Members ────────────────────────────────────────────
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              Text('FAMILY MEMBERS',
                  style: AppTextStyles.label.copyWith(
                    color: AppColors.textMuted, letterSpacing: 0.8)),
              GestureDetector(
                onTap: () {},
                child: Text('Manage',
                    style: AppTextStyles.bodySm.copyWith(
                      color: AppColors.primary500, fontWeight: FontWeight.w600)),
              ),
            ]),
            const SizedBox(height: 8),
            membersAsync.when(
              loading: () => Column(
                mainAxisSize: MainAxisSize.min,
                children: const [
                  LoadingSkeleton(height: 90, borderRadius: 12),
                  SizedBox(height: 8),
                  LoadingSkeleton(height: 90, borderRadius: 12),
                ],
              ),
              error: (e, _) => ErrorView(
                message: e.toString(),
                onRetry: () => ref.invalidate(familyMembersProvider),
              ),
              data: (members) => members.isEmpty
                  ? _EmptyMembers(onAdd: () => _showAddSheet(context))
                  : Column(
                      mainAxisSize: MainAxisSize.min,
                      children: members
                          .map((m) => Padding(
                                padding: const EdgeInsets.only(bottom: 8),
                                child: _MemberCard(
                                  member: m,
                                  onTap: () {},
                                ),
                              ))
                          .toList(),
                    ),
            ),

            // ── Pending Invitations ───────────────────────────────────────
            invitesAsync.when(
              loading: () => const SizedBox.shrink(),
              error: (_, __) => const SizedBox.shrink(),
              data: (invites) {
                if (invites.isEmpty) return const SizedBox.shrink();
                return Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SizedBox(height: 4),
                    ...invites.map((inv) => Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: _PendingInviteCard(invitation: inv),
                        )),
                  ],
                );
              },
            ),

            const SizedBox(height: 8),
            // ── Add / Invite CTA ──────────────────────────────────────────
            GestureDetector(
              onTap: () => _showAddSheet(context),
              child: Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppColors.primary50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                      color: AppColors.primary200,
                      style: BorderStyle.solid),
                ),
                child: Row(children: [
                  Container(
                    width: 36, height: 36,
                    decoration: BoxDecoration(
                      color: AppColors.primary100,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(LucideIcons.userPlus,
                        size: 18, color: AppColors.primary500),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                      Text('Add or Invite a Family Member',
                          style: AppTextStyles.body.copyWith(
                            fontWeight: FontWeight.w700,
                            color: AppColors.primary500,
                          )),
                      const SizedBox(height: 2),
                      Text('Register a dependent or send an invite link',
                          style: AppTextStyles.bodySm.copyWith(
                              color: AppColors.primary400)),
                    ]),
                  ),
                  const Icon(LucideIcons.chevronRight,
                      size: 16, color: AppColors.primary400),
                ]),
              ),
            ),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  void _showAddSheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _AddSheet(),
    );
  }
}

// ── Bottom sheet: choose add method ─────────────────────────────────────────

class _AddSheet extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(24),
      ),
      padding: const EdgeInsets.fromLTRB(24, 16, 24, 32),
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        Container(
          width: 40, height: 4,
          decoration: BoxDecoration(
            color: AppColors.divider, borderRadius: BorderRadius.circular(2)),
        ),
        const SizedBox(height: 20),
        Text('Add a Family Member', style: AppTextStyles.h3),
        const SizedBox(height: 6),
        Text('Register a new dependent or invite an existing OpesCare user.',
            style: AppTextStyles.bodySm, textAlign: TextAlign.center),
        const SizedBox(height: 24),
        _SheetOption(
          icon: LucideIcons.userPlus,
          iconBg: AppColors.primary50,
          iconColor: AppColors.primary500,
          title: 'Add New Member',
          subtitle: 'Register a child or dependent with a new Health ID',
          onTap: () {
            Navigator.pop(context);
            Navigator.push(context, MaterialPageRoute(
                builder: (_) => const AddFamilyMemberScreen()));
          },
        ),
        const SizedBox(height: 10),
        _SheetOption(
          icon: LucideIcons.send,
          iconBg: AppColors.successLight,
          iconColor: AppColors.success,
          title: 'Invite Family Member',
          subtitle: 'Send a link to someone with an existing OpesCare account',
          onTap: () {
            Navigator.pop(context);
            Navigator.push(context, MaterialPageRoute(
                builder: (_) => const InviteFamilyMemberScreen()));
          },
        ),
        const SizedBox(height: 10),
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: Text('Cancel',
              style: AppTextStyles.body.copyWith(color: AppColors.textSecondary)),
        ),
      ]),
    );
  }
}

class _SheetOption extends StatelessWidget {
  const _SheetOption({
    required this.icon,
    required this.iconBg,
    required this.iconColor,
    required this.title,
    required this.subtitle,
    required this.onTap,
  });
  final IconData icon;
  final Color iconBg, iconColor;
  final String title, subtitle;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.divider),
        ),
        child: Row(children: [
          Container(
            width: 44, height: 44,
            decoration: BoxDecoration(color: iconBg, borderRadius: BorderRadius.circular(12)),
            child: Icon(icon, size: 20, color: iconColor),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(title, style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w700)),
              const SizedBox(height: 2),
              Text(subtitle, style: AppTextStyles.bodySm),
            ]),
          ),
          const Icon(LucideIcons.chevronRight, size: 16, color: AppColors.neutral400),
        ]),
      ),
    );
  }
}

// ── Member Card ──────────────────────────────────────────────────────────────

class _MemberCard extends StatelessWidget {
  const _MemberCard({required this.member, required this.onTap});
  final FamilyMember member;
  final VoidCallback onTap;

  static const _avatarColors = [
    Color(0xFFFEF3C7), Color(0xFFEFF6FF), Color(0xFFF0FDF4),
    Color(0xFFFEE2E2), Color(0xFFF5F3FF),
  ];
  static const _textColors = [
    Color(0xFF92400E), Color(0xFF1E40AF), Color(0xFF065F46),
    Color(0xFF991B1B), Color(0xFF4C1D95),
  ];

  @override
  Widget build(BuildContext context) {
    final idx = member.name.codeUnitAt(0) % _avatarColors.length;
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.divider),
        ),
        child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Container(
            width: 44, height: 44,
            decoration: BoxDecoration(
              color: _avatarColors[idx], shape: BoxShape.circle),
            child: Center(
              child: Text(member.initials,
                  style: AppTextStyles.body.copyWith(
                    color: _textColors[idx], fontWeight: FontWeight.w800)),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(member.name,
                  style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w700)),
              const SizedBox(height: 2),
              Text(
                [
                  _capitalize(member.relationship),
                  if (member.dateOfBirth != null) 'DOB ${member.dateOfBirth}',
                  if (member.age != null) 'Age ${member.age}',
                ].join(' · '),
                style: AppTextStyles.bodySm,
              ),
              const SizedBox(height: 6),
              Wrap(spacing: 5, runSpacing: 4, children: [
                if (member.activeRxCount > 0)
                  _Chip('${member.activeRxCount} Active Rx',
                      AppColors.warningLight, const Color(0xFF92400E)),
                if (member.upcomingAppointment != null)
                  _Chip('Appt ${member.upcomingAppointment!}',
                      AppColors.infoLight, const Color(0xFF1E40AF)),
                if (member.hasAlert)
                  _Chip(member.alertMessage ?? 'Alert',
                      AppColors.dangerLight, const Color(0xFF991B1B)),
                if (!member.hasAlert && member.activeRxCount == 0)
                  _Chip('All good', AppColors.successLight, AppColors.successDark),
              ]),
            ]),
          ),
          const Icon(LucideIcons.chevronRight, size: 16, color: AppColors.neutral400),
        ]),
      ),
    );
  }

  String _capitalize(String s) =>
      s.isEmpty ? s : '${s[0].toUpperCase()}${s.substring(1)}';
}

class _Chip extends StatelessWidget {
  const _Chip(this.label, this.bg, this.fg);
  final String label;
  final Color bg, fg;
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: bg, borderRadius: BorderRadius.circular(999)),
      child: Text(label,
          style: AppTextStyles.caption.copyWith(
            fontSize: 9, fontWeight: FontWeight.w700, color: fg)),
    );
  }
}

// ── Pending Invite Card ──────────────────────────────────────────────────────

class _PendingInviteCard extends StatelessWidget {
  const _PendingInviteCard({required this.invitation});
  final FamilyInvitation invitation;
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Row(children: [
        Container(
          width: 44, height: 44,
          decoration: BoxDecoration(
            color: AppColors.warningLight, shape: BoxShape.circle),
          child: const Icon(LucideIcons.user,
              size: 18, color: AppColors.warning),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(invitation.contact,
                style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w700)),
            const SizedBox(height: 2),
            Text('Invite pending · expires ${invitation.expiresAt}',
                style: AppTextStyles.bodySm),
          ]),
        ),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
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
    );
  }
}

// ── Empty state ──────────────────────────────────────────────────────────────

class _EmptyMembers extends StatelessWidget {
  const _EmptyMembers({required this.onAdd});
  final VoidCallback onAdd;
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        const Icon(LucideIcons.users, size: 40, color: AppColors.neutral300),
        const SizedBox(height: 12),
        Text('No family members yet',
            style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600)),
        const SizedBox(height: 4),
        Text('Add dependents or invite family members to share health access.',
            style: AppTextStyles.bodySm, textAlign: TextAlign.center),
      ]),
    );
  }
}
