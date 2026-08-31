import { ActivityIndicator, Pressable, ScrollView, Text, View } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import type { TFunction } from 'i18next';
import {
  ArrowLeft,
  Calendar,
  CheckCircle2,
  Circle,
  MapPin,
  Pill,
  RotateCcw,
  StickyNote,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { colors } from '../../theme/tokens';
import {
  usePrescriptionDetail,
  type PrescriptionItemDetail,
  type PrescriptionSummary,
} from '../../lib/api/queries';

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

/** Detail view of one prescription: status, prescribing facility, dates, notes,
 * and the full list of medications with each item's own dispense status. */
export default function PrescriptionDetailScreen() {
  const params = useLocalSearchParams<{ id: string }>();
  const id = Array.isArray(params.id) ? params.id[0] : params.id;
  const { t } = useTranslation();
  const router = useRouter();
  const { data: prescription, isLoading, isError, refetch } = usePrescriptionDetail(id);

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
        <Text className="ml-3 text-xl font-extrabold text-navy-text">{t('prescriptions.detailTitle')}</Text>
      </View>

      {isLoading ? (
        <View className="flex-1 items-center justify-center">
          <ActivityIndicator color={colors.gold[500]} />
        </View>
      ) : isError || !prescription ? (
        <View className="flex-1 items-center justify-center px-6">
          <Text className="text-center text-sm text-navy-secondary">
            {isError ? t('prescriptions.detailLoadError') : t('prescriptions.notFound')}
          </Text>
          {isError ? (
            <Pressable
              onPress={() => refetch()}
              className="mt-4 flex-row items-center rounded-full border border-gold-300 px-4 py-2"
            >
              <RotateCcw size={14} color={colors.gold[600]} />
              <Text className="ml-2 text-sm font-semibold text-gold-600">{t('prescriptions.retry')}</Text>
            </Pressable>
          ) : null}
        </View>
      ) : (
        <ScrollView className="flex-1 px-6" contentContainerStyle={{ paddingBottom: 40 }} showsVerticalScrollIndicator={false}>
          <View className="mt-2 rounded-2xl bg-white p-4">
            <View className="flex-row items-start">
              <View className="h-11 w-11 items-center justify-center rounded-full bg-gold-100">
                <Pill size={20} color={colors.gold[600]} />
              </View>
              <View className="ml-3 flex-1">
                <Text className="text-lg font-extrabold text-navy-text">
                  {prescription.facility_name ?? t('prescriptions.title')}
                </Text>
                {prescription.prescribed_at ? (
                  <Text className="mt-0.5 text-xs text-navy-muted">
                    {t('prescriptions.prescribedOn', { date: formatDate(prescription.prescribed_at) })}
                  </Text>
                ) : null}
              </View>
            </View>
            <View
              className="mt-3 self-start rounded-full px-3 py-1"
              style={{ backgroundColor: statusStyles(prescription.status_color).surface }}
            >
              <Text className={`text-xs font-semibold ${statusStyles(prescription.status_color).text}`}>
                {t(statusLabelKey(prescription.status))}
              </Text>
            </View>
          </View>

          <View className="mt-4 rounded-2xl bg-white p-4">
            <InfoRow icon={MapPin} label={t('prescriptions.facility')} value={prescription.facility_name ?? '—'} />
            <InfoRow
              icon={Calendar}
              label={t('prescriptions.prescribedAt')}
              value={formatDate(prescription.prescribed_at) ?? '—'}
            />
            {prescription.dispensed_at ? (
              <InfoRow
                icon={CheckCircle2}
                label={t('prescriptions.dispensedAt')}
                value={formatDate(prescription.dispensed_at) ?? '—'}
              />
            ) : null}
            {prescription.expires_at ? (
              <InfoRow
                icon={Calendar}
                label={t('prescriptions.expiresAt')}
                value={formatDate(prescription.expires_at) ?? '—'}
                last
              />
            ) : null}
          </View>

          {prescription.notes ? (
            <View className="mt-4 rounded-2xl bg-white p-4">
              <View className="flex-row items-center">
                <StickyNote size={16} color={colors.gold[600]} />
                <Text className="ml-2 text-sm font-bold text-navy-text">{t('prescriptions.notes')}</Text>
              </View>
              <Text className="mt-2 text-sm text-navy-secondary">{prescription.notes}</Text>
            </View>
          ) : null}

          <Text className="mb-3 mt-6 text-base font-bold text-navy-text">
            {t('prescriptions.medications')} ({prescription.items.length})
          </Text>
          <View style={{ gap: 12 }}>
            {prescription.items.map((item) => (
              <MedicationCard key={item.id} item={item} t={t} />
            ))}
          </View>
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

function MedicationCard({ item, t }: { item: PrescriptionItemDetail; t: TFunction }) {
  return (
    <View className="rounded-2xl bg-white p-4">
      <View className="flex-row items-start justify-between">
        <Text className="flex-1 text-base font-bold text-navy-text">{item.drug_name}</Text>
        <View className="ml-2 flex-row items-center">
          {item.is_dispensed ? (
            <CheckCircle2 size={16} color={colors.semantic.success} />
          ) : (
            <Circle size={16} color={colors.navy.muted} />
          )}
          <Text className={`ml-1 text-xs font-semibold ${item.is_dispensed ? 'text-success' : 'text-navy-muted'}`}>
            {item.is_dispensed ? t('prescriptions.itemDispensed') : t('prescriptions.itemPending')}
          </Text>
        </View>
      </View>
      <View className="mt-3 flex-row flex-wrap" style={{ gap: 16 }}>
        <Detail label={t('prescriptions.dose')} value={item.dose} />
        <Detail label={t('prescriptions.frequency')} value={item.frequency} />
        <Detail label={t('prescriptions.route')} value={item.route} />
        <Detail
          label={t('prescriptions.duration')}
          value={item.duration_days != null ? t('prescriptions.durationDays', { count: item.duration_days }) : null}
        />
        <Detail label={t('prescriptions.quantity')} value={item.quantity != null ? String(item.quantity) : null} />
      </View>
    </View>
  );
}

function Detail({ label, value }: { label: string; value: string | null }) {
  return (
    <View style={{ minWidth: 90 }}>
      <Text className="text-[10px] uppercase text-navy-muted">{label}</Text>
      <Text className="mt-0.5 text-sm font-semibold text-navy-text">{value ?? '—'}</Text>
    </View>
  );
}
