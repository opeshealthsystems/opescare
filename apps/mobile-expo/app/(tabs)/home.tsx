import { useCallback, useState } from 'react';
import {
  ActivityIndicator,
  Pressable,
  RefreshControl,
  ScrollView,
  Text,
  View,
  type ViewStyle,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import { LinearGradient } from 'expo-linear-gradient';
import QRCode from 'react-native-qrcode-svg';
import {
  Activity,
  BadgeCheck,
  Bell,
  Cake,
  CalendarDays,
  CalendarPlus,
  ChevronRight,
  CircleAlert,
  Clock,
  Droplets,
  FlaskConical,
  FolderHeart,
  Hand,
  HeartPulse,
  Hourglass,
  MapPin,
  Pill,
  QrCode,
  RefreshCw,
  ShieldCheck,
  Store,
  Target,
  TriangleAlert,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { UnreadBadge } from '../../components/ui/UnreadBadge';
import { useAuthStore } from '../../lib/store/auth';
import { useHealthIdCard, useUpcomingAppointments } from '../../lib/api/queries';
import { useUnreadNotificationCount } from '../../lib/api/appConfigQueries';
import { colors } from '../../theme/tokens';

/**
 * Home — the daily landing surface.
 *
 * Layout follows the dashboard references in `Mobile app screens/`
 * (a_clean_mobile_app_home_dashboard_ui_screenshot,
 * a_full_screen_mobile_app_dashboard_ui_screenshot and
 * a_clean_high_end_smartphone_ui_mockup_app_dashb): a greeting block, a rich
 * gold Health ID hero carrying a REAL scannable QR, a deliberate 3x2 grid of
 * quick actions, the next appointment, and a care-plan promo.
 *
 * Every value on this screen is real: `useHealthIdCard` (health id, blood
 * type, dob, status, qr_payload), the auth store's `patient` (allergy and
 * condition counts) and `useUpcomingAppointments`. The demo patient has no
 * appointments and no allergies, so the empty/`None` states below are the
 * ones actually rendered — they are designed to look intentional.
 *
 * NativeWind note: `className` is inert on `LinearGradient` (no cssInterop is
 * registered for it), so every gradient here is styled with inline `style`.
 */

/** Soft elevation shared by every white card, so the surfaces read as one set. */
const CARD_SHADOW: ViewStyle = {
  shadowColor: colors.navy.text,
  shadowOpacity: 0.06,
  shadowRadius: 14,
  shadowOffset: { width: 0, height: 6 },
  elevation: 2,
};

/** Warmer, deeper lift for the Health ID hero — it should sit above the rest. */
const HERO_SHADOW: ViewStyle = {
  shadowColor: colors.gold[900],
  shadowOpacity: 0.3,
  shadowRadius: 22,
  shadowOffset: { width: 0, height: 12 },
  elevation: 8,
};

interface QuickActionSpec {
  key: string;
  icon: LucideIcon;
  labelKey: string;
  route: string;
}

/**
 * Six real destinations, every one a file under `app/`:
 * appointments/book.tsx, (tabs)/records.tsx, labs/index.tsx,
 * prescriptions/index.tsx, pharmacy.tsx, blood-finder.tsx.
 *
 * "Health Check" from the earlier revision is gone: no health-check /
 * health-score screen exists, and it pointed at Records like two other tiles.
 * The reference's second slot is "Lab Results", which is a real screen.
 */
const QUICK_ACTIONS: QuickActionSpec[] = [
  { key: 'book', icon: CalendarPlus, labelKey: 'home.quickActions.bookAppointment', route: '/appointments/book' },
  { key: 'records', icon: FolderHeart, labelKey: 'home.quickActions.healthRecords', route: '/(tabs)/records' },
  { key: 'labs', icon: FlaskConical, labelKey: 'home.quickActions.labResults', route: '/labs' },
  { key: 'prescriptions', icon: Pill, labelKey: 'home.quickActions.prescriptions', route: '/prescriptions' },
  { key: 'medicine', icon: Store, labelKey: 'home.quickActions.findMedicine', route: '/pharmacy' },
  { key: 'blood', icon: Droplets, labelKey: 'home.quickActions.findBlood', route: '/blood-finder' },
];

export default function HomeScreen() {
  const { t, i18n } = useTranslation();
  const patient = useAuthStore((s) => s.patient);
  const router = useRouter();
  const healthId = useHealthIdCard();
  const appointments = useUpcomingAppointments();
  const nextAppointment = appointments.data?.data?.[0];
  // Lightweight count endpoint, not the heavy list. Resolves to 0 (no badge)
  // while loading or on error; the existing mark-read mutations invalidate the
  // ['notifications'] key prefix, which refreshes this immediately. Gated on
  // auth so it can never fire a pre-auth 401 into the refresh interceptor.
  const authStatus = useAuthStore((s) => s.status);
  const unreadCount = useUnreadNotificationCount(authStatus === 'authenticated');

  const [refreshing, setRefreshing] = useState(false);
  const onRefresh = useCallback(async () => {
    setRefreshing(true);
    try {
      await Promise.all([healthId.refetch(), appointments.refetch()]);
    } finally {
      setRefreshing(false);
    }
  }, [healthId, appointments]);

  const initial = patient?.first_name?.[0]?.toUpperCase() ?? '?';
  const statusValue = (healthId.data?.status ?? patient?.status ?? '').toLowerCase();
  const isVerified = statusValue === 'verified' || statusValue === 'active';
  const qrPayload = healthId.data?.qr_payload;
  const displayName = healthId.data?.display_name ?? patient?.display_name ?? '';

  return (
    <Screen className="px-0">
      <ScrollView
        className="flex-1 px-6"
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.gold[500]} />
        }
      >
        {/* ── Header ─────────────────────────────────────────────────────── */}
        <View className="mt-2 flex-row items-center justify-between">
          <View className="flex-1 flex-row items-center pr-3">
            <LinearGradient
              colors={[colors.gold[300], colors.gold[500], colors.gold[700]]}
              start={{ x: 0, y: 0 }}
              end={{ x: 1, y: 1 }}
              style={{
                width: 36,
                height: 36,
                borderRadius: 18,
                alignItems: 'center',
                justifyContent: 'center',
                marginRight: 10,
              }}
            >
              <HeartPulse color={colors.cream[50]} size={17} />
            </LinearGradient>
            <View className="flex-1">
              <View className="flex-row items-center">
                <Text className="text-[17px] font-extrabold text-navy-text">Opes</Text>
                <Text className="text-[17px] font-extrabold text-gold-500">Care</Text>
              </View>
              <Text className="text-[9px] font-medium text-navy-secondary" numberOfLines={1}>
                {t('auth.tagline')}
              </Text>
            </View>
          </View>

          <View className="flex-row items-center gap-4">
            <Pressable
              onPress={() => router.push('/notifications')}
              hitSlop={8}
              accessibilityRole="button"
              accessibilityLabel={t('notifications.title')}
            >
              <View
                className="h-10 w-10 items-center justify-center rounded-full bg-white"
                style={{ borderWidth: 1, borderColor: colors.cream[300] }}
              >
                <Bell size={19} color={colors.navy.text} />
                {/* Count renders as its own accessible text node; the button
                    above carries the "Notification Center" label. No new
                    notifications.* i18n key needed. */}
                <UnreadBadge count={unreadCount} />
              </View>
            </Pressable>
            <Pressable
              onPress={() => router.push('/(tabs)/profile')}
              hitSlop={4}
              accessibilityRole="button"
              accessibilityLabel={t('profile.title')}
            >
              <View
                className="h-10 w-10 items-center justify-center rounded-full bg-gold-100"
                style={{ borderWidth: 1.5, borderColor: colors.gold[300] }}
              >
                <Text className="text-[15px] font-extrabold text-gold-600">{initial}</Text>
              </View>
            </Pressable>
          </View>
        </View>

        {/* ── Greeting ───────────────────────────────────────────────────── */}
        <View className="mt-7 flex-row items-center justify-between">
          <View className="flex-1 pr-4">
            <Text className="text-[26px] font-extrabold leading-8 text-navy-text">
              {t(greetingKey())}
            </Text>
            <View className="flex-row items-center">
              <Text className="text-[26px] font-extrabold leading-9 text-gold-500" numberOfLines={1}>
                {patient?.first_name ?? '—'}
              </Text>
              <Hand size={21} color={colors.gold[300]} style={{ marginLeft: 8 }} />
            </View>
            <Text className="mt-1.5 text-[13px] leading-5 text-navy-secondary">{t('home.subtitle')}</Text>
          </View>

          {/* Brand medallion — the shield motif from the reference header. */}
          <View
            className="h-[84px] w-[84px] items-center justify-center rounded-full"
            style={{ backgroundColor: colors.gold[50] }}
          >
            <View
              className="h-[70px] w-[70px] items-center justify-center rounded-full"
              style={{ borderWidth: 1, borderColor: colors.gold[100] }}
            >
              <LinearGradient
                colors={[colors.gold[300], colors.gold[500], colors.gold[700]]}
                start={{ x: 0.2, y: 0 }}
                end={{ x: 0.8, y: 1 }}
                style={{
                  width: 58,
                  height: 58,
                  borderRadius: 20,
                  alignItems: 'center',
                  justifyContent: 'center',
                }}
              >
                <ShieldCheck color={colors.white} size={28} />
              </LinearGradient>
            </View>
          </View>
        </View>

        {/* ── Health ID hero ─────────────────────────────────────────────── */}
        <LinearGradient
          colors={[colors.gold[600], colors.gold[500], colors.gold[300]]}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
          style={{
            borderRadius: 24,
            marginTop: 24,
            padding: 20,
            overflow: 'hidden',
            ...HERO_SHADOW,
          }}
        >
          {/* Decorative watermarks — non-interactive, clipped by the card. */}
          <View
            style={{
              position: 'absolute',
              right: -46,
              top: -56,
              width: 168,
              height: 168,
              borderRadius: 84,
              backgroundColor: 'rgba(255,255,255,0.10)',
              pointerEvents: 'none',
            }}
          />
          <View
            style={{
              position: 'absolute',
              left: -60,
              bottom: -70,
              width: 150,
              height: 150,
              borderRadius: 75,
              backgroundColor: 'rgba(255,255,255,0.07)',
              pointerEvents: 'none',
            }}
          />

          <View className="flex-row items-start justify-between">
            <View className="flex-1 pr-3">
              <Text
                className="text-[10px] font-bold uppercase text-white/80"
                style={{ letterSpacing: 1 }}
                numberOfLines={1}
              >
                {t('home.healthId.title')}
              </Text>

              {displayName ? (
                <Text className="mt-1.5 text-[18px] font-extrabold text-white" numberOfLines={1}>
                  {displayName}
                </Text>
              ) : null}

              {healthId.isLoading ? (
                <ActivityIndicator color={colors.white} style={{ marginTop: 8, alignSelf: 'flex-start' }} />
              ) : (
                <Text
                  className="mt-0.5 text-[17px] font-bold text-white/95"
                  style={{ letterSpacing: 0.6 }}
                  numberOfLines={1}
                >
                  {healthId.data?.health_id ?? patient?.health_id ?? '—'}
                </Text>
              )}

              <View
                className="mt-3 flex-row items-center self-start rounded-full px-2.5 py-1"
                style={{ backgroundColor: 'rgba(255,255,255,0.24)' }}
              >
                {isVerified ? (
                  <BadgeCheck size={13} color={colors.white} />
                ) : (
                  <Hourglass size={12} color={colors.white} />
                )}
                <Text className="ml-1.5 text-[11px] font-bold text-white">
                  {t(isVerified ? 'home.healthId.verified' : 'home.healthId.pending')}
                </Text>
              </View>
            </View>

            {/* Real, scannable QR straight off the card payload. */}
            <Pressable
              onPress={() => router.push('/(tabs)/health-id')}
              accessibilityRole="button"
              accessibilityLabel={t('home.healthId.showQr')}
            >
              <View className="items-center rounded-2xl bg-white px-2 pb-1.5 pt-2" style={{ width: 86 }}>
                <View className="items-center justify-center" style={{ height: 66, width: 66 }}>
                  {qrPayload ? (
                    <QRCode
                      value={qrPayload}
                      size={66}
                      color={colors.navy.text}
                      backgroundColor={colors.white}
                    />
                  ) : (
                    <QrCode size={32} color={colors.navy.muted} />
                  )}
                </View>
                <Text
                  className="mt-1 text-[8px] font-bold uppercase text-navy-secondary"
                  style={{ letterSpacing: 0.5 }}
                  numberOfLines={1}
                >
                  {t('home.healthId.showQr')}
                </Text>
              </View>
            </Pressable>
          </View>

          <View className="my-4 h-px" style={{ backgroundColor: 'rgba(255,255,255,0.28)' }} />

          {healthId.isError ? (
            <View className="flex-row items-center justify-between">
              <View className="flex-1 flex-row items-center pr-3">
                <CircleAlert size={15} color={colors.white} />
                <Text className="ml-2 flex-1 text-[12px] text-white/95">
                  {t('home.healthId.loadError')}
                </Text>
              </View>
              <Pressable
                onPress={() => healthId.refetch()}
                hitSlop={8}
                accessibilityRole="button"
                className="flex-row items-center rounded-full px-3 py-1.5"
                style={{ backgroundColor: 'rgba(255,255,255,0.24)' }}
              >
                <RefreshCw size={12} color={colors.white} />
                <Text className="ml-1.5 text-[11px] font-bold text-white">{t('home.retry')}</Text>
              </Pressable>
            </View>
          ) : (
            <View className="flex-row flex-wrap">
              <HeroStat
                icon={Droplets}
                label={t('home.healthId.bloodGroup')}
                value={healthId.data?.blood_type ?? patient?.blood_group ?? '—'}
              />
              <HeroStat
                icon={TriangleAlert}
                label={t('home.healthId.allergies')}
                value={formatCount(patient?.allergies_count, t)}
              />
              <HeroStat
                icon={Cake}
                label={t('home.healthId.dateOfBirth')}
                value={formatDate(healthId.data?.dob ?? patient?.dob, i18n.language)}
              />
              <HeroStat
                icon={Activity}
                label={t('home.healthId.conditions')}
                value={formatCount(patient?.conditions_count, t)}
              />
            </View>
          )}

          <Pressable
            onPress={() => router.push('/(tabs)/health-id')}
            accessibilityRole="button"
            className="mt-4 flex-row items-center justify-center rounded-2xl py-3"
            style={{ backgroundColor: 'rgba(255,255,255,0.22)' }}
          >
            <Text className="text-[13px] font-bold text-white">{t('home.healthId.viewCard')}</Text>
            <ChevronRight size={16} color={colors.white} style={{ marginLeft: 2 }} />
          </Pressable>
        </LinearGradient>

        {/* ── Quick actions ──────────────────────────────────────────────── */}
        <SectionHeader
          title={t('home.quickActions.title')}
          actionLabel={t('home.quickActions.viewAllServices')}
          onAction={() => router.push('/care-map')}
        />
        <View className="flex-row flex-wrap justify-between">
          {QUICK_ACTIONS.map((action) => (
            <ActionTile
              key={action.key}
              icon={action.icon}
              label={t(action.labelKey)}
              onPress={() => router.push(action.route)}
            />
          ))}
        </View>

        {/* ── Next appointment ───────────────────────────────────────────── */}
        <SectionHeader
          title={t('home.appointment.title')}
          actionLabel={t('home.appointment.viewAll')}
          onAction={() => router.push('/appointments')}
        />

        <View
          className="rounded-3xl bg-white p-5"
          style={{ borderWidth: 1, borderColor: colors.cream[300], ...CARD_SHADOW }}
        >
          {appointments.isLoading ? (
            <View className="items-center py-6">
              <ActivityIndicator color={colors.gold[500]} />
            </View>
          ) : appointments.isError ? (
            <View className="items-center py-4">
              <CircleAlert size={22} color={colors.semantic.danger} />
              <Text className="mt-2 text-center text-[13px] text-navy-secondary">
                {t('home.appointment.loadError')}
              </Text>
              <Pressable
                onPress={() => appointments.refetch()}
                accessibilityRole="button"
                className="mt-3 flex-row items-center rounded-xl px-4 py-2"
                style={{ backgroundColor: colors.cream[200] }}
              >
                <RefreshCw size={13} color={colors.gold[600]} />
                <Text className="ml-2 text-[13px] font-bold text-gold-600">{t('home.retry')}</Text>
              </Pressable>
            </View>
          ) : nextAppointment ? (
            <>
              <View className="flex-row items-start">
                {nextAppointment.scheduled_at ? (
                  <DateChip value={nextAppointment.scheduled_at} language={i18n.language} />
                ) : null}

                <View className="flex-1 pl-4">
                  <Text className="text-[16px] font-extrabold text-navy-text" numberOfLines={2}>
                    {nextAppointment.provider_name ?? nextAppointment.appointment_type}
                  </Text>
                  {nextAppointment.provider_name ? (
                    <Text className="mt-0.5 text-[12px] text-navy-muted" numberOfLines={1}>
                      {nextAppointment.appointment_type}
                    </Text>
                  ) : null}

                  {nextAppointment.scheduled_at ? (
                    <View className="mt-2 flex-row items-center">
                      <Clock size={13} color={colors.gold[600]} />
                      <Text className="ml-1.5 text-[13px] font-semibold text-gold-600">
                        {formatTime(nextAppointment.scheduled_at, i18n.language)}
                      </Text>
                    </View>
                  ) : null}

                  {nextAppointment.facility_name ? (
                    <View className="mt-1 flex-row items-center">
                      <MapPin size={13} color={colors.navy.muted} />
                      <Text className="ml-1.5 flex-1 text-[13px] text-navy-secondary" numberOfLines={1}>
                        {nextAppointment.facility_name}
                      </Text>
                    </View>
                  ) : null}
                </View>
              </View>

              <Pressable
                onPress={() => router.push(`/appointments/${nextAppointment.id}`)}
                accessibilityRole="button"
                className="mt-4 flex-row items-center justify-between rounded-2xl px-4 py-3"
                style={{ backgroundColor: colors.cream[100] }}
              >
                <Text className="text-[13px] font-bold text-navy-text">
                  {t('home.appointment.viewDetails')}
                </Text>
                <ChevronRight size={16} color={colors.gold[600]} />
              </Pressable>
            </>
          ) : (
            /* Deliberate empty state — this is what the demo patient sees. */
            <View className="items-center px-2 py-3">
              <View
                className="h-16 w-16 items-center justify-center rounded-full"
                style={{ backgroundColor: colors.gold[50] }}
              >
                <CalendarDays size={26} color={colors.gold[500]} />
              </View>
              <Text className="mt-3.5 text-[16px] font-extrabold text-navy-text">
                {t('home.appointment.empty')}
              </Text>
              <Text className="mt-1.5 text-center text-[13px] leading-5 text-navy-secondary">
                {t('home.appointment.emptyBody')}
              </Text>
              <Pressable
                onPress={() => router.push('/appointments/book')}
                accessibilityRole="button"
                className="mt-4 w-full flex-row items-center justify-center rounded-2xl py-3.5"
                style={{ backgroundColor: colors.gold[500] }}
              >
                <CalendarPlus size={16} color={colors.white} />
                <Text className="ml-2 text-[14px] font-bold text-white">
                  {t('home.appointment.emptyCta')}
                </Text>
              </Pressable>
            </View>
          )}
        </View>

        {/* ── Care-plan promo ────────────────────────────────────────────── */}
        <Pressable
          onPress={() => router.push('/care-plans')}
          accessibilityRole="button"
          className="mb-2 mt-7 overflow-hidden rounded-3xl p-5"
          style={{
            backgroundColor: colors.cream[200],
            borderWidth: 1,
            borderColor: colors.cream[300],
          }}
        >
          <View
            style={{
              position: 'absolute',
              right: -34,
              top: -34,
              width: 128,
              height: 128,
              borderRadius: 64,
              backgroundColor: colors.cream[50],
              opacity: 0.7,
              pointerEvents: 'none',
            }}
          />
          <View className="flex-row items-center">
            <View
              className="h-10 w-10 items-center justify-center rounded-2xl"
              style={{ backgroundColor: colors.gold[100] }}
            >
              <Target size={19} color={colors.gold[600]} />
            </View>
            <Text className="ml-3 flex-1 text-[16px] font-extrabold leading-5 text-navy-text">
              {t('home.insights.title')}
            </Text>
          </View>
          <Text className="mt-3 text-[13px] leading-5 text-navy-secondary">
            {t('home.insights.body')}
          </Text>
          <View
            className="mt-4 flex-row items-center self-start rounded-2xl px-4 py-2.5"
            style={{ backgroundColor: colors.gold[500] }}
          >
            <Text className="text-[13px] font-bold text-white">{t('home.insights.cta')}</Text>
            <ChevronRight size={15} color={colors.white} style={{ marginLeft: 3 }} />
          </View>
        </Pressable>

        <View className="h-10" />
      </ScrollView>
    </Screen>
  );
}

