import { ActivityIndicator, FlatList, Pressable, RefreshControl, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  AlertCircle,
  AlertTriangle,
  ArrowLeft,
  Building2,
  CalendarClock,
  FileText,
  Inbox,
  MoveRight,
} from 'lucide-react-native';
import { Screen } from '../components/ui/Screen';
import { colors } from '../theme/tokens';
import { useReferrals, type Referral, type ReferralStatus, type ReferralUrgency } from '../lib/api/queries';

/** Read-only list of the patient's referral cases (GET /mobile/referrals).
 * No reference image for this screen — built from theme tokens + shared
 * primitives only, matching the card/list style established on Home. */
export default function ReferralsScreen() {
  const { t, i18n } = useTranslation();
  const router = useRouter();
  const { data, isLoading, isError, isRefetching, refetch } = useReferrals();
  const referrals = data?.data ?? [];

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
          <Text className="text-xl font-extrabold text-navy-text">{t('referrals.title')}</Text>
          <Text className="text-sm text-navy-secondary">{t('referrals.subtitle')}</Text>
        </View>
      </View>

      {isLoading ? (
        <View className="flex-1 items-center justify-center">
          <ActivityIndicator color={colors.gold[500]} size="large" />
        </View>
      ) : isError ? (
        <View className="flex-1 items-center justify-center px-10">
          <AlertCircle size={40} color={colors.semantic.danger} />
          <Text className="mt-4 text-center text-base font-bold text-navy-text">
            {t('referrals.loadErrorTitle')}
          </Text>
          <Text className="mt-1 text-center text-sm text-navy-secondary">
            {t('referrals.loadErrorBody')}
          </Text>
          <Pressable
            onPress={() => refetch()}
            className="mt-5 rounded-2xl bg-gold-500 px-6 py-3"
          >
            <Text className="text-sm font-bold text-white">{t('referrals.retry')}</Text>
          </Pressable>
        </View>
      ) : referrals.length === 0 ? (
        <View className="flex-1 items-center justify-center px-10">
          <View className="h-16 w-16 items-center justify-center rounded-full bg-gold-100">
            <Inbox size={26} color={colors.gold[600]} />
          </View>
          <Text className="mt-4 text-center text-base font-bold text-navy-text">
            {t('referrals.emptyTitle')}
          </Text>
          <Text className="mt-1 text-center text-sm text-navy-secondary">
            {t('referrals.emptyBody')}
          </Text>
        </View>
      ) : (
        <FlatList
          data={referrals}
          keyExtractor={(item) => item.id}
          contentContainerStyle={{ paddingHorizontal: 24, paddingTop: 16, paddingBottom: 32 }}
          ItemSeparatorComponent={() => <View className="h-3" />}
          refreshControl={
            <RefreshControl
              refreshing={isRefetching}
              onRefresh={() => refetch()}
              tintColor={colors.gold[500]}
            />
          }
          renderItem={({ item }) => <ReferralCard referral={item} locale={i18n.language} />}
        />
      )}
    </Screen>
  );
}

const STATUS_STYLE: Record<ReferralStatus, { fg: string; bg: string }> = {
  draft: { fg: colors.navy.secondary, bg: colors.cream[200] },
  sent: { fg: colors.semantic.info, bg: colors.semantic.infoSurface },
  accepted: { fg: colors.semantic.success, bg: colors.semantic.successSurface },
  rejected: { fg: colors.semantic.danger, bg: colors.semantic.dangerSurface },
  cancelled: { fg: colors.navy.secondary, bg: colors.cream[200] },
  completed: { fg: colors.semantic.success, bg: colors.semantic.successSurface },
  expired: { fg: colors.semantic.warning, bg: colors.semantic.warningSurface },
};

function formatDate(iso: string | null, locale: string): string | null {
  if (!iso) return null;
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) return null;
  return date.toLocaleDateString(locale === 'fr' ? 'fr-FR' : 'en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

function ReferralCard({ referral, locale }: { referral: Referral; locale: string }) {
  const { t } = useTranslation();
  const statusStyle = STATUS_STYLE[referral.status] ?? STATUS_STYLE.draft;
  const referredOn = formatDate(referral.referred_at, locale);
  const acceptedOn = formatDate(referral.accepted_at, locale);
  const completedOn = formatDate(referral.completed_at, locale);
  const isUrgent: ReferralUrgency[] = ['urgent', 'emergency'];

  return (
    <View className="rounded-2xl bg-white p-4">
      <View className="flex-row items-start justify-between">
        <View
          className="flex-row items-center rounded-full px-3 py-1"
          style={{ backgroundColor: statusStyle.bg }}
        >
          <Text className="text-xs font-bold" style={{ color: statusStyle.fg }}>
            {t(`referrals.status.${referral.status}`)}
          </Text>
        </View>
        {isUrgent.includes(referral.urgency) ? (
          <View className="flex-row items-center gap-1">
            <AlertTriangle
              size={14}
              color={referral.urgency === 'emergency' ? colors.semantic.danger : colors.semantic.warning}
            />
            <Text
              className="text-xs font-bold"
              style={{
                color: referral.urgency === 'emergency' ? colors.semantic.danger : colors.semantic.warning,
              }}
            >
              {t(`referrals.urgency.${referral.urgency}`)}
            </Text>
          </View>
        ) : null}
      </View>

      <View className="mt-3 flex-row items-center">
        <Building2 size={16} color={colors.navy.muted} />
        <Text className="ml-2 flex-1 text-sm font-semibold text-navy-text" numberOfLines={1}>
          {referral.referring_facility}
        </Text>
        <MoveRight size={14} color={colors.navy.muted} style={{ marginHorizontal: 6 }} />
        <Text className="flex-1 text-right text-sm font-semibold text-navy-text" numberOfLines={1}>
          {referral.receiving_facility}
        </Text>
      </View>

      <Text className="mt-3 text-sm text-navy-secondary">{referral.reason}</Text>

      {referral.notes ? (
        <View className="mt-2 flex-row items-start rounded-xl bg-cream-100 p-3">
          <FileText size={14} color={colors.navy.muted} style={{ marginTop: 1 }} />
          <Text className="ml-2 flex-1 text-xs text-navy-secondary">{referral.notes}</Text>
        </View>
      ) : null}

      <View className="mt-3 flex-row items-center border-t border-cream-300 pt-3">
        <CalendarClock size={13} color={colors.navy.muted} />
        <Text className="ml-1.5 text-xs text-navy-muted">
          {completedOn
            ? t('referrals.completedOn', { date: completedOn })
            : acceptedOn
              ? t('referrals.acceptedOn', { date: acceptedOn })
              : referredOn
                ? t('referrals.referredOn', { date: referredOn })
                : '—'}
        </Text>
      </View>
    </View>
  );
}
