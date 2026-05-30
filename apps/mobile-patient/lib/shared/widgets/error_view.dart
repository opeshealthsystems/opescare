import 'package:flutter/material.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../core/api/api_exception.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_text_styles.dart';

class ErrorView extends StatelessWidget {
  const ErrorView({
    super.key,
    required this.message,
    this.onRetry,
    this.error,
  });

  final String message;
  final VoidCallback? onRetry;
  final Object? error; // pass the raw error to auto-detect network errors

  bool get _isNetwork =>
      error is ApiException &&
      (error as ApiException).type == ApiErrorType.network;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 64, height: 64,
              decoration: BoxDecoration(
                color: _isNetwork
                    ? AppColors.warningLight
                    : AppColors.dangerLight,
                shape: BoxShape.circle,
              ),
              child: Icon(
                LucideIcons.wifiOff,
                color: _isNetwork
                    ? AppColors.warningDark
                    : AppColors.danger,
                size: 28,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              _isNetwork ? 'No internet connection' : 'Something went wrong',
              style: AppTextStyles.h4,
            ),
            const SizedBox(height: 8),
            Text(
              _isNetwork
                  ? 'Check your network and try again.'
                  : message,
              style: AppTextStyles.bodySm,
              textAlign: TextAlign.center,
            ),
            if (onRetry != null) ...[
              const SizedBox(height: 24),
              OutlinedButton.icon(
                onPressed: onRetry,
                icon: const Icon(LucideIcons.refreshCw, size: 16),
                label: const Text('Try again'),
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(160, 44),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
