import { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Pressable,
  RefreshControl,
  ScrollView,
  Text,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  Calendar,
  FileText,
  Lock,
  MessageCircle,
  Mic,
  MicOff,
  PhoneOff,
  User,
  Video,
  VideoOff,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { colors } from '../../theme/tokens';
import {
  useEndTeleconsultation,
  useGrantTelemedicineConsent,
  useJoinTeleconsultation,
  useTeleconsultation,
  useTeleconsultations,
  type TeleconsultationSummary,
} from '../../lib/api/queries';

type Scope = 'upcoming' | 'past';

/** Telemedicine visit hub — reachable from the Messages tab ("Video visits").
 * Reference: a_screenshot_of_a_mobile_telehealth_video_call_ui.png, translated
 * to our gold/cream tokens. Real throughout: list, consent, join and end-call
 * all hit apps/api-laravel/.../MobileTelemedicineController. There is no
 * WebRTC/camera SDK in this app (none installed, none in scope to add), so
 * the "video panel" is an honest connected/live state — provider initials,
 * a real Live badge and a duration timer driven by the actual call_session
 * start time — rather than a simulated camera feed. Mute/camera controls are
 * local device-level toggles (no backend effect, matching what a real client
 * SDK would handle client-side); End Call is real. */
export default function TelemedicineScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const [scope, setScope] = useState<Scope>('upcoming');
  const [selectedId, setSelectedId] = useState<string | null>(null);

  const listQuery = useTeleconsultations(scope);

  if (selectedId) {
    return <ConsultationDetail id={selectedId} onBack={() => setSelectedId(null)} />;
  }

  return (
    <Screen className="px-0">
      <View className="flex-row items-center px-6 pb-2 pt-2">
        <Pressable onPress={() => router.back()} hitSlop={8} className="mr-3">
          <ArrowLeft size={20} color={colors.gold[600]} />
        </Pressable>
        <Text className="text-2xl font-extrabold text-navy-text">{t('telemedicine.title')}</Text>
      </View>

      <View className="mx-6 mb-4 flex-row rounded-full bg-white p-1">
        <SegButton label={t('telemedicine.tabUpcoming')} active={scope === 'upcoming'} onPress={() => setScope('upcoming')} />
        <SegButton label={t('telemedicine.tabPast')} active={scope === 'past'} onPress={() => setScope('past')} />
      </View>

      {listQuery.isLoading ? (
        <View className="flex-1 items-center justify-center">
          <ActivityIndicator color={colors.gold[500]} />
        </View>
      ) : listQuery.isError ? (
        <View className="flex-1 items-center justify-center px-6">
          <Text className="text-center text-sm text-navy-muted">{t('telemedicine.loadError')}</Text>
        </View>
      ) : listQuery.data && listQuery.data.length > 0 ? (
        <FlatList
          data={listQuery.data}
          keyExtractor={(item) => item.id}
          contentContainerStyle={{ paddingHorizontal: 24, paddingBottom: 24 }}
          refreshControl={
            <RefreshControl
              refreshing={listQuery.isRefetching}
              onRefresh={() => listQuery.refetch()}
              tintColor={colors.gold[500]}
            />
          }
          renderItem={({ item }) => <ConsultationRow item={item} onPress={() => setSelectedId(item.id)} />}
        />
      ) : (
        <View className="flex-1 items-center justify-center px-8">
          <View className="mb-4 h-16 w-16 items-center justify-center rounded-full bg-gold-100">
            <Video size={26} color={colors.gold[500]} />
          </View>
          <Text className="mb-2 text-center text-lg font-bold text-navy-text">
            {scope === 'upcoming' ? t('telemedicine.emptyUpcomingTitle') : t('telemedicine.emptyPastTitle')}
          </Text>
          <Text className="text-center text-sm text-navy-secondary">
            {scope === 'upcoming' ? t('telemedicine.emptyUpcomingBody') : t('telemedicine.emptyPastBody')}
          </Text>
        </View>
      )}
    </Screen>
  );
}

function SegButton({ label, active, onPress }: { label: string; active: boolean; onPress: () => void }) {
  return (
    <Pressable
      onPress={onPress}
      className="flex-1 items-center rounded-full py-2"
      style={{ backgroundColor: active ? colors.gold[500] : 'transparent' }}
    >
      <Text className={active ? 'text-sm font-semibold text-white' : 'text-sm font-semibold text-navy-secondary'}>
        {label}
      </Text>
    </Pressable>
  );
}

