import { useEffect, useMemo, useState } from 'react';
import { ActivityIndicator, Pressable, ScrollView, Text, TextInput, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  Baby,
  Bug,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  CircleAlert,
  CircleDot,
  Crosshair,
  Droplet,
  FlaskConical,
  HeartPulse,
  LayoutGrid,
  MapPin,
  Package,
  Pill,
  Search,
  ShieldCheck,
  ShieldPlus,
  Sparkles,
  Store,
  Wind,
  X,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../components/ui/Screen';
import { colors } from '../theme/tokens';
import {
  useMedicineCategories,
  useMedicineSearch,
  useNearbyPharmacies,
  type Medicine,
  type MedicineCategoryValue,
  type NearbyPharmacy,
} from '../lib/api/queries';

/**
 * Medicine Finder - search the catalog, then see which pharmacies near the
 * chosen city stock the selected medicine.
 *
 * The design reference for this screen used a green/white palette; per the
 * design spec (section 2.2) that style is superseded by the gold/cream brand,
 * so the layout and interaction pattern are kept while every colour comes from
 * theme/tokens.
 *
 * Location is a city picker rather than a device GPS fix: the app ships no
 * native location dependency yet, and the search endpoint takes a plain
 * lat/lng, so a chosen city is a real (not stubbed) search origin.
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

const RADIUS_OPTIONS = [2, 5, 10, 25];

/** Maps the `category_icon` key the API returns onto a Lucide icon. */
const CATEGORY_ICONS: Record<string, LucideIcon> = {
  pill: Pill,
  'shield-plus': ShieldPlus,
  droplet: Droplet,
  'heart-pulse': HeartPulse,
  'flask-conical': FlaskConical,
  wind: Wind,
  sparkles: Sparkles,
  'circle-dot': CircleDot,
  bug: Bug,
  baby: Baby,
  package: Package,
};

/** XAF is quoted in whole francs, grouped with spaces - "12 500 FCFA". */
function formatPrice(value: number | null | undefined): string | null {
  if (value === null || value === undefined) return null;
  const grouped = String(Math.round(value)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
  return `${grouped} FCFA`;
}

export default function PharmacyScreen() {
  const { t } = useTranslation();
  const router = useRouter();

  const [term, setTerm] = useState('');
  const [debouncedTerm, setDebouncedTerm] = useState('');
  const [category, setCategory] = useState<MedicineCategoryValue | null>(null);
  const [city, setCity] = useState<City>(CITIES[0]);
  const [radiusKm, setRadiusKm] = useState(5);
  const [onlyStocking, setOnlyStocking] = useState(false);
  const [cityPickerOpen, setCityPickerOpen] = useState(false);
  const [radiusPickerOpen, setRadiusPickerOpen] = useState(false);
  const [selectedId, setSelectedId] = useState<string | null>(null);

  useEffect(() => {
    const handle = setTimeout(() => setDebouncedTerm(term.trim()), 300);
    return () => clearTimeout(handle);
  }, [term]);

  const categories = useMedicineCategories();
  const medicines = useMedicineSearch({ q: debouncedTerm, category });

  const results = useMemo(() => medicines.data ?? [], [medicines.data]);
  const selected = useMemo(
    () => results.find((m) => m.id === selectedId) ?? results[0] ?? null,
    [results, selectedId],
  );

  // Keep the highlighted medicine inside the current result set.
  useEffect(() => {
    if (selectedId && !results.some((m) => m.id === selectedId)) {
      setSelectedId(null);
    }
  }, [results, selectedId]);

  const pharmacies = useNearbyPharmacies({
    lat: city.lat,
    lng: city.lng,
    radiusKm,
    medicineId: selected?.id ?? null,
    onlyStocking,
    enabled: !!selected,
  });

  const pharmacyRows = pharmacies.data ?? [];

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
            accessibilityLabel={t('pharmacy.back')}
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
              setCityPickerOpen((open) => !open);
              setRadiusPickerOpen(false);
            }}
            className="h-10 flex-row items-center rounded-xl border border-cream-300 bg-white px-3"
            accessibilityRole="button"
            accessibilityLabel={t('pharmacy.changeLocation')}
          >
            <MapPin size={15} color={colors.gold[600]} />
            <Text className="ml-1.5 text-xs font-semibold text-navy-text">
              {t('pharmacy.myLocation')}
            </Text>
          </Pressable>
        </View>

        {/* Title */}
        <Text className="mt-6 text-3xl font-extrabold text-navy-text">{t('pharmacy.title')}</Text>
        <Text className="mt-1 text-sm text-navy-secondary">{t('pharmacy.subtitle')}</Text>

        {/* Search */}
        <View className="mt-5 h-14 flex-row items-center rounded-2xl border border-cream-300 bg-white px-4">
          <Search size={18} color={colors.gold[600]} />
          <TextInput
            value={term}
            onChangeText={setTerm}
            placeholder={t('pharmacy.searchPlaceholder')}
            placeholderTextColor={colors.navy.muted}
            className="ml-3 flex-1 text-base text-navy-text"
            autoCorrect={false}
            returnKeyType="search"
          />
          {term.length > 0 ? (
            <Pressable
              onPress={() => setTerm('')}
              hitSlop={8}
              accessibilityLabel={t('pharmacy.close')}
            >
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
              setCityPickerOpen((open) => !open);
              setRadiusPickerOpen(false);
            }}
          />
          <FilterChip
            icon={Crosshair}
            label={t('pharmacy.radius', { km: radiusKm })}
            caret
            active={radiusPickerOpen}
            onPress={() => {
              setRadiusPickerOpen((open) => !open);
              setCityPickerOpen(false);
            }}
          />
          <FilterChip
            icon={Store}
            label={onlyStocking ? t('pharmacy.stockingOnly') : t('pharmacy.allPharmacies')}
            active={onlyStocking}
            onPress={() => setOnlyStocking((only) => !only)}
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
                label={t('pharmacy.radius', { km: option })}
                selected={option === radiusKm}
                onPress={() => {
                  setRadiusKm(option);
                  setRadiusPickerOpen(false);
                }}
              />
            ))}
          </OptionPanel>
        ) : null}

        {/* Category chips */}
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          className="mt-4"
          contentContainerStyle={{ gap: 8, paddingRight: 24 }}
        >
          <CategoryChip
            icon={LayoutGrid}
            label={t('pharmacy.categoryAll')}
            selected={category === null}
            onPress={() => setCategory(null)}
          />
          {(categories.data?.categories ?? [])
            .filter((c) => c.medicine_count > 0)
            .map((c) => (
              <CategoryChip
                key={c.value}
                icon={CATEGORY_ICONS[c.icon] ?? Package}
                label={t(`pharmacy.categories.${c.value}`)}
                selected={category === c.value}
                onPress={() => setCategory(category === c.value ? null : c.value)}
              />
            ))}
        </ScrollView>

        {/* Results */}
        {medicines.isPending ? (
          <View className="mt-10 items-center">
            <ActivityIndicator color={colors.gold[500]} />
          </View>
        ) : medicines.isError ? (
          <ErrorPanel
            message={t('pharmacy.loadFailed')}
            actionLabel={t('pharmacy.retry')}
            onRetry={() => medicines.refetch()}
          />
        ) : results.length === 0 ? (
          <EmptyPanel message={t('pharmacy.noMedicines')} />
        ) : (
          <>
            {selected ? <MedicineHero medicine={selected} /> : null}

            {results.length > 1 ? (
              <ScrollView
                horizontal
                showsHorizontalScrollIndicator={false}
                className="mt-3"
                contentContainerStyle={{ gap: 8, paddingRight: 24 }}
              >
                {results.map((medicine) => {
                  const isSelected = selected?.id === medicine.id;
                  return (
                    <Pressable
                      key={medicine.id}
                      onPress={() => setSelectedId(medicine.id)}
                      className={`rounded-xl border px-3 py-2 ${
                        isSelected ? 'border-gold-500 bg-gold-50' : 'border-cream-300 bg-white'
                      }`}
                      style={{ maxWidth: 220 }}
                    >
                      <Text
                        className={`text-xs font-semibold ${
                          isSelected ? 'text-gold-700' : 'text-navy-secondary'
                        }`}
                        numberOfLines={1}
                      >
                        {medicine.name}
                      </Text>
                    </Pressable>
                  );
                })}
              </ScrollView>
            ) : null}

            {/* Nearby pharmacies */}
            <View className="mt-6 flex-row items-center justify-between">
              <Text className="text-base font-bold text-navy-text">
                {t('pharmacy.nearbyPharmacies', { count: pharmacyRows.length })}
              </Text>
              <View className="rounded-xl border border-cream-300 bg-white px-3 py-1.5">
                <Text className="text-[11px] font-semibold text-navy-secondary">
                  {t('pharmacy.sortByDistance')}
                </Text>
              </View>
            </View>

            {pharmacies.isPending ? (
              <View className="mt-6 items-center">
                <ActivityIndicator color={colors.gold[500]} />
              </View>
            ) : pharmacies.isError ? (
              <ErrorPanel
                message={t('pharmacy.loadFailed')}
                actionLabel={t('pharmacy.retry')}
                onRetry={() => pharmacies.refetch()}
              />
            ) : pharmacyRows.length === 0 ? (
              <EmptyPanel message={t('pharmacy.noPharmacies', { km: radiusKm })} />
            ) : (
              <View className="mt-3" style={{ gap: 12 }}>
                {pharmacyRows.map((pharmacy) => (
                  <PharmacyCard
                    key={pharmacy.id}
                    pharmacy={pharmacy}
                    onPress={() => {
                      if (!selected) return;
                      router.push({
                        pathname: '/pharmacy/[medicineId]',
                        params: {
                          medicineId: selected.id,
                          pharmacyId: pharmacy.id,
                          lat: String(city.lat),
                          lng: String(city.lng),
                          radiusKm: String(radiusKm),
                        },
                      });
                    }}
                  />
                ))}
              </View>
            )}
          </>
        )}

        {/* Safety notice */}
        <View className="mt-6 flex-row rounded-2xl border border-gold-100 bg-gold-50 p-4">
          <View className="mr-3 h-10 w-10 items-center justify-center rounded-full bg-white">
            <ShieldCheck size={18} color={colors.gold[600]} />
          </View>
          <View className="flex-1">
            <Text className="text-sm font-bold text-navy-text">{t('pharmacy.safetyTitle')}</Text>
            <Text className="mt-1 text-xs leading-4 text-navy-secondary">
              {t('pharmacy.safetyBody')}
            </Text>
          </View>
        </View>

        <View className="h-10" />
      </ScrollView>
    </Screen>
  );
}

