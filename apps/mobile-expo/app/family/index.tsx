import { useMemo, useState } from 'react';
import { ActivityIndicator, Alert, Pressable, RefreshControl, ScrollView, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  Clock,
  Mail,
  Phone,
  Send,
  UserPlus,
  Users,
  X,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { TextField } from '../../components/ui/TextField';
import { colors } from '../../theme/tokens';
import {
  useCancelFamilyInvitation,
  useFamilyInvitations,
  useFamilyMembers,
  useSendFamilyInvitation,
  type FamilyInvitation,
} from '../../lib/api/queries';

const RELATIONSHIP_OPTIONS = [
  'parent',
  'grandparent',
  'spouse',
  'sibling',
  'caregiver',
  'legal_guardian',
  'child',
  'other',
] as const;

function extractErrorMessage(err: unknown, fallback: string): string {
  const anyErr = err as any;
  return anyErr?.response?.data?.message ?? fallback;
}

function initialsOf(name: string): string {
  const parts = name.trim().split(/\s+/).filter(Boolean);
  if (parts.length === 0) return '?';
  if (parts.length === 1) return parts[0]!.slice(0, 2).toUpperCase();
  return `${parts[0]![0]}${parts[parts.length - 1]![0]}`.toUpperCase();
}

/**
 * Family: linked members + invitations. GET /mobile/family (active + pending
 * links), GET/POST /mobile/family/invitations, DELETE /mobile/family/invitations/{id}.
 * Backend: App\Http\Controllers\Api\Mobile\MobileFamilyController (already wired).
 */
export default function FamilyScreen() {
  const { t } = useTranslation();
  const router = useRouter();

  const membersQuery = useFamilyMembers();
  const invitationsQuery = useFamilyInvitations();
  const sendInvitation = useSendFamilyInvitation();
  const cancelInvitation = useCancelFamilyInvitation();

  const [showForm, setShowForm] = useState(false);
  const [contact, setContact] = useState('');
  const [relationship, setRelationship] = useState<(typeof RELATIONSHIP_OPTIONS)[number]>('parent');
  const [formError, setFormError] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);
  const [cancellingId, setCancellingId] = useState<string | null>(null);

  const members = useMemo(
    () => (membersQuery.data ?? []).filter((m) => !m.is_pending && m.patient),
    [membersQuery.data],
  );
  const invitations = invitationsQuery.data ?? [];

  const refreshing = membersQuery.isRefetching || invitationsQuery.isRefetching;
  const initialLoading =
    (membersQuery.isLoading && !membersQuery.data) ||
    (invitationsQuery.isLoading && !invitationsQuery.data);
  const hasLoadError = membersQuery.isError || invitationsQuery.isError;

  const onRefresh = () => {
    membersQuery.refetch();
    invitationsQuery.refetch();
  };

  const handleSendInvitation = async () => {
    setFormError(null);
    setSuccessMessage(null);
    if (!contact.trim()) {
      setFormError(t('family.contactRequired'));
      return;
    }
    try {
      await sendInvitation.mutateAsync({ contact: contact.trim(), relationship });
      setContact('');
      setRelationship('parent');
      setShowForm(false);
      setSuccessMessage(t('family.invitationSent'));
    } catch (err) {
      setFormError(extractErrorMessage(err, t('family.invitationFailed')));
    }
  };

  const handleCancelInvitation = (invitation: FamilyInvitation) => {
    Alert.alert(
      t('family.cancelInviteTitle'),
      t('family.cancelInviteBody', { contact: invitation.contact }),
      [
        { text: t('family.dismiss'), style: 'cancel' },
        {
          text: t('family.cancelInviteConfirm'),
          style: 'destructive',
          onPress: async () => {
            setCancellingId(invitation.id);
            try {
              await cancelInvitation.mutateAsync(invitation.id);
            } catch (err) {
              Alert.alert(t('family.error'), extractErrorMessage(err, t('family.cancelInviteFailed')));
            } finally {
              setCancellingId(null);
            }
          },
        },
      ],
    );
  };

  return (
    <Screen>
      <View className="flex-row items-center pt-2">
        <Pressable
          onPress={() => router.back()}
          hitSlop={8}
          className="h-11 w-11 items-center justify-center rounded-full border border-gold-300"
        >
          <ArrowLeft size={18} color={colors.gold[600]} />
        </Pressable>
        <Text className="ml-4 text-xl font-extrabold text-navy-text">{t('family.title')}</Text>
      </View>

      <ScrollView
        className="flex-1"
        contentContainerStyle={{ paddingBottom: 40 }}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.gold[500]} />
        }
      >
        <Text className="mt-2 text-sm text-navy-secondary">{t('family.subtitle')}</Text>

        {hasLoadError ? (
          <View className="mt-4 rounded-2xl p-4" style={{ backgroundColor: colors.semantic.dangerSurface }}>
            <Text className="text-center text-sm font-semibold" style={{ color: colors.semantic.danger }}>
              {t('family.loadFailed')}
            </Text>
            <Pressable onPress={onRefresh} className="mt-2 items-center">
              <Text className="text-sm font-bold" style={{ color: colors.semantic.danger }}>
                {t('family.retry')}
              </Text>
            </Pressable>
          </View>
        ) : null}

        {/* Invite card */}
        <View className="mt-5 rounded-2xl border border-cream-300 bg-white p-4">
          <Pressable
            onPress={() => {
              setShowForm((v) => !v);
              setFormError(null);
            }}
            className="flex-row items-center justify-between"
          >
            <View className="flex-row items-center">
              <View className="h-10 w-10 items-center justify-center rounded-full bg-gold-50">
                <UserPlus size={18} color={colors.gold[600]} />
              </View>
              <Text className="ml-3 text-base font-bold text-navy-text">{t('family.inviteTitle')}</Text>
            </View>
            <Text className="text-sm font-semibold text-gold-500">
              {showForm ? t('family.close') : t('family.invite')}
            </Text>
          </Pressable>

          {showForm ? (
            <View className="mt-4">
              <TextField
                label={t('family.contactLabel')}
                placeholder={t('family.contactPlaceholder')}
                icon={contact.includes('@') ? Mail : Phone}
                autoCapitalize="none"
                keyboardType="email-address"
                value={contact}
                onChangeText={setContact}
              />

              <Text className="mb-2 text-sm font-semibold text-navy-text">
                {t('family.relationshipLabel')}
              </Text>
              <View className="mb-4 flex-row flex-wrap" style={{ gap: 8 }}>
                {RELATIONSHIP_OPTIONS.map((option) => {
                  const active = relationship === option;
                  return (
                    <Pressable
                      key={option}
                      onPress={() => setRelationship(option)}
                      className="rounded-full border px-3 py-2"
                      style={{
                        borderColor: active ? colors.gold[500] : colors.cream[300],
                        backgroundColor: active ? colors.gold[50] : colors.white,
                      }}
                    >
                      <Text
                        className="text-xs font-semibold"
                        style={{ color: active ? colors.gold[600] : colors.navy.secondary }}
                      >
                        {t(`family.relationships.${option}`)}
                      </Text>
                    </Pressable>
                  );
                })}
              </View>

              {formError ? <Text className="mb-3 text-sm text-danger">{formError}</Text> : null}

              <Button
                label={t('family.sendInvite')}
                onPress={handleSendInvitation}
                loading={sendInvitation.isPending}
                leftIcon={Send}
                showChevron={false}
              />
            </View>
          ) : null}
        </View>

        {successMessage ? (
          <View
            className="mt-4 rounded-2xl p-3"
            style={{ backgroundColor: colors.semantic.successSurface }}
          >
            <Text
              className="text-center text-sm font-semibold"
              style={{ color: colors.semantic.success }}
            >
              {successMessage}
            </Text>
          </View>
        ) : null}

        {/* Pending invitations */}
        <Text className="mb-3 mt-7 text-base font-bold text-navy-text">
          {t('family.pendingInvitations')}
          {invitations.length > 0 ? ` (${invitations.length})` : ''}
        </Text>

        {initialLoading ? (
          <ActivityIndicator color={colors.gold[500]} />
        ) : invitations.length === 0 ? (
          <Text className="text-sm text-navy-secondary">{t('family.noPendingInvitations')}</Text>
        ) : (
          invitations.map((invitation) => (
            <View
              key={invitation.id}
              className="mb-3 flex-row items-center rounded-2xl border border-cream-300 bg-white p-4"
            >
              <View className="h-10 w-10 items-center justify-center rounded-full bg-gold-50">
                <Clock size={16} color={colors.gold[600]} />
              </View>
              <View className="ml-3 flex-1">
                <Text className="text-sm font-bold text-navy-text">{invitation.contact}</Text>
                <Text className="mt-0.5 text-xs text-navy-secondary">
                  {t(`family.relationships.${invitation.relationship}`, {
                    defaultValue: invitation.relationship,
                  })}
                </Text>
                <Text className="mt-0.5 text-xs text-navy-muted">
                  {t('family.expiresOn', { date: invitation.expires_at ?? '—' })}
                </Text>
              </View>
              <Pressable
                onPress={() => handleCancelInvitation(invitation)}
                hitSlop={8}
                disabled={cancellingId === invitation.id}
                className="h-9 w-9 items-center justify-center rounded-full"
                style={{
                  backgroundColor: colors.semantic.dangerSurface,
                  opacity: cancellingId === invitation.id ? 0.5 : 1,
                }}
              >
                {cancellingId === invitation.id ? (
                  <ActivityIndicator size="small" color={colors.semantic.danger} />
                ) : (
                  <X size={16} color={colors.semantic.danger} />
                )}
              </Pressable>
            </View>
          ))
        )}

        {/* Linked members */}
        <Text className="mb-3 mt-7 text-base font-bold text-navy-text">
          {t('family.linkedMembers')}
          {members.length > 0 ? ` (${members.length})` : ''}
        </Text>

        {initialLoading ? (
          <ActivityIndicator color={colors.gold[500]} />
        ) : members.length === 0 ? (
          <View className="items-center rounded-2xl border border-dashed border-cream-300 p-6">
            <Users size={22} color={colors.navy.muted} />
            <Text className="mt-2 text-center text-sm text-navy-secondary">
              {t('family.noMembers')}
            </Text>
          </View>
        ) : (
          members.map((member) => (
            <View
              key={member.id}
              className="mb-3 flex-row items-center rounded-2xl border border-cream-300 bg-white p-4"
            >
              <View className="h-11 w-11 items-center justify-center rounded-full bg-gold-100">
                <Text className="text-sm font-bold text-gold-600">
                  {initialsOf(member.patient?.full_name ?? '?')}
                </Text>
              </View>
              <View className="ml-3 flex-1">
                <Text className="text-sm font-bold text-navy-text">{member.patient?.full_name}</Text>
                <View className="mt-0.5 flex-row items-center">
                  <Text className="text-xs text-navy-secondary">
                    {t(`family.relationships.${member.relationship}`, {
                      defaultValue: member.relationship,
                    })}
                  </Text>
                  {member.patient?.age != null ? (
                    <Text className="text-xs text-navy-muted">
                      {' '}
                      · {t('family.age', { age: member.patient.age })}
                    </Text>
                  ) : null}
                </View>
              </View>
              <View className="rounded-full bg-cream-200 px-3 py-1">
                <Text className="text-[10px] font-semibold text-navy-secondary">
                  {t(`family.accessLevels.${member.access_level}`, {
                    defaultValue: member.access_level,
                  })}
                </Text>
              </View>
            </View>
          ))
        )}
      </ScrollView>
    </Screen>
  );
}
