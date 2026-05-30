import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../models/facility.dart';
import '../models/slot.dart';
import '../providers/appointments_provider.dart';

class BookAppointmentScreen extends ConsumerStatefulWidget {
  const BookAppointmentScreen({super.key});

  @override
  ConsumerState<BookAppointmentScreen> createState() =>
      _BookAppointmentScreenState();
}

class _BookAppointmentScreenState
    extends ConsumerState<BookAppointmentScreen> {
  int _step = 0; // 0 = facility, 1 = slot, 2 = confirm
  Facility? _selectedFacility;
  Slot? _selectedSlot;
  final _notesController = TextEditingController();
  bool _isBooking = false;
  String? _bookingError;

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(['Select Facility', 'Select Time', 'Confirm'][_step]),
        leading: IconButton(
          icon: const Icon(LucideIcons.arrowLeft),
          onPressed: () {
            if (_step == 0) {
              context.pop();
            } else {
              setState(() => _step--);
            }
          },
        ),
      ),
      body: [
        _FacilityStep(
          onSelect: (f) => setState(() {
            _selectedFacility = f;
            _step = 1;
          }),
        ),
        if (_selectedFacility != null)
          _SlotStep(
            facility: _selectedFacility!,
            onSelect: (s) => setState(() {
              _selectedSlot = s;
              _step = 2;
            }),
          )
        else
          const SizedBox.shrink(),
        _ConfirmStep(
          facility: _selectedFacility,
          slot: _selectedSlot,
          notesController: _notesController,
          isBooking: _isBooking,
          error: _bookingError,
          onConfirm: _confirmBooking,
        ),
      ][_step],
    );
  }

  Future<void> _confirmBooking() async {
    if (_selectedFacility == null || _selectedSlot == null) return;
    setState(() {
      _isBooking = true;
      _bookingError = null;
    });
    try {
      await ref.read(appointmentsRepositoryProvider).book(
            facilityId: _selectedFacility!.id,
            slotId: _selectedSlot!.id,
            notes: _notesController.text.trim(),
          );
      ref.invalidate(appointmentsListProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Appointment booked successfully!')),
        );
        context.pop();
      }
    } catch (e) {
      setState(() {
        _isBooking = false;
        _bookingError = 'Booking failed. Please try again.';
      });
    }
  }
}

// ── Step 1: Select Facility ──────────────────────────────────────────────────

class _FacilityStep extends ConsumerWidget {
  const _FacilityStep({required this.onSelect});
  final void Function(Facility) onSelect;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final facilitiesAsync = ref.watch(facilitiesProvider);
    return facilitiesAsync.when(
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (e, _) => Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            const Icon(LucideIcons.wifiOff, size: 40, color: AppColors.neutral400),
            const SizedBox(height: 12),
            Text('Could not load facilities', style: AppTextStyles.body),
            const SizedBox(height: 8),
            OutlinedButton(
              onPressed: () => ref.invalidate(facilitiesProvider),
              child: const Text('Retry'),
            ),
          ]),
        ),
      ),
      data: (facilities) => ListView.separated(
        padding: const EdgeInsets.all(16),
        itemCount: facilities.length,
        separatorBuilder: (_, __) => const SizedBox(height: 10),
        itemBuilder: (_, i) {
          final f = facilities[i];
          return InkWell(
            onTap: () => onSelect(f),
            borderRadius: BorderRadius.circular(12),
            child: Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.divider),
              ),
              child: Row(children: [
                Container(
                  width: 40, height: 40,
                  decoration: BoxDecoration(
                    color: AppColors.primary50,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(LucideIcons.building2,
                      size: 20, color: AppColors.primary500),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(f.name,
                          style: AppTextStyles.body
                              .copyWith(fontWeight: FontWeight.w600)),
                      const SizedBox(height: 2),
                      Text(f.address, style: AppTextStyles.bodySm),
                    ],
                  ),
                ),
                const Icon(LucideIcons.chevronRight,
                    size: 16, color: AppColors.neutral400),
              ]),
            ),
          );
        },
      ),
    );
  }
}

// ── Step 2: Select Slot ──────────────────────────────────────────────────────

