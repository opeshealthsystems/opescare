import { useState } from 'react';
import { ActivityIndicator, Alert, Pressable, RefreshControl, ScrollView, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  Ban,
  Building2,
  CheckCircle2,
  ChevronDown,
  ChevronRight,
  ChevronUp,
  Clock,
  Download,
  History,
  ShieldCheck,
  XCircle,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { colors } from '../../theme/tokens';
import {
  type ConsentRequestItem,
  extractApiErrorMessage,
  useApproveConsent,
  useConsentRequests,
  useDenyConsent,
  useRevokeConsent,
} from '../../lib/api/queries';

/** Statuses that represent a decision already made — shown collapsed under "History". */
const HISTORY_STATUSES = new Set(['denied', 'expired']);

export default function PrivacyHubScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const [historyOpen, setHistoryOpen] = useState(false);

  const { data, isLoading, isError, refetch, isRefetching } = useConsentRequests();
  const approveMutation = useApproveConsent();
  const denyMutation = useDenyConsent();
  const revokeMutation = useRevokeConsent();

  const requests = data ?? [];
  const pending = requests.filter((r) => r.status === 'pending_patient_approval');
  const active = requests.filter((r) => r.status === 'approved' && r.grant_status === 'active');
  const history = requests.filter(
    (r) => HISTORY_STATUSES.has(r.status) || (r.status === 'approved' && r.grant_status !== 'active'),
  );

  const facilityLabel = (item: ConsentRequestItem) =>
    item.requesting_facility_name ?? t('privacy.unknownFacility');

  const confirmApprove = (item: ConsentRequestItem) => {
    Alert.alert(
      t('privacy.confirmApproveTitle'),
      t('privacy.confirmApproveBody', {
        facility: facilityLabel(item),
        scope: item.requested_scope?.join(', ') || '—',
        purpose: item.purpose,
        duration: item.duration_minutes,
      }),
      [
        { text: t('privacy.cancel'), style: 'cancel' },
        {
          text: t('privacy.approve'),
          onPress: () =>
            approveMutation.mutate(item.id, {
              onError: (err) => Alert.alert(t('privacy.actionFailed'), extractApiErrorMessage(err)),
            }),
        },
      ],
    );
  };

  const confirmDeny = (item: ConsentRequestItem) => {
    Alert.alert(
      t('privacy.confirmDenyTitle'),
      t('privacy.confirmDenyBody', { facility: facilityLabel(item) }),
      [
        { text: t('privacy.cancel'), style: 'cancel' },
        {
          text: t('privacy.deny'),
          style: 'destructive',
          onPress: () =>
            denyMutation.mutate(item.id, {
              onError: (err) => Alert.alert(t('privacy.actionFailed'), extractApiErrorMessage(err)),
            }),
        },
      ],
    );
  };

  const confirmRevoke = (item: ConsentRequestItem) => {
    if (!item.grant_id) return;
    Alert.alert(
      t('privacy.confirmRevokeTitle'),
      t('privacy.confirmRevokeBody', { facility: facilityLabel(item) }),
      [
        { text: t('privacy.cancel'), style: 'cancel' },
        {
          text: t('privacy.revoke'),
          style: 'destructive',
          onPress: () =>
            revokeMutation.mutate(item.grant_id as string, {
              onError: (err) => Alert.alert(t('privacy.actionFailed'), extractApiErrorMessage(err)),
            }),
        },
      ],
    );
  };

  return (
    <Screen className="px-0">
      <ScrollView
        className="flex-1 px-6"
        showsVerticalScrollIndicator={false}
        contentContainerStyle={{ paddingBottom: 32 }}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} tintColor={colors.gold[500]} />
        }
      >
        <View className="mt-2 flex-row items-center">
          <Pressable
            onPress={() => router.back()}
            hitSlop={8}
            className="h-11 w-11 items-center justify-center rounded-full border border-gold-300"
          >
            <ArrowLeft size={18} color={colors.gold[600]} />
          </Pressable>
          <View className="ml-3 flex-1">
            <Text className="text-xl font-extrabold text-navy-text">{t('privacy.title')}</Text>
          </View>
          <ShieldCheck size={22} color={colors.gold[500]} />
        </View>
        <Text className="mt-2 text-sm text-navy-secondary">{t('privacy.subtitle')}</Text>

        {isLoading ? (
          <ActivityIndicator color={colors.gold[500]} className="mt-10" />
        ) : isError ? (
          <Text className="mt-8 text-center text-sm text-danger">{t('privacy.actionFailed')}</Text>
        ) : (
          <>
            <SectionHeader label={t('privacy.pendingSection')} count={pending.length} />
            {pending.length === 0 ? (
              <EmptyNote text={t('privacy.pendingEmpty')} />
            ) : (
              pending.map((item) => (
                <ConsentCard key={item.id} item={item} t={t}>
                  <View className="mt-4 flex-row gap-3">
                    <ActionPill
                      label={t('privacy.deny')}
                      icon={XCircle}
                      tone="danger"
                      onPress={() => confirmDeny(item)}
                      loading={denyMutation.isPending && denyMutation.variables === item.id}
                    />
                    <ActionPill
                      label={t('privacy.approve')}
                      icon={CheckCircle2}
                      tone="success"
                      onPress={() => confirmApprove(item)}
                      loading={approveMutation.isPending && approveMutation.variables === item.id}
                    />
                  </View>
                </ConsentCard>
              ))
            )}

            <SectionHeader label={t('privacy.activeSection')} count={active.length} />
            {active.length === 0 ? (
              <EmptyNote text={t('privacy.activeEmpty')} />
            ) : (
              active.map((item) => (
                <ConsentCard key={item.id} item={item} t={t}>
                  <View className="mt-4 flex-row items-center justify-between">
                    <StatusBadge tone="success" label={t('privacy.status.active')} />
                    <ActionPill
                      label={t('privacy.revoke')}
                      icon={Ban}
                      tone="danger"
                      onPress={() => confirmRevoke(item)}
                      loading={revokeMutation.isPending && revokeMutation.variables === item.grant_id}
                    />
                  </View>
                </ConsentCard>
              ))
            )}

            <Pressable
              onPress={() => setHistoryOpen((v) => !v)}
              className="mt-6 flex-row items-center justify-between"
            >
              <Text className="text-base font-bold text-navy-text">
                {t('privacy.historySection')} {history.length > 0 ? `(${history.length})` : ''}
              </Text>
              {historyOpen ? (
                <ChevronUp size={18} color={colors.navy.secondary} />
              ) : (
                <ChevronDown size={18} color={colors.navy.secondary} />
              )}
            </Pressable>
            {historyOpen ? (
              history.length === 0 ? (
                <EmptyNote text={t('privacy.historyEmpty')} />
              ) : (
                history.map((item) => (
                  <ConsentCard key={item.id} item={item} t={t}>
                    <View className="mt-3">
                      <StatusBadge
                        tone={item.status === 'denied' ? 'danger' : 'muted'}
                        label={t(`privacy.status.${item.status === 'approved' ? 'revoked' : item.status}`, {
                          defaultValue: item.status,
                        })}
                      />
                    </View>
                  </ConsentCard>
                ))
              )
            ) : null}
          </>
        )}

        <View className="mt-8 h-px bg-cream-300" />

        <View className="mt-6">
          <NavCard
            icon={History}
            title={t('privacy.accessLogsCard')}
            description={t('privacy.accessLogsCardDesc')}
            onPress={() => router.push('/privacy/access-logs')}
          />
          <View className="h-3" />
          <NavCard
            icon={Download}
            title={t('privacy.exportCard')}
            description={t('privacy.exportCardDesc')}
            onPress={() => router.push('/privacy/export')}
          />
        </View>
      </ScrollView>
    </Screen>
  );
}

