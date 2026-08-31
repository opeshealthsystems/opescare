import { useEffect, useMemo, useState } from 'react';
import { ActivityIndicator, Pressable, ScrollView, Text, TextInput, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  Building2,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  CircleAlert,
  CircleDot,
  Clock,
  Crosshair,
  Droplet,
  Droplets,
  FlaskConical,
  Hexagon,
  Hospital,
  Info,
  MapPin,
  Search,
  ShieldCheck,
  X,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../components/ui/Screen';
import { colors } from '../theme/tokens';
import {
  useBloodOptions,
  useBloodRequests,
  useBloodSearch,
  useCancelBloodRequest,
  type BloodBankResult,
  type BloodComponentValue,
  type BloodGroupValue,
  type BloodRequest,
} from '../lib/api/queries';

/**
 * Blood Finder — pick a blood group + component, see which facilities near the
 * chosen city report units as available, then request (reserve) some.
 *
 * Deliberately the same shape as the Medicine Finder (app/pharmacy.tsx): search
 * controls, filter chips, a nearby-results list, and a drill-in that performs
 * the action. No reference screen exists for this feature, so it borrows that
 * proven layout rather than inventing a second grammar for the same job.
 *
 * Location is a city picker, not a device GPS fix: the app ships no native
 * location dependency yet, and the search endpoint takes a plain lat/lng, so a
 * chosen city is a real (not stubbed) search origin. Blood is also often
 * searched *for someone else*, in a city the patient is not standing in.
 */

interface City {
  key: string;
  name: string;
  lat: number;
  lng: number;
}

const CITIES: City[] = [
  { key: 'douala', name: 'Douala, Cameroon', lat: 4.0511, lng: 9.7679 },
  { key: 'yaounde', name: 'Yaounde, Cameroon', lat: 3.848, lng: 11.5021 },
  { key: 'bafoussam', name: 'Bafoussam, Cameroon', lat: 5.4737, lng: 10.4179 },
  { key: 'bamenda', name: 'Bamenda, Cameroon', lat: 5.9631, lng: 10.1591 },
  { key: 'buea', name: 'Buea, Cameroon', lat: 4.1527, lng: 9.292 },
  { key: 'garoua', name: 'Garoua, Cameroon', lat: 9.3017, lng: 13.3921 },
];

/** Blood travels further than a pharmacy trip — the radii start wider. */
const RADIUS_OPTIONS = [10, 25, 50, 100];

/** Maps the `icon` key App\Enums\BloodComponentType returns onto a Lucide icon. */
const COMPONENT_ICONS: Record<string, LucideIcon> = {
  droplet: Droplet,
  'circle-dot': CircleDot,
  hexagon: Hexagon,
  'flask-conical': FlaskConical,
};

/** Blood banks and hospitals read differently on a list; give them their own mark. */
function iconForFacility(type: string): LucideIcon {
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

export default function BloodFinderScreen() {
  const { t } = useTranslation();
  const router = useRouter();

  const [bloodGroup, setBloodGroup] = useState<BloodGroupValue | null>(null);
  const [componentType, setComponentType] = useState<BloodComponentValue>('whole_blood');
  const [city, setCity] = useState<City>(CITIES[0]);
  const [radiusKm, setRadiusKm] = useState(25);
  const [term, setTerm] = useState('');
  const [debouncedTerm, setDebouncedTerm] = useState('');
  const [cityPickerOpen, setCityPickerOpen] = useState(false);
  const [radiusPickerOpen, setRadiusPickerOpen] = useState(false);
  const [componentPickerOpen, setComponentPickerOpen] = useState(false);

  useEffect(() => {
    const handle = setTimeout(() => setDebouncedTerm(term.trim().toLowerCase()), 300);
    return () => clearTimeout(handle);
  }, [term]);

  const options = useBloodOptions();
  const search = useBloodSearch({
    bloodGroup,
    componentType,
    lat: city.lat,
    lng: city.lng,
    radiusKm,
  });
  const requests = useBloodRequests('open');
  const cancelRequest = useCancelBloodRequest();

  const groupOptions = options.data?.blood_groups ?? [];
  const componentOptions = options.data?.component_types ?? [];

  const selectedGroup = useMemo(
    () => groupOptions.find((g) => g.value === bloodGroup) ?? null,
    [groupOptions, bloodGroup],
  );

  const selectedComponentLabel =
    componentOptions.find((c) => c.value === componentType)?.label ??
    t(`bloodFinder.components.${componentType}`);

  // The endpoint has no facility-name search (a patient-facing name LIKE query
  // is exactly the enumeration pattern the platform forbids on patient data,
  // and facility rows come back small enough to filter locally), so the search
  // box narrows the returned list rather than issuing another query.
  const results = useMemo(() => {
    const rows = search.data ?? [];
    if (!debouncedTerm) return rows;
    return rows.filter(
      (row) =>
        row.name.toLowerCase().includes(debouncedTerm) ||
        (row.city ?? '').toLowerCase().includes(debouncedTerm),
    );
  }, [search.data, debouncedTerm]);

  const openRequests = requests.data ?? [];

  const closePickers = () => {
    setCityPickerOpen(false);
    setRadiusPickerOpen(false);
    setComponentPickerOpen(false);
  };

  return (
    <Screen className="px-0">
      <ScrollView
        className="flex-1 px-6"
        showsVerticalScrollIndicator={false}
        keyboardShouldPersistTaps="handled"
      >
        {/* Header */}
        <View className="mt-2 flex-row items-center justify-between">
          <Pressable
            onPress={() => (router.canGoBack() ? router.back() : router.replace('/(tabs)/home'))}
            hitSlop={8}
            accessibilityRole="button"
            accessibilityLabel={t('bloodFinder.back')}
            className="h-10 w-10 items-center justify-center rounded-xl border border-cream-300 bg-white"
          >
            <ChevronLeft size={20} color={colors.navy.text} />
          </Pressable>

          <View className="flex-row items-center">
            <Text className="text-lg font-extrabold text-navy-text">Opes</Text>
            <Text className="text-lg font-extrabold text-gold-500">Care</Text>
          </View>

          <Pressable
            onPress={() => {
              closePickers();
              setCityPickerOpen(true);
            }}
            className="h-10 flex-row items-center rounded-xl border border-cream-300 bg-white px-3"
            accessibilityRole="button"
            accessibilityLabel={t('bloodFinder.changeLocation')}
          >
            <MapPin size={15} color={colors.gold[600]} />
            <Text className="ml-1.5 text-xs font-semibold text-navy-text">
              {t('bloodFinder.myLocation')}
            </Text>
          </Pressable>
        </View>

        {/* Title */}
        <Text className="mt-6 text-3xl font-extrabold text-navy-text">
          {t('bloodFinder.title')}
        </Text>
        <Text className="mt-1 text-sm text-navy-secondary">{t('bloodFinder.subtitle')}</Text>

        {/* Filter box */}
        <View className="mt-5 h-14 flex-row items-center rounded-2xl border border-cream-300 bg-white px-4">
          <Search size={18} color={colors.gold[600]} />
          <TextInput
            value={term}
            onChangeText={setTerm}
            placeholder={t('bloodFinder.filterPlaceholder')}
            placeholderTextColor={colors.navy.muted}
            className="ml-3 flex-1 text-base text-navy-text"
            autoCorrect={false}
            returnKeyType="search"
          />
          {term.length > 0 ? (
            <Pressable onPress={() => setTerm('')} hitSlop={8} accessibilityLabel={t('bloodFinder.clear')}>
              <View className="h-6 w-6 items-center justify-center rounded-full bg-cream-200">
                <X size={13} color={colors.navy.secondary} />
              </View>
            </Pressable>
          ) : null}
        </View>

        {/* Filter chips */}
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          className="mt-3"
          contentContainerStyle={{ gap: 8, paddingRight: 24 }}
        >
          <FilterChip
            icon={MapPin}
            label={city.name}
            caret
            active={cityPickerOpen}
            onPress={() => {
              const next = !cityPickerOpen;
              closePickers();
              setCityPickerOpen(next);
            }}
          />
          <FilterChip
            icon={Crosshair}
            label={t('bloodFinder.radius', { km: radiusKm })}
            caret
            active={radiusPickerOpen}
            onPress={() => {
              const next = !radiusPickerOpen;
              closePickers();
              setRadiusPickerOpen(next);
            }}
          />
          <FilterChip
            icon={COMPONENT_ICONS[
              componentOptions.find((c) => c.value === componentType)?.icon ?? 'droplet'
            ] ?? Droplet}
            label={selectedComponentLabel}
            caret
            active={componentPickerOpen}
            onPress={() => {
              const next = !componentPickerOpen;
              closePickers();
              setComponentPickerOpen(next);
            }}
          />
        </ScrollView>

        {cityPickerOpen ? (
          <OptionPanel>
            {CITIES.map((option) => (
              <OptionRow
                key={option.key}
                label={option.name}
                selected={option.key === city.key}
                onPress={() => {
                  setCity(option);
                  setCityPickerOpen(false);
                }}
              />
            ))}
          </OptionPanel>
        ) : null}

        {radiusPickerOpen ? (
          <OptionPanel>
            {RADIUS_OPTIONS.map((option) => (
              <OptionRow
                key={option}
                label={t('bloodFinder.radius', { km: option })}
                selected={option === radiusKm}
                onPress={() => {
                  setRadiusKm(option);
                  setRadiusPickerOpen(false);
                }}
              />
            ))}
          </OptionPanel>
        ) : null}

        {componentPickerOpen ? (
          <OptionPanel>
            {(componentOptions.length > 0
              ? componentOptions.map((c) => c.value)
              : (['whole_blood', 'red_cells', 'platelets', 'plasma'] as BloodComponentValue[])
            ).map((value) => (
              <OptionRow
                key={value}
                label={t(`bloodFinder.components.${value}`)}
                selected={value === componentType}
                onPress={() => {
                  setComponentType(value);
                  setComponentPickerOpen(false);
                }}
              />
            ))}
          </OptionPanel>
        ) : null}

        {/* Blood group chips */}
        <Text className="mt-6 text-base font-bold text-navy-text">
          {t('bloodFinder.bloodGroup')}
        </Text>
        {options.isPending ? (
          <View className="mt-4 items-center">
            <ActivityIndicator color={colors.gold[500]} />
          </View>
        ) : (
          <View className="mt-2 flex-row flex-wrap" style={{ gap: 8 }}>
            {groupOptions.map((group) => {
              const selected = group.value === bloodGroup;
              return (
                <Pressable
                  key={group.value}
                  onPress={() => setBloodGroup(selected ? null : group.value)}
                  accessibilityRole="button"
                  className={`items-center justify-center rounded-2xl border px-3 py-2.5 ${
                    selected ? 'border-gold-500 bg-gold-50' : 'border-cream-300 bg-white'
                  }`}
                  style={{ width: 76 }}
                >
                  <Text
                    className={`text-base font-extrabold ${
                      selected ? 'text-gold-700' : 'text-navy-text'
                    }`}
                  >
                    {group.label}
                  </Text>
                  <Text
                    className="mt-0.5 text-[10px] font-semibold"
                    style={{
                      color:
                        group.facility_count > 0 ? colors.semantic.success : colors.navy.muted,
                    }}
                  >
                    {group.facility_count}
                  </Text>
                </Pressable>
              );
            })}
          </View>
        )}

        {/* Compatibility hint for the chosen group */}
        {selectedGroup ? (
          <View className="mt-4 flex-row rounded-2xl border border-cream-300 bg-white p-4">
            <Info size={16} color={colors.semantic.info} />
            <View className="ml-3 flex-1">
              <Text className="text-xs font-bold text-navy-text">
                {t('bloodFinder.compatibleTitle', { group: selectedGroup.label })}
              </Text>
              <Text className="mt-1 text-[11px] leading-4 text-navy-secondary">
                {t('bloodFinder.compatibleBody', {
                  group: selectedGroup.label,
                  groups: selectedGroup.can_receive_from.join(', '),
                })}
              </Text>
            </View>
          </View>
        ) : null}

        {/* Results */}
        {!bloodGroup ? (
          <View className="mt-6 items-center rounded-2xl border border-cream-300 bg-white px-6 py-8">
            <Droplets size={22} color={colors.gold[600]} />
            <Text className="mt-2 text-center text-sm font-bold text-navy-text">
              {t('bloodFinder.chooseGroupTitle')}
            </Text>
            <Text className="mt-1 text-center text-xs leading-4 text-navy-secondary">
              {t('bloodFinder.chooseGroupBody')}
            </Text>
          </View>
        ) : (
          <>
            <View className="mt-6 flex-row items-center justify-between">
              <Text className="flex-1 pr-2 text-base font-bold text-navy-text">
                {t('bloodFinder.nearbyBanks', { count: results.length })}
              </Text>
              <View className="rounded-xl border border-cream-300 bg-white px-3 py-1.5">
                <Text className="text-[11px] font-semibold text-navy-secondary">
                  {t('bloodFinder.sortByDistance')}
                </Text>
              </View>
            </View>

            {search.isPending ? (
              <View className="mt-6 items-center">
                <ActivityIndicator color={colors.gold[500]} />
              </View>
            ) : search.isError ? (
              <ErrorPanel
                message={t('bloodFinder.loadFailed')}
                actionLabel={t('bloodFinder.retry')}
                onRetry={() => search.refetch()}
              />
            ) : results.length === 0 ? (
              <View className="mt-4 items-center rounded-2xl border border-cream-300 bg-white px-6 py-8">
                <CircleAlert size={22} color={colors.navy.muted} />
                <Text className="mt-2 text-center text-sm text-navy-secondary">
                  {t('bloodFinder.noResults', { km: radiusKm })}
                </Text>
                <Text className="mt-1 text-center text-xs text-navy-muted">
                  {t('bloodFinder.noResultsHint')}
                </Text>
              </View>
            ) : (
              <View className="mt-3" style={{ gap: 12 }}>
                {results.map((facility) => (
                  <BloodBankCard
                    key={facility.id}
                    facility={facility}
                    onPress={() =>
                      router.push({
                        pathname: '/blood-finder/request',
                        params: {
                          facilityId: facility.id,
                          bloodGroup: bloodGroup as string,
                          componentType,
                          lat: String(city.lat),
                          lng: String(city.lng),
                          radiusKm: String(radiusKm),
                        },
                      })
                    }
                  />
                ))}
              </View>
            )}
          </>
        )}

        {/* Open requests */}
        {openRequests.length > 0 ? (
          <>
            <Text className="mt-8 text-base font-bold text-navy-text">
              {t('bloodFinder.myRequests')}
            </Text>
            <View className="mt-3" style={{ gap: 10 }}>
              {openRequests.map((request) => (
                <OpenRequestCard
                  key={request.id}
                  request={request}
                  cancelling={cancelRequest.isPending}
                  onCancel={() => cancelRequest.mutate({ id: request.id })}
                />
              ))}
            </View>
          </>
        ) : null}

        {/* Safety notice */}
        <View className="mt-6 flex-row rounded-2xl border border-gold-100 bg-gold-50 p-4">
          <View className="mr-3 h-10 w-10 items-center justify-center rounded-full bg-white">
            <ShieldCheck size={18} color={colors.gold[600]} />
          </View>
          <View className="flex-1">
            <Text className="text-sm font-bold text-navy-text">
              {t('bloodFinder.safetyTitle')}
            </Text>
            <Text className="mt-1 text-xs leading-4 text-navy-secondary">
              {t('bloodFinder.safetyBody')}
            </Text>
          </View>
        </View>

        <View className="h-10" />
      </ScrollView>
    </Screen>
  );
}

/* -- Pieces ---------------------------------------------------------------- */

function BloodBankCard({
  facility,
  onPress,
}: {
  facility: BloodBankResult;
  onPress: () => void;
}) {
  const { t } = useTranslation();
  const FacilityIcon = iconForFacility(facility.facility_type);
  const availability = facility.availability;

  return (
    <Pressable
      onPress={onPress}
      className="flex-row rounded-2xl border border-cream-300 bg-white p-3"
      accessibilityRole="button"
    >
      <View className="h-20 w-20 items-center justify-center rounded-xl bg-gold-50">
        <FacilityIcon size={24} color={colors.gold[600]} />
      </View>

      <View className="ml-3 flex-1">
        <View className="flex-row items-start justify-between">
          <Text className="flex-1 pr-2 text-sm font-bold text-navy-text" numberOfLines={1}>
            {facility.name}
          </Text>
          {availability ? (
            <View
              className="rounded-lg px-2 py-0.5"
              style={{ backgroundColor: colors.semantic.successSurface }}
            >
              <Text
                className="text-[10px] font-bold"
                style={{ color: colors.semantic.success }}
              >
                {availability.blood_group}
              </Text>
            </View>
          ) : null}
        </View>

        <Text className="mt-1 text-[11px] text-navy-secondary" numberOfLines={1}>
          {facility.distance_km === null
            ? t('bloodFinder.distanceUnknown')
            : t('bloodFinder.distanceKm', { km: facility.distance_km })}
          {facility.city ? `  •  ${facility.city}` : ''}
        </Text>

        <Text className="mt-1 text-[11px] font-semibold text-navy-text">
          {availability?.units_range
            ? t('bloodFinder.unitsRange', { range: availability.units_range })
            : t('bloodFinder.unitsUnknown')}
        </Text>

        <View className="mt-2 flex-row items-center justify-between">
          <View className="flex-row items-center">
            <Clock size={11} color={freshnessTone(availability?.freshness)} />
            <Text
              className="ml-1 text-[11px] font-semibold"
              style={{ color: freshnessTone(availability?.freshness) }}
            >
              {t(`bloodFinder.freshness.${availability?.freshness ?? 'stale'}`, {
                defaultValue: t('bloodFinder.freshness.stale'),
              })}
            </Text>
          </View>

          <View className="flex-row items-center rounded-lg border border-gold-500 px-2.5 py-1">
            <Text className="text-[11px] font-bold text-gold-600">
              {t('bloodFinder.requestUnits')}
            </Text>
            <ChevronRight size={12} color={colors.gold[600]} />
          </View>
        </View>
      </View>
    </Pressable>
  );
}

function OpenRequestCard({
  request,
  cancelling,
  onCancel,
}: {
  request: BloodRequest;
  cancelling: boolean;
  onCancel: () => void;
}) {
  const { t } = useTranslation();

  return (
    <View className="rounded-2xl border border-cream-300 bg-white p-4">
      <View className="flex-row items-start justify-between">
        <View className="flex-1 pr-2">
          <Text className="text-sm font-bold text-navy-text">
            {`${request.blood_group}  •  ${t(`bloodFinder.components.${request.component_type}`)}`}
          </Text>
          <Text className="mt-0.5 text-[11px] text-navy-secondary" numberOfLines={1}>
            {request.facility?.name ?? ''}
          </Text>
          <Text className="mt-1 text-[11px] text-navy-muted">
            {t('bloodFinder.reference', { reference: request.reference })}
          </Text>
        </View>

        <View className="rounded-lg bg-gold-50 px-2 py-1">
          <Text className="text-[10px] font-bold text-gold-700">
            {t(`bloodFinder.statuses.${request.status}`, { defaultValue: request.status_label })}
          </Text>
        </View>
      </View>

      {request.is_cancellable ? (
        <Pressable
          onPress={onCancel}
          disabled={cancelling}
          accessibilityRole="button"
          className="mt-3 h-10 items-center justify-center rounded-xl border border-cream-300"
          style={{ opacity: cancelling ? 0.5 : 1 }}
        >
          <Text className="text-xs font-bold" style={{ color: colors.semantic.danger }}>
            {t('bloodFinder.request.cancel')}
          </Text>
        </Pressable>
      ) : null}
    </View>
  );
}

function FilterChip({
  icon: Icon,
  label,
  onPress,
  active,
  caret,
}: {
  icon: LucideIcon;
  label: string;
  onPress: () => void;
  active?: boolean;
  caret?: boolean;
}) {
  return (
    <Pressable
      onPress={onPress}
      className={`h-10 flex-row items-center rounded-xl border px-3 ${
        active ? 'border-gold-500 bg-gold-50' : 'border-cream-300 bg-white'
      }`}
    >
      <Icon size={14} color={active ? colors.gold[600] : colors.navy.secondary} />
      <Text
        className={`ml-1.5 text-xs font-semibold ${active ? 'text-gold-700' : 'text-navy-text'}`}
        numberOfLines={1}
      >
        {label}
      </Text>
      {caret ? <ChevronDown size={13} color={colors.navy.muted} style={{ marginLeft: 4 }} /> : null}
    </Pressable>
  );
}

function OptionPanel({ children }: { children: React.ReactNode }) {
  return (
    <View className="mt-2 overflow-hidden rounded-2xl border border-cream-300 bg-white">
      {children}
    </View>
  );
}

function OptionRow({
  label,
  selected,
  onPress,
}: {
  label: string;
  selected: boolean;
  onPress: () => void;
}) {
  return (
    <Pressable
      onPress={onPress}
      className="flex-row items-center justify-between border-b border-cream-200 px-4 py-3"
    >
      <Text className={`text-sm ${selected ? 'font-bold text-gold-700' : 'text-navy-text'}`}>
        {label}
      </Text>
      {selected ? <View className="h-2 w-2 rounded-full bg-gold-500" /> : null}
    </Pressable>
  );
}

function ErrorPanel({
  message,
  actionLabel,
  onRetry,
}: {
  message: string;
  actionLabel: string;
  onRetry: () => void;
}) {
  return (
    <View className="mt-6 items-center rounded-2xl border border-cream-300 bg-white px-6 py-8">
      <CircleAlert size={22} color={colors.semantic.danger} />
      <Text className="mt-2 text-center text-sm text-navy-secondary">{message}</Text>
      <Pressable onPress={onRetry} className="mt-3 rounded-xl border border-gold-500 px-4 py-2">
        <Text className="text-xs font-bold text-gold-600">{actionLabel}</Text>
      </Pressable>
    </View>
  );
}
