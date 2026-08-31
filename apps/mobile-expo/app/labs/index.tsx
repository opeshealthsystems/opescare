import { useState } from 'react';
import { ActivityIndicator, FlatList, Pressable, RefreshControl, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import { AlertTriangle, ArrowLeft, ChevronRight, FlaskConical, RotateCcw } from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { colors } from '../../theme/tokens';
import { useLabOrders, type LabOrderSummary } from '../../lib/api/queries';

const STATUS_FILTERS = ['all', 'pending', 'collected', 'processing', 'resulted', 'cancelled'] as const;
type StatusFilter = (typeof STATUS_FILTERS)[number];

function statusLabelKey(status: string): string {
  switch (status) {
    case 'pending':
      return 'labs.statusPending';
    case 'collected':
      return 'labs.statusCollected';
    case 'processing':
      return 'labs.statusProcessing';
    case 'resulted':
      return 'labs.statusResulted';
    case 'cancelled':
      return 'labs.statusCancelled';
    default:
      return 'labs.statusPending';
  }
}

function statusStyles(statusColor: LabOrderSummary['status_color']): { text: string; surface: string } {
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

/** Read-only list of the patient's lab orders — test requests issued by a
 * care team, filterable by status. Full results (value/unit/reference
 * range/flag) live in the detail screen; this list only summarizes. */
export default function LabsListScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const [filter, setFilter] = useState<StatusFilter>('all');
  const { data, isLoading, isError, isFetching, refetch } = useLabOrders(
    filter === 'all' ? undefined : filter,
  );
  const orders = data?.data ?? [];

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
          <Text className="text-xl font-extrabold text-navy-text">{t('labs.title')}</Text>
          <Text className="text-xs text-navy-secondary">{t('labs.subtitle')}</Text>
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
                {item === 'all' ? t('labs.filterAll') : t(statusLabelKey(item))}
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
          <Text className="text-center text-sm text-navy-secondary">{t('labs.loadError')}</Text>
          <Pressable
            onPress={() => refetch()}
            className="mt-4 flex-row items-center rounded-full border border-gold-300 px-4 py-2"
          >
            <RotateCcw size={14} color={colors.gold[600]} />
            <Text className="ml-2 text-sm font-semibold text-gold-600">{t('labs.retry')}</Text>
          </Pressable>
        </View>
      ) : orders.length === 0 ? (
        <View className="flex-1 items-center justify-center px-10">
          <View className="h-16 w-16 items-center justify-center rounded-full bg-gold-100">
            <FlaskConical size={26} color={colors.gold[500]} />
          </View>
          <Text className="mt-4 text-center text-base font-bold text-navy-text">{t('labs.emptyTitle')}</Text>
          <Text className="mt-1 text-center text-sm text-navy-secondary">{t('labs.emptyBody')}</Text>
        </View>
      ) : (
        <FlatList
          data={orders}
          keyExtractor={(o) => o.id}
          style={{ flex: 1 }}
          contentContainerStyle={{ paddingHorizontal: 24, paddingBottom: 32, gap: 12 }}
          refreshControl={
            <RefreshControl refreshing={isFetching && !isLoading} onRefresh={refetch} tintColor={colors.gold[500]} />
          }
          renderItem={({ item }) => {
            const sc = statusStyles(item.status_color);
            const orderedDate = formatDate(item.ordered_at);
            const resultedDate = formatDate(item.resulted_at);
            return (
              <Pressable onPress={() => router.push(`/labs/${item.id}`)} className="rounded-2xl bg-white p-4">
                <View className="flex-row items-start justify-between">
                  <View className="flex-1 flex-row items-start">
                    <View className="h-10 w-10 items-center justify-center rounded-full" style={{ backgroundColor: colors.semantic.infoSurface }}>
                      <FlaskConical size={18} color={colors.semantic.info} />
                    </View>
                    <View className="ml-3 flex-1">
                      <Text className="text-base font-bold text-navy-text" numberOfLines={1}>
                        {item.test_name}
                      </Text>
                      <Text className="mt-0.5 text-xs text-navy-muted" numberOfLines={1}>
                        {item.facility_name ?? t('labs.title')}
                      </Text>
                      {resultedDate ? (
                        <Text className="mt-1 text-xs text-navy-secondary">
                          {t('labs.resultedOn', { date: resultedDate })}
                        </Text>
                      ) : orderedDate ? (
                        <Text className="mt-1 text-xs text-navy-secondary">
                          {t('labs.orderedOn', { date: orderedDate })}
                        </Text>
                      ) : null}
                    </View>
                  </View>
                  <ChevronRight size={18} color={colors.navy.muted} />
                </View>
                <View className="mt-3 flex-row items-center" style={{ gap: 8 }}>
                  <View className="self-start rounded-full px-3 py-1" style={{ backgroundColor: sc.surface }}>
                    <Text className={`text-xs font-semibold ${sc.text}`}>{t(statusLabelKey(item.status))}</Text>
                  </View>
                  {item.status === 'resulted' ? (
                    <Text className="text-xs text-navy-muted">
                      {t('labs.resultCount', { count: item.result_count })}
                    </Text>
                  ) : null}
                  {item.has_abnormal ? (
                    <View className="ml-auto flex-row items-center rounded-full px-2.5 py-1" style={{ backgroundColor: colors.semantic.dangerSurface }}>
                      <AlertTriangle size={12} color={colors.semantic.danger} />
                      <Text className="ml-1 text-xs font-semibold text-danger">{t('labs.abnormalBadge')}</Text>
                    </View>
                  ) : null}
                </View>
              </Pressable>
            );
          }}
        />
      )}
    </Screen>
  );
}
