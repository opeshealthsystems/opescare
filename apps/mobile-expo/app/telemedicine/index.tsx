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
import { LinearGradient } from 'expo-linear-gradient';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  Building2,
  CalendarPlus,
  Check,
  ChevronRight,
  Clock,
  FileText,
  Lock,
  MessageCircle,
  Mic,
  MicOff,
  Monitor,
  PhoneOff,
  Pill,
  ShieldCheck,
  Stethoscope,
  User,
  Video,
  VideoOff,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { colors } from '../../theme/tokens';
import { initialsFor } from '../../lib/api/messagingQueries';
import {
  useEndTeleconsultation,
  useGrantTelemedicineConsent,
  useJoinTeleconsultation,
  useTeleconsultation,
  useTeleconsultations,
  type TeleconsultationSummary,
} from '../../lib/api/queries';

type Scope = 'upcoming' | 'past';

/**
 * Telemedicine visit hub — reachable from the Messages tab ("Video visits").
 * Reference: a_screenshot_of_a_mobile_telehealth_video_call_ui.png, translated
 * to our gold/cream tokens.
 *
 * Everything here is real against `MobileTelemedicineController`: the list,
 * the consent grant, join and end-call all hit live endpoints.
 *
 * Two honest constraints shape this screen:
 *
 *  1. **The demo patient has zero teleconsultations, and no patient-facing
 *     endpoint can create one.** `Teleconsultation` rows are only ever written
 *     by `TelemedicineService::schedule()`, whose two callers are the staff web
 *     portal and the B2B integration API — the mobile route group has no POST
 *     that creates a consultation, and booking a normal appointment never
 *     produces one. So the empty state is the primary state, and it is built to
 *     *explain how a visit gets scheduled* rather than to look like a failure.
 *     Nothing is invented to fill the list.
 *  2. **There is no WebRTC/camera SDK in this app** (none installed, none in
 *     scope to add), so the call panel is an honest connected/live state —
 *     provider initials, a real Live badge and a duration timer driven by the
 *     actual `call_session.started_at` — not a simulated camera feed. Mute and
 *     camera are local device-level toggles, exactly what a client SDK would
 *     own; End call is a real mutation.
 */
