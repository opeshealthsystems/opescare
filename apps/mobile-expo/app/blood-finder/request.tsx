import { useMemo, useState } from 'react';
import { ActivityIndicator, Linking, Pressable, ScrollView, Text, TextInput, View } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  Building2,
  ChevronLeft,
  CircleAlert,
  Clock,
  Droplets,
  Hospital,
  Info,
  Minus,
  Phone,
  Plus,
  Send,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { colors } from '../../theme/tokens';
import {
  useBloodOptions,
  useBloodSearch,
  useCreateBloodRequest,
  type BloodBankResult,
  type BloodComponentValue,
  type BloodGroupValue,
  type BloodRequest,
  type BloodUrgencyValue,
} from '../../lib/api/queries';

/**
 * Request Blood Units — the action panel of the Blood Finder.
 *
 * The chosen facility is re-resolved from the same search the list ran (origin
 * and radius are carried in the params) rather than passed through as loose
 * strings, so what the patient confirms against is live availability, not a
 * snapshot that may already be stale by the time they got here.
 *
 * Requesting reserves a place at the counter. No payment is taken, no unit is
 * issued, and nothing here is a cross-match or a clinical approval — the
 * facility performs its own checks before releasing anything.
 */

const URGENCIES: BloodUrgencyValue[] = ['routine', 'urgent', 'emergency'];

function iconForFacility(type: string | undefined): LucideIcon {
  switch (type) {
    case 'blood_bank':
      return Droplets;
    case 'hospital':
      return Hospital;
    default:
      return Building2;
  }
}

function freshnessTone(freshness: string | undefined): string {
  switch (freshness) {
    case 'fresh':
      return colors.semantic.success;
    case 'recent':
      return colors.semantic.warning;
    case 'stale':
      return colors.semantic.danger;
    default:
      return colors.navy.muted;
  }
}

/** Pulls the backend's `error_code` out of an axios failure, if there is one. */
function errorCodeOf(error: unknown): string | null {
  const body = (error as { response?: { data?: { error_code?: string } } })?.response?.data;
  return body?.error_code ?? null;
}

