import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/connectivity_banner.dart';

class MainShell extends StatelessWidget {
  const MainShell({super.key, required this.navigationShell});

  final StatefulNavigationShell navigationShell;

  static const _tabs = [
    _TabItem(icon: LucideIcons.home,     label: 'Home',      index: 0),
    _TabItem(icon: LucideIcons.creditCard, label: 'Health ID', index: 1),
    _TabItem(icon: LucideIcons.shield,   label: 'Consent',   index: 2),
    _TabItem(icon: LucideIcons.activity, label: 'Timeline',  index: 3),
    _TabItem(icon: LucideIcons.settings, label: 'Settings',  index: 4),
  ];

  @override
  Widget build(BuildContext context) {
    return ConnectivityBanner(
      child: Scaffold(
      body: navigationShell,
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: AppColors.surface,
          border: Border(top: BorderSide(color: AppColors.divider)),
        ),
        child: SafeArea(
          top: false,
          child: SizedBox(
            height: 60,
            child: Row(
              children: _tabs.map((tab) {
                final isSelected =
                    navigationShell.currentIndex == tab.index;
                return Expanded(
                  child: InkWell(
                    onTap: () => navigationShell.goBranch(
                      tab.index,
                      initialLocation:
                          tab.index == navigationShell.currentIndex,
                    ),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          tab.icon,
                          size: 22,
                          color: isSelected
                              ? AppColors.primary500
                              : AppColors.neutral400,
                        ),
                        const SizedBox(height: 3),
                        Text(
                          tab.label,
                          style: AppTextStyles.caption.copyWith(
                            fontSize: 10,
                            color: isSelected
                                ? AppColors.primary500
                                : AppColors.neutral400,
                            fontWeight: isSelected
                                ? FontWeight.w600
                                : FontWeight.w400,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
        ),
      ),
      ),
    );
  }
}

class _TabItem {
  const _TabItem({
    required this.icon,
    required this.label,
    required this.index,
  });

  final IconData icon;
  final String label;
  final int index;
}
