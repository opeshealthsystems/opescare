import { useState } from 'react';
import { ActivityIndicator, FlatList, Pressable, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  Activity,
  ArrowLeft,
  Building2,
  CheckCircle2,
  ChevronLeft,
  ChevronRight,
  Clock,
  Download,
  Eye,
  FilePlus,
  History,
  Pencil,
  ShieldAlert,
  XCircle,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { colors } from '../../theme/tokens';
import { type AccessLogItem, useAccessLogs } from '../../lib/api/queries';

const ACCESS_TYPE_ICON: Record<string, typeof Eye> = {
  view: Eye,
  create: FilePlus,
  update: Pencil,
  approve: CheckCircle2,
  download: Download,
  override: ShieldAlert,
  reject: XCircle,
};

export default function AccessLogsScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const [page, setPage] = useState(1);

  const { data, isLoading, isFetching, isError } = useAccessLogs(page);
  const logs = data?.data ?? [];

  return (
    <Screen className="px-0">
      <View className="px-6">
        <View className="mt-2 flex-row items-center">
          <Pressable
            onPress={() => router.back()}
            hitSlop={8}
            className="h-11 w-11 items-center justify-center rounded-full border border-gold-300"
          >
            <ArrowLeft size={18} color={colors.gold[600]} />
          </Pressable>
          <View className="ml-3 flex-1">
            <Text className="text-xl font-extrabold text-navy-text">{t('privacy.accessLogsTitle')}</Text>
          </View>
        </View>
        <Text className="mt-2 text-sm text-navy-secondary">{t('privacy.accessLogsSubtitle')}</Text>
      </View>

      {isLoading ? (
        <ActivityIndicator color={colors.gold[500]} className="mt-10" />
      ) : isError ? (
        <Text className="mt-8 px-6 text-center text-sm text-danger">{t('privacy.actionFailed')}</Text>
      ) : (
        <FlatList
          className="mt-4 flex-1 px-6"
          data={logs}
          keyExtractor={(item) => item.id}
          ListEmptyComponent={
            <View className="items-center rounded-2xl bg-white p-6">
              <History size={22} color={colors.navy.muted} />
              <Text className="mt-2 text-sm text-navy-muted">{t('privacy.accessLogsEmpty')}</Text>
            </View>
          }
          renderItem={({ item }) => <AccessLogRow item={item} t={t} />}
          ItemSeparatorComponent={() => <View className="h-3" />}
          contentContainerStyle={{ paddingBottom: 16 }}
        />
      )}

      {data && data.last_page > 1 ? (
        <View className="flex-row items-center justify-between border-t border-cream-300 px-6 py-4">
          <PagerButton
            label={t('privacy.previous')}
            icon={ChevronLeft}
            iconSide="left"
            disabled={page <= 1 || isFetching}
            onPress={() => setPage((p) => Math.max(1, p - 1))}
          />
          <Text className="text-xs text-navy-muted">
            {t('privacy.pageIndicator', { current: data.current_page, last: data.last_page })}
          </Text>
          <PagerButton
            label={t('privacy.next')}
            icon={ChevronRight}
            iconSide="right"
            disabled={page >= data.last_page || isFetching}
            onPress={() => setPage((p) => Math.min(data.last_page, p + 1))}
          />
        </View>
      ) : null}
    </Screen>
  );
}

function AccessLogRow({
  item,
  t,
}: {
  item: AccessLogItem;
  t: (key: string, opts?: Record<string, unknown>) => string;
}) {
  const Icon = ACCESS_TYPE_ICON[item.access_type] ?? Activity;
  return (
    <View className="rounded-2xl bg-white p-4">
      <View className="flex-row items-start">
        <View
          className="h-9 w-9 items-center justify-center rounded-full"
          style={{ backgroundColor: item.emergency_access ? colors.semantic.warningSurface : colors.gold[100] }}
        >
          <Icon size={16} color={item.emergency_access ? colors.semantic.warning : colors.gold[600]} />
        </View>
        <View className="ml-3 flex-1">
          <Text className="text-sm font-bold text-navy-text">
            {t(`privacy.accessType.${item.access_type}`, { defaultValue: item.access_type })}
          </Text>
          <View className="mt-1 flex-row items-center">
            <Building2 size={12} color={colors.navy.muted} />
            <Text className="ml-1 text-xs text-navy-muted" numberOfLines={1}>
              {item.facility
                ? t('privacy.viaFacility', { facility: item.facility.name })
                : t('privacy.viaPlatform')}
            </Text>
          </View>
          <Text className="mt-0.5 text-xs text-navy-secondary" numberOfLines={1}>
            {item.purpose} · {item.data_category}
          </Text>
          <View className="mt-1.5 flex-row items-center">
            <Clock size={11} color={colors.navy.muted} />
            <Text className="ml-1 text-[11px] text-navy-muted">
              {new Date(item.created_at).toLocaleString()}
            </Text>
          </View>
        </View>
        {item.emergency_access ? (
          <View className="rounded-full px-2 py-1" style={{ backgroundColor: colors.semantic.warningSurface }}>
            <Text className="text-[10px] font-bold" style={{ color: colors.semantic.warning }}>
              {t('privacy.emergencyAccess')}
            </Text>
          </View>
        ) : null}
      </View>
    </View>
  );
}

function PagerButton({
  label,
  icon: Icon,
  iconSide,
  disabled,
  onPress,
}: {
  label: string;
  icon: typeof ChevronLeft;
  iconSide: 'left' | 'right';
  disabled?: boolean;
  onPress: () => void;
}) {
  return (
    <Pressable
      onPress={onPress}
      disabled={disabled}
      className="flex-row items-center rounded-xl px-3 py-2"
      style={{ opacity: disabled ? 0.4 : 1, backgroundColor: colors.cream[200] }}
    >
      {iconSide === 'left' ? <Icon size={14} color={colors.navy.text} /> : null}
      <Text className="mx-1 text-xs font-semibold text-navy-text">{label}</Text>
      {iconSide === 'right' ? <Icon size={14} color={colors.navy.text} /> : null}
    </Pressable>
  );
}
