import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../auth/providers/auth_provider.dart';
import '../models/app_settings.dart';
import '../providers/settings_provider.dart';

class SettingsScreen extends ConsumerWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final settingsAsync = ref.watch(settingsProvider);
    return Scaffold(
      appBar: AppBar(title: const Text('Settings')),
      body: settingsAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.read(settingsProvider.notifier).load(),
        ),
        data: (settings) => _SettingsBody(settings: settings),
      ),
    );
  }
}

class _SettingsBody extends ConsumerWidget {
  const _SettingsBody({required this.settings});
  final AppSettings settings;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    void update(AppSettings s) =>
        ref.read(settingsProvider.notifier).update(s);

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _SectionTitle(icon: LucideIcons.bell, title: 'Notifications'),
        _Card(children: [
          _SwitchRow(
            icon: LucideIcons.bell,
            label: 'Enable notifications',
            value: settings.notificationsEnabled,
            onChanged: (v) =>
                update(settings.copyWith(notificationsEnabled: v)),
          ),
          const Divider(height: 1),
          _SwitchRow(
            icon: LucideIcons.flaskConical,
            label: 'Lab result alerts',
            value: settings.receiveLabAlerts,
            onChanged: (v) =>
                update(settings.copyWith(receiveLabAlerts: v)),
          ),
          const Divider(height: 1),
          _SwitchRow(
            icon: LucideIcons.shield,
            label: 'Consent request alerts',
            value: settings.receiveConsentAlerts,
            onChanged: (v) =>
                update(settings.copyWith(receiveConsentAlerts: v)),
          ),
          const Divider(height: 1),
          _SwitchRow(
            icon: LucideIcons.calendar,
            label: 'Appointment reminders',
            value: settings.receiveAppointmentReminders,
            onChanged: (v) =>
                update(settings.copyWith(receiveAppointmentReminders: v)),
          ),
        ]),
        const SizedBox(height: 20),

        _SectionTitle(icon: LucideIcons.lock, title: 'Privacy & Data'),
        _Card(children: [
          _NavRow(
            icon: LucideIcons.eye,
            label: 'Access logs',
            onTap: () => context.push(Routes.accessLogs),
          ),
          const Divider(height: 1),
          _NavRow(
            icon: LucideIcons.download,
            label: 'Request data export',
            onTap: () async {
              final confirmed = await showDialog<bool>(
                context: context,
                builder: (_) => AlertDialog(
                  title: const Text('Request data export?'),
                  content: const Text(
                      'We will prepare a copy of all your health data. '
                      'You will be notified when it is ready to download.'),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.pop(context, false),
                      child: const Text('Cancel'),
                    ),
                    ElevatedButton(
                      onPressed: () => Navigator.pop(context, true),
                      child: const Text('Request'),
                    ),
                  ],
                ),
              );
              if (confirmed == true && context.mounted) {
                try {
                  await ref.read(settingsRepositoryProvider).requestDataExport();
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                      content: Text(
                          "Export requested. You'll be notified when it's ready."),
                    ));
                  }
                } catch (_) {
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                      content: Text('Failed to request export. Please try again.'),
                      backgroundColor: AppColors.danger,
                    ));
                  }
                }
              }
            },
          ),
          const Divider(height: 1),
          _NavRow(
            icon: LucideIcons.fileEdit,
            label: 'File a correction request',
            onTap: () => _showCorrectionSheet(context, ref),
          ),
        ]),
        const SizedBox(height: 20),

        _SectionTitle(icon: LucideIcons.user, title: 'Account'),
        _Card(children: [
          _NavRow(
            icon: LucideIcons.logOut,
            label: 'Sign out',
            labelColor: AppColors.danger,
            iconColor: AppColors.danger,
            onTap: () async {
              final confirmed = await showDialog<bool>(
                context: context,
                builder: (_) => AlertDialog(
                  title: const Text('Sign out?'),
                  content: const Text(
                      'You will need to verify your phone number again to sign in.'),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.pop(context, false),
                      child: const Text('Cancel'),
                    ),
                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.danger,
                          minimumSize: const Size(80, 40)),
                      onPressed: () => Navigator.pop(context, true),
                      child: const Text('Sign out'),
                    ),
                  ],
                ),
              );
              if (confirmed == true && context.mounted) {
                await ref.read(authProvider.notifier).logout();
              }
            },
          ),
        ]),
        const SizedBox(height: 32),
        Center(
          child: Text('OpesCare Patient App · v1.0.0',
              style: AppTextStyles.caption),
        ),
        const SizedBox(height: 16),
      ],
    );
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle({required this.icon, required this.title});
  final IconData icon;
  final String title;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(children: [
        Icon(icon, size: 15, color: AppColors.primary500),
        const SizedBox(width: 6),
        Text(title.toUpperCase(), style: AppTextStyles.label),
      ]),
    );
  }
}

class _Card extends StatelessWidget {
  const _Card({required this.children});
  final List<Widget> children;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Column(children: children),
    );
  }
}

class _SwitchRow extends StatelessWidget {
  const _SwitchRow({
    required this.icon,
    required this.label,
    required this.value,
    required this.onChanged,
  });
  final IconData icon;
  final String label;
  final bool value;
  final ValueChanged<bool> onChanged;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
      child: Row(children: [
        Icon(icon, size: 18, color: AppColors.neutral500),
        const SizedBox(width: 12),
        Expanded(child: Text(label, style: AppTextStyles.body)),
        Switch(value: value, onChanged: onChanged,
            activeColor: AppColors.primary500),
      ]),
    );
  }
}

class _NavRow extends StatelessWidget {
  const _NavRow({
    required this.icon,
    required this.label,
    required this.onTap,
    this.labelColor,
    this.iconColor,
  });
  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final Color? labelColor;
  final Color? iconColor;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      button: true,
      label: label,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
          child: Row(children: [
            Icon(icon, size: 18, color: iconColor ?? AppColors.neutral500),
            const SizedBox(width: 12),
            Expanded(child: Text(label,
                style: AppTextStyles.body.copyWith(color: labelColor))),
            Icon(LucideIcons.chevronRight,
                size: 16, color: labelColor ?? AppColors.neutral400),
          ]),
        ),
      ),
    );
  }
}

void _showCorrectionSheet(BuildContext context, WidgetRef ref) {
  final controller = TextEditingController();
  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
    ),
    builder: (_) => Padding(
      padding: EdgeInsets.only(
        left: 20, right: 20, top: 20,
        bottom: MediaQuery.viewInsetsOf(context).bottom + 20,
      ),
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        Text('File a Correction Request', style: AppTextStyles.h4),
        const SizedBox(height: 4),
        Text(
          'Describe the information that needs to be corrected.',
          style: AppTextStyles.bodySm,
        ),
        const SizedBox(height: 16),
        TextField(
          controller: controller,
          maxLines: 4,
          autofocus: true,
          decoration: const InputDecoration(
            hintText: 'Describe what needs to be corrected...',
          ),
        ),
        const SizedBox(height: 16),
        ElevatedButton(
          onPressed: () async {
            final desc = controller.text.trim();
            if (desc.isEmpty) return;
            Navigator.pop(context);
            try {
              await ref
                  .read(settingsRepositoryProvider)
                  .submitCorrectionRequest(desc);
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                  content: Text('Correction request submitted.'),
                ));
              }
            } catch (_) {
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                  content: Text('Failed to submit. Please try again.'),
                  backgroundColor: AppColors.danger,
                ));
              }
            }
          },
          child: const Text('Submit Request'),
        ),
        const SizedBox(height: 8),
      ]),
    ),
  );
}