export default function BloodRequestScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const params = useLocalSearchParams<{
    facilityId?: string;
    bloodGroup?: string;
    componentType?: string;
    lat?: string;
    lng?: string;
    radiusKm?: string;
  }>();

  const facilityId = params.facilityId ?? null;
  const bloodGroup = (params.bloodGroup ?? null) as BloodGroupValue | null;
  const componentType = (params.componentType ?? 'whole_blood') as BloodComponentValue;
  const lat = Number(params.lat ?? 4.0511);
  const lng = Number(params.lng ?? 9.7679);
  const radiusKm = Number(params.radiusKm ?? 25);

  const [quantity, setQuantity] = useState(1);
  const [urgency, setUrgency] = useState<BloodUrgencyValue>('routine');
  const [contactPhone, setContactPhone] = useState('');
  const [note, setNote] = useState('');
  const [confirmed, setConfirmed] = useState<BloodRequest | null>(null);
  const [errorCode, setErrorCode] = useState<string | null>(null);

  const options = useBloodOptions();
  const search = useBloodSearch({
    bloodGroup,
    componentType,
    lat,
    lng,
    radiusKm,
    enabled: !!bloodGroup,
  });
  const createRequest = useCreateBloodRequest();

  const maxUnits = options.data?.max_units ?? 6;

  const facility: BloodBankResult | null = useMemo(
    () => (search.data ?? []).find((row) => row.id === facilityId) ?? null,
    [search.data, facilityId],
  );

  const FacilityIcon = iconForFacility(facility?.facility_type);

  if (!facilityId || !bloodGroup) {
    return (
      <Screen>
        <View className="flex-1 items-center justify-center">
          <Text className="text-sm text-navy-secondary">{t('bloodFinder.request.noFacility')}</Text>
        </View>
      </Screen>
    );
  }

  const onSubmit = () => {
    setErrorCode(null);
    createRequest.mutate(
      {
        care_facility_id: facilityId,
        blood_group: bloodGroup,
        component_type: componentType,
        quantity,
        urgency,
        contact_phone: contactPhone.trim() || null,
        note: note.trim() || null,
      },
      {
        onSuccess: (request) => setConfirmed(request),
        onError: (error) => setErrorCode(errorCodeOf(error) ?? 'generic'),
      },
    );
  };

  return (
    <Screen className="px-0">
      <ScrollView
        className="flex-1 px-6"
        showsVerticalScrollIndicator={false}
        keyboardShouldPersistTaps="handled"
      >
        {/* Header */}
        <View className="mt-2 flex-row items-center">
          <Pressable
            onPress={() => (router.canGoBack() ? router.back() : router.replace('/blood-finder'))}
            hitSlop={8}
            accessibilityRole="button"
            accessibilityLabel={t('bloodFinder.back')}
            className="h-10 w-10 items-center justify-center rounded-xl border border-cream-300 bg-white"
          >
            <ChevronLeft size={20} color={colors.navy.text} />
          </Pressable>
          <Text className="ml-3 text-lg font-extrabold text-navy-text">
            {t('bloodFinder.request.title')}
          </Text>
        </View>

        <Text className="mt-3 text-xs leading-4 text-navy-secondary">
          {t('bloodFinder.request.subtitle')}
        </Text>

        {/* Facility + live availability */}
        {search.isPending ? (
          <View className="mt-10 items-center">
            <ActivityIndicator color={colors.gold[500]} />
          </View>
        ) : search.isError ? (
          <View className="mt-6 items-center rounded-2xl border border-cream-300 bg-white px-6 py-8">
            <CircleAlert size={22} color={colors.semantic.danger} />
            <Text className="mt-2 text-center text-sm text-navy-secondary">
              {t('bloodFinder.loadFailed')}
            </Text>
            <Pressable
              onPress={() => search.refetch()}
              className="mt-3 rounded-xl border border-gold-500 px-4 py-2"
            >
              <Text className="text-xs font-bold text-gold-600">{t('bloodFinder.retry')}</Text>
            </Pressable>
          </View>
        ) : !facility ? (
          <View className="mt-6 items-center rounded-2xl border border-cream-300 bg-white px-6 py-8">
            <CircleAlert size={22} color={colors.navy.muted} />
            <Text className="mt-2 text-center text-sm text-navy-secondary">
              {t('bloodFinder.errors.BLOOD_NOT_AVAILABLE')}
            </Text>
          </View>
        ) : (
          <>
            <View className="mt-5 flex-row rounded-2xl border border-gold-100 bg-cream-200 p-4">
              <View className="h-16 w-16 items-center justify-center rounded-xl bg-white">
                <FacilityIcon size={24} color={colors.gold[600]} />
              </View>
              <View className="ml-4 flex-1">
                <Text className="text-base font-extrabold text-navy-text">{facility.name}</Text>
                <Text className="mt-1 text-xs text-navy-secondary" numberOfLines={2}>
                  {[facility.address, facility.city].filter(Boolean).join(', ')}
                </Text>
                <Text className="mt-1 text-[11px] text-navy-secondary">
                  {facility.distance_km === null
                    ? t('bloodFinder.distanceUnknown')
                    : t('bloodFinder.distanceKm', { km: facility.distance_km })}
                </Text>
              </View>
            </View>

            <View className="mt-3 flex-row items-center justify-between rounded-2xl border border-cream-300 bg-white p-4">
              <View className="flex-1 pr-2">
                <Text className="text-[11px] text-navy-muted">
                  {`${bloodGroup}  •  ${t(`bloodFinder.components.${componentType}`)}`}
                </Text>
                <Text className="mt-0.5 text-sm font-bold text-navy-text">
                  {facility.availability?.units_range
                    ? t('bloodFinder.unitsRange', { range: facility.availability.units_range })
                    : t('bloodFinder.unitsUnknown')}
                </Text>
              </View>
              <View className="flex-row items-center">
                <Clock size={12} color={freshnessTone(facility.availability?.freshness)} />
                <Text
                  className="ml-1 text-[11px] font-semibold"
                  style={{ color: freshnessTone(facility.availability?.freshness) }}
                >
                  {t(`bloodFinder.freshness.${facility.availability?.freshness ?? 'stale'}`, {
                    defaultValue: t('bloodFinder.freshness.stale'),
                  })}
                </Text>
              </View>
            </View>

            {/* Units */}
            <Text className="mt-6 text-base font-bold text-navy-text">
              {t('bloodFinder.request.quantity')}
            </Text>
            <View className="mt-2 flex-row items-center">
              <Pressable
                onPress={() => setQuantity((q) => Math.max(1, q - 1))}
                className="h-10 w-10 items-center justify-center rounded-xl border border-cream-300 bg-white"
                accessibilityRole="button"
              >
                <Minus size={16} color={colors.navy.text} />
              </Pressable>
              <Text className="mx-5 text-base font-extrabold text-navy-text">{quantity}</Text>
              <Pressable
                onPress={() => setQuantity((q) => Math.min(maxUnits, q + 1))}
                className="h-10 w-10 items-center justify-center rounded-xl border border-cream-300 bg-white"
                accessibilityRole="button"
              >
                <Plus size={16} color={colors.navy.text} />
              </Pressable>
            </View>

            {/* Urgency */}
            <Text className="mt-6 text-base font-bold text-navy-text">
              {t('bloodFinder.request.urgency')}
            </Text>
            <View className="mt-2 flex-row flex-wrap" style={{ gap: 8 }}>
              {URGENCIES.map((value) => {
                const active = value === urgency;
                return (
                  <Pressable
                    key={value}
                    onPress={() => setUrgency(value)}
                    accessibilityRole="button"
                    className={`rounded-xl border px-3 py-2 ${
                      active ? 'border-gold-500 bg-gold-50' : 'border-cream-300 bg-white'
                    }`}
                  >
                    <Text
                      className={`text-xs font-semibold ${
                        active ? 'text-gold-700' : 'text-navy-secondary'
                      }`}
                    >
                      {t(`bloodFinder.urgencies.${value}`)}
                    </Text>
                  </Pressable>
                );
              })}
            </View>

            {/* Contact phone */}
            <Text className="mt-6 text-base font-bold text-navy-text">
              {t('bloodFinder.request.contactPhone')}
            </Text>
            <View className="mt-2 h-14 flex-row items-center rounded-2xl border border-cream-300 bg-white px-4">
              <Phone size={16} color={colors.gold[600]} />
              <TextInput
                value={contactPhone}
                onChangeText={setContactPhone}
                placeholder={t('bloodFinder.request.contactPhonePlaceholder')}
                placeholderTextColor={colors.navy.muted}
                keyboardType="phone-pad"
                className="ml-3 flex-1 text-base text-navy-text"
              />
            </View>

            {/* Note */}
            <Text className="mt-6 text-base font-bold text-navy-text">
              {t('bloodFinder.request.note')}
            </Text>
            <View className="mt-2 rounded-2xl border border-cream-300 bg-white px-4 py-3">
              <TextInput
                value={note}
                onChangeText={setNote}
                placeholder={t('bloodFinder.request.notePlaceholder')}
                placeholderTextColor={colors.navy.muted}
                multiline
                maxLength={500}
                className="min-h-[72px] text-sm text-navy-text"
                style={{ textAlignVertical: 'top' }}
              />
            </View>

            {/* Clinical + payment notes */}
            <View className="mt-5 flex-row rounded-2xl border border-cream-300 bg-white p-4">
              <Info size={16} color={colors.semantic.info} />
              <View className="ml-3 flex-1">
                <Text className="text-[11px] leading-4 text-navy-secondary">
                  {t('bloodFinder.request.clinicalNote')}
                </Text>
                <Text className="mt-2 text-[11px] leading-4 text-navy-muted">
                  {t('bloodFinder.request.paymentNote')}
                </Text>
              </View>
            </View>

            {/* Result banners */}
            {confirmed ? (
              <View
                className="mt-4 rounded-2xl p-4"
                style={{ backgroundColor: colors.semantic.successSurface }}
              >
                <Text className="text-sm font-bold" style={{ color: colors.semantic.success }}>
                  {t('bloodFinder.request.successTitle')}
                </Text>
                <Text className="mt-1 text-xs leading-4 text-navy-secondary">
                  {t('bloodFinder.request.successBody', {
                    reference: confirmed.reference,
                    facility: confirmed.facility?.name ?? facility.name,
                  })}
                </Text>
              </View>
            ) : null}

            {errorCode ? (
              <View
                className="mt-4 rounded-2xl p-4"
                style={{ backgroundColor: colors.semantic.dangerSurface }}
              >
                <Text className="text-xs leading-4" style={{ color: colors.semantic.danger }}>
                  {t(`bloodFinder.errors.${errorCode}`, {
                    defaultValue: t('bloodFinder.errors.generic'),
                  })}
                </Text>
              </View>
            ) : null}

            {/* Actions */}
            <View className="mt-5" style={{ gap: 12 }}>
              <Button
                label={t('bloodFinder.request.submit')}
                onPress={onSubmit}
                leftIcon={Send}
                showChevron={false}
                loading={createRequest.isPending}
                disabled={!!confirmed}
              />

              {facility.phone || facility.emergency_contact ? (
                <Button
                  label={t('bloodFinder.call')}
                  variant="outline"
                  onPress={() => {
                    const number = facility.emergency_contact ?? facility.phone;
                    if (!number) return;
                    Linking.openURL(`tel:${number}`).catch(() => undefined);
                  }}
                />
              ) : null}

              {facility.latitude != null && facility.longitude != null ? (
                <Button
                  label={t('bloodFinder.getDirections')}
                  variant="outline"
                  onPress={() => {
                    Linking.openURL(
                      `https://www.google.com/maps/search/?api=1&query=${facility.latitude},${facility.longitude}`,
                    ).catch(() => undefined);
                  }}
                />
              ) : null}
            </View>
          </>
        )}

        <View className="h-10" />
      </ScrollView>
    </Screen>
  );
}