/* ── Pieces ──────────────────────────────────────────────────────────────── */

function SectionHeader({
  title,
  actionLabel,
  onAction,
}: {
  title: string;
  actionLabel?: string;
  onAction?: () => void;
}) {
  return (
    <View className="mb-3.5 mt-7 flex-row items-center justify-between">
      <Text className="text-[17px] font-extrabold text-navy-text">{title}</Text>
      {actionLabel && onAction ? (
        <Pressable onPress={onAction} hitSlop={8} accessibilityRole="button">
          <View className="flex-row items-center">
            <Text className="text-[13px] font-bold text-gold-600">{actionLabel}</Text>
            <ChevronRight size={14} color={colors.gold[600]} style={{ marginLeft: 1 }} />
          </View>
        </Pressable>
      ) : null}
    </View>
  );
}

/** One quick-action tile. Three per row, two rows — deliberate, not a strip. */
function ActionTile({ icon: Icon, label, onPress }: { icon: LucideIcon; label: string; onPress: () => void }) {
  return (
    <Pressable
      onPress={onPress}
      accessibilityRole="button"
      accessibilityLabel={label}
      className="mb-3 items-center rounded-2xl bg-white px-2 py-4"
      style={({ pressed }) => ({
        width: '31.5%',
        borderWidth: 1,
        borderColor: colors.cream[300],
        opacity: pressed ? 0.75 : 1,
        ...CARD_SHADOW,
      })}
    >
      <View
        className="h-11 w-11 items-center justify-center rounded-2xl"
        style={{ backgroundColor: colors.gold[50] }}
      >
        <Icon size={20} color={colors.gold[600]} />
      </View>
      <Text
        className="mt-2.5 text-center text-[11px] font-bold text-navy-text"
        style={{ lineHeight: 14 }}
        numberOfLines={2}
      >
        {label}
      </Text>
    </Pressable>
  );
}

