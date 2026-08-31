import { useMemo, useState } from 'react';
import { ActivityIndicator, FlatList, Linking, Platform, Pressable, ScrollView, Share, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  Building2,
  Calendar,
  CheckCircle2,
  Clock,
  Info,
  MapPin,
  Navigation,
  Search,
  Share2,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { TextField } from '../../components/ui/TextField';
import { useAuthStore } from '../../lib/store/auth';
import {
  useFacilities,
  useFacilitySlots,
  useBookAppointment,
  type AppointmentDetail,
  type AppointmentSlotOption,
  type CareFacilitySummary,
} from '../../lib/api/queries';
import { colors } from '../../theme/tokens';

type Step = 'facility' | 'slot' | 'confirm' | 'success';

const FACILITY_TYPE_FILTERS: { value: string; key: string }[] = [
  { value: '', key: 'filterAll' },
  { value: 'hospital', key: 'filterHospital' },
  { value: 'clinic', key: 'filterClinic' },
  { value: 'pharmacy', key: 'filterPharmacy' },
  { value: 'laboratory', key: 'filterLab' },
];

const APPOINTMENT_TYPES: { value: string; key: string }[] = [
  { value: 'consultation', key: 'typeConsultation' },
  { value: 'follow_up', key: 'typeFollowUp' },
  { value: 'check_up', key: 'typeCheckUp' },
  { value: 'vaccination', key: 'typeVaccination' },
  { value: 'lab_test', key: 'typeLabTest' },
  { value: 'other', key: 'typeOther' },
];

function nextDays(count: number): { iso: string; date: Date }[] {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return Array.from({ length: count }, (_, i) => {
    const date = new Date(today);
    date.setDate(date.getDate() + i);
    return { date, iso: date.toISOString().slice(0, 10) };
  });
}

function formatDay(date: Date, locale: string) {
  const localeTag = locale === 'fr' ? 'fr-FR' : 'en-US';
  return {
    weekday: date.toLocaleDateString(localeTag, { weekday: 'short' }),
    day: date.toLocaleDateString(localeTag, { day: 'numeric' }),
  };
}

function formatTime(iso: string, locale: string) {
  return new Date(iso).toLocaleTimeString(locale === 'fr' ? 'fr-FR' : 'en-US', {
    hour: '2-digit',
    minute: '2-digit',
  });
}

function formatFullDateTime(iso: string, locale: string) {
  const localeTag = locale === 'fr' ? 'fr-FR' : 'en-US';
  const date = new Date(iso);
  const day = date.toLocaleDateString(localeTag, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  const time = date.toLocaleTimeString(localeTag, { hour: '2-digit', minute: '2-digit' });
  return `${day}, ${time}`;
}

/** Deep-link to a maps app for directions, same pattern as care-map.tsx. */
function directionsUrl(facility: CareFacilitySummary): string | null {
  const label = encodeURIComponent(facility.facility_name);
  if (facility.latitude != null && facility.longitude != null) {
    const { latitude: lat, longitude: lng } = facility;
    return (
      Platform.select({
        ios: `maps:0,0?q=${label}@${lat},${lng}`,
        android: `geo:${lat},${lng}?q=${lat},${lng}(${label})`,
        default: `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`,
      }) ?? `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`
    );
  }
  const address = [facility.address, facility.city, facility.region].filter(Boolean).join(', ');
  return address ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(address)}` : null;
}

function StepHeader({ title, onBack }: { title: string; onBack: () => void }) {
  return (
    <View className="flex-row items-center px-6 pt-2">
      <Pressable
        onPress={onBack}
        hitSlop={8}
        className="h-11 w-11 items-center justify-center rounded-full border border-gold-300"
      >
        <ArrowLeft size={18} color={colors.gold[600]} />
      </Pressable>
      <Text className="ml-4 flex-1 text-lg font-extrabold text-navy-text" numberOfLines={1}>
        {title}
      </Text>
    </View>
  );
}

function StepDots({ step }: { step: Step }) {
  const order: Step[] = ['facility', 'slot', 'confirm'];
  const activeIndex = order.indexOf(step);
  if (activeIndex < 0) return null;
  return (
    <View className="mt-4 flex-row justify-center gap-2 px-6">
      {order.map((s, i) => (
        <View
          key={s}
          className="h-1.5 flex-1 rounded-full"
          style={{ backgroundColor: i <= activeIndex ? colors.gold[500] : colors.cream[300] }}
        />
      ))}
    </View>
  );
}

function FacilityCard({ facility, onPress }: { facility: CareFacilitySummary; onPress: () => void }) {
  return (
    <Pressable onPress={onPress} className="mb-3 rounded-2xl bg-white p-4">
      <View className="flex-row items-start">
        <View className="mr-3 h-11 w-11 items-center justify-center rounded-full bg-gold-50">
          <Building2 size={20} color={colors.gold[600]} />
        </View>
        <View className="flex-1">
          <Text className="text-base font-bold text-navy-text" numberOfLines={1}>
            {facility.facility_name}
          </Text>
          <View className="mt-1 flex-row items-center">
            <MapPin size={12} color={colors.navy.muted} />
            <Text className="ml-1 flex-1 text-xs text-navy-secondary" numberOfLines={1}>
              {[facility.address, facility.city].filter(Boolean).join(', ') || '—'}
            </Text>
          </View>
          <View className="mt-2 self-start rounded-full bg-cream-200 px-2.5 py-1">
            <Text className="text-[10px] font-semibold uppercase text-navy-secondary">
              {facility.facility_type}
            </Text>
          </View>
        </View>
      </View>
    </Pressable>
  );
}

export default function BookAppointmentScreen() {
  const { t, i18n } = useTranslation();
  const router = useRouter();
  const patient = useAuthStore((s) => s.patient);

  const [step, setStep] = useState<Step>('facility');
  const [searchInput, setSearchInput] = useState('');
  const [appliedSearch, setAppliedSearch] = useState('');
  const [typeFilter, setTypeFilter] = useState('');
  const [selectedFacility, setSelectedFacility] = useState<CareFacilitySummary | null>(null);

  const days = useMemo(() => nextDays(14), []);
  const [selectedDate, setSelectedDate] = useState(days[0].iso);
  const [selectedSlot, setSelectedSlot] = useState<AppointmentSlotOption | null>(null);

  const [appointmentType, setAppointmentType] = useState(APPOINTMENT_TYPES[0].value);
  const [reason, setReason] = useState('');
  const [bookedAppointment, setBookedAppointment] = useState<AppointmentDetail | null>(null);

  const facilitiesQuery = useFacilities({
    q: appliedSearch || undefined,
    type: typeFilter || undefined,
  });
  const slotsQuery = useFacilitySlots(selectedFacility?.id, selectedDate);
  const bookMutation = useBookAppointment();

  const goBack = () => {
    if (step === 'facility') {
      router.back();
    } else if (step === 'slot') {
      setSelectedSlot(null);
      setStep('facility');
    } else if (step === 'confirm') {
      setStep('slot');
    }
  };

  const handleSelectFacility = (facility: CareFacilitySummary) => {
    setSelectedFacility(facility);
    setSelectedSlot(null);
    setStep('slot');
  };

  const handleConfirmBooking = () => {
    if (!selectedFacility || !selectedSlot || !slotsQuery.data?.facility_id) return;
    bookMutation.mutate(
      {
        facility_id: slotsQuery.data.facility_id,
        appointment_slot_id: selectedSlot.id,
        appointment_type: appointmentType,
        reason: reason.trim() || undefined,
      },
      {
        onSuccess: (data) => {
          setBookedAppointment(data);
          setStep('success');
        },
      },
    );
  };

  const handleGetDirections = () => {
    if (!selectedFacility) return;
    const url = directionsUrl(selectedFacility);
    if (url) Linking.openURL(url).catch(() => {});
  };

  const handleShareAppointment = async () => {
    if (!bookedAppointment) return;
    try {
      await Share.share({
        message: t('appointments.book.shareMessage', {
          type: bookedAppointment.appointment_type,
          facility: bookedAppointment.facility_name ?? selectedFacility?.facility_name ?? '',
          datetime: bookedAppointment.scheduled_at
            ? formatFullDateTime(bookedAppointment.scheduled_at, i18n.language)
            : '',
          id: bookedAppointment.id.slice(0, 8).toUpperCase(),
        }),
      });
    } catch {
      // Share sheet dismissed/cancelled — nothing to surface.
    }
  };

  return (
    <Screen className="px-0">
      {step !== 'success' ? (
        <>
          <StepHeader
            title={
              step === 'facility'
                ? t('appointments.book.stepFacility')
                : step === 'slot'
                  ? t('appointments.book.stepSlot')
                  : t('appointments.book.stepConfirm')
            }
            onBack={goBack}
          />
          <StepDots step={step} />
        </>
      ) : null}

      {step === 'facility' ? (
        <View className="flex-1 px-6 pt-4">
          <TextField
            placeholder={t('appointments.book.searchPlaceholder')}
            icon={Search}
            value={searchInput}
            onChangeText={setSearchInput}
            onSubmitEditing={() => setAppliedSearch(searchInput.trim())}
            returnKeyType="search"
          />
          <ScrollView
            horizontal
            showsHorizontalScrollIndicator={false}
            className="mb-4 -mt-1"
            contentContainerStyle={{ gap: 8 }}
          >
            {FACILITY_TYPE_FILTERS.map((f) => (
              <Pressable
                key={f.value}
                onPress={() => setTypeFilter(f.value)}
                className="rounded-full border px-4 py-2"
                style={{
                  borderColor: typeFilter === f.value ? colors.gold[500] : colors.cream[300],
                  backgroundColor: typeFilter === f.value ? colors.gold[50] : 'white',
                }}
              >
                <Text
                  className="text-xs font-semibold"
                  style={{ color: typeFilter === f.value ? colors.gold[600] : colors.navy.secondary }}
                >
                  {t(`appointments.book.${f.key}`)}
                </Text>
              </Pressable>
            ))}
          </ScrollView>

          {facilitiesQuery.isLoading ? (
            <View className="flex-1 items-center justify-center">
              <ActivityIndicator color={colors.gold[500]} />
            </View>
          ) : facilitiesQuery.isError ? (
            <Text className="mt-8 text-center text-sm text-navy-secondary">
              {t('appointments.book.loadFacilitiesError')}
            </Text>
          ) : (
            <FlatList
              data={facilitiesQuery.data?.data ?? []}
              keyExtractor={(item) => item.id}
              contentContainerStyle={{ paddingBottom: 24 }}
              renderItem={({ item }) => (
                <FacilityCard facility={item} onPress={() => handleSelectFacility(item)} />
              )}
              ListEmptyComponent={
                <Text className="mt-8 text-center text-sm text-navy-secondary">
                  {t('appointments.book.noFacilities')}
                </Text>
              }
            />
          )}
        </View>
      ) : null}

      {step === 'slot' && selectedFacility ? (
        <View className="flex-1 px-6 pt-4">
          <View className="mb-4 rounded-2xl bg-white p-4">
            <Text className="text-base font-bold text-navy-text">{selectedFacility.facility_name}</Text>
            <View className="mt-1 flex-row items-center">
              <MapPin size={12} color={colors.navy.muted} />
              <Text className="ml-1 text-xs text-navy-secondary" numberOfLines={1}>
                {[selectedFacility.address, selectedFacility.city].filter(Boolean).join(', ') || '—'}
              </Text>
            </View>
          </View>

          <Text className="mb-2 text-sm font-semibold text-navy-text">{t('appointments.book.selectDate')}</Text>
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ gap: 8 }}>
            {days.map(({ date, iso }) => {
              const { weekday, day } = formatDay(date, i18n.language);
              const active = selectedDate === iso;
              return (
                <Pressable
                  key={iso}
                  onPress={() => {
                    setSelectedDate(iso);
                    setSelectedSlot(null);
                  }}
                  className="items-center rounded-2xl px-4 py-3"
                  style={{ backgroundColor: active ? colors.gold[500] : 'white', minWidth: 56 }}
                >
                  <Text className="text-[10px] font-semibold" style={{ color: active ? 'white' : colors.navy.muted }}>
                    {weekday}
                  </Text>
                  <Text className="mt-1 text-base font-bold" style={{ color: active ? 'white' : colors.navy.text }}>
                    {day}
                  </Text>
                </Pressable>
              );
            })}
          </ScrollView>

          <Text className="mb-3 mt-5 text-sm font-semibold text-navy-text">
            {t('appointments.book.chooseSlotPrompt')}
          </Text>

          {slotsQuery.isLoading ? (
            <ActivityIndicator color={colors.gold[500]} />
          ) : slotsQuery.isError ? (
            <Text className="text-center text-sm text-navy-secondary">{t('appointments.book.loadSlotsError')}</Text>
          ) : (slotsQuery.data?.data.length ?? 0) === 0 ? (
            <Text className="text-center text-sm text-navy-secondary">{t('appointments.book.noSlots')}</Text>
          ) : (
            <View className="flex-1">
              <ScrollView showsVerticalScrollIndicator={false}>
                <View className="flex-row flex-wrap gap-2 pb-4">
                  {slotsQuery.data!.data.map((slot) => {
                    const active = selectedSlot?.id === slot.id;
                    return (
                      <Pressable
                        key={slot.id}
                        onPress={() => setSelectedSlot(slot)}
                        className="rounded-xl border px-4 py-3"
                        style={{
                          borderColor: active ? colors.gold[500] : colors.cream[300],
                          backgroundColor: active ? colors.gold[500] : 'white',
                        }}
                      >
                        <Text
                          className="text-sm font-semibold"
                          style={{ color: active ? 'white' : colors.navy.text }}
                        >
                          {formatTime(slot.starts_at, i18n.language)}
                        </Text>
                      </Pressable>
                    );
                  })}
                </View>
              </ScrollView>
            </View>
          )}

          <View className="pb-4 pt-2">
            <Button
              label={t('appointments.book.next')}
              onPress={() => setStep('confirm')}
              disabled={!selectedSlot}
            />
          </View>
        </View>
      ) : null}

      {step === 'confirm' && selectedFacility && selectedSlot ? (
        <ScrollView className="flex-1 px-6 pt-4" contentContainerStyle={{ paddingBottom: 32 }}>
          <Text className="mb-2 text-sm font-semibold text-navy-text">{t('appointments.book.typeLabel')}</Text>
          <View className="mb-5 flex-row flex-wrap gap-2">
            {APPOINTMENT_TYPES.map((type) => {
              const active = appointmentType === type.value;
              return (
                <Pressable
                  key={type.value}
                  onPress={() => setAppointmentType(type.value)}
                  className="rounded-full border px-4 py-2"
                  style={{
                    borderColor: active ? colors.gold[500] : colors.cream[300],
                    backgroundColor: active ? colors.gold[50] : 'white',
                  }}
                >
                  <Text
                    className="text-xs font-semibold"
                    style={{ color: active ? colors.gold[600] : colors.navy.secondary }}
                  >
                    {t(`appointments.book.${type.key}`)}
                  </Text>
                </Pressable>
              );
            })}
          </View>

          <TextField
            label={t('appointments.book.reasonLabel')}
            placeholder={t('appointments.book.reasonPlaceholder')}
            value={reason}
            onChangeText={setReason}
          />

          <Text className="mb-2 mt-2 text-sm font-semibold text-navy-text">
            {t('appointments.book.reviewTitle')}
          </Text>
          <View className="rounded-2xl bg-white p-4">
            <View className="flex-row items-start py-2">
              <Building2 size={16} color={colors.gold[600]} />
              <View className="ml-3 flex-1">
                <Text className="text-xs text-navy-muted">{t('appointments.book.reviewFacility')}</Text>
                <Text className="mt-0.5 text-sm font-semibold text-navy-text">
                  {selectedFacility.facility_name}
                </Text>
              </View>
            </View>
            <View
              className="flex-row items-start py-2"
              style={{ borderTopWidth: 1, borderTopColor: colors.cream[300] }}
            >
              <Calendar size={16} color={colors.gold[600]} />
              <View className="ml-3 flex-1">
                <Text className="text-xs text-navy-muted">{t('appointments.book.reviewDateTime')}</Text>
                <Text className="mt-0.5 text-sm font-semibold text-navy-text">
                  {formatFullDateTime(selectedSlot.starts_at, i18n.language)}
                </Text>
              </View>
            </View>
            {patient ? (
              <View
                className="flex-row items-start py-2"
                style={{ borderTopWidth: 1, borderTopColor: colors.cream[300] }}
              >
                <CheckCircle2 size={16} color={colors.gold[600]} />
                <View className="ml-3 flex-1">
                  <Text className="text-xs text-navy-muted">{t('appointments.book.reviewPatient')}</Text>
                  <Text className="mt-0.5 text-sm font-semibold text-navy-text">{patient.display_name}</Text>
                  <Text className="text-xs text-navy-muted">{patient.health_id}</Text>
                </View>
              </View>
            ) : null}
          </View>

          {bookMutation.isError ? (
            <Text className="mt-4 text-center text-sm text-danger">{t('appointments.book.bookError')}</Text>
          ) : null}

          <View className="mt-6">
            <Button
              label={bookMutation.isPending ? t('appointments.book.booking') : t('appointments.book.confirmButton')}
              onPress={handleConfirmBooking}
              loading={bookMutation.isPending}
              showChevron={false}
              leftIcon={CheckCircle2}
            />
          </View>
        </ScrollView>
      ) : null}

      {step === 'success' && bookedAppointment ? (
        <ScrollView className="flex-1 px-6" contentContainerStyle={{ paddingTop: 24, paddingBottom: 40 }}>
          <View className="items-center">
            <View
              className="h-16 w-16 items-center justify-center rounded-full"
              style={{ backgroundColor: colors.semantic.successSurface }}
            >
              <CheckCircle2 size={32} color={colors.semantic.success} />
            </View>
            <Text className="mt-4 text-xl font-extrabold text-navy-text">
              {t('appointments.book.confirmedTitle')}
            </Text>
            <Text className="mt-1 text-center text-sm text-navy-secondary">
              {t('appointments.book.confirmedBody')}
            </Text>
          </View>

          <View
            className="mt-5 flex-row items-center justify-between rounded-2xl px-4 py-3"
            style={{ backgroundColor: colors.semantic.successSurface }}
          >
            <Text className="text-xs font-semibold" style={{ color: colors.semantic.success }}>
              {t('appointments.book.bookingIdLabel')}
            </Text>
            <Text className="text-xs font-bold" style={{ color: colors.semantic.success }}>
              {bookedAppointment.id.slice(0, 8).toUpperCase()}
            </Text>
          </View>

          <View className="mt-4 rounded-2xl bg-white p-4">
            <View className="flex-row items-start py-2">
              <Building2 size={16} color={colors.gold[600]} />
              <View className="ml-3 flex-1">
                <Text className="text-xs text-navy-muted">{t('appointments.book.reviewFacility')}</Text>
                <Text className="mt-0.5 text-sm font-semibold text-navy-text">
                  {bookedAppointment.facility_name ?? selectedFacility?.facility_name}
                </Text>
              </View>
              {selectedFacility && directionsUrl(selectedFacility) ? (
                <Pressable
                  onPress={handleGetDirections}
                  hitSlop={8}
                  className="ml-2 flex-row items-center rounded-full border px-3 py-1.5"
                  style={{ borderColor: colors.gold[300] }}
                >
                  <Navigation size={12} color={colors.gold[600]} />
                  <Text className="ml-1 text-xs font-semibold text-gold-600">
                    {t('appointments.book.getDirections')}
                  </Text>
                </Pressable>
              ) : null}
            </View>
            <View
              className="flex-row items-start py-2"
              style={{ borderTopWidth: 1, borderTopColor: colors.cream[300] }}
            >
              <Clock size={16} color={colors.gold[600]} />
              <View className="ml-3 flex-1">
                <Text className="text-xs text-navy-muted">{t('appointments.book.reviewDateTime')}</Text>
                <Text className="mt-0.5 text-sm font-semibold text-navy-text">
                  {bookedAppointment.scheduled_at
                    ? formatFullDateTime(bookedAppointment.scheduled_at, i18n.language)
                    : '—'}
                </Text>
              </View>
            </View>
            <View
              className="flex-row items-start py-2"
              style={{ borderTopWidth: 1, borderTopColor: colors.cream[300] }}
            >
              <Calendar size={16} color={colors.gold[600]} />
              <View className="ml-3 flex-1">
                <Text className="text-xs text-navy-muted">{t('appointments.book.reviewType')}</Text>
                <Text className="mt-0.5 text-sm font-semibold text-navy-text">
                  {bookedAppointment.appointment_type}
                </Text>
              </View>
            </View>
            {patient ? (
              <View
                className="flex-row items-start py-2"
                style={{ borderTopWidth: 1, borderTopColor: colors.cream[300] }}
              >
                <CheckCircle2 size={16} color={colors.gold[600]} />
                <View className="ml-3 flex-1">
                  <Text className="text-xs text-navy-muted">{t('appointments.book.reviewPatient')}</Text>
                  <Text className="mt-0.5 text-sm font-semibold text-navy-text">{patient.display_name}</Text>
                  <Text className="text-xs text-navy-muted">
                    {t('appointments.book.healthId')}: {patient.health_id}
                  </Text>
                </View>
              </View>
            ) : null}
          </View>

          <View
            className="mt-4 flex-row items-start rounded-2xl p-4"
            style={{ backgroundColor: colors.semantic.infoSurface }}
          >
            <Info size={18} color={colors.semantic.info} />
            <View className="ml-3 flex-1">
              <Text className="text-sm font-bold text-navy-text">
                {t('appointments.book.whatToExpectTitle')}
              </Text>
              <Text className="mt-1 text-xs text-navy-secondary">
                {t('appointments.book.whatToExpectArrive')}
              </Text>
              <Text className="mt-1 text-xs text-navy-secondary">
                {t('appointments.book.whatToExpectBring')}
              </Text>
            </View>
          </View>

          <View className="mt-5">
            <Pressable
              onPress={handleShareAppointment}
              className="flex-row items-center justify-center rounded-2xl border py-3"
              style={{ borderColor: colors.cream[300] }}
            >
              <Share2 size={16} color={colors.gold[600]} />
              <Text className="ml-2 text-sm font-semibold text-navy-text">
                {t('appointments.book.shareAppointment')}
              </Text>
            </Pressable>
          </View>

          <View className="mt-6">
            <Button
              label={t('appointments.book.viewAppointments')}
              onPress={() => router.replace('/appointments')}
            />
          </View>
          <Pressable onPress={() => router.replace('/(tabs)/home')} className="mt-4 items-center">
            <Text className="text-sm font-semibold text-gold-500">{t('appointments.book.done')}</Text>
          </Pressable>
        </ScrollView>
      ) : null}
    </Screen>
  );
}