/* -- Pieces ---------------------------------------------------------------- */

function MedicineHero({ medicine }: { medicine: Medicine }) {
  const { t } = useTranslation();
  const CategoryIcon = CATEGORY_ICONS[medicine.category_icon] ?? Pill;
  const priceMin = formatPrice(medicine.availability.price_min);
  const priceMax = formatPrice(medicine.availability.price_max);
  const isAvailable = medicine.availability.is_available;

  return (
    <View className="mt-5 rounded-2xl border border-gold-100 bg-cream-200 p-4">
      <View className="flex-row">
        <View className="h-20 w-20 items-center justify-center rounded-xl bg-white">
          <CategoryIcon size={28} color={colors.gold[600]} />
        </View>

        <View className="ml-4 flex-1">
          <Text className="text-base font-extrabold text-navy-text">{medicine.name}</Text>
          <Text className="mt-1 text-xs text-navy-secondary">
            {t('pharmacy.generic', { name: medicine.generic_name })}
          </Text>

          <View className="mt-2 flex-row flex-wrap" style={{ gap: 6 }}>
            {medicine.indications.slice(0, 3).map((tag) => (
              <View key={tag} className="rounded-lg bg-white px-2 py-1">
                <Text className="text-[11px] font-medium text-navy-secondary">{tag}</Text>
              </View>
            ))}
          </View>
        </View>
      </View>

      <View className="mt-4 flex-row items-center justify-between">
        <View className="flex-row items-center">
          <View
            className="mr-2 h-4 w-4 items-center justify-center rounded-full"
            style={{
              backgroundColor: isAvailable
                ? colors.semantic.successSurface
                : colors.semantic.dangerSurface,
            }}
          >
            <View
              className="h-2 w-2 rounded-full"
              style={{
                backgroundColor: isAvailable ? colors.semantic.success : colors.semantic.danger,
              }}
            />
          </View>
          <Text
            className="text-xs font-semibold"
            style={{ color: isAvailable ? colors.semantic.success : colors.semantic.danger }}
          >
            {isAvailable ? t('pharmacy.available') : t('pharmacy.unavailable')}
          </Text>
        </View>

        <Text className="text-[11px] font-medium text-navy-secondary">
          {medicine.prescription_required
            ? t('pharmacy.prescriptionRequired')
            : t('pharmacy.prescriptionNotRequired')}
        </Text>
      </View>

      <View className="mt-4 flex-row items-end justify-between border-t border-cream-300 pt-3">
        <View className="flex-1 pr-2">
          <Text className="text-[11px] text-navy-muted">{t('pharmacy.priceRange')}</Text>
          <Text className="mt-0.5 text-lg font-extrabold text-navy-text">
            {priceMin && priceMax
              ? `${priceMin.replace(' FCFA', '')} - ${priceMax}`
              : t('pharmacy.priceUnavailable')}
          </Text>
        </View>
        {medicine.default_pack_size ? (
          <Text className="text-[11px] text-navy-secondary">
            {t('pharmacy.packSize', { size: medicine.default_pack_size })}
          </Text>
        ) : null}
      </View>
    </View>
  );
}