export default function TelemedicineScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const [scope, setScope] = useState<Scope>('upcoming');
  const [selectedId, setSelectedId] = useState<string | null>(null);

  const listQuery = useTeleconsultations(scope);

  if (selectedId) {
    return <ConsultationDetail id={selectedId} onBack={() => setSelectedId(null)} />;
  }

  const consultations = listQuery.data ?? [];

  return (
    <Screen className="px-0">
      <View className="flex-row items-center px-6 pb-4 pt-2">
        <Pressable
          onPress={() => router.back()}
          hitSlop={8}
          accessibilityRole="button"
          accessibilityLabel={t('telemedicine.backToVisits')}
          className="h-11 w-11 items-center justify-center rounded-full border"
          style={{ borderColor: colors.gold[300] }}
        >
          <ArrowLeft size={18} color={colors.gold[600]} />
        </Pressable>
        <View className="ml-3 flex-1">
          <Text className="text-2xl font-extrabold text-navy-text">{t('telemedicine.title')}</Text>
          <Text className="mt-0.5 text-xs text-navy-secondary">{t('telemedicine.subtitle')}</Text>
        </View>
      </View>

      <View
        className="mx-6 mb-4 flex-row rounded-2xl p-1"
        style={{ backgroundColor: colors.white, borderWidth: 1, borderColor: colors.cream[300] }}
      >
        <SegButton
          label={t('telemedicine.tabUpcoming')}
          active={scope === 'upcoming'}
          onPress={() => setScope('upcoming')}
        />
        <SegButton
          label={t('telemedicine.tabPast')}
          active={scope === 'past'}
          onPress={() => setScope('past')}
        />
      </View>

      {listQuery.isLoading ? (
        <View className="flex-1 items-center justify-center">
          <ActivityIndicator color={colors.gold[500]} />
        </View>
      ) : listQuery.isError ? (
        <View className="flex-1 items-center justify-center px-8">
          <View className="mb-4 h-16 w-16 items-center justify-center rounded-full bg-cream-200">
            <Video size={26} color={colors.navy.muted} />
          </View>
          <Text className="mb-4 text-center text-sm text-navy-secondary">
            {t('telemedicine.loadError')}
          </Text>
          <Pressable
            onPress={() => listQuery.refetch()}
            className="rounded-full border px-5 py-2.5"
            style={{ borderColor: colors.gold[500] }}
          >
            <Text className="text-sm font-semibold text-gold-600">{t('telemedicine.retry')}</Text>
          </Pressable>
        </View>
      ) : consultations.length > 0 ? (
        <FlatList
          data={consultations}
          keyExtractor={(item) => item.id}
          contentContainerStyle={{ paddingHorizontal: 24, paddingBottom: 28 }}
          refreshControl={
            <RefreshControl
              refreshing={listQuery.isRefetching}
              onRefresh={() => listQuery.refetch()}
              tintColor={colors.gold[500]}
            />
          }
          renderItem={({ item }) => (
            <ConsultationRow item={item} onPress={() => setSelectedId(item.id)} />
          )}
        />
      ) : scope === 'upcoming' ? (
        <NoUpcomingVisits onRefresh={() => listQuery.refetch()} refreshing={listQuery.isRefetching} />
      ) : (
        <ScrollView
          contentContainerStyle={{ flexGrow: 1, justifyContent: 'center', paddingHorizontal: 32 }}
          refreshControl={
            <RefreshControl
              refreshing={listQuery.isRefetching}
              onRefresh={() => listQuery.refetch()}
              tintColor={colors.gold[500]}
            />
          }
        >
          <View className="items-center">
            <View className="mb-4 h-16 w-16 items-center justify-center rounded-full bg-cream-200">
              <Clock size={26} color={colors.navy.muted} />
            </View>
            <Text className="mb-2 text-center text-lg font-bold text-navy-text">
              {t('telemedicine.emptyPastTitle')}
            </Text>
            <Text className="text-center text-sm leading-5 text-navy-secondary">
              {t('telemedicine.emptyPastBody')}
            </Text>
          </View>
        </ScrollView>
      )}
    </Screen>
  );
}

/**
 * The empty state IS the design here — see the class docblock. It has to
 * answer "why is this blank and what do I do about it?", so it walks through
 * how a video visit actually comes into existence in this platform and then
 * offers the two things the patient genuinely can do from the app.
 */
function NoUpcomingVisits({ onRefresh, refreshing }: { onRefresh: () => void; refreshing: boolean }) {
  const { t } = useTranslation();
  const router = useRouter();

  return (
    <ScrollView
      contentContainerStyle={{ paddingHorizontal: 24, paddingBottom: 32 }}
      showsVerticalScrollIndicator={false}
      refreshControl={
        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.gold[500]} />
      }
    >
      <LinearGradient
        colors={[colors.gold[600], colors.gold[500]]}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        // Inline style: className has no effect on LinearGradient (no cssInterop).
        style={{ borderRadius: 24, paddingVertical: 28, paddingHorizontal: 20, alignItems: 'center' }}
      >
        <View
          className="h-16 w-16 items-center justify-center rounded-full"
          style={{ backgroundColor: 'rgba(255,255,255,0.2)' }}
        >
          <Video size={28} color={colors.white} />
        </View>
        <Text className="mt-4 text-center text-lg font-extrabold text-white">
          {t('telemedicine.emptyUpcomingTitle')}
        </Text>
        <Text
          className="mt-1.5 text-center text-xs leading-5"
          style={{ color: 'rgba(255,255,255,0.88)' }}
        >
          {t('telemedicine.emptyUpcomingBody')}
        </Text>
      </LinearGradient>

      <Text className="mb-3 mt-6 text-sm font-extrabold text-navy-text">
        {t('telemedicine.howTitle')}
      </Text>
      <View className="rounded-2xl bg-white p-4">
        <HowStep
          index={1}
          icon={CalendarPlus}
          title={t('telemedicine.step1Title')}
          body={t('telemedicine.step1Body')}
        />
        <HowStep
          index={2}
          icon={Stethoscope}
          title={t('telemedicine.step2Title')}
          body={t('telemedicine.step2Body')}
        />
        <HowStep
          index={3}
          icon={Video}
          title={t('telemedicine.step3Title')}
          body={t('telemedicine.step3Body')}
          last
        />
      </View>

      <View className="mt-5">
        <Button
          label={t('telemedicine.ctaBook')}
          onPress={() => router.push('/appointments/book')}
          leftIcon={CalendarPlus}
          showChevron={false}
        />
        <View className="mt-3">
          <Button
            label={t('telemedicine.ctaMessage')}
            onPress={() => router.push('/(tabs)/messages')}
            variant="outline"
          />
        </View>
      </View>

      <View
        className="mt-6 rounded-2xl p-4"
        style={{ backgroundColor: colors.semantic.infoSurface }}
      >
        <View className="mb-2 flex-row items-center">
          <ShieldCheck size={15} color={colors.semantic.info} />
          <Text className="ml-2 text-xs font-extrabold" style={{ color: colors.semantic.info }}>
            {t('telemedicine.prepareTitle')}
          </Text>
        </View>
        {[
          t('telemedicine.prepare1'),
          t('telemedicine.prepare2'),
          t('telemedicine.prepare3'),
        ].map((line) => (
          <View key={line} className="mb-1 flex-row items-start">
            <View className="mt-1">
              <Check size={12} color={colors.semantic.info} />
            </View>
            <Text className="ml-2 flex-1 text-[11px] leading-4 text-navy-secondary">{line}</Text>
          </View>
        ))}
      </View>
    </ScrollView>
  );
}

