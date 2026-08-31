import { useCallback, useState } from 'react';
import { ActivityIndicator, FlatList, Pressable, RefreshControl, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import { Calendar, ChevronRight, MapPin, Plus, ArrowLeft } from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { useAppointmentsList, type AppointmentScope } from '../../lib/api/queries';
import type { Appointment } from '../../lib/api/types';
import { colors } from '../../theme/tokens';

function formatDateTime(iso: string | null, locale: string) {
  if (!iso) return '';
  const date = new Date(iso);
  const localeTag = locale === 'fr' ? 'fr-FR' : 'en-US';
  const day = date.toLocaleDateString(localeTag, { weekday: 'short', day: 'numeric', month: 'short' });
  const time = date.toLocaleTimeString(localeTag, { hour: '2-digit', minute: '2-digit' });
  return `${day} · ${time}`;
}

function statusStyle(status: string) {
  switch (status) {
    case 'completed':
      return { bg: colors.semantic.successSurface, fg: colors.semantic.success };
    case 'cancelled':
      return { bg: colors.semantic.dangerSurface, fg: colors.semantic.danger };
    case 'no_show':
      return { bg: colors.semantic.warningSurface, fg: colors.semantic.warning };
    case 'checked_in':
      return { bg: colors.gold[50], fg: colors.gold[600] };
    default:
      return { bg: colors.semantic.infoSurface, fg: colors.semantic.info };
  }
}

function StatusBadge({ status }: { status: string }) {
  const { t } = useTranslation();
  const style = statusStyle(status);
  const label = t(`appointments.status.${status}`, { defaultValue: status });
  return (
    <View className="rounded-full px-3 py-1" style={{ backgroundColor: style.bg }}>
      <Text className="text-xs font-semibold" style={{ color: style.fg }}>
        {label}
      </Text>
    </View>
  );
}

function AppointmentCard({ item, locale, onPress }: { item: Appointment; locale: string; onPress: () => void }) {
  const { t } = useTranslation();
  return (
    <Pressable onPress={onPress} className="mb-3 rounded-2xl bg-white p-4">
      <View className="flex-row items-start justify-between">
        <View className="flex-1 flex-row items-start">
          <View className="mr-3 h-11 w-11 items-center justify-center rounded-full bg-gold-50">
            <Calendar size={20} color={colors.gold[600]} />
          </View>
          <View className="flex-1">
            <Text className="text-base font-bold text-navy-text" numberOfLines={1}>
              {item.appointment_type}
            </Text>
            {item.facility_name ? (
              <View className="mt-1 flex-row items-center">
                <MapPin size={12} color={colors.navy.muted} />
                <Text className="ml-1 flex-1 text-xs text-navy-secondary" numberOfLines={1}>
                  {item.facility_name}
                </Text>
              </View>
            ) : null}
            {item.provider_name ? (
              <Text className="mt-0.5 text-xs text-navy-muted" numberOfLines={1}>
                {t('appointments.with')} {item.provider_name}
              </Text>
            ) : null}
          </View>
        </View>
        <ChevronRight size={18} color={colors.navy.muted} />
      </View>
      <View className="mt-3 flex-row items-center justify-between">
        <Text className="text-sm font-semibold text-navy-text">
          {formatDateTime(item.scheduled_at, locale)}
        </Text>
        <StatusBadge status={item.status} />
      </View>
    </Pressable>
  );
}

export default function AppointmentsListScreen() {
  const { t, i18n } = useTranslation();
  const router = useRouter();
  const [scope, setScope] = useState<AppointmentScope>('upcoming');
  const { data, isLoading, isError, isFetching, refetch } = useAppointmentsList(scope, 50);

  const onRefresh = useCallback(() => {
    refetch();
  }, [refetch]);

  const appointments = data?.data ?? [];

  return (
    <Screen className="px-0">
      <View className="flex-row items-center justify-between px-6 pt-2">
        <Pressable
          onPress={() => router.back()}
          hitSlop={8}
          className="h-11 w-11 items-center justify-center rounded-full border border-gold-300"
        >
          <ArrowLeft size={18} color={colors.gold[600]} />
        </Pressable>
        <Text className="text-lg font-extrabold text-navy-text">{t('appointments.title')}</Text>
        <Pressable
          onPress={() => router.push('/appointments/book')}
          hitSlop={8}
          className="h-11 w-11 items-center justify-center rounded-full bg-gold-500"
        >
          <Plus size={20} color="white" />
        </Pressable>
      </View>

      <View className="mx-6 mt-5 flex-row rounded-2xl bg-white p-1">
        {(['upcoming', 'past'] as AppointmentScope[]).map((s) => (
          <Pressable
            key={s}
            onPress={() => setScope(s)}
            className="flex-1 items-center rounded-xl py-2.5"
            style={{ backgroundColor: scope === s ? colors.gold[500] : 'transparent' }}
          >
            <Text
              className="text-sm font-semibold"
              style={{ color: scope === s ? 'white' : colors.navy.secondary }}
            >
              {t(`appointments.${s}`)}
            </Text>
          </Pressable>
        ))}
      </View>

      {isLoading ? (
        <View className="flex-1 items-center justify-center">
          <ActivityIndicator color={colors.gold[500]} />
        </View>
      ) : isError ? (
        <View className="flex-1 items-center justify-center px-10">
          <Text className="text-center text-sm text-navy-secondary">{t('appointments.loadError')}</Text>
          <Pressable onPress={() => refetch()} className="mt-4 rounded-xl bg-gold-500 px-5 py-2.5">
            <Text className="text-sm font-semibold text-white">{t('appointments.retry')}</Text>
          </Pressable>
        </View>
      ) : (
        <FlatList
          data={appointments}
          keyExtractor={(item) => item.id}
          contentContainerStyle={{ paddingHorizontal: 24, paddingTop: 16, paddingBottom: 32, flexGrow: 1 }}
          refreshControl={
            <RefreshControl refreshing={isFetching && !isLoading} onRefresh={onRefresh} tintColor={colors.gold[500]} />
          }
          renderItem={({ item }) => (
            <AppointmentCard
              item={item}
              locale={i18n.language}
              onPress={() => router.push(`/appointments/${item.id}`)}
            />
          )}
          ListEmptyComponent={
            <View className="flex-1 items-center justify-center pt-16">
              <View className="mb-4 h-16 w-16 items-center justify-center rounded-full bg-gold-50">
                <Calendar size={26} color={colors.gold[500]} />
              </View>
              <Text className="text-base font-semibold text-navy-text">
                {scope === 'upcoming' ? t('appointments.emptyUpcoming') : t('appointments.emptyPast')}
              </Text>
              {scope === 'upcoming' ? (
                <Text className="mt-1 text-center text-sm text-navy-secondary">
                  {t('appointments.emptyUpcomingBody')}
                </Text>
              ) : null}
            </View>
          }
        />
      )}
    </Screen>
  );
}
