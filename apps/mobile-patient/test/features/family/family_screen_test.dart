import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:opescare_patient/features/family/models/family_member.dart';
import 'package:opescare_patient/features/family/presentation/family_screen.dart';
import 'package:opescare_patient/features/family/providers/family_provider.dart';
import 'package:opescare_patient/features/health_id/models/health_id_card.dart';
import 'package:opescare_patient/features/health_id/providers/health_id_provider.dart';

Widget _buildSubject() {
  return ProviderScope(
    overrides: [
      healthIdCardProvider.overrideWith((ref) async => const HealthIdCard(
            healthId: 'OPC-0001',
            displayName: 'Primary Patient',
            dateOfBirth: '1990-01-01',
            sex: 'F',
            bloodGroup: 'O+',
            isVerified: true,
            issuedAt: '2026-01-01',
          )),
      familyMembersProvider.overrideWith((ref) async => const [
            FamilyMember(
              id: 'family-1',
              name: 'Ada Patient',
              relationship: 'daughter',
              status: 'active',
              healthId: 'OPC-2001',
              age: 8,
              activeRxCount: 1,
              upcomingAppointment: '2026-06-20',
            ),
          ]),
      familyInvitationsProvider.overrideWith((ref) async => const []),
    ],
    child: const MaterialApp(home: FamilyScreen()),
  );
}

void main() {
  testWidgets('manage action opens the family management sheet',
      (tester) async {
    await tester.pumpWidget(_buildSubject());
    await tester.pumpAndSettle();

    await tester.tap(find.text('Manage'));
    await tester.pumpAndSettle();

    expect(find.text('Add a Family Member'), findsOneWidget);
    expect(find.text('Add New Member'), findsOneWidget);
    expect(find.text('Invite Family Member'), findsOneWidget);
  });

  testWidgets('member card opens relationship and health id details',
      (tester) async {
    await tester.pumpWidget(_buildSubject());
    await tester.pumpAndSettle();

    await tester.tap(find.text('Ada Patient'));
    await tester.pumpAndSettle();

    expect(find.text('Ada Patient'), findsWidgets);
    expect(find.text('OPC-2001'), findsOneWidget);
    expect(find.text('Relationship'), findsOneWidget);
    expect(find.text('Daughter'), findsOneWidget);
    expect(find.text('Status'), findsOneWidget);
    expect(find.text('Active'), findsOneWidget);
  });
}
