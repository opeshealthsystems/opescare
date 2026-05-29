import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../shared/widgets/error_view.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../models/document.dart';
import '../providers/documents_provider.dart';

class DocumentsScreen extends ConsumerWidget {
  const DocumentsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final docsAsync = ref.watch(documentsListProvider);
    return Scaffold(
      appBar: AppBar(title: const Text('My Documents')),
      body: docsAsync.when(
        loading: () => ListView(padding: const EdgeInsets.all(16), children: const [
          LoadingSkeleton(height: 80, borderRadius: 10),
          SizedBox(height: 8),
          LoadingSkeleton(height: 80, borderRadius: 10),
        ]),
        error: (e, _) => ErrorView(
          message: e.toString(),
          onRetry: () => ref.invalidate(documentsListProvider),
        ),
        data: (docs) {
          if (docs.isEmpty) {
            return Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
              const Icon(LucideIcons.fileText, size: 48, color: AppColors.neutral300),
              const SizedBox(height: 12),
              Text('No documents yet.', style: AppTextStyles.bodySm),
            ]));
          }
          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(documentsListProvider),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: docs.length,
              separatorBuilder: (_, __) => const SizedBox(height: 8),
              itemBuilder: (_, i) => _DocCard(doc: docs[i]),
            ),
          );
        },
      ),
    );
  }
}

class _DocCard extends StatelessWidget {
  const _DocCard({required this.doc});
  final PatientDocument doc;

  @override
  Widget build(BuildContext context) {
    String date = doc.issuedAt;
    try { date = DateFormat('d MMM yyyy').format(DateTime.parse(doc.issuedAt)); }
    catch (_) {}

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.divider),
      ),
      child: Row(children: [
        Container(
          width: 44, height: 44,
          decoration: BoxDecoration(
            color: AppColors.primary50,
            borderRadius: BorderRadius.circular(10),
          ),
          child: const Icon(LucideIcons.fileText, size: 22, color: AppColors.primary500),
        ),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Expanded(child: Text(doc.title,
                style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600),
                overflow: TextOverflow.ellipsis)),
            if (doc.isVerified)
              const Icon(LucideIcons.checkCircle, size: 14, color: AppColors.success),
          ]),
          const SizedBox(height: 3),
          Text(doc.type, style: AppTextStyles.bodySm),
          const SizedBox(height: 3),
          Row(children: [
            const Icon(LucideIcons.building2, size: 11, color: AppColors.neutral400),
            const SizedBox(width: 4),
            Expanded(child: Text(doc.facilityName,
                style: AppTextStyles.caption, overflow: TextOverflow.ellipsis)),
            Text(date, style: AppTextStyles.caption),
          ]),
        ])),
        if (doc.fileUrl != null)
          const Padding(
            padding: EdgeInsets.only(left: 8),
            child: Icon(LucideIcons.download, size: 16, color: AppColors.primary500),
          ),
      ]),
    );
  }
}
