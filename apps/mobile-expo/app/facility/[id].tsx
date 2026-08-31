import { useMemo } from 'react';
import { ActivityIndicator, Linking, Platform, Pressable, ScrollView, Text, View } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  Building2,
  CalendarPlus,
  ChevronRight,
  Clock,
  Globe,
  Mail,
  MapPin,
  Navigation,
  Phone,
  Stethoscope,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { colors } from '../../theme/tokens';
import {
  useFacilityDetail,
  useFacilityProviders,
  type CareFacilityDetail,
  type CareFacilityHour,
  type FacilityProviderSummary,
} from '../../lib/api/queries';

/**
 * Facility profile — the drill-in from the care map / booking facility picker.
 *
 * Two things live here that had no home before: the full facility record
 * (GET /mobile/facilities/{id} existed but nothing called it) and the roster
 * of clinicians who actually practise here (GET /mobile/facilities/{id}/providers).
 * A patient could previously pick a building and a time, never a person.
 */

/** Deep-link to a maps app for directions — same shape as care-map / booking. */
function directionsUrl(facility: CareFacilityDetail): string | null {
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

/** Two-letter monogram — staff_profiles carries no photo column, so avatars are initials. */
function providerInitials(name: string): string {
  const parts = name.trim().split(/\s+/).filter(Boolean);
  if (parts.length === 0) return '—';
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

/** ISO weekday order, rendered through the device locale rather than hardcoded. */
function weekdayLabel(dayOfWeek: number, locale: string): string {
  // 2024-01-01 was a Monday; day_of_week is 0=Sunday..6=Saturday (Carbon/PHP `w`).
  const base = new Date(Date.UTC(2024, 0, 7 + dayOfWeek));
  return base.toLocaleDateString(locale === 'fr' ? 'fr-FR' : 'en-US', {
    weekday: 'long',
    timeZone: 'UTC',
  });
}

function trimTime(value: string | null): string {
  if (!value) return '—';
  // Postgres time columns come back as HH:MM:SS.
  return value.length >= 5 ? value.slice(0, 5) : value;
}

function ContactRow({
  icon: Icon,
  label,
  value,
  onPress,
}: {
  icon: LucideIcon;
  label: string;
  value: string;
  onPress?: () => void;
}) {
  const body = (
    <View className="flex-row items-center py-3">
      <Icon size={16} color={colors.gold[600]} />
      <View className="ml-3 flex-1">
        <Text className="text-xs text-navy-muted">{label}</Text>
        <Text className="mt-0.5 text-sm font-semibold text-navy-text">{value}</Text>
      </View>
      {onPress ? <ChevronRight size={16} color={colors.navy.muted} /> : null}
    </View>
  );
  return onPress ? <Pressable onPress={onPress}>{body}</Pressable> : body;
}

function ProviderRow({
  provider,
  onPress,
}: {
  provider: FacilityProviderSummary;
  onPress: () => void;
}) {
  const subtitle = [provider.job_title, provider.department].filter(Boolean).join(' · ');
  return (
    <Pressable onPress={onPress} className="mb-3 flex-row items-center rounded-2xl bg-white p-4">
      <View
        className="h-12 w-12 items-center justify-center rounded-full"
        style={{ backgroundColor: colors.gold[50] }}
      >
        <Text className="text-sm font-extrabold text-gold-600">
          {providerInitials(provider.name)}
        </Text>
      </View>
      <View className="ml-3 flex-1">
        <Text className="text-base font-bold text-navy-text" numberOfLines={1}>
          {provider.name}
        </Text>
        {subtitle ? (
          <Text className="mt-0.5 text-xs text-navy-secondary" numberOfLines={1}>
            {subtitle}
          </Text>
        ) : null}
      </View>
      <ChevronRight size={18} color={colors.navy.muted} />
    </Pressable>
  );
}

export default function FacilityDetailScreen() {
  const { t, i18n } = useTranslation();
  const router = useRouter();
  const params = useLocalSearchParams<{ id?: string }>();
  const facilityId = params.id;

  const facilityQuery = useFacilityDetail(facilityId);
  const providersQuery = useFacilityProviders(facilityId);

  const facility = facilityQuery.data;
  const providers = providersQuery.data?.data ?? [];

  const hours = useMemo(
    () =>
      [...(facility?.hours ?? [])].sort(
        (a: CareFacilityHour, b: CareFacilityHour) => a.day_of_week - b.day_of_week,
      ),
    [facility?.hours],
  );

  const mapsUrl = facility ? directionsUrl(facility) : null;

  const openUrl = (url: string) => {
    Linking.openURL(url).catch(() => {});
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
          {facility?.facility_name ?? t('facility.title')}
        </Text>
      </View>

      {facilityQuery.isLoading ? (
        <View className="flex-1 items-center justify-center">
          <ActivityIndicator color={colors.gold[500]} />
        </View>
      ) : facilityQuery.isError || !facility ? (
        <View className="flex-1 items-center justify-center px-6">
          <Text className="text-center text-sm text-navy-secondary">{t('facility.loadError')}</Text>
        </View>
      ) : (
        <ScrollView className="flex-1 px-6 pt-4" contentContainerStyle={{ paddingBottom: 40 }}>
          {/* ── Identity card ─────────────────────────────────────────── */}
          <View className="rounded-2xl bg-white p-4">
            <View className="flex-row items-start">
              <View className="mr-3 h-12 w-12 items-center justify-center rounded-full bg-gold-50">
                <Building2 size={22} color={colors.gold[600]} />
              </View>
              <View className="flex-1">
                <Text className="text-lg font-extrabold text-navy-text">
                  {facility.facility_name}
                </Text>
                <View className="mt-1 flex-row items-center">
                  <MapPin size={12} color={colors.navy.muted} />
                  <Text className="ml-1 flex-1 text-xs text-navy-secondary">
                    {[facility.address, facility.city, facility.region].filter(Boolean).join(', ') ||
                      '—'}
                  </Text>
                </View>
                <View className="mt-2 flex-row flex-wrap gap-2">
                  <View className="rounded-full bg-cream-200 px-2.5 py-1">
                    <Text className="text-[10px] font-semibold uppercase text-navy-secondary">
                      {facility.facility_type}
                    </Text>
                  </View>
                  {facility.ownership_type ? (
                    <View className="rounded-full bg-cream-200 px-2.5 py-1">
                      <Text className="text-[10px] font-semibold uppercase text-navy-secondary">
                        {facility.ownership_type}
                      </Text>
                    </View>
                  ) : null}
                </View>
              </View>
            </View>

            {mapsUrl ? (
              <Pressable
                onPress={() => openUrl(mapsUrl)}
                className="mt-4 flex-row items-center justify-center rounded-2xl border py-3"
                style={{ borderColor: colors.gold[300] }}
              >
                <Navigation size={16} color={colors.gold[600]} />
                <Text className="ml-2 text-sm font-semibold text-gold-600">
                  {t('facility.getDirections')}
                </Text>
              </Pressable>
            ) : null}
          </View>

          {facility.description ? (
            <>
              <Text className="mb-2 mt-6 text-sm font-bold text-navy-text">
                {t('facility.aboutTitle')}
              </Text>
              <View className="rounded-2xl bg-white p-4">
                <Text className="text-sm leading-5 text-navy-secondary">{facility.description}</Text>
              </View>
            </>
          ) : null}

          {/* ── Doctors & specialists — the point of this screen ───────── */}
          <View className="mb-2 mt-6 flex-row items-end justify-between">
            <View>
              <Text className="text-sm font-bold text-navy-text">{t('facility.doctorsTitle')}</Text>
              <Text className="text-xs text-navy-muted">{t('facility.doctorsSubtitle')}</Text>
            </View>
            <Stethoscope size={18} color={colors.gold[600]} />
          </View>

          {providersQuery.isLoading ? (
            <View className="items-center py-6">
              <ActivityIndicator color={colors.gold[500]} />
            </View>
          ) : providersQuery.isError ? (
            <View className="rounded-2xl bg-white p-4">
              <Text className="text-sm text-navy-secondary">{t('facility.loadDoctorsError')}</Text>
            </View>
          ) : providers.length === 0 ? (
            <View className="rounded-2xl bg-white p-4">
              <Text className="text-sm text-navy-secondary">{t('facility.noDoctors')}</Text>
            </View>
          ) : (
            providers.map((provider) => (
              <ProviderRow
                key={provider.id}
                provider={provider}
                onPress={() => router.push(`/doctor/${provider.id}`)}
              />
            ))
          )}

          {/* ── Services ───────────────────────────────────────────────── */}
          {facility.services.length > 0 ? (
            <>
              <Text className="mb-2 mt-6 text-sm font-bold text-navy-text">
                {t('facility.servicesTitle')}
              </Text>
              <View className="rounded-2xl bg-white p-4">
                {facility.services.map((service, index) => (
                  <View
                    key={`${service.service_name}-${index}`}
                    className="py-2"
                    style={
                      index > 0
                        ? { borderTopWidth: 1, borderTopColor: colors.cream[300] }
                        : undefined
                    }
                  >
                    <Text className="text-sm font-semibold text-navy-text">
                      {service.service_name}
                    </Text>
                    <View className="mt-1 flex-row flex-wrap gap-2">
                      {service.specialty ? (
                        <Text className="text-xs text-navy-secondary">{service.specialty}</Text>
                      ) : null}
                      {service.appointment_required ? (
                        <Text className="text-xs text-navy-muted">
                          {t('facility.appointmentRequired')}
                        </Text>
                      ) : null}
                      {service.walk_in_allowed ? (
                        <Text className="text-xs text-navy-muted">
                          {t('facility.walkInAllowed')}
                        </Text>
                      ) : null}
                    </View>
                  </View>
                ))}
              </View>
            </>
          ) : null}

          {/* ── Opening hours ─────────────────────────────────────────── */}
          {hours.length > 0 ? (
            <>
              <Text className="mb-2 mt-6 text-sm font-bold text-navy-text">
                {t('facility.hoursTitle')}
              </Text>
              <View className="rounded-2xl bg-white p-4">
                {hours.map((hour, index) => (
                  <View
                    key={`${hour.day_of_week}-${index}`}
                    className="flex-row items-center justify-between py-2"
                    style={
                      index > 0
                        ? { borderTopWidth: 1, borderTopColor: colors.cream[300] }
                        : undefined
                    }
                  >
                    <View className="flex-row items-center">
                      <Clock size={14} color={colors.gold[600]} />
                      <Text className="ml-2 text-sm text-navy-text">
                        {weekdayLabel(hour.day_of_week, i18n.language)}
                      </Text>
                    </View>
                    <Text className="text-sm font-semibold text-navy-secondary">
                      {hour.is_24_hours
                        ? t('facility.open24')
                        : hour.is_closed
                          ? t('facility.closed')
                          : `${trimTime(hour.opens_at)} – ${trimTime(hour.closes_at)}`}
                    </Text>
                  </View>
                ))}
              </View>
            </>
          ) : null}

          {/* ── Contact ───────────────────────────────────────────────── */}
          {facility.phone_primary || facility.email || facility.website ? (
            <>
              <Text className="mb-2 mt-6 text-sm font-bold text-navy-text">
                {t('facility.contactTitle')}
              </Text>
              <View className="rounded-2xl bg-white px-4">
                {facility.phone_primary ? (
                  <ContactRow
                    icon={Phone}
                    label={t('facility.phone')}
                    value={facility.phone_primary}
                    onPress={() => openUrl(`tel:${facility.phone_primary}`)}
                  />
                ) : null}
                {facility.email ? (
                  <ContactRow
                    icon={Mail}
                    label={t('facility.email')}
                    value={facility.email}
                    onPress={() => openUrl(`mailto:${facility.email}`)}
                  />
                ) : null}
                {facility.website ? (
                  <ContactRow
                    icon={Globe}
                    label={t('facility.website')}
                    value={facility.website}
                    onPress={() => openUrl(facility.website as string)}
                  />
                ) : null}
              </View>
            </>
          ) : null}

          <View className="mt-6">
            <Button
              label={t('facility.bookHere')}
              leftIcon={CalendarPlus}
              onPress={() =>
                router.push({
                  pathname: '/appointments/book',
                  params: { facilityId: facility.id },
                })
              }
            />
          </View>
        </ScrollView>
      )}
    </Screen>
  );
}