function ConsultationRow({ item, onPress }: { item: TeleconsultationSummary; onPress: () => void }) {
  const { t } = useTranslation();
  const sc = statusColors(item.status);
  return (
    <Pressable onPress={onPress} className="mb-3 flex-row items-center rounded-2xl bg-white p-4">
      <View className="h-12 w-12 items-center justify-center rounded-full bg-gold-100">
        <Video size={20} color={colors.gold[600]} />
      </View>
      <View className="ml-3 flex-1">
        <Text className="text-sm font-bold text-navy-text" numberOfLines={1}>
          {item.provider_name ?? t('telemedicine.careTeam')}
        </Text>
        <Text className="mt-0.5 text-xs text-navy-muted">
          {item.scheduled_at ? formatDateTime(item.scheduled_at) : '—'}
        </Text>
      </View>
      <View className="rounded-full px-3 py-1" style={{ backgroundColor: sc.bg }}>
        <Text className="text-[10px] font-bold" style={{ color: sc.fg }}>
          {statusLabel(t, item.status)}
        </Text>
      </View>
    </Pressable>
  );
}

function ConsultationDetail({ id, onBack }: { id: string; onBack: () => void }) {
  const { t } = useTranslation();
  const router = useRouter();
  const detailQuery = useTeleconsultation(id);
  const grantConsent = useGrantTelemedicineConsent(id);
  const joinCall = useJoinTeleconsultation(id);
  const endCall = useEndTeleconsultation(id);

  const [muted, setMuted] = useState(false);
  const [cameraOff, setCameraOff] = useState(false);
  const [elapsed, setElapsed] = useState(0);
  const [busy, setBusy] = useState(false);

  const consultation = detailQuery.data;
  const isActive = consultation?.call_session?.status === 'active';

  useEffect(() => {
    if (!isActive || !consultation?.call_session?.started_at) {
      setElapsed(0);
      return;
    }
    const startedAt = new Date(consultation.call_session.started_at).getTime();
    const tick = () => setElapsed(Math.max(0, Math.floor((Date.now() - startedAt) / 1000)));
    tick();
    const interval = setInterval(tick, 1000);
    return () => clearInterval(interval);
  }, [isActive, consultation?.call_session?.started_at]);

  const performJoin = async () => {
    setBusy(true);
    try {
      if (!consultation?.consent?.consented) {
        await grantConsent.mutateAsync(false);
      }
      await joinCall.mutateAsync();
      await detailQuery.refetch();
    } catch {
      Alert.alert(t('telemedicine.actionError'));
    } finally {
      setBusy(false);
    }
  };

  const handleJoinPress = () => {
    if (consultation?.consent?.consented) {
      performJoin();
      return;
    }
    Alert.alert(t('telemedicine.consentAlertTitle'), t('telemedicine.consentAlertBody'), [
      { text: t('telemedicine.consentAlertCancel'), style: 'cancel' },
      { text: t('telemedicine.consentAlertAccept'), onPress: performJoin },
    ]);
  };

  const handleEndCall = async () => {
    setBusy(true);
    try {
      await endCall.mutateAsync();
      await detailQuery.refetch();
    } catch {
      Alert.alert(t('telemedicine.actionError'));
    } finally {
      setBusy(false);
    }
  };

  if (detailQuery.isLoading) {
    return (
      <Screen className="items-center justify-center">
        <ActivityIndicator color={colors.gold[500]} />
      </Screen>
    );
  }

  if (detailQuery.isError || !consultation) {
    return (
      <Screen className="items-center justify-center px-8">
        <Text className="mb-4 text-center text-sm text-navy-muted">{t('telemedicine.detailLoadError')}</Text>
        <Pressable onPress={onBack}>
          <Text className="text-sm font-semibold text-gold-600">{t('telemedicine.backToVisits')}</Text>
        </Pressable>
      </Screen>
    );
  }

  const isFinished = ['completed', 'cancelled', 'failed'].includes(consultation.status);
  const providerLabel = consultation.provider_name ?? t('telemedicine.careTeam');
  const providerInitial = providerLabel.trim().charAt(0).toUpperCase() || '?';

  return (
    <Screen className="px-0">
      <View className="flex-row items-center px-6 pb-3 pt-2">
        <Pressable onPress={onBack} hitSlop={8} className="mr-3">
          <ArrowLeft size={20} color={colors.gold[600]} />
        </Pressable>
        <Text className="flex-1 text-lg font-bold text-navy-text" numberOfLines={1}>
          {providerLabel}
        </Text>
      </View>

      <ScrollView className="flex-1 px-6" showsVerticalScrollIndicator={false}>
        <View className="flex-row items-center rounded-2xl bg-white p-4">
          <View className="h-14 w-14 items-center justify-center rounded-full bg-gold-100">
            <Text className="text-lg font-extrabold text-gold-600">{providerInitial}</Text>
          </View>
          <View className="ml-3 flex-1">
            <Text className="text-base font-bold text-navy-text">{providerLabel}</Text>
            <Text className="mt-0.5 text-xs text-navy-muted">
              {consultation.scheduled_at
                ? t('telemedicine.scheduledFor', { date: formatDateTime(consultation.scheduled_at) })
                : statusLabel(t, consultation.status)}
            </Text>
          </View>
        </View>

        <View
          className="mt-3 flex-row items-center rounded-2xl p-4"
          style={{ backgroundColor: colors.semantic.successSurface }}
        >
          <Lock size={16} color={colors.semantic.success} />
          <View className="ml-3 flex-1">
            <Text className="text-sm font-bold" style={{ color: colors.semantic.success }}>
              {t('telemedicine.encryptedTitle')}
            </Text>
            <Text className="text-xs text-navy-secondary">{t('telemedicine.encryptedBody')}</Text>
          </View>
        </View>

        <View
          className="mt-4 items-center justify-center rounded-2xl px-4 py-10"
          style={{ backgroundColor: colors.navy.text }}
        >
          {isActive ? (
            <View
              className="mb-3 flex-row items-center self-start rounded-full px-3 py-1"
              style={{ backgroundColor: 'rgba(0,0,0,0.3)' }}
            >
              <View className="mr-2 h-2 w-2 rounded-full bg-danger" />
              <Text className="text-xs font-semibold text-white">
                {t('telemedicine.live')} · {formatDuration(elapsed)}
              </Text>
            </View>
          ) : null}
          <View className="h-20 w-20 items-center justify-center rounded-full" style={{ backgroundColor: 'rgba(255,255,255,0.15)' }}>
            <Text className="text-2xl font-extrabold text-white">{providerInitial}</Text>
          </View>
          <Text className="mt-3 text-sm" style={{ color: 'rgba(255,255,255,0.8)' }}>
            {isActive ? providerLabel : isFinished ? t('telemedicine.callEnded') : statusLabel(t, consultation.status)}
          </Text>

          {isActive ? (
            <View className="mt-6 flex-row items-center gap-4">
              <CallControl
                icon={muted ? MicOff : Mic}
                active={muted}
                onPress={() => setMuted((m) => !m)}
                label={muted ? t('telemedicine.unmute') : t('telemedicine.mute')}
              />
              <CallControl
                icon={cameraOff ? VideoOff : Video}
                active={cameraOff}
                onPress={() => setCameraOff((c) => !c)}
                label={cameraOff ? t('telemedicine.camera') : t('telemedicine.cameraOff')}
              />
              <CallControl icon={PhoneOff} danger loading={busy} onPress={handleEndCall} label={t('telemedicine.endCall')} />
              <CallControl icon={MessageCircle} onPress={() => router.push('/(tabs)/messages')} label={t('telemedicine.chat')} />
            </View>
          ) : null}
        </View>

        {!isActive && !isFinished ? (
          <View className="mt-4">
            <Button
              label={busy ? t('telemedicine.connecting') : t('telemedicine.join')}
              onPress={handleJoinPress}
              loading={busy}
              leftIcon={Video}
              showChevron={false}
            />
          </View>
        ) : null}

        {isFinished ? (
          <View className="mt-4 rounded-2xl bg-white p-4">
            <Text className="text-sm font-bold text-navy-text">{statusLabel(t, consultation.status)}</Text>
            {consultation.duration_seconds ? (
              <Text className="mt-1 text-xs text-navy-muted">
                {t('telemedicine.callDuration', { duration: formatDuration(consultation.duration_seconds) })}
              </Text>
            ) : null}
          </View>
        ) : null}

        <View className="mt-4 rounded-2xl bg-white p-4">
          <Text className="mb-3 text-sm font-bold text-navy-text">{t('telemedicine.visitDetails')}</Text>
          <DetailRow icon={User} label={t('telemedicine.providerLabel')} value={providerLabel} />
          <DetailRow icon={Calendar} label={t('telemedicine.facilityLabel')} value={consultation.facility_name ?? '—'} />
        </View>

        <View className="mb-8 mt-4 flex-row gap-3">
          <QuickTile
            icon={FileText}
            label={t('telemedicine.quickPrescriptions')}
            body={t('telemedicine.quickPrescriptionsBody')}
            onPress={() => router.push('/(tabs)/records')}
          />
          <QuickTile
            icon={MessageCircle}
            label={t('telemedicine.quickMessages')}
            body={t('telemedicine.quickMessagesBody')}
            onPress={() => router.push('/(tabs)/messages')}
          />
        </View>
      </ScrollView>
    </Screen>
  );
}

