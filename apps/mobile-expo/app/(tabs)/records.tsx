import { useMemo, useState } from 'react';
import { ActivityIndicator, Pressable, RefreshControl, ScrollView, Text, View } from 'react-native';
import { useTranslation } from 'react-i18next';
import {
  Activity,
  AlertTriangle,
  FlaskConical,
  Inbox,
  Pill,
  Stethoscope,
  Syringe,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { useAuthStore } from '../../lib/store/auth';
import { useAllergies, useClinical, useImmunizations, useTimeline } from '../../lib/api/queries';
import { colors } from '../../theme/tokens';

/**
 * Records / Timeline — merges GET /mobile/timeline (visits, resulted labs,
 * prescriptions) with GET /mobile/allergies, /mobile/clinical (conditions),
 * and /mobile/immunizations into one chronological feed, newest first.
 * `entered-in-error` clinical rows are never surfaced to the patient.
 */

type FeedType = 'visit' | 'lab_result' | 'prescription' | 'allergy' | 'condition' | 'immunization';
type FilterValue = 'all' | FeedType;

interface FeedItem {
  key: string;
  type: FeedType;
  title: string;
  subtitle: string | null;
  dateLabel: string;
  sortValue: number;
}

const FEED_META: Record<FeedType, { icon: LucideIcon; color: string; surface: string }> = {
  visit: { icon: Stethoscope, color: colors.navy.secondary, surface: colors.cream[200] },
  lab_result: { icon: FlaskConical, color: colors.semantic.info, surface: colors.semantic.infoSurface },
  prescription: { icon: Pill, color: colors.gold[600], surface: colors.gold[50] },
  allergy: { icon: AlertTriangle, color: colors.semantic.danger, surface: colors.semantic.dangerSurface },
  condition: { icon: Activity, color: colors.semantic.warning, surface: colors.semantic.warningSurface },
  immunization: { icon: Syringe, color: colors.semantic.success, surface: colors.semantic.successSurface },
};

const FILTERS: FilterValue[] = ['all', 'visit', 'lab_result', 'prescription', 'allergy', 'condition', 'immunization'];

export default function RecordsScreen() {
  const { t, i18n } = useTranslation();
  const patient = useAuthStore((s) => s.patient);
  const locale = i18n.language?.startsWith('fr') ? 'fr-FR' : 'en-US';

  const timeline = useTimeline();
  const allergies = useAllergies();
  const clinical = useClinical();
  const immunizations = useImmunizations();

  const [filter, setFilter] = useState<FilterValue>('all');

  const isLoading = timeline.isLoading || allergies.isLoading || clinical.isLoading || immunizations.isLoading;
  const isRefreshing =
    timeline.isRefetching || allergies.isRefetching || clinical.isRefetching || immunizations.isRefetching;
  const isError =
    !isLoading && timeline.isError && allergies.isError && clinical.isError && immunizations.isError;

  const refetchAll = () => {
    timeline.refetch();
    allergies.refetch();
    clinical.refetch();
    immunizations.refetch();
  };

  const formatDate = (iso: string | null | undefined) => {
    if (!iso) return '';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '';
    return d.toLocaleDateString(locale, { day: 'numeric', month: 'short', year: 'numeric' });
  };

  const statusLabel = (status: string | null | undefined) => {
    if (!status) return null;
    return t(`records.status.${status}`, {
      defaultValue: status.charAt(0).toUpperCase() + status.slice(1).replace(/[-_]/g, ' '),
    });
  };

  const feed = useMemo<FeedItem[]>(() => {
    const items: FeedItem[] = [];

    for (const ev of timeline.data?.timeline ?? []) {
      const d = new Date(ev.occurred_at);
      items.push({
        key: `${ev.event_type}-${ev.id}`,
        type: ev.event_type,
        title: ev.summary,
        subtitle: ev.facility_name ?? t('records.item.unknownFacility'),
        dateLabel: formatDate(ev.occurred_at),
        sortValue: Number.isNaN(d.getTime()) ? 0 : d.getTime(),
      });
    }

    for (const a of allergies.data?.allergies ?? []) {
      if (a.status === 'entered-in-error') continue;
      const d = a.recorded ? new Date(a.recorded) : null;
      items.push({
        key: `allergy-${a.id}`,
        type: 'allergy',
        title: t('records.item.allergyTitle', { substance: a.substance }),
        subtitle: statusLabel(a.severity),
        dateLabel: a.recorded ? t('records.item.recordedOn', { date: formatDate(a.recorded) }) : '',
        sortValue: d && !Number.isNaN(d.getTime()) ? d.getTime() : 0,
      });
    }

    for (const c of clinical.data?.conditions ?? []) {
      if (c.status === 'entered-in-error') continue;
      const d = c.recorded ? new Date(c.recorded) : null;
      items.push({
        key: `condition-${c.id}`,
        type: 'condition',
        title: c.display_name,
        subtitle: statusLabel(c.status),
        dateLabel: c.recorded ? t('records.item.recordedOn', { date: formatDate(c.recorded) }) : '',
        sortValue: d && !Number.isNaN(d.getTime()) ? d.getTime() : 0,
      });
    }

    for (const im of immunizations.data?.immunizations ?? []) {
      const d = im.administered_at ? new Date(im.administered_at) : null;
      items.push({
        key: `immunization-${im.id}`,
        type: 'immunization',
        title: im.vaccine_name,
        subtitle: im.dose_number ? t('records.item.immunizationDose', { n: im.dose_number }) : statusLabel(im.status),
        dateLabel: im.administered_at ? t('records.item.administeredOn', { date: formatDate(im.administered_at) }) : '',
        sortValue: d && !Number.isNaN(d.getTime()) ? d.getTime() : 0,
      });
    }

    return items.sort((x, y) => y.sortValue - x.sortValue);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [timeline.data, allergies.data, clinical.data, immunizations.data, locale]);

  const visibleFeed = filter === 'all' ? feed : feed.filter((item) => item.type === filter);

  return (
    <Screen className="px-0">
      <ScrollView
        className="flex-1 px-6"
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl refreshing={isRefreshing} onRefresh={refetchAll} tintColor={colors.gold[500]} />
        }
      >
        <View className="mt-2">
          <Text className="text-2xl font-extrabold text-navy-text">{t('records.title')}</Text>
          <Text className="mt-1 text-sm text-navy-secondary">{t('records.subtitle')}</Text>
        </View>

        <View className="mt-5 flex-row gap-3">
          <SummaryCard
            icon={AlertTriangle}
            color={colors.semantic.danger}
            surface={colors.semantic.dangerSurface}
            label={t('records.summary.allergies')}
            value={patient?.allergies_count ?? 0}
          />
          <SummaryCard
            icon={Activity}
            color={colors.semantic.warning}
            surface={colors.semantic.warningSurface}
            label={t('records.summary.conditions')}
            value={patient?.conditions_count ?? 0}
          />
        </View>

        <ScrollView horizontal showsHorizontalScrollIndicator={false} className="-mx-6 mt-5 px-6">
          <View className="flex-row gap-2">
            {FILTERS.map((f) => (
              <FilterChip key={f} active={f === filter} label={t(`records.filter.${f}`)} onPress={() => setFilter(f)} />
            ))}
          </View>
        </ScrollView>

        <View className="mb-3 mt-5">
          {isLoading ? (
            <View className="items-center py-16">
              <ActivityIndicator color={colors.gold[500]} />
              <Text className="mt-3 text-sm text-navy-muted">{t('records.loading')}</Text>
            </View>
          ) : isError ? (
            <View className="items-center py-16">
              <Text className="text-center text-sm text-navy-muted">{t('records.error')}</Text>
            </View>
          ) : visibleFeed.length === 0 ? (
            <View className="items-center py-16">
              <Inbox size={32} color={colors.navy.muted} />
              <Text className="mt-3 text-base font-semibold text-navy-text">{t('records.empty.title')}</Text>
              <Text className="mt-1 text-center text-sm text-navy-muted">{t('records.empty.body')}</Text>
            </View>
          ) : (
            visibleFeed.map((item) => <FeedRow key={item.key} item={item} />)
          )}
        </View>

        <View className="h-10" />
      </ScrollView>
    </Screen>
  );
}

function SummaryCard({
  icon: Icon,
  color,
  surface,
  label,
  value,
}: {
  icon: LucideIcon;
  color: string;
  surface: string;
  label: string;
  value: number;
}) {
  return (
    <View className="flex-1 rounded-2xl bg-white p-4">
      <View className="h-10 w-10 items-center justify-center rounded-full" style={{ backgroundColor: surface }}>
        <Icon size={18} color={color} />
      </View>
      <Text className="mt-3 text-2xl font-extrabold text-navy-text">{value}</Text>
      <Text className="text-xs text-navy-secondary">{label}</Text>
    </View>
  );
}

function FilterChip({ active, label, onPress }: { active: boolean; label: string; onPress: () => void }) {
  return (
    <Pressable
      onPress={onPress}
      className="rounded-full border px-4 py-2"
      style={{
        borderColor: active ? colors.gold[500] : colors.cream[300],
        backgroundColor: active ? colors.gold[500] : colors.white,
      }}
    >
      <Text className="text-xs font-semibold" style={{ color: active ? colors.white : colors.navy.secondary }}>
        {label}
      </Text>
    </Pressable>
  );
}

function FeedRow({ item }: { item: FeedItem }) {
  const meta = FEED_META[item.type];
  const Icon = meta.icon;
  return (
    <View className="mb-3 flex-row rounded-2xl bg-white p-4">
      <View
        className="h-10 w-10 items-center justify-center rounded-full"
        style={{ backgroundColor: meta.surface }}
      >
        <Icon size={18} color={meta.color} />
      </View>
      <View className="ml-3 flex-1">
        <Text className="text-sm font-bold text-navy-text">{item.title}</Text>
        {item.subtitle ? <Text className="mt-0.5 text-xs text-navy-secondary">{item.subtitle}</Text> : null}
        {item.dateLabel ? <Text className="mt-1 text-xs text-navy-muted">{item.dateLabel}</Text> : null}
      </View>
    </View>
  );
}
