import { useCallback, useState } from 'react';
import { ActivityIndicator, Alert, Pressable, RefreshControl, ScrollView, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  AlertTriangle,
  ArrowLeft,
  Check,
  CloudOff,
  Database,
  HeartPulse,
  Lock,
  RefreshCw,
  ShieldCheck,
  Smartphone,
  Trash2,
  UploadCloud,
  Wifi,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../components/ui/Screen';
import { Button } from '../components/ui/Button';
import {
  useDisableOfflineAccess,
  useEnableOfflineAccess,
  useFlushOfflineOutbox,
  useOfflineStatus,
  useSyncOfflineCache,
} from '../lib/api/offlineQueries';
import { useIsOnline } from '../lib/offline/connectivity';
import { formatDateTime, formatSavedAt } from '../lib/offline/relativeTime';
import type { OfflineScope } from '../lib/offline/scopes';
import type { ScopeCacheSummary } from '../lib/offline/cache';
import { colors } from '../theme/tokens';

/**
 * Offline access — the patient-facing control for the backend's limited
 * encrypted offline mode (App\Modules\Offline\Services\SyncService).
 *
 * The patient opts in, sees exactly what is held on this device and when it
 * was last refreshed, and can erase it. Nothing is stored before they say yes.
 */

const SCOPE_LABEL_KEY: Record<OfflineScope, string> = {
  demographics: 'offline.scopeDemographics',
  appointments: 'offline.scopeAppointments',
  medications: 'offline.scopeMedications',
  allergies: 'offline.scopeAllergies',
  emergency_profile: 'offline.scopeEmergencyProfile',
};

const SCOPE_ICON: Record<OfflineScope, LucideIcon> = {
  demographics: Smartphone,
  appointments: Database,
  medications: Database,
  allergies: AlertTriangle,
  emergency_profile: HeartPulse,
};

type Mode = 'full' | 'emergency';

export default function OfflineAccessScreen() {
  const { t, i18n } = useTranslation();
  const router = useRouter();
  const isOnline = useIsOnline();

  const { data: status, isLoading, refetch, isRefetching } = useOfflineStatus();
  const enableMutation = useEnableOfflineAccess();
  const syncMutation = useSyncOfflineCache();
  const disableMutation = useDisableOfflineAccess();
  const flushMutation = useFlushOfflineOutbox();

  const [mode, setMode] = useState<Mode>('full');

  const enabled = status?.enabled ?? false;
  const policy = status?.policy ?? null;
  const grantedScopes = policy?.allowed_scopes ?? [];
  const outbox = status?.outbox ?? [];

  const lastSavedLabel = formatSavedAt(status?.lastCachedAt, t);

  const handleEnable = useCallback(() => {
    if (!isOnline) {
      Alert.alert(t('offline.title'), t('offline.errorNeedsConnection'));
      return;
    }
    enableMutation.mutate(
      { emergencyAccess: mode === 'emergency' },
      {
        onError: () => Alert.alert(t('offline.title'), t('offline.errorEnable')),
      },
    );
  }, [enableMutation, isOnline, mode, t]);

  const handleSync = useCallback(() => {
    if (!isOnline) {
      Alert.alert(t('offline.title'), t('offline.errorNeedsConnection'));
      return;
    }
    syncMutation.mutate(undefined, {
      onError: () => Alert.alert(t('offline.title'), t('offline.errorSync')),
    });
  }, [isOnline, syncMutation, t]);

  const handleFlush = useCallback(() => {
    if (!isOnline) {
      Alert.alert(t('offline.title'), t('offline.errorNeedsConnection'));
      return;
    }
    flushMutation.mutate(undefined, {
      onError: () => Alert.alert(t('offline.title'), t('offline.errorSync')),
    });
  }, [flushMutation, isOnline, t]);

  const handleDisable = useCallback(() => {
    Alert.alert(t('offline.disableTitle'), t('offline.disableBody'), [
      { text: t('permissions.skip'), style: 'cancel' },
      {
        text: t('offline.disableCta'),
        style: 'destructive',
        onPress: () => disableMutation.mutate(),
      },
    ]);
  }, [disableMutation, t]);

  return (
    <Screen className="px-0">
      <ScrollView
        className="flex-1 px-6"
        showsVerticalScrollIndicator={false}
        contentContainerStyle={{ paddingBottom: 40 }}
        refreshControl={
          <RefreshControl
            refreshing={isRefetching}
            onRefresh={refetch}
            tintColor={colors.gold[500]}
          />
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
            <Text className="text-xl font-extrabold text-navy-text">{t('offline.title')}</Text>
          </View>
          <CloudOff size={22} color={colors.gold[500]} />
        </View>
        <Text className="mt-2 text-sm text-navy-secondary">{t('offline.subtitle')}</Text>

        <ConnectivityPill isOnline={isOnline} t={t} />

        {status && !status.encryptedAtRest ? (
          <NoteCard tone="warning" icon={AlertTriangle} body={t('offline.webNotice')} />
        ) : null}

        {isLoading ? (
          <ActivityIndicator color={colors.gold[500]} className="mt-10" />
        ) : !enabled ? (
          <>
            <View className="mt-5 rounded-2xl bg-white p-5">
              <Text className="text-base font-bold text-navy-text">
                {t('offline.notEnabledTitle')}
              </Text>
              <Text className="mt-1 text-sm text-navy-secondary">
                {t('offline.notEnabledBody')}
              </Text>
            </View>

            <SectionTitle text={t('offline.chooseMode')} />
            <ModeOption
              selected={mode === 'full'}
              onPress={() => setMode('full')}
              icon={Database}
              title={t('offline.modeFull')}
              body={t('offline.modeFullBody')}
            />
            <ModeOption
              selected={mode === 'emergency'}
              onPress={() => setMode('emergency')}
              icon={HeartPulse}
              title={t('offline.modeEmergency')}
              body={t('offline.modeEmergencyBody')}
            />

            <View className="mt-5">
              <Button
                label={enableMutation.isPending ? t('offline.enabling') : t('offline.enableCta')}
                onPress={handleEnable}
                loading={enableMutation.isPending}
                disabled={!isOnline}
                leftIcon={Lock}
              />
            </View>
            {!isOnline ? (
              <Text className="mt-2 text-center text-xs text-navy-muted">
                {t('offline.errorNeedsConnection')}
              </Text>
            ) : null}
          </>
        ) : (
          <>
            {/* Device authorisation — the server-side policy this device holds */}
            <SectionTitle text={t('offline.policyTitle')} />
            <View className="mt-2 rounded-2xl bg-white p-5">
              {policy?.emergency_access ? (
                <View
                  className="mb-3 flex-row items-center self-start rounded-full px-2.5 py-1"
                  style={{ backgroundColor: colors.semantic.dangerSurface }}
                >
                  <HeartPulse size={12} color={colors.semantic.danger} />
                  <Text
                    className="ml-1 text-xs font-semibold"
                    style={{ color: colors.semantic.danger }}
                  >
                    {t('offline.policyEmergencyBadge')}
                  </Text>
                </View>
              ) : null}

              <DetailRow
                icon={Smartphone}
                label={t('offline.policyDevice')}
                value={policy?.device_id ?? '—'}
              />
              <DetailRow
                icon={Lock}
                label={t('offline.policyEncryption')}
                value={
                  status?.encryptedAtRest
                    ? t('offline.policyEncryptionOn')
                    : t('offline.policyEncryptionOff')
                }
              />
              <DetailRow
                icon={ShieldCheck}
                label={t('offline.policyExpires')}
                value={
                  status?.policyUsable
                    ? (formatDateTime(policy?.expires_at, i18n.language) ?? '—')
                    : t('offline.policyExpired')
                }
                danger={!status?.policyUsable}
              />

              {policy?.review_required ? (
                <Text className="mt-2 text-xs text-navy-muted">
                  {t('offline.policyReviewNote')}
                </Text>
              ) : null}
            </View>

            {/* What is actually on the device right now */}
            <SectionTitle text={t('offline.savedDataTitle')} />
            <Text className="mt-1 text-xs text-navy-muted">
              {lastSavedLabel ? t('offline.lastSaved', { when: lastSavedLabel }) : t('offline.neverSaved')}
            </Text>

            <View className="mt-2 overflow-hidden rounded-2xl bg-white">
              {(status?.scopes ?? [])
                .filter((scope) => grantedScopes.includes(scope.scope))
                .map((scope, index, list) => (
                  <ScopeRow
                    key={scope.scope}
                    scope={scope}
                    isLast={index === list.length - 1}
                    t={t}
                  />
                ))}
            </View>

            <View className="mt-4">
              <Button
                label={syncMutation.isPending ? t('offline.syncing') : t('offline.syncCta')}
                onPress={handleSync}
                loading={syncMutation.isPending}
                disabled={!isOnline}
                leftIcon={RefreshCw}
                variant="outline"
              />
            </View>
            {!isOnline ? (
              <Text className="mt-2 text-center text-xs text-navy-muted">
                {t('offline.syncOfflineHint')}
              </Text>
            ) : null}
            {syncMutation.isSuccess && syncMutation.data ? (
              <Text className="mt-2 text-center text-xs" style={{ color: colors.semantic.success }}>
                {t('offline.syncDone', {
                  done: syncMutation.data.outcomes.filter((outcome) => outcome.ok).length,
                  total: syncMutation.data.outcomes.length,
                })}
              </Text>
            ) : null}

            {/* Changes captured while offline, awaiting reconciliation */}
            <SectionTitle text={t('offline.outboxTitle')} />
            <View className="mt-2 rounded-2xl bg-white p-5">
              <Text className="text-xs text-navy-secondary">{t('offline.outboxBody')}</Text>
              {outbox.length === 0 ? (
                <Text className="mt-3 text-sm font-semibold text-navy-muted">
                  {t('offline.outboxEmpty')}
                </Text>
              ) : (
                <>
                  {outbox.map((item) => (
                    <View key={item.id} className="mt-3 flex-row items-center">
                      <UploadCloud size={15} color={colors.gold[600]} />
                      <Text className="ml-2 flex-1 text-xs text-navy-text" numberOfLines={1}>
                        {item.method} {item.path}
                      </Text>
                      <Text className="text-xs text-navy-muted">
                        {formatSavedAt(item.capturedAt, t)}
                      </Text>
                    </View>
                  ))}
                  <View className="mt-4">
                    <Button
                      label={
                        flushMutation.isPending
                          ? t('offline.outboxSending')
                          : t('offline.outboxSendCta')
                      }
                      onPress={handleFlush}
                      loading={flushMutation.isPending}
                      disabled={!isOnline}
                      leftIcon={UploadCloud}
                      variant="outline"
                    />
                  </View>
                </>
              )}
              {flushMutation.isSuccess && flushMutation.data ? (
                <Text className="mt-2 text-xs" style={{ color: colors.semantic.success }}>
                  {t('offline.outboxSent', { count: flushMutation.data.queued })}
                </Text>
              ) : null}
            </View>

            <NoteCard tone="info" icon={ShieldCheck} body={t('offline.privacyNote')} />

            {/* Opt out */}
            <SectionTitle text={t('offline.disableTitle')} />
            <Text className="mt-1 text-xs text-navy-muted">{t('offline.disableBody')}</Text>
            <Pressable
              onPress={handleDisable}
              disabled={disableMutation.isPending}
              className="mt-3 h-14 flex-row items-center justify-center rounded-2xl border"
              style={{ borderColor: colors.semantic.danger, opacity: disableMutation.isPending ? 0.6 : 1 }}
            >
              <Trash2 size={17} color={colors.semantic.danger} />
              <Text className="ml-2 font-semibold" style={{ color: colors.semantic.danger }}>
                {disableMutation.isPending ? t('offline.erasing') : t('offline.disableCta')}
              </Text>
            </Pressable>
          </>
        )}
      </ScrollView>
    </Screen>
  );
}

function ConnectivityPill({ isOnline, t }: { isOnline: boolean; t: (key: string) => string }) {
  const tone = isOnline
    ? { bg: colors.semantic.successSurface, fg: colors.semantic.success, Icon: Wifi }
    : { bg: colors.semantic.warningSurface, fg: colors.semantic.warning, Icon: CloudOff };

  return (
    <View
      className="mt-4 flex-row items-center self-start rounded-full px-3 py-1.5"
      style={{ backgroundColor: tone.bg }}
    >
      <tone.Icon size={13} color={tone.fg} />
      <Text className="ml-1.5 text-xs font-semibold" style={{ color: tone.fg }}>
        {isOnline ? t('offline.statusOnline') : t('offline.statusOffline')}
      </Text>
    </View>
  );
}

function SectionTitle({ text }: { text: string }) {
  return (
    <Text className="mt-6 text-xs font-bold uppercase tracking-wide text-navy-muted">{text}</Text>
  );
}

function ModeOption({
  selected,
  onPress,
  icon: Icon,
  title,
  body,
}: {
  selected: boolean;
  onPress: () => void;
  icon: LucideIcon;
  title: string;
  body: string;
}) {
  return (
    <Pressable
      onPress={onPress}
      className="mt-2 flex-row items-start rounded-2xl border bg-white p-4"
      style={{ borderColor: selected ? colors.gold[500] : colors.cream[300] }}
    >
      <View className="h-10 w-10 items-center justify-center rounded-full bg-gold-50">
        <Icon size={18} color={colors.gold[600]} />
      </View>
      <View className="ml-3 flex-1">
        <Text className="text-sm font-bold text-navy-text">{title}</Text>
        <Text className="mt-0.5 text-xs text-navy-secondary">{body}</Text>
      </View>
      <View
        className="ml-2 h-5 w-5 items-center justify-center rounded-full border"
        style={{
          borderColor: selected ? colors.gold[500] : colors.cream[300],
          backgroundColor: selected ? colors.gold[500] : 'transparent',
        }}
      >
        {selected ? <Check size={12} color={colors.white} /> : null}
      </View>
    </Pressable>
  );
}

function DetailRow({
  icon: Icon,
  label,
  value,
  danger = false,
}: {
  icon: LucideIcon;
  label: string;
  value: string;
  danger?: boolean;
}) {
  return (
    <View className="mb-3 flex-row items-start">
      <Icon size={15} color={colors.navy.muted} />
      <Text className="ml-2 text-xs text-navy-muted" style={{ width: 110 }}>
        {label}
      </Text>
      <Text
        className="flex-1 text-xs font-semibold"
        style={{ color: danger ? colors.semantic.danger : colors.navy.text }}
        numberOfLines={2}
      >
        {value}
      </Text>
    </View>
  );
}

function ScopeRow({
  scope,
  isLast,
  t,
}: {
  scope: ScopeCacheSummary;
  isLast: boolean;
  t: (key: string, options?: Record<string, unknown>) => string;
}) {
  const Icon = SCOPE_ICON[scope.scope];
  const savedWhen = formatSavedAt(scope.cachedAt, t);

  return (
    <>
      <View className="flex-row items-center px-4 py-4">
        <View className="h-9 w-9 items-center justify-center rounded-full bg-gold-50">
          <Icon size={16} color={colors.gold[600]} />
        </View>
        <View className="ml-3 flex-1">
          <Text className="text-sm font-semibold text-navy-text">
            {t(SCOPE_LABEL_KEY[scope.scope])}
          </Text>
          <Text className="mt-0.5 text-xs text-navy-muted">
            {savedWhen ? t('offline.lastSaved', { when: savedWhen }) : t('offline.scopeEmpty')}
          </Text>
        </View>
        {scope.itemCount !== null ? (
          <Text className="text-xs font-semibold text-gold-600">
            {t('offline.itemsSaved', { count: scope.itemCount })}
          </Text>
        ) : null}
      </View>
      {!isLast ? <View className="h-px bg-cream-200" style={{ marginLeft: 60 }} /> : null}
    </>
  );
}

function NoteCard({
  tone,
  icon: Icon,
  body,
}: {
  tone: 'warning' | 'info';
  icon: LucideIcon;
  body: string;
}) {
  const palette =
    tone === 'warning'
      ? { bg: colors.semantic.warningSurface, fg: colors.semantic.warning }
      : { bg: colors.gold[50], fg: colors.gold[600] };

  return (
    <View
      className="mt-4 flex-row items-start rounded-2xl p-4"
      style={{ backgroundColor: palette.bg }}
    >
      <Icon size={16} color={palette.fg} />
      <Text className="ml-3 flex-1 text-xs text-navy-secondary">{body}</Text>
    </View>
  );
}
