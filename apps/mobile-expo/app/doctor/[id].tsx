import { useState } from 'react';
import { ActivityIndicator, Pressable, ScrollView, Text, TextInput, View } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  Award,
  Building2,
  CalendarPlus,
  CheckCircle2,
  ChevronRight,
  Clock,
  Info,
  MapPin,
  MessageSquare,
  Stethoscope,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { colors } from '../../theme/tokens';
import {
  useProviderDetail,
  useStartMessageThread,
  type AppointmentSlotOption,
  type ProviderDetail,
} from '../../lib/api/queries';

/**
 * Clinician profile — the person behind a slot.
 *
 * Reachable from the facility page and from the booking flow's doctor step.
 * Two primary actions:
 *   • Book — deep-links into /appointments/book pre-filled with this
 *     clinician's facility AND provider id, so the patient lands straight on
 *     that clinician's slots instead of re-picking the facility.
 *   • Message — messaging requires an existing care relationship (the backend
 *     validates the appointment belongs to the caller), so the action is only
 *     wired to a composer when the API hands back a `messaging_appointment_id`.
 *     With no appointment yet it stays visible but explains itself and routes
 *     to booking — never a button that silently 422s.
 *
 * There is no bio: `staff_profiles` has no bio or photo column (verified
 * against the migration), so the profile is title + department + verified
 * credentials, and the avatar is a monogram.
 */