function SectionHeader({ label, count }: { label: string; count: number }) {
  return (
    <Text className="mb-3 mt-6 text-base font-bold text-navy-text">
      {label} {count > 0 ? `(${count})` : ''}
    </Text>
  );
}

function EmptyNote({ text }: { text: string }) {
  return (
    <View className="rounded-2xl bg-white p-4">
      <Text className="text-sm text-navy-muted">{text}</Text>
    </View>
  );
}

function ConsentCard({
  item,
  t,
  children,
}: {
  item: ConsentRequestItem;
  t: (key: string, opts?: Record<string, unknown>) => string;
  children?: React.ReactNode;
}) {
  return (
    <View className="mb-3 rounded-2xl bg-white p-4">
      <View className="flex-row items-center">
        <View className="h-9 w-9 items-center justify-center rounded-full bg-gold-100">
          <Building2 size={16} color={colors.gold[600]} />
        </View>
        <View className="ml-3 flex-1">
          <Text className="text-sm font-bold text-navy-text" numberOfLines={1}>
            {item.requesting_facility_name ?? t('privacy.unknownFacility')}
          </Text>
          <Text className="text-xs text-navy-muted" numberOfLines={1}>
            {item.purpose}
          </Text>
        </View>
      </View>

      {item.requested_scope?.length ? (
        <View className="mt-3 flex-row flex-wrap gap-2">
          {item.requested_scope.map((scope) => (
            <View key={scope} className="rounded-full bg-cream-200 px-3 py-1">
              <Text className="text-[11px] font-medium text-navy-secondary">{scope}</Text>
            </View>
          ))}
        </View>
      ) : null}

      <View className="mt-3 flex-row items-center">
        <Clock size={13} color={colors.navy.muted} />
        <Text className="ml-1.5 text-xs text-navy-muted">
          {item.grant_expires_at
            ? t('privacy.expiresLabel', { date: new Date(item.grant_expires_at).toLocaleString() })
            : t('privacy.durationLabel', { count: item.duration_minutes })}
        </Text>
      </View>

      {children}
    </View>
  );
}