function HowStep({
  index,
  icon: Icon,
  title,
  body,
  last,
}: {
  index: number;
  icon: LucideIcon;
  title: string;
  body: string;
  last?: boolean;
}) {
  return (
    <View className="flex-row">
      <View className="items-center" style={{ width: 36 }}>
        <View
          className="h-9 w-9 items-center justify-center rounded-full"
          style={{ backgroundColor: colors.gold[50], borderWidth: 1, borderColor: colors.gold[100] }}
        >
          <Icon size={16} color={colors.gold[600]} />
        </View>
        {!last ? (
          <View className="my-1 w-0.5 flex-1" style={{ backgroundColor: colors.cream[300] }} />
        ) : null}
      </View>
      <View className={`ml-3 flex-1 ${last ? '' : 'pb-4'}`}>
        <View className="flex-row items-center">
          <View
            className="mr-2 h-4 w-4 items-center justify-center rounded-full"
            style={{ backgroundColor: colors.gold[500] }}
          >
            <Text className="text-[9px] font-extrabold text-white">{index}</Text>
          </View>
          <Text className="flex-1 text-xs font-extrabold text-navy-text">{title}</Text>
        </View>
        <Text className="mt-1 text-[11px] leading-4 text-navy-secondary">{body}</Text>
      </View>
    </View>
  );
}

function SegButton({ label, active, onPress }: { label: string; active: boolean; onPress: () => void }) {
  return (
    <Pressable
      onPress={onPress}
      accessibilityRole="button"
      accessibilityState={{ selected: active }}
      className="flex-1 items-center rounded-xl py-2.5"
      style={{ backgroundColor: active ? colors.gold[500] : 'transparent' }}
    >
      <Text
        className="text-sm font-bold"
        style={{ color: active ? colors.white : colors.navy.secondary }}
      >
        {label}
      </Text>
    </Pressable>
  );
}