/** Two-letter monogram — the schema stores no provider photo. */
function providerInitials(name: string): string {
  const parts = name.trim().split(/\s+/).filter(Boolean);
  if (parts.length === 0) return '—';
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

/** provider_credentials.credential_type is a fixed DB enum — map it, never print it raw. */
const CREDENTIAL_KEYS: Record<string, string> = {
  medical_license: 'doctor.credentialMedicalLicense',
  specialist_cert: 'doctor.credentialSpecialistCert',
  board_certification: 'doctor.credentialBoardCertification',
  dea_registration: 'doctor.credentialDeaRegistration',
  cpr_cert: 'doctor.credentialCprCert',
  hospital_privilege: 'doctor.credentialHospitalPrivilege',
  other: 'doctor.credentialOther',
};

/** professional_licenses.profession — same treatment, with a raw-value fallback. */
const PROFESSION_KEYS: Record<string, string> = {
  doctor: 'doctor.professionDoctor',
  nurse: 'doctor.professionNurse',
  pharmacist: 'doctor.professionPharmacist',
  lab_technician: 'doctor.professionLabTechnician',
  midwife: 'doctor.professionMidwife',
};

function formatSlot(slot: AppointmentSlotOption, locale: string): string {
  const localeTag = locale === 'fr' ? 'fr-FR' : 'en-US';
  const date = new Date(slot.starts_at);
  const day = date.toLocaleDateString(localeTag, { weekday: 'short', day: 'numeric', month: 'short' });
  const time = date.toLocaleTimeString(localeTag, { hour: '2-digit', minute: '2-digit' });
  return `${day} · ${time}`;
}

export default function DoctorDetailScreen() {
  const { t, i18n } = useTranslation();
  const router = useRouter();
  const params = useLocalSearchParams<{ id?: string }>();
  const providerId = params.id;

  const providerQuery = useProviderDetail(providerId);
  const startThread = useStartMessageThread();

  const [composeOpen, setComposeOpen] = useState(false);
  const [composeBody, setComposeBody] = useState('');
  const [sent, setSent] = useState(false);

  const provider = providerQuery.data;

  const goToBooking = (provider: ProviderDetail) =>
    router.push({
      pathname: '/appointments/book',
      params: { facilityId: provider.care_facility_id, providerId: provider.id },
    });

  const submitMessage = async (appointmentId: string) => {
    const body = composeBody.trim();
    if (!body || startThread.isPending) return;
    try {
      await startThread.mutateAsync({ appointment_id: appointmentId, body });
      setComposeBody('');
      setComposeOpen(false);
      setSent(true);
    } catch {
      // startThread.isError renders the inline error below the composer.
    }
  };

  const professionLabel = (value: string | null) => {
    if (!value) return null;
    const key = PROFESSION_KEYS[value];
    return key ? t(key) : value;
  };

  return (
    <Screen className="px-0">
      <View className="flex-row items-center px-6 pt-2">
        <Pressable
          onPress={() => router.back()}
          hitSlop={8}
          className="h-11 w-11 items-center justify-center rounded-full border border-gold-300"
        >
          <ArrowLeft size={18} color={colors.gold[600]} />
        </Pressable>
        <Text className="ml-4 flex-1 text-lg font-extrabold text-navy-text" numberOfLines={1}>
          {provider?.name ?? t('doctor.title')}
        </Text>
      </View>

      {providerQuery.isLoading ? (
        <View className="flex-1 items-center justify-center">
          <ActivityIndicator color={colors.gold[500]} />
        </View>
      ) : providerQuery.isError || !provider ? (
        <View className="flex-1 items-center justify-center px-6">
          <Text className="text-center text-sm text-navy-secondary">{t('doctor.loadError')}</Text>
        </View>
      ) : (
        <ScrollView className="flex-1 px-6 pt-4" contentContainerStyle={{ paddingBottom: 40 }}>
          {/* ── Identity ──────────────────────────────────────────────── */}
          <View className="items-center rounded-2xl bg-white p-5">
            <View
              className="h-20 w-20 items-center justify-center rounded-full"
              style={{ backgroundColor: colors.gold[50] }}
            >
              <Text className="text-xl font-extrabold text-gold-600">
                {providerInitials(provider.name)}
              </Text>
            </View>
            <Text className="mt-3 text-xl font-extrabold text-navy-text">{provider.name}</Text>
            {provider.job_title ? (
              <Text className="mt-1 text-sm font-semibold text-gold-600">{provider.job_title}</Text>
            ) : null}
            <View className="mt-2 flex-row flex-wrap justify-center gap-2">
              {provider.department ? (
                <View className="flex-row items-center rounded-full bg-cream-200 px-3 py-1">
                  <Stethoscope size={12} color={colors.navy.secondary} />
                  <Text className="ml-1 text-[11px] font-semibold text-navy-secondary">
                    {provider.department}
                  </Text>
                </View>
              ) : null}
              {professionLabel(provider.profession) ? (
                <View className="rounded-full bg-cream-200 px-3 py-1">
                  <Text className="text-[11px] font-semibold text-navy-secondary">
                    {professionLabel(provider.profession)}
                  </Text>
                </View>
              ) : null}
            </View>
          </View>

          {/* ── Where they practise ───────────────────────────────────── */}
          <Text className="mb-2 mt-6 text-sm font-bold text-navy-text">
            {t('doctor.practisesAt')}
          </Text>
          <Pressable
            onPress={() => router.push(`/facility/${provider.care_facility_id}`)}
            className="flex-row items-center rounded-2xl bg-white p-4"
          >
            <View className="mr-3 h-11 w-11 items-center justify-center rounded-full bg-gold-50">
              <Building2 size={20} color={colors.gold[600]} />
            </View>
            <View className="flex-1">
              <Text className="text-base font-bold text-navy-text" numberOfLines={1}>
                {provider.facility_name}
              </Text>
              {provider.city ? (
                <View className="mt-1 flex-row items-center">
                  <MapPin size={12} color={colors.navy.muted} />
                  <Text className="ml-1 text-xs text-navy-secondary">{provider.city}</Text>
                </View>
              ) : null}
            </View>
            <ChevronRight size={18} color={colors.navy.muted} />
          </Pressable>

          {/* ── Credentials (the schema's stand-in for a bio) ──────────── */}
          <Text className="mb-2 mt-6 text-sm font-bold text-navy-text">
            {t('doctor.credentialsTitle')}
          </Text>
          <View className="rounded-2xl bg-white p-4">
            {provider.credentials.length === 0 && provider.licenses.length === 0 ? (
              <Text className="text-sm text-navy-secondary">{t('doctor.noCredentials')}</Text>
            ) : (
              <>
                {provider.licenses.map((license, index) => (
                  <View key={`lic-${index}`} className="flex-row items-start py-2">
                    <Award size={16} color={colors.gold[600]} />
                    <View className="ml-3 flex-1">
                      <Text className="text-sm font-semibold text-navy-text">
                        {professionLabel(license.profession) ?? t('doctor.credentialOther')}
                      </Text>
                      {license.issuing_body ? (
                        <Text className="mt-0.5 text-xs text-navy-muted">{license.issuing_body}</Text>
                      ) : null}
                    </View>
                  </View>
                ))}
                {provider.credentials.map((credential, index) => (
                  <View
                    key={`cred-${index}`}
                    className="flex-row items-start py-2"
                    style={
                      index > 0 || provider.licenses.length > 0
                        ? { borderTopWidth: 1, borderTopColor: colors.cream[300] }
                        : undefined
                    }
                  >
                    <CheckCircle2 size={16} color={colors.gold[600]} />
                    <View className="ml-3 flex-1">
                      <Text className="text-sm font-semibold text-navy-text">
                        {t(CREDENTIAL_KEYS[credential.type] ?? 'doctor.credentialOther')}
                      </Text>
                      {credential.issuing_body ? (
                        <Text className="mt-0.5 text-xs text-navy-muted">
                          {credential.issuing_body}
                        </Text>
                      ) : null}
                    </View>
                  </View>
                ))}
              </>
            )}
          </View>

          {/* ── Next available slots ──────────────────────────────────── */}
          <Text className="mb-2 mt-6 text-sm font-bold text-navy-text">
            {t('doctor.nextSlotsTitle')}
          </Text>
          {provider.next_slots.length === 0 ? (
            <View className="rounded-2xl bg-white p-4">
              <Text className="text-sm text-navy-secondary">{t('doctor.noSlots')}</Text>
            </View>
          ) : (
            <View className="flex-row flex-wrap gap-2">
              {provider.next_slots.map((slot) => (
                <Pressable
                  key={slot.id}
                  onPress={() => goToBooking(provider)}
                  className="flex-row items-center rounded-xl border bg-white px-3 py-2.5"
                  style={{ borderColor: colors.cream[300] }}
                >
                  <Clock size={13} color={colors.gold[600]} />
                  <Text className="ml-2 text-xs font-semibold text-navy-text">
                    {formatSlot(slot, i18n.language)}
                  </Text>
                </Pressable>
              ))}
            </View>
          )}

          {/* ── Primary actions ───────────────────────────────────────── */}
          <View className="mt-7">
            <Button
              label={t('doctor.bookAppointment')}
              leftIcon={CalendarPlus}
              onPress={() => goToBooking(provider)}
            />
          </View>

          {sent ? (
            <View
              className="mt-4 flex-row items-start rounded-2xl p-4"
              style={{ backgroundColor: colors.semantic.successSurface }}
            >
              <CheckCircle2 size={18} color={colors.semantic.success} />
              <View className="ml-3 flex-1">
                <Text className="text-sm text-navy-text">{t('doctor.messageSent')}</Text>
                <Pressable onPress={() => router.push('/(tabs)/messages')} className="mt-2">
                  <Text className="text-sm font-semibold text-gold-600">
                    {t('doctor.openMessages')}
                  </Text>
                </Pressable>
              </View>
            </View>
          ) : provider.messaging_appointment_id ? (
            <>
              <Pressable
                onPress={() => setComposeOpen((open) => !open)}
                className="mt-3 flex-row items-center justify-center rounded-2xl border py-3.5"
                style={{ borderColor: colors.gold[500] }}
              >
                <MessageSquare size={16} color={colors.gold[600]} />
                <Text className="ml-2 text-base font-semibold text-gold-600">
                  {t('doctor.message')}
                </Text>
              </Pressable>

              {composeOpen ? (
                <View className="mt-3 rounded-2xl bg-white p-4">
                  <TextInput
                    className="min-h-[88px] rounded-xl border px-3 py-2 text-sm text-navy-text"
                    style={{ borderColor: colors.cream[300], textAlignVertical: 'top' }}
                    placeholder={t('doctor.messagePlaceholder')}
                    placeholderTextColor={colors.navy.muted}
                    value={composeBody}
                    onChangeText={setComposeBody}
                    multiline
                  />
                  {startThread.isError ? (
                    <Text className="mt-2 text-xs text-danger">{t('doctor.messageError')}</Text>
                  ) : null}
                  <View className="mt-3">
                    <Button
                      label={
                        startThread.isPending ? t('doctor.messageSending') : t('doctor.messageSend')
                      }
                      leftIcon={MessageSquare}
                      showChevron={false}
                      loading={startThread.isPending}
                      disabled={!composeBody.trim()}
                      onPress={() =>
                        submitMessage(provider.messaging_appointment_id as string)
                      }
                    />
                  </View>
                </View>
              ) : null}
            </>
          ) : (
            // No appointment with this clinician yet — the action stays visible
            // and explains itself instead of failing on tap.
            <Pressable
              onPress={() => goToBooking(provider)}
              className="mt-3 flex-row items-start rounded-2xl p-4"
              style={{ backgroundColor: colors.semantic.infoSurface }}
            >
              <Info size={18} color={colors.semantic.info} />
              <View className="ml-3 flex-1">
                <View className="flex-row items-center">
                  <MessageSquare size={14} color={colors.navy.text} />
                  <Text className="ml-2 text-sm font-bold text-navy-text">
                    {t('doctor.message')}
                  </Text>
                </View>
                <Text className="mt-1 text-xs text-navy-secondary">
                  {t('doctor.messageNeedsAppointment')}
                </Text>
              </View>
              <ChevronRight size={16} color={colors.navy.muted} />
            </Pressable>
          )}
        </ScrollView>
      )}
    </Screen>
  );
}
