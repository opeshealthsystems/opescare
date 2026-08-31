import { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Linking,
  Platform,
  Pressable,
  RefreshControl,
  ScrollView,
  Text,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import type { TFunction } from 'i18next';
import {
  ArrowLeft,
  Building2,
  Camera,
  FlaskConical,
  Hospital,
  LocateFixed,
  MapPin,
  MapPinned,
  Navigation,
  Phone,
  Pill,
  ScanLine,
  Search,
  Stethoscope,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../components/ui/Screen';
import { TextField } from '../components/ui/TextField';
import { useFacilities, type CareFacilitySummary } from '../lib/api/queries';
import { colors } from '../theme/tokens';

/**
 * Care Map / Facility Directory — browses the same GET /mobile/facilities
 * directory used by the appointments booking flow, with a search box, a
 * facility-type filter row, and per-card distance + Get-Directions/Call
 * actions. A full native map view is a fast-follow (see the design spec) —
 * this is the list-view iteration.
 */

type Coords = { lat: number; lng: number };

const TYPE_FILTERS = [
  { value: '', key: 'all' },
  { value: 'hospital', key: 'hospital' },
  { value: 'clinic', key: 'clinic' },
  { value: 'health_center', key: 'healthCenter' },
  { value: 'pharmacy', key: 'pharmacy' },
  { value: 'laboratory', key: 'laboratory' },
  { value: 'dental', key: 'dental' },
  { value: 'diagnostic_center', key: 'diagnosticCenter' },
  { value: 'imaging_center', key: 'imagingCenter' },
] as const;

const TYPE_ICONS: Record<string, LucideIcon> = {
  hospital: Hospital,
  clinic: Stethoscope,
  health_center: Building2,
  pharmacy: Pill,
  laboratory: FlaskConical,
  dental: Stethoscope,
  diagnostic_center: ScanLine,
  imaging_center: Camera,
};

function iconForType(type: string): LucideIcon {
  return TYPE_ICONS[type] ?? MapPinned;
}

/** Great-circle distance between two coordinates, in kilometers (haversine). */
function distanceKm(a: Coords, b: Coords): number {
  const R = 6371;
  const dLat = ((b.lat - a.lat) * Math.PI) / 180;
  const dLng = ((b.lng - a.lng) * Math.PI) / 180;
  const sinLat = Math.sin(dLat / 2);
  const sinLng = Math.sin(dLng / 2);
  const h =
    sinLat * sinLat +
    Math.cos((a.lat * Math.PI) / 180) * Math.cos((b.lat * Math.PI) / 180) * sinLng * sinLng;
  return R * 2 * Math.atan2(Math.sqrt(h), Math.sqrt(1 - h));
}

function formatDistance(km: number): string {
  return km < 1 ? `${Math.max(1, Math.round(km * 1000))} m` : `${km.toFixed(1)} km`;
}

/**
 * Best-effort device location for the "distance from you" hint on each card.
 * Uses the standard browser Geolocation API, reachable today on the web
 * build (react-native-web). Native iOS/Android needs `expo-location` added
 * as a project dependency — not yet installed anywhere in this app — so this
 * resolves to `null` there and the UI simply omits the distance line, exactly
 * as it does when the user declines the permission prompt. Swapping in
 * `expo-location`'s `getCurrentPositionAsync` here is the only change needed
 * once that dependency lands.
 */
function getDeviceCoords(): Promise<Coords | null> {
  return new Promise((resolve) => {
    try {
      const nav: any = typeof navigator !== 'undefined' ? navigator : null;
      if (Platform.OS === 'web' && nav?.geolocation?.getCurrentPosition) {
        nav.geolocation.getCurrentPosition(
          (pos: any) => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
          () => resolve(null),
          { enableHighAccuracy: false, timeout: 8000 },
        );
      } else {
        resolve(null);
      }
    } catch {
      resolve(null);
    }
  });
}

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

function FacilityCard({
  facility,
  distanceLabel,
  t,
}: {
  facility: CareFacilitySummary;
  distanceLabel: string | null;
  t: TFunction;
}) {
  const Icon = iconForType(facility.facility_type);

  const handleDirections = () => {
    const url = directionsUrl(facility);
    if (url) Linking.openURL(url).catch(() => {});
  };

  const handleCall = () => {
    if (!facility.phone_primary) return;
    Linking.openURL(`tel:${facility.phone_primary.replace(/[^+\d]/g, '')}`).catch(() => {});
  };

  return (
    <View className="mb-3 rounded-2xl bg-white p-4">
      <View className="flex-row items-start">
        <View className="mr-3 h-11 w-11 items-center justify-center rounded-full bg-gold-50">
          <Icon size={20} color={colors.gold[600]} />
        </View>
        <View className="flex-1">
          <View className="flex-row items-start justify-between">
            <Text className="mr-2 flex-1 text-base font-bold text-navy-text" numberOfLines={1}>
              {facility.facility_name}
            </Text>
            {distanceLabel ? (
              <View className="flex-row items-center">
                <Navigation size={11} color={colors.gold[500]} />
                <Text className="ml-1 text-xs font-semibold text-gold-600">{distanceLabel}</Text>
              </View>
            ) : null}
          </View>
          <View className="mt-1 flex-row items-start">
            <MapPin size={12} color={colors.navy.muted} style={{ marginTop: 2 }} />
            <Text className="ml-1 flex-1 text-xs text-navy-secondary">
              {[facility.address, facility.city].filter(Boolean).join(', ') || '—'}
            </Text>
          </View>
          <View className="mt-2 self-start rounded-full bg-cream-200 px-2.5 py-1">
            <Text className="text-[10px] font-semibold uppercase text-navy-secondary">
              {t(`careMap.types.${facility.facility_type}`, { defaultValue: facility.facility_type })}
            </Text>
          </View>
        </View>
      </View>

      <View className="mt-3 flex-row" style={{ gap: 8 }}>
        <Pressable
          onPress={handleDirections}
          className="flex-1 flex-row items-center justify-center rounded-xl border py-2.5"
          style={{ borderColor: colors.gold[300] }}
        >
          <Navigation size={14} color={colors.gold[600]} />
          <Text className="ml-2 text-xs font-semibold text-gold-600">{t('careMap.directions')}</Text>
        </Pressable>
        {facility.phone_primary ? (
          <Pressable
            onPress={handleCall}
            className="flex-1 flex-row items-center justify-center rounded-xl py-2.5"
            style={{ backgroundColor: colors.gold[500] }}
          >
            <Phone size={14} color="white" />
            <Text className="ml-2 text-xs font-semibold text-white">{t('careMap.call')}</Text>
          </Pressable>
        ) : null}
      </View>
    </View>
  );
}

export default function CareMapScreen() {
  const { t } = useTranslation();
  const router = useRouter();

  const [searchInput, setSearchInput] = useState('');
  const [appliedSearch, setAppliedSearch] = useState('');
  const [typeFilter, setTypeFilter] = useState('');
  const [page, setPage] = useState(1);
  const [items, setItems] = useState<CareFacilitySummary[]>([]);
  const [coords, setCoords] = useState<Coords | null>(null);
  const [locationChecked, setLocationChecked] = useState(false);

  const facilitiesQuery = useFacilities({
    q: appliedSearch || undefined,
    type: typeFilter || undefined,
    page,
  });

  // Reset pagination whenever the search or type filter changes.
  useEffect(() => {
    setPage(1);
    setItems([]);
  }, [appliedSearch, typeFilter]);

  // Accumulate pages into one flat list (page 1 replaces, later pages append).
  useEffect(() => {
    if (!facilitiesQuery.data) return;
    const data = facilitiesQuery.data.data;
    setItems((prev) => (page === 1 ? data : [...prev, ...data]));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [facilitiesQuery.data, page]);

  useEffect(() => {
    let cancelled = false;
    getDeviceCoords().then((c) => {
      if (!cancelled) {
        setCoords(c);
        setLocationChecked(true);
      }
    });
    return () => {
      cancelled = true;
    };
  }, []);

  const pagination = facilitiesQuery.data?.pagination;
  const canLoadMore = !!pagination && pagination.current_page < pagination.last_page;
  const isInitialLoading = facilitiesQuery.isLoading && page === 1;
  const isRefreshing = facilitiesQuery.isFetching && page === 1 && items.length > 0;

  const onRefresh = () => {
    setPage(1);
    setItems([]);
    facilitiesQuery.refetch();
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
        <View className="ml-4 flex-1">
          <Text className="text-lg font-extrabold text-navy-text" numberOfLines={1}>
            {t('careMap.title')}
          </Text>
          <Text className="text-xs text-navy-secondary" numberOfLines={1}>
            {t('careMap.subtitle')}
          </Text>
        </View>
      </View>

      <View className="px-6 pt-4">
        <TextField
          placeholder={t('careMap.searchPlaceholder')}
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
          {TYPE_FILTERS.map((f) => (
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
                {t(`careMap.filters.${f.key}`)}
              </Text>
            </Pressable>
          ))}
        </ScrollView>
      </View>

      {locationChecked && !coords ? (
        <View
          className="mx-6 mb-3 flex-row items-center rounded-xl px-3 py-2"
          style={{ backgroundColor: colors.gold[50] }}
        >
          <LocateFixed size={14} color={colors.gold[600]} />
          <Text className="ml-2 flex-1 text-[11px] text-navy-secondary">{t('careMap.locationHint')}</Text>
        </View>
      ) : null}

      {isInitialLoading ? (
        <View className="flex-1 items-center justify-center">
          <ActivityIndicator color={colors.gold[500]} />
        </View>
      ) : facilitiesQuery.isError && items.length === 0 ? (
        <View className="flex-1 items-center justify-center px-10">
          <Text className="text-center text-sm text-navy-secondary">{t('careMap.loadError')}</Text>
          <Pressable
            onPress={() => facilitiesQuery.refetch()}
            className="mt-4 rounded-xl bg-gold-500 px-5 py-2.5"
          >
            <Text className="text-sm font-semibold text-white">{t('careMap.retry')}</Text>
          </Pressable>
        </View>
      ) : (
        <FlatList
          data={items}
          keyExtractor={(item) => item.id}
          contentContainerStyle={{ paddingHorizontal: 24, paddingBottom: 32, flexGrow: 1 }}
          refreshControl={
            <RefreshControl refreshing={isRefreshing} onRefresh={onRefresh} tintColor={colors.gold[500]} />
          }
          renderItem={({ item }) => (
            <FacilityCard
              facility={item}
              distanceLabel={
                coords && item.latitude != null && item.longitude != null
                  ? formatDistance(distanceKm(coords, { lat: item.latitude, lng: item.longitude }))
                  : null
              }
              t={t}
            />
          )}
          ListEmptyComponent={
            <View className="flex-1 items-center justify-center pt-16">
              <View className="mb-4 h-16 w-16 items-center justify-center rounded-full bg-gold-50">
                <MapPinned size={26} color={colors.gold[500]} />
              </View>
              <Text className="text-base font-semibold text-navy-text">{t('careMap.empty')}</Text>
              <Text className="mt-1 text-center text-sm text-navy-secondary">{t('careMap.emptyBody')}</Text>
            </View>
          }
          ListFooterComponent={
            canLoadMore ? (
              <Pressable
                onPress={() => setPage((p) => p + 1)}
                disabled={facilitiesQuery.isFetching}
                className="mt-2 items-center rounded-xl border py-3"
                style={{ borderColor: colors.gold[300], opacity: facilitiesQuery.isFetching ? 0.6 : 1 }}
              >
                {facilitiesQuery.isFetching && page > 1 ? (
                  <ActivityIndicator size="small" color={colors.gold[500]} />
                ) : (
                  <Text className="text-xs font-semibold text-gold-600">{t('careMap.loadMore')}</Text>
                )}
              </Pressable>
            ) : null
          }
        />
      )}
    </Screen>
  );
}