function ConsultationRow({ item, onPress }: { item: TeleconsultationSummary; onPress: () => void }) {
  const { t, i18n } = useTranslation();
  const sc = statusColors(item.status);
  const name = item.provider_name ?? t('telemedicine.careTeam');
  return (
    <Pressable onPress={onPress} accessibilityRole="button" className="mb-3 rounded-2xl bg-white p-4">
      <View className="flex-row items-center">
        <View
          className="h-12 w-12 items-center justify-center rounded-full"
          style={{ backgroundColor: colors.gold[50], borderWidth: 1.5, borderColor: colors.gold[100] }}
        >
          <Text className="text-xs font-extrabold" style={{ color: colors.gold[700] }}>
            {initialsFor(name)}
          </Text>
        </View>
        <View className="ml-3 flex-1">
          <Text className="text-sm font-bold text-navy-text" numberOfLines={1}>
            {name}
          </Text>
          <View className="mt-1 flex-row items-center">
            <Clock size={11} color={colors.navy.muted} />
            <Text className="ml-1 text-[11px] text-navy-secondary">
              {item.scheduled_at ? formatDateTime(item.scheduled_at, i18n.language) : '—'}
            </Text>
          </View>
        </View>
        <ChevronRight size={18} color={colors.navy.muted} />
      </View>
      <View className="mt-3 flex-row items-center justify-between">
        <View className="rounded-full px-3 py-1" style={{ backgroundColor: sc.bg }}>
          <Text className="text-[10px] font-bold" style={{ color: sc.fg }}>
            {statusLabel(t, item.status)}
          </Text>
        </View>
        {item.duration_seconds ? (
          <Text className="text-[11px] text-navy-muted">
            {t('telemedicine.callDuration', { duration: formatDuration(item.duration_seconds) })}
          </Text>
        ) : item.platform ? (
          <View className="flex-row items-center">
            <Monitor size={11} color={colors.navy.muted} />
            <Text className="ml-1 text-[11px] text-navy-muted">
              {platformLabel(item.platform)}
            </Text>
          </View>
        ) : null}
      </View>
    </Pressable>
  );
}