function CallControl({
  icon: Icon,
  onPress,
  label,
  active,
  danger,
  loading,
}: {
  icon: LucideIcon;
  onPress: () => void;
  label: string;
  active?: boolean;
  danger?: boolean;
  loading?: boolean;
}) {
  return (
    <Pressable onPress={onPress} disabled={loading} className="items-center" style={{ width: 56 }}>
      <View
        className="h-12 w-12 items-center justify-center rounded-full"
        style={{
          backgroundColor: danger ? colors.semantic.danger : active ? colors.gold[500] : 'rgba(255,255,255,0.15)',
        }}
      >
        {loading ? <ActivityIndicator size="small" color="white" /> : <Icon size={18} color="white" />}
      </View>
      <Text className="mt-1 text-center text-[10px]" style={{ color: 'rgba(255,255,255,0.8)' }} numberOfLines={1}>
        {label}
      </Text>
    </Pressable>
  );
}

function DetailRow({ icon: Icon, label, value }: { icon: LucideIcon; label: string; value: string }) {
  return (
    <View className="mb-2 flex-row items-center">
      <Icon size={14} color={colors.navy.muted} />
      <Text className="ml-2 text-xs text-navy-muted">{label}</Text>
      <Text className="ml-auto text-xs font-semibold text-navy-text">{value}</Text>
    </View>
  );
}

