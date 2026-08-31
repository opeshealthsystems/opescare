import { ActivityIndicator, Pressable, ScrollView, Text, View } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import type { TFunction } from 'i18next';
import {
  AlertTriangle,
  ArrowLeft,
  Calendar,
  CheckCircle2,
  FlaskConical,
  Gauge,
  MapPin,
  RotateCcw,
  StickyNote,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { colors } from '../../theme/tokens';
import { useLabOrderDetail, type LabOrderSummary, type LabResultParameter } from '../../lib/api/queries';

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

function urgencyLabelKey(urgency: string): string {
  switch (urgency) {
    case 'urgent':
      return 'labs.urgencyUrgent';
    case 'stat':
      return 'labs.urgencyStat';
    default:
      return 'labs.urgencyRoutine';
  }
}

function flagLabelKey(flag: string | null): string {
  switch (flag) {
    case 'H':
      return 'labs.flagHigh';
    case 'HH':
      return 'labs.flagCriticalHigh';
    case 'L':
      return 'labs.flagLow';
    case 'LL':
      return 'labs.flagCriticalLow';
    case 'abnormal':
      return 'labs.flagAbnormal';
    default:
      return 'labs.flagNormal';
  }
}

/** Normal | High/Low (warning) | Critical High/Low or Abnormal (danger). */
function flagStyles(flag: string | null): { text: string; surface: string; dot: string } {
  switch (flag) {
    case 'H':
    case 'L':
      return { text: 'text-warning', surface: colors.semantic.warningSurface, dot: colors.semantic.warning };
    case 'HH':
    case 'LL':
    case 'abnormal':
      return { text: 'text-danger', surface: colors.semantic.dangerSurface, dot: colors.semantic.danger };
    default:
      return { text: 'text-success', surface: colors.semantic.successSurface, dot: colors.semantic.success };
  }
}

function formatDate(iso: string | null): string | null {
  if (!iso) return null;
  return new Date(iso).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

/** Detail view of one lab order: status, ordering facility, key dates,
 * clinical indication/notes, and the full set of resulted parameters —
 * each with its value, unit, reference range, and abnormal flag. */
export default function LabOrderDetailScreen() {
  const params = useLocalSearchParams<{ id: string }>();
  const id = Array.isArray(params.id) ? params.id[0] : params.id;
  const { t } = useTranslation();
  const router = useRouter();
  const { data: order, isLoading, isError, refetch } = useLabOrderDetail(id);

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
        <Text className="ml-3 text-xl font-extrabold text-navy-text">{t('labs.detailTitle')}</Text>
      </View>

      {isLoading ? (
        <View className="flex-1 items-center justify-center">
          <ActivityIndicator color={colors.gold[500]} />
        </View>
      ) : isError || !order ? (
        <View className="flex-1 items-center justify-center px-6">
          <Text className="text-center text-sm text-navy-secondary">
            {isError ? t('labs.detailLoadError') : t('labs.notFound')}
          </Text>
          {isError ? (
            <Pressable
              onPress={() => refetch()}
              className="mt-4 flex-row items-center rounded-full border border-gold-300 px-4 py-2"
            >
              <RotateCcw size={14} color={colors.gold[600]} />
              <Text className="ml-2 text-sm font-semibold text-gold-600">{t('labs.retry')}</Text>
            </Pressable>
          ) : null}
        </View>
      ) : (
        <ScrollView className="flex-1 px-6" contentContainerStyle={{ paddingBottom: 40 }} showsVerticalScrollIndicator={false}>
          <View className="mt-2 rounded-2xl bg-white p-4">
            <View className="flex-row items-start">
              <View className="h-11 w-11 items-center justify-center rounded-full" style={{ backgroundColor: colors.semantic.infoSurface }}>
                <FlaskConical size={20} color={colors.semantic.info} />
              </View>
              <View className="ml-3 flex-1">
                <Text className="text-lg font-extrabold text-navy-text">{order.test_name}</Text>
                {order.facility_name ? (
                  <Text className="mt-0.5 text-xs text-navy-muted">{order.facility_name}</Text>
                ) : null}
              </View>
            </View>
            <View className="mt-3 flex-row flex-wrap" style={{ gap: 8 }}>
              <View
                className="self-start rounded-full px-3 py-1"
                style={{ backgroundColor: statusStyles(order.status_color).surface }}
              >
                <Text className={`text-xs font-semibold ${statusStyles(order.status_color).text}`}>
                  {t(statusLabelKey(order.status))}
                </Text>
              </View>
              {order.has_abnormal ? (
                <View className="flex-row items-center self-start rounded-full px-3 py-1" style={{ backgroundColor: colors.semantic.dangerSurface }}>
                  <AlertTriangle size={12} color={colors.semantic.danger} />
                  <Text className="ml-1 text-xs font-semibold text-danger">{t('labs.abnormalBadge')}</Text>
                </View>
              ) : null}
            </View>
          </View>

          <View className="mt-4 rounded-2xl bg-white p-4">
            <InfoRow icon={MapPin} label={t('labs.facility')} value={order.facility_name ?? '—'} />
            <InfoRow icon={Gauge} label={t('labs.urgency')} value={t(urgencyLabelKey(order.urgency))} />
            <InfoRow icon={Calendar} label={t('labs.orderedAt')} value={formatDate(order.ordered_at) ?? '—'} />
            {order.collected_at ? (
              <InfoRow icon={CheckCircle2} label={t('labs.collectedAt')} value={formatDate(order.collected_at) ?? '—'} />
            ) : null}
            <InfoRow
              icon={CheckCircle2}
              label={t('labs.resultedAt')}
              value={formatDate(order.resulted_at) ?? '—'}
              last
            />
          </View>

          {order.clinical_indication ? (
            <View className="mt-4 rounded-2xl bg-white p-4">
              <View className="flex-row items-center">
                <StickyNote size={16} color={colors.gold[600]} />
                <Text className="ml-2 text-sm font-bold text-navy-text">{t('labs.clinicalIndication')}</Text>
              </View>
              <Text className="mt-2 text-sm text-navy-secondary">{order.clinical_indication}</Text>
            </View>
          ) : null}

          {order.notes ? (
            <View className="mt-4 rounded-2xl bg-white p-4">
              <View className="flex-row items-center">
                <StickyNote size={16} color={colors.gold[600]} />
                <Text className="ml-2 text-sm font-bold text-navy-text">{t('labs.notes')}</Text>
              </View>
              <Text className="mt-2 text-sm text-navy-secondary">{order.notes}</Text>
            </View>
          ) : null}

          <Text className="mb-3 mt-6 text-base font-bold text-navy-text">
            {t('labs.results')} ({order.results.length})
          </Text>
          {order.results.length === 0 ? (
            <View className="items-center rounded-2xl bg-white px-4 py-8">
              <FlaskConical size={24} color={colors.navy.muted} />
              <Text className="mt-3 text-center text-sm text-navy-secondary">{t('labs.noResultsYet')}</Text>
            </View>
          ) : (
            <View style={{ gap: 12 }}>
              {order.results.map((r) => (
                <ResultCard key={r.id} result={r} t={t} />
              ))}
            </View>
          )}
        </ScrollView>
      )}
    </Screen>
  );
}

function InfoRow({
  icon: Icon,
  label,
  value,
  last,
}: {
  icon: LucideIcon;
  label: string;
  value: string;
  last?: boolean;
}) {
  return (
    <View className={`flex-row items-center py-2 ${last ? '' : 'border-b border-cream-200'}`}>
      <Icon size={16} color={colors.navy.muted} />
      <Text className="ml-2 flex-1 text-sm text-navy-secondary">{label}</Text>
      <Text className="text-sm font-semibold text-navy-text">{value}</Text>
    </View>
  );
}

function ResultCard({ result, t }: { result: LabResultParameter; t: TFunction }) {
  const fs = flagStyles(result.flag);
  return (
    <View className="rounded-2xl bg-white p-4">
      <View className="flex-row items-start justify-between">
        <Text className="flex-1 text-base font-bold text-navy-text">{result.parameter_name}</Text>
        <View className="ml-2 flex-row items-center rounded-full px-2.5 py-1" style={{ backgroundColor: fs.surface }}>
          <View className="h-1.5 w-1.5 rounded-full" style={{ backgroundColor: fs.dot }} />
          <Text className={`ml-1.5 text-xs font-semibold ${fs.text}`}>{t(flagLabelKey(result.flag))}</Text>
        </View>
      </View>
      <View className="mt-3 flex-row items-end" style={{ gap: 4 }}>
        <Text className="text-2xl font-extrabold text-navy-text">{result.value}</Text>
        {result.unit ? <Text className="mb-0.5 text-sm text-navy-secondary">{result.unit}</Text> : null}
      </View>
      {result.reference_range ? (
        <Text className="mt-1 text-xs text-navy-muted">
          {t('labs.referenceRange')}: {result.reference_range}
          {result.unit ? ` ${result.unit}` : ''}
        </Text>
      ) : null}
      {result.notes ? <Text className="mt-2 text-sm text-navy-secondary">{result.notes}</Text> : null}
    </View>
  );
}