function ConsultationDetail({ id, onBack }: { id: string; onBack: () => void }) {
  const { t, i18n } = useTranslation();
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
        // `recording_consent` is the only field the endpoint validates.
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
        <View className="mb-4 h-16 w-16 items-center justify-center rounded-full bg-cream-200">
          <Video size={26} color={colors.navy.muted} />
        </View>
        <Text className="mb-4 text-center text-sm text-navy-secondary">
          {t('telemedicine.detailLoadError')}
        </Text>
        <Pressable
          onPress={onBack}
          className="rounded-full border px-5 py-2.5"
          style={{ borderColor: colors.gold[500] }}
        >
          <Text className="text-sm font-semibold text-gold-600">{t('telemedicine.backToVisits')}</Text>
        </Pressable>
      </Screen>
    );
  }

  const isFinished = ['completed', 'cancelled', 'failed'].includes(consultation.status);
  const providerLabel = consultation.provider_name ?? t('telemedicine.careTeam');
  const consented = !!consultation.consent?.consented && !consultation.consent?.revoked_at;
  const waiting = consultation.waiting_room;

  return (
    <Screen className="px-0">
      <View className="flex-row items-center px-6 pb-3 pt-2">
        <Pressable
          onPress={onBack}
          hitSlop={8}
          accessibilityRole="button"
          accessibilityLabel={t('telemedicine.backToVisits')}
          className="h-11 w-11 items-center justify-center rounded-full border"
          style={{ borderColor: colors.gold[300] }}
        >
          <ArrowLeft size={18} color={colors.gold[600]} />
        </Pressable>
        <Text className="ml-3 flex-1 text-lg font-extrabold text-navy-text" numberOfLines={1}>
          {providerLabel}
        </Text>
        <View className="rounded-full px-3 py-1" style={{ backgroundColor: statusColors(consultation.status).bg }}>
          <Text className="text-[10px] font-bold" style={{ color: statusColors(consultation.status).fg }}>
            {statusLabel(t, consultation.status)}
          </Text>
        </View>
      </View>

      <ScrollView className="flex-1 px-6" showsVerticalScrollIndicator={false}>
        <View className="flex-row items-center rounded-2xl bg-white p-4">
          <View
            className="h-14 w-14 items-center justify-center rounded-full"
            style={{ backgroundColor: colors.gold[50], borderWidth: 1.5, borderColor: colors.gold[100] }}
          >
            <Text className="text-sm font-extrabold" style={{ color: colors.gold[700] }}>
              {initialsFor(providerLabel)}
            </Text>
          </View>
          <View className="ml-3 flex-1">
            <Text className="text-base font-bold text-navy-text">{providerLabel}</Text>
            <Text className="mt-0.5 text-xs text-navy-secondary">
              {consultation.scheduled_at
                ? t('telemedicine.scheduledFor', {
                    date: formatDateTime(consultation.scheduled_at, i18n.language),
                  })
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
            <Text className="text-xs leading-4 text-navy-secondary">
              {t('telemedicine.encryptedBody')}
            </Text>
          </View>
        </View>

        {waiting && !isActive && !isFinished ? (
          <View
            className="mt-3 flex-row items-center rounded-2xl p-4"
            style={{ backgroundColor: colors.semantic.warningSurface }}
          >
            <Clock size={16} color={colors.semantic.warning} />
            <View className="ml-3 flex-1">
              <Text className="text-sm font-bold" style={{ color: colors.semantic.warning }}>
                {t('telemedicine.waitingTitle')}
              </Text>
              <Text className="text-xs text-navy-secondary">
                {waiting.estimated_wait_minutes != null
                  ? t('telemedicine.waitingEstimate', { minutes: waiting.estimated_wait_minutes })
                  : t('telemedicine.waitingNoEstimate')}
              </Text>
            </View>
          </View>
        ) : null}

        <View
          className="mt-4 items-center justify-center rounded-2xl px-4 py-10"
          style={{ backgroundColor: colors.navy.text }}
        >
          {isActive ? (
            <View
              className="mb-3 flex-row items-center self-start rounded-full px-3 py-1"
              style={{ backgroundColor: 'rgba(0,0,0,0.35)' }}
            >
              <View
                className="mr-2 h-2 w-2 rounded-full"
                style={{ backgroundColor: colors.semantic.danger }}
              />
              <Text className="text-xs font-bold text-white">
                {t('telemedicine.live')} · {formatDuration(elapsed)}
              </Text>
            </View>
          ) : null}
          <View
            className="h-20 w-20 items-center justify-center rounded-full"
            style={{ backgroundColor: 'rgba(255,255,255,0.15)' }}
          >
            <Text className="text-xl font-extrabold text-white">{initialsFor(providerLabel)}</Text>
          </View>
          <Text className="mt-3 text-sm" style={{ color: 'rgba(255,255,255,0.85)' }}>
            {isActive
              ? providerLabel
              : isFinished
                ? t('telemedicine.callEnded')
                : statusLabel(t, consultation.status)}
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
              <CallControl
                icon={PhoneOff}
                danger
                loading={busy}
                onPress={handleEndCall}
                label={t('telemedicine.endCall')}
              />
              <CallControl
                icon={MessageCircle}
                onPress={() => router.push('/(tabs)/messages')}
                label={t('telemedicine.chat')}
              />
            </View>
          ) : null}
        </View>

        {!isActive && !isFinished ? (
          <View className="mt-4">
            {!consented ? (
              <View className="mb-3 rounded-2xl bg-white p-4">
                <View className="mb-2 flex-row items-center">
                  <ShieldCheck size={16} color={colors.gold[600]} />
                  <Text className="ml-2 text-sm font-extrabold text-navy-text">
                    {t('telemedicine.consentTitle')}
                  </Text>
                </View>
                <Text className="text-[11px] leading-5 text-navy-secondary">
                  {t('telemedicine.consentAlertBody')}
                </Text>
              </View>
            ) : (
              <View
                className="mb-3 flex-row items-center rounded-2xl px-4 py-3"
                style={{ backgroundColor: colors.semantic.successSurface }}
              >
                <Check size={14} color={colors.semantic.success} />
                <Text className="ml-2 flex-1 text-[11px] font-semibold text-navy-secondary">
                  {consultation.consent?.consented_at
                    ? t('telemedicine.consentGrantedOn', {
                        date: formatDateTime(consultation.consent.consented_at, i18n.language),
                      })
                    : t('telemedicine.consentGranted')}
                </Text>
              </View>
            )}
            <Button
              label={
                busy
                  ? t('telemedicine.connecting')
                  : consented
                    ? t('telemedicine.join')
                    : t('telemedicine.consentAndJoin')
              }
              onPress={performJoin}
              loading={busy}
              leftIcon={Video}
              showChevron={false}
            />
          </View>
        ) : null}

        {isFinished ? (
          <View className="mt-4 rounded-2xl bg-white p-4">
            <Text className="text-sm font-bold text-navy-text">
              {statusLabel(t, consultation.status)}
            </Text>
            {consultation.duration_seconds ? (
              <Text className="mt-1 text-xs text-navy-muted">
                {t('telemedicine.callDuration', {
                  duration: formatDuration(consultation.duration_seconds),
                })}
              </Text>
            ) : null}
          </View>
        ) : null}

        <View className="mt-4 rounded-2xl bg-white p-4">
          <Text className="mb-3 text-sm font-extrabold text-navy-text">
            {t('telemedicine.visitDetails')}
          </Text>
          <DetailRow icon={User} label={t('telemedicine.providerLabel')} value={providerLabel} />
          <DetailRow
            icon={Building2}
            label={t('telemedicine.facilityLabel')}
            value={consultation.facility_name ?? '—'}
          />
          <DetailRow
            icon={Monitor}
            label={t('telemedicine.platformLabel')}
            value={consultation.platform ? platformLabel(consultation.platform) : '—'}
            last
          />
        </View>

        <Text className="mb-3 mt-5 text-sm font-extrabold text-navy-text">
          {t('telemedicine.duringVisit')}
        </Text>
        <View className="mb-8 flex-row gap-3">
          <QuickTile
            icon={Pill}
            label={t('telemedicine.quickPrescriptions')}
            body={t('telemedicine.quickPrescriptionsBody')}
            onPress={() => router.push('/prescriptions')}
          />
          <QuickTile
            icon={FileText}
            label={t('telemedicine.quickDocuments')}
            body={t('telemedicine.quickDocumentsBody')}
            onPress={() => router.push('/documents')}
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
    <Pressable
      onPress={onPress}
      disabled={loading}
      accessibilityRole="button"
      accessibilityLabel={label}
      className="items-center"
      style={{ width: 56 }}
    >
      <View
        className="h-12 w-12 items-center justify-center rounded-full"
        style={{
          backgroundColor: danger
            ? colors.semantic.danger
            : active
              ? colors.gold[500]
              : 'rgba(255,255,255,0.15)',
        }}
      >
        {loading ? (
          <ActivityIndicator size="small" color={colors.white} />
        ) : (
          <Icon size={18} color={colors.white} />
        )}
      </View>
      <Text
        className="mt-1 text-center text-[10px]"
        style={{ color: 'rgba(255,255,255,0.85)' }}
        numberOfLines={1}
      >
        {label}
      </Text>
    </Pressable>
  );
}