/** Icon + label + value cell on the gold hero. Two per row. */
function HeroStat({ icon: Icon, label, value }: { icon: LucideIcon; label: string; value: string }) {
  return (
    <View className="mb-1 w-1/2 flex-row items-center pr-2 pt-2">
      <View
        className="h-8 w-8 items-center justify-center"
        style={{ backgroundColor: 'rgba(255,255,255,0.22)', borderRadius: 11 }}
      >
        <Icon size={15} color={colors.white} />
      </View>
      <View className="ml-2 flex-1">
        <Text className="text-[10px] font-medium text-white/75" numberOfLines={1}>
          {label}
        </Text>
        <Text className="text-[13px] font-extrabold text-white" numberOfLines={1}>
          {value}
        </Text>
      </View>
    </View>
  );
}

/** MAY / 22 / THU calendar chip, as in the appointment references. */
function DateChip({ value, language }: { value: string; language: string }) {
  return (
    <View
      className="overflow-hidden rounded-2xl"
      style={{ width: 62, borderWidth: 1, borderColor: colors.cream[300] }}
    >
      <View className="items-center py-1" style={{ backgroundColor: colors.gold[500] }}>
        <Text className="text-[10px] font-extrabold uppercase text-white" style={{ letterSpacing: 0.6 }}>
          {formatMonth(value, language)}
        </Text>
      </View>
      <View className="items-center py-1.5" style={{ backgroundColor: colors.cream[50] }}>
        <Text className="text-[20px] font-extrabold leading-6 text-navy-text">
          {formatDayNumber(value, language)}
        </Text>
        <Text className="text-[10px] font-semibold uppercase text-navy-muted">
          {formatWeekday(value, language)}
        </Text>
      </View>
    </View>
  );
}