function stockTone(status: string | undefined): string {
  switch (status) {
    case 'in_stock':
      return colors.semantic.success;
    case 'low_stock':
      return colors.semantic.warning;
    case 'out_of_stock':
      return colors.semantic.danger;
    default:
      return colors.navy.muted;
  }
}

function PharmacyCard({ pharmacy, onPress }: { pharmacy: NearbyPharmacy; onPress: () => void }) {
  const { t } = useTranslation();
  const price = formatPrice(pharmacy.stock?.unit_price);

  const openLabel = pharmacy.is_24_hours
    ? t('pharmacy.open24')
    : pharmacy.is_open === null
      ? t('pharmacy.hoursUnknown')
      : pharmacy.is_open
        ? t('pharmacy.open')
        : t('pharmacy.closed');

  const hoursLine = pharmacy.is_24_hours
    ? null
    : pharmacy.is_open && pharmacy.closes_at
      ? t('pharmacy.closesAt', { time: pharmacy.closes_at })
      : !pharmacy.is_open && pharmacy.opens_at
        ? t('pharmacy.opensAt', { time: pharmacy.opens_at })
        : null;

  return (
    <Pressable
      onPress={onPress}
      className="flex-row rounded-2xl border border-cream-300 bg-white p-3"
      accessibilityRole="button"
    >
      <View className="h-20 w-20 items-center justify-center rounded-xl bg-gold-50">
        <Store size={24} color={colors.gold[600]} />
      </View>

      <View className="ml-3 flex-1">
        <View className="flex-row items-start justify-between">
          <Text className="flex-1 pr-2 text-sm font-bold text-navy-text" numberOfLines={1}>
            {pharmacy.name}
          </Text>
          <View
            className="rounded-lg px-2 py-0.5"
            style={{
              backgroundColor:
                pharmacy.is_open === false
                  ? colors.semantic.dangerSurface
                  : colors.semantic.successSurface,
            }}
          >
            <Text
              className="text-[10px] font-bold"
              style={{
                color:
                  pharmacy.is_open === false ? colors.semantic.danger : colors.semantic.success,
              }}
            >
              {openLabel}
            </Text>
          </View>
        </View>

        <Text className="mt-1 text-[11px] text-navy-secondary">
          {t('pharmacy.distanceKm', { km: pharmacy.distance_km })}
          {hoursLine ? `  •  ${hoursLine}` : ''}
        </Text>

        <View className="mt-1 flex-row items-center">
          <Text
            className="text-[11px] font-semibold"
            style={{ color: stockTone(pharmacy.stock?.status) }}
          >
            {pharmacy.stock ? t(`pharmacy.stock.${pharmacy.stock.status}`) : t('pharmacy.stock.unknown')}
          </Text>
          {pharmacy.stock?.packs_available ? (
            <Text className="ml-2 text-[11px] text-navy-secondary">
              {`•  ${t('pharmacy.packsAvailable', { count: pharmacy.stock.packs_available })}`}
            </Text>
          ) : null}
        </View>

        <View className="mt-2 flex-row items-center justify-between">
          {price ? (
            <View className="rounded-lg bg-gold-50 px-2 py-1">
              <Text className="text-[11px] font-bold text-gold-700">{price}</Text>
            </View>
          ) : (
            <View />
          )}
          <View className="flex-row items-center rounded-lg border border-gold-500 px-2.5 py-1">
            <Text className="text-[11px] font-bold text-gold-600">{t('pharmacy.viewDetails')}</Text>
            <ChevronRight size={12} color={colors.gold[600]} />
          </View>
        </View>
      </View>
    </Pressable>
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

function CategoryChip({
  icon: Icon,
  label,
  selected,
  onPress,
}: {
  icon: LucideIcon;
  label: string;
  selected: boolean;
  onPress: () => void;
}) {
  return (
    <Pressable
      onPress={onPress}
      className={`items-center justify-center rounded-2xl border px-3 py-3 ${
        selected ? 'border-gold-500 bg-gold-50' : 'border-cream-300 bg-white'
      }`}
      style={{ width: 84 }}
    >
      <Icon size={20} color={selected ? colors.gold[600] : colors.navy.secondary} />
      <Text
        className={`mt-1.5 text-center text-[10px] font-semibold ${
          selected ? 'text-gold-700' : 'text-navy-secondary'
        }`}
        numberOfLines={2}
      >
        {label}
      </Text>
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

function EmptyPanel({ message }: { message: string }) {
  return (
    <View className="mt-6 items-center rounded-2xl border border-cream-300 bg-white px-6 py-8">
      <Package size={22} color={colors.navy.muted} />
      <Text className="mt-2 text-center text-sm text-navy-secondary">{message}</Text>
    </View>
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