function DetailRow({
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
    <View
      className="flex-row items-center py-2"
      style={last ? undefined : { borderBottomWidth: 1, borderColor: colors.cream[200] }}
    >
      <Icon size={14} color={colors.navy.muted} />
      <Text className="ml-2 text-xs text-navy-muted">{label}</Text>
      <Text className="ml-auto flex-1 text-right text-xs font-bold text-navy-text" numberOfLines={1}>
        {value}
      </Text>
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
    <Pressable onPress={onPress} accessibilityRole="button" className="flex-1 rounded-2xl bg-white p-3">
      <View
        className="mb-2 h-9 w-9 items-center justify-center rounded-full"
        style={{ backgroundColor: colors.gold[50] }}
      >
        <Icon size={16} color={colors.gold[600]} />
      </View>
      <Text className="text-[11px] font-extrabold text-navy-text">{label}</Text>
      <Text className="mt-0.5 text-[10px] leading-3 text-navy-muted" numberOfLines={2}>
        {body}
      </Text>
    </Pressable>
  );
}

/* ── Formatting ───────────────────────────────────────────────────────── */

function formatDateTime(iso: string, locale: string) {
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) return iso;
  return date.toLocaleString(locale?.startsWith('fr') ? 'fr-FR' : 'en-US', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function formatDuration(totalSeconds: number) {
  const m = Math.floor(totalSeconds / 60);
  const s = totalSeconds % 60;
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
}

/** `teleconsultations.platform` is a free string; only these four values are
 * documented by the migration, so anything else is shown verbatim. */
function platformLabel(platform: string) {
  switch (platform.toLowerCase()) {
    case 'own':
      return 'OpesCare';
    case 'zoom':
      return 'Zoom';
    case 'meet':
      return 'Google Meet';
    case 'teams':
      return 'Microsoft Teams';
    default:
      return platform;
  }
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
