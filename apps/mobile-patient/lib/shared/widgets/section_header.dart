import 'package:flutter/material.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_text_styles.dart';

class SectionHeader extends StatelessWidget {
  const SectionHeader({
    super.key,
    required this.title,
    this.onSeeAll,
    this.icon,
  });

  final String title;
  final VoidCallback? onSeeAll;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        if (icon != null) ...[
          Icon(icon, size: 16, color: AppColors.primary500),
          const SizedBox(width: 6),
        ],
        Text(title, style: AppTextStyles.h4),
        const Spacer(),
        if (onSeeAll != null)
          GestureDetector(
            onTap: onSeeAll,
            child: Row(children: [
              Text(
                'See all',
                style: AppTextStyles.bodySm.copyWith(
                  color: AppColors.primary500,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(width: 2),
              const Icon(LucideIcons.chevronRight,
                  size: 14, color: AppColors.primary500),
            ]),
          ),
      ],
    );
  }
}
