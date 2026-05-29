import 'package:flutter/material.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_text_styles.dart';

enum BadgeStatus {
  verified,
  provisional,
  pending,
  active,
  revoked,
  critical,
  released,
  cancelled,
  synced,
  failed,
}

class StatusBadge extends StatelessWidget {
  const StatusBadge(this.status, {super.key, this.small = false});

  final BadgeStatus status;
  final bool small;

  @override
  Widget build(BuildContext context) {
    final cfg = _config[status]!;
    final fs = small ? 10.0 : 11.0;
    final iconSize = small ? 10.0 : 12.0;
    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: small ? 6 : 8,
        vertical: small ? 2 : 3,
      ),
      decoration: BoxDecoration(
        color: cfg.bg,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(cfg.icon, size: iconSize, color: cfg.fg),
          const SizedBox(width: 4),
          Text(
            cfg.label,
            style: AppTextStyles.label.copyWith(
              fontSize: fs, color: cfg.fg, letterSpacing: 0.4,
            ),
          ),
        ],
      ),
    );
  }

  static const _config = <BadgeStatus, _BadgeCfg>{
    BadgeStatus.verified:    _BadgeCfg(AppColors.successLight, AppColors.successDark, LucideIcons.checkCircle, 'Verified'),
    BadgeStatus.provisional: _BadgeCfg(AppColors.warningLight, AppColors.warningDark, LucideIcons.clock, 'Provisional'),
    BadgeStatus.pending:     _BadgeCfg(AppColors.warningLight, AppColors.warningDark, LucideIcons.clock, 'Pending'),
    BadgeStatus.active:      _BadgeCfg(AppColors.successLight, AppColors.successDark, LucideIcons.activity, 'Active'),
    BadgeStatus.revoked:     _BadgeCfg(AppColors.dangerLight,  AppColors.dangerDark,  LucideIcons.xCircle, 'Revoked'),
    BadgeStatus.critical:    _BadgeCfg(AppColors.dangerLight,  AppColors.dangerDark,  LucideIcons.alertTriangle, 'Critical'),
    BadgeStatus.released:    _BadgeCfg(AppColors.successLight, AppColors.successDark, LucideIcons.checkCircle, 'Released'),
    BadgeStatus.cancelled:   _BadgeCfg(AppColors.neutral100,   AppColors.neutral600,  LucideIcons.xCircle, 'Cancelled'),
    BadgeStatus.synced:      _BadgeCfg(AppColors.successLight, AppColors.successDark, LucideIcons.refreshCw, 'Synced'),
    BadgeStatus.failed:      _BadgeCfg(AppColors.dangerLight,  AppColors.dangerDark,  LucideIcons.wifiOff, 'Failed'),
  };
}

class _BadgeCfg {
  const _BadgeCfg(this.bg, this.fg, this.icon, this.label);
  final Color bg, fg;
  final IconData icon;
  final String label;
}
