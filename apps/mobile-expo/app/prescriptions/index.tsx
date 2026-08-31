import { useState } from 'react';
import { ActivityIndicator, FlatList, Pressable, RefreshControl, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import { ArrowLeft, ChevronRight, Pill, RotateCcw } from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { colors } from '../../theme/tokens';
import { usePrescriptions, type PrescriptionSummary } from '../../lib/api/queries';

const STATUS_FILTERS = ['all', 'active', 'dispensed', 'partially_dispensed', 'expired', 'cancelled'] as const;
type StatusFilter = (typeof STATUS_FILTERS)[number];

function statusLabelKey(status: string): string {
  switch (status) {
    case 'active':
      return 'prescriptions.statusActive';
    case 'dispensed':
      return 'prescriptions.statusDispensed';
    case 'partially_dispensed':
      return 'prescriptions.statusPartiallyDispensed';
    case 'expired':
      return 'prescriptions.statusExpired';
    case 'cancelled':
      return 'prescriptions.statusCancelled';
    default:
      return 'prescriptions.statusActive';
  }
}

function statusStyles(statusColor: PrescriptionSummary['status_color']): { text: string; surface: string } {
  switch (statusColor) {
    case 'success':
      return { text: 'text-success', surface: colors.semantic.successSurface };
    case 'info':
      return { text: 'text-info', surface: colors.semantic.infoSurface };
    case 'warning':
      return { text: 'text-warning', surface: colors.semantic.warningSurface };
    default:
      return { text: 'text-navy-muted', surface: colors.cream[200] };
  }
}

function formatDate(iso: string | null): string | null {
  if (!iso) return null;
  return new Date(iso).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

/** Read-only list of the patient's prescriptions, distinct from pharmacy/medicine
 * search — this is what a care team has prescribed, filterable by status. */
export default function PrescriptionsListScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const [filter, setFilter] = useState<StatusFilter>('all');
  const { data, isLoading, isError, isFetching, refetch } = usePrescriptions(
    filter === 'all' ? undefined : filter,
  );
  const prescriptions = data?.data ?? [];

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
        <View className="ml-3 flex-1">
          <Text className="text-xl font-extrabold text-navy-text">{t('prescriptions.title')}</Text>
          <Text className="text-xs text-navy-secondary">{t('prescriptions.subtitle')}</Text>
        </View>
      </View>

      <FlatList
        data={STATUS_FILTERS}
        horizontal
        showsHorizontalScrollIndicator={false}
        keyExtractor={(s) => s}
        contentContainerStyle={{ paddingHorizontal: 24, paddingVertical: 16, gap: 8 }}
        renderItem={({ item }) => {
          const active = item === filter;
          return (
            <Pressable
              onPress={() => setFilter(item)}
              className="rounded-full px-4 py-2"
              style={{
                backgroundColor: active ? colors.gold[500] : colors.white,
                borderWidth: active ? 0 : 1,
                borderColor: colors.cream[300],
              }}
            >
              <Text
                className={active ? 'text-sm font-semibold text-white' : 'text-sm font-semibold text-navy-secondary'}
              >
                {item === 'all' ? t('prescriptions.filterAll') : t(statusLabelKey(item))}
              </Text>
            </Pressable>
          );
        }}
      />

      {isLoading ? (
        <View className="flex-1 items-center justify-center">
          <ActivityIndicator color={colors.gold[500]} />
        </View>
      ) : isError ? (
        <View className="flex-1 items-center justify-center px-6">
          <Text className="text-center text-sm text-navy-secondary">{t('prescriptions.loadError')}</Text>
          <Pressable
            onPress={() => refetch()}
            className="mt-4 flex-row items-center rounded-full border border-gold-300 px-4 py-2"
          >
            <RotateCcw size={14} color={colors.gold[600]} />
            <Text className="ml-2 text-sm font-semibold text-gold-600">{t('prescriptions.retry')}</Text>
          </Pressable>
        </View>
      ) : prescriptions.length === 0 ? (
        <View className="flex-1 items-center justify-center px-10">
          <View className="h-16 w-16 items-center justify-center rounded-full bg-gold-100">
            <Pill size={26} color={colors.gold[500]} />
          </View>
          <Text className="mt-4 text-center text-base font-bold text-navy-text">
            {t('prescriptions.emptyTitle')}
          </Text>
          <Text className="mt-1 text-center text-sm text-navy-secondary">{t('prescriptions.emptyBody')}</Text>
        </View>
      ) : (
        <FlatList
          data={prescriptions}
          keyExtractor={(p) => p.id}
          style={{ flex: 1 }}
          contentContainerStyle={{ paddingHorizontal: 24, paddingBottom: 32, gap: 12 }}
          refreshControl={
            <RefreshControl refreshing={isFetching && !isLoading} onRefresh={refetch} tintColor={colors.gold[500]} />
          }
          renderItem={({ item }) => {
            const sc = statusStyles(item.status_color);
            const prescribedDate = formatDate(item.prescribed_at);
            return (
              <Pressable onPress={() => router.push(`/prescriptions/${item.id}`)} className="rounded-2xl bg-white p-4">
                <View className="flex-row items-start justify-between">
                  <View className="flex-1 flex-row items-start">
                    <View className="h-10 w-10 items-center justify-center rounded-full bg-gold-100">
                      <Pill size={18} color={colors.gold[600]} />
                    </View>
                    <View className="ml-3 flex-1">
                      <Text className="text-base font-bold text-navy-text" numberOfLines={1}>
                        {item.facility_name ?? t('prescriptions.title')}
                      </Text>
                      {prescribedDate ? (
                        <Text className="mt-0.5 text-xs text-navy-muted">
                          {t('prescriptions.prescribedOn', { date: prescribedDate })}
                        </Text>
                      ) : null}
                      <Text className="mt-1 text-xs text-navy-secondary">
                        {t('prescriptions.medicationCount', { count: item.item_count })}
                      </Text>
                    </View>
                  </View>
                  <ChevronRight size={18} color={colors.navy.muted} />
                </View>
                <View className="mt-3 self-start rounded-full px-3 py-1" style={{ backgroundColor: sc.surface }}>
                  <Text className={`text-xs font-semibold ${sc.text}`}>{t(statusLabelKey(item.status))}</Text>
                </View>
              </Pressable>
            );
          }}
        />
      )}
    </Screen>
  );
}