function ActionPill({
  label,
  icon: Icon,
  tone,
  onPress,
  loading,
}: {
  label: string;
  icon: typeof CheckCircle2;
  tone: 'success' | 'danger';
  onPress: () => void;
  loading?: boolean;
}) {
  const bg = tone === 'success' ? colors.semantic.successSurface : colors.semantic.dangerSurface;
  const fg = tone === 'success' ? colors.semantic.success : colors.semantic.danger;
  return (
    <Pressable
      onPress={onPress}
      disabled={loading}
      className="flex-1 flex-row items-center justify-center rounded-xl py-3"
      style={{ backgroundColor: bg, opacity: loading ? 0.6 : 1 }}
    >
      {loading ? (
        <ActivityIndicator size="small" color={fg} />
      ) : (
        <>
          <Icon size={15} color={fg} />
          <Text className="ml-1.5 text-sm font-bold" style={{ color: fg }}>
            {label}
          </Text>
        </>
      )}
    </Pressable>
  );
}

function StatusBadge({ tone, label }: { tone: 'success' | 'danger' | 'muted'; label: string }) {
  const bg =
    tone === 'success'
      ? colors.semantic.successSurface
      : tone === 'danger'
        ? colors.semantic.dangerSurface
        : colors.cream[200];
  const fg =
    tone === 'success' ? colors.semantic.success : tone === 'danger' ? colors.semantic.danger : colors.navy.secondary;
  return (
    <View className="self-start rounded-full px-3 py-1" style={{ backgroundColor: bg }}>
      <Text className="text-[11px] font-bold" style={{ color: fg }}>
        {label}
      </Text>
    </View>
  );
}

function NavCard({
  icon: Icon,
  title,
  description,
  onPress,
}: {
  icon: typeof History;
  title: string;
  description: string;
  onPress: () => void;
}) {
  return (
    <Pressable onPress={onPress} className="flex-row items-center rounded-2xl bg-white p-4">
      <View className="h-11 w-11 items-center justify-center rounded-full bg-gold-100">
        <Icon size={18} color={colors.gold[600]} />
      </View>
      <View className="ml-3 flex-1">
        <Text className="text-sm font-bold text-navy-text">{title}</Text>
        <Text className="mt-0.5 text-xs text-navy-secondary">{description}</Text>
      </View>
      <ChevronRight size={18} color={colors.navy.muted} />
    </Pressable>
  );
}