class _SlotStep extends ConsumerWidget {
  const _SlotStep({required this.facility, required this.onSelect});
  final Facility facility;
  final void Function(Slot) onSelect;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final slotsAsync = ref.watch(slotsProvider(facility.id));
    return slotsAsync.when(
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (e, _) => Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            const Icon(LucideIcons.calendarX2,
                size: 40, color: AppColors.neutral400),
            const SizedBox(height: 12),
            Text('Could not load slots', style: AppTextStyles.body),
            const SizedBox(height: 8),
            OutlinedButton(
              onPressed: () => ref.invalidate(slotsProvider(facility.id)),
              child: const Text('Retry'),
            ),
          ]),
        ),
      ),
      data: (slots) {
        if (slots.isEmpty) {
          return Center(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                const Icon(LucideIcons.calendarX2,
                    size: 48, color: AppColors.neutral300),
                const SizedBox(height: 12),
                Text('No available slots at ${facility.name}',
                    style: AppTextStyles.body, textAlign: TextAlign.center),
              ]),
            ),
          );
        }
        return ListView.separated(
          padding: const EdgeInsets.all(16),
          itemCount: slots.length,
          separatorBuilder: (_, __) => const SizedBox(height: 10),
          itemBuilder: (_, i) {
            final s = slots[i];
            String formatted = s.startsAt;
            try {
              formatted = DateFormat('EEE, d MMM · h:mm a')
                  .format(DateTime.parse(s.startsAt));
            } catch (_) {}
            return InkWell(
              onTap: () => onSelect(s),
              borderRadius: BorderRadius.circular(12),
              child: Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.divider),
                ),
                child: Row(children: [
                  Container(
                    width: 40, height: 40,
                    decoration: BoxDecoration(
                      color: AppColors.primary50,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(LucideIcons.clock,
                        size: 18, color: AppColors.primary500),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(s.serviceType,
                            style: AppTextStyles.body
                                .copyWith(fontWeight: FontWeight.w600)),
                        const SizedBox(height: 2),
                        Text(formatted, style: AppTextStyles.bodySm),
                        const SizedBox(height: 2),
                        Text(s.providerName, style: AppTextStyles.caption),
                      ],
                    ),
                  ),
                  const Icon(LucideIcons.chevronRight,
                      size: 16, color: AppColors.neutral400),
                ]),
              ),
            );
          },
        );
      },
    );
  }
}

// ── Step 3: Confirm ──────────────────────────────────────────────────────────

class _ConfirmStep extends StatelessWidget {
  const _ConfirmStep({
    required this.facility,
    required this.slot,
    required this.notesController,
    required this.isBooking,
    required this.onConfirm,
    this.error,
  });

  final Facility? facility;
  final Slot? slot;
  final TextEditingController notesController;
  final bool isBooking;
  final String? error;
  final VoidCallback onConfirm;

  @override
  Widget build(BuildContext context) {
    if (facility == null || slot == null) {
      return const Center(child: Text('Missing selection'));
    }
    String formatted = slot!.startsAt;
    try {
      formatted = DateFormat('EEEE, d MMMM yyyy · h:mm a')
          .format(DateTime.parse(slot!.startsAt));
    } catch (_) {}

    return ListView(padding: const EdgeInsets.all(16), children: [
      Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.divider),
        ),
        child: Column(children: [
          _Row('Facility',  facility!.name),
          const Divider(height: 1),
          _Row('Service',   slot!.serviceType),
          const Divider(height: 1),
          _Row('Provider',  slot!.providerName),
          const Divider(height: 1),
          _Row('Date & Time', formatted),
        ]),
      ),
      const SizedBox(height: 20),
      Text('NOTES (OPTIONAL)', style: AppTextStyles.label),
      const SizedBox(height: 8),
      TextFormField(
        controller: notesController,
        maxLines: 3,
        style: AppTextStyles.body,
        decoration: const InputDecoration(
          hintText: 'Any information you want the provider to know...',
        ),
      ),
      if (error != null) ...[
        const SizedBox(height: 12),
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: AppColors.dangerLight,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Text(error!,
              style: AppTextStyles.bodySm.copyWith(color: AppColors.danger)),
        ),
      ],
      const SizedBox(height: 24),
      ElevatedButton(
        onPressed: isBooking ? null : onConfirm,
        child: isBooking
            ? const SizedBox(
                height: 20, width: 20,
                child: CircularProgressIndicator(
                    color: Colors.white, strokeWidth: 2))
            : const Text('Confirm Booking'),
      ),
      const SizedBox(height: 32),
    ]);
  }
}

class _Row extends StatelessWidget {
  const _Row(this.label, this.value);
  final String label, value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 12),
      child: Row(children: [
        Expanded(child: Text(label, style: AppTextStyles.bodySm)),
        Expanded(
          flex: 2,
          child: Text(value,
              style: AppTextStyles.body.copyWith(fontWeight: FontWeight.w600),
              textAlign: TextAlign.right),
        ),
      ]),
    );
  }
}
