import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';
import '../../core/theme/app_colors.dart';

class LoadingSkeleton extends StatelessWidget {
  const LoadingSkeleton({
    super.key,
    this.width,
    this.height = 16,
    this.borderRadius = 8,
  });

  final double? width;
  final double height;
  final double borderRadius;

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: AppColors.neutral200,
      highlightColor: AppColors.neutral100,
      child: Container(
        width: width ?? double.infinity,
        height: height,
        decoration: BoxDecoration(
          color: AppColors.neutral200,
          borderRadius: BorderRadius.circular(borderRadius),
        ),
      ),
    );
  }
}

class HomeScreenSkeleton extends StatelessWidget {
  const HomeScreenSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(children: [
        const LoadingSkeleton(height: 100, borderRadius: 14),
        const SizedBox(height: 12),
        Row(children: const [
          Expanded(child: LoadingSkeleton(height: 72, borderRadius: 10)),
          SizedBox(width: 8),
          Expanded(child: LoadingSkeleton(height: 72, borderRadius: 10)),
          SizedBox(width: 8),
          Expanded(child: LoadingSkeleton(height: 72, borderRadius: 10)),
        ]),
        const SizedBox(height: 20),
        const LoadingSkeleton(height: 90, borderRadius: 10),
        const SizedBox(height: 12),
        const LoadingSkeleton(height: 90, borderRadius: 10),
      ]),
    );
  }
}