function QuickTile({
  icon: Icon,
  label,
  body,
  onPress,
}: {
  icon: LucideIcon;
  label: string;
  body: string;
  onPress: () => void;
}) {
  return (
    <Pressable onPress={onPress} className="flex-1 rounded-2xl bg-white p-4">
      <View className="mb-2 h-9 w-9 items-center justify-center rounded-full bg-gold-100">
        <Icon size={16} color={colors.gold[600]} />
      </View>
      <Text className="text-xs font-bold text-navy-text">{label}</Text>
      <Text className="mt-0.5 text-[10px] text-navy-muted" numberOfLines={2}>
        {body}
      </Text>
    </Pressable>
  );
}

function formatDateTime(iso: string) {
  try {
    return new Date(iso).toLocaleString(undefined, {
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  } catch {
    return iso;
  }
}

function formatDuration(totalSeconds: number) {
  const m = Math.floor(totalSeconds / 60);
  const s = totalSeconds % 60;
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
}

function statusLabel(t: (key: string) => string, status: string) {
  switch (status) {
    case 'scheduled':
      return t('telemedicine.statusScheduled');
    case 'waiting':
      return t('telemedicine.statusWaiting');
    case 'active':
      return t('telemedicine.statusActive');
    case 'completed':
      return t('telemedicine.statusCompleted');
    case 'cancelled':
      return t('telemedicine.statusCancelled');
    case 'failed':
      return t('telemedicine.statusFailed');
    default:
      return status;
  }
}

function statusColors(status: string): { bg: string; fg: string } {
  switch (status) {
    case 'active':
      return { bg: colors.semantic.successSurface, fg: colors.semantic.success };
    case 'waiting':
      return { bg: colors.semantic.warningSurface, fg: colors.semantic.warning };
    case 'scheduled':
      return { bg: colors.semantic.infoSurface, fg: colors.semantic.info };
    case 'cancelled':
    case 'failed':
      return { bg: colors.semantic.dangerSurface, fg: colors.semantic.danger };
    default:
      return { bg: colors.cream[200], fg: colors.navy.secondary };
  }
}