/* ── Formatting ──────────────────────────────────────────────────────────── */

/** Time-of-day greeting, from the device clock — matches the references. */
function greetingKey(): string {
  const hour = new Date().getHours();
  if (hour < 12) return 'home.greetingMorning';
  if (hour < 18) return 'home.greetingAfternoon';
  return 'home.greetingEvening';
}

/** `0` is a real, reassuring answer ("None") — not a missing value. */
function formatCount(count: number | null | undefined, t: (key: string) => string): string {
  if (count == null) return '—';
  if (count === 0) return t('home.healthId.none');
  return String(count);
}

function localeTag(language: string): string {
  return language?.startsWith('fr') ? 'fr-FR' : 'en-US';
}

function formatDate(value: string | null | undefined, language: string): string {
  if (!value) return '—';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return new Intl.DateTimeFormat(localeTag(language), { day: 'numeric', month: 'short', year: 'numeric' }).format(
    date,
  );
}

function formatMonth(value: string, language: string): string {
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '';
  return new Intl.DateTimeFormat(localeTag(language), { month: 'short' }).format(date);
}

function formatDayNumber(value: string, language: string): string {
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '';
  return new Intl.DateTimeFormat(localeTag(language), { day: 'numeric' }).format(date);
}

function formatWeekday(value: string, language: string): string {
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '';
  return new Intl.DateTimeFormat(localeTag(language), { weekday: 'short' }).format(date);
}

function formatTime(value: string, language: string): string {
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '';
  return new Intl.DateTimeFormat(localeTag(language), { hour: 'numeric', minute: '2-digit' }).format(date);
}
