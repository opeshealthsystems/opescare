import { ActivityIndicator, Pressable, ScrollView, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import { LinearGradient } from 'expo-linear-gradient';
import {
  Bell,
  Calendar,
  CheckCircle2,
  ChevronRight,
  ClipboardPlus,
  Clock,
  Hand,
  HeartPulse,
  LayoutGrid,
  MapPin,
  Menu,
  Pill,
  QrCode,
  ShieldCheck,
  Sparkles,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { useAuthStore } from '../../lib/store/auth';
import { useHealthIdCard, useUpcomingAppointments } from '../../lib/api/queries';
import { colors } from '../../theme/tokens';

export default function HomeScreen() {
  const { t, i18n } = useTranslation();
  const patient = useAuthStore((s) => s.patient);
  const router = useRouter();
  const healthId = useHealthIdCard();
  const appointments = useUpcomingAppointments();
  const nextAppointment = appointments.data?.data?.[0];

  const initial = patient?.first_name?.[0]?.toUpperCase() ?? '?';
  const statusValue = (healthId.data?.status ?? patient?.status ?? '').toLowerCase();
  const isVerified = statusValue === 'verified' || statusValue === 'active';

  return (
    <Screen className="px-0">
      <ScrollView className="flex-1 px-6" showsVerticalScrollIndicator={false}>
        {/* Header */}
        <View className="mt-2 flex-row items-center justify-between">
          <Pressable hitSlop={8}>
            <Menu size={22} color={colors.gold[600]} />
          </Pressable>

          <View className="flex-row items-center">
            <LinearGradient
              colors={[colors.gold[300], colors.gold[500], colors.gold[700]]}
              style={{
                width: 32,
                height: 32,
                borderRadius: 16,
                alignItems: 'center',
                justifyContent: 'center',
                marginRight: 8,
              }}
            >
              <HeartPulse color={colors.cream[50]} size={15} />
            </LinearGradient>
            <View>
              <View className="flex-row items-center">
                <Text className="text-base font-extrabold text-navy-text">Opes</Text>
                <Text className="text-base font-extrabold text-gold-500">Care</Text>
              </View>
              <Text className="text-[9px] text-navy-secondary" numberOfLines={1}>
                {t('auth.tagline')}
              </Text>
            </View>
          </View>

          <View className="flex-row items-center gap-4">
            <Pressable onPress={() => router.push('/notifications')} hitSlop={8}>
              <Bell size={20} color={colors.navy.text} />
            </Pressable>
            <Pressable onPress={() => router.push('/(tabs)/profile')} hitSlop={4}>
              <View className="h-9 w-9 items-center justify-center rounded-full bg-gold-100">
                <Text className="font-bold text-gold-600">{initial}</Text>
              </View>
            </Pressable>
          </View>
        </View>

        {/* Welcome */}
        <View className="mt-6 flex-row items-start justify-between">
          <View className="flex-1 pr-3">
            <View className="flex-row flex-wrap items-center">
              <Text className="text-2xl font-extrabold text-navy-text">{t('home.greeting')} </Text>
              <Text className="text-2xl font-extrabold text-gold-500">{patient?.first_name ?? '...'}</Text>
              <Hand size={20} color={colors.gold[500]} style={{ marginLeft: 6 }} />
            </View>
            <Text className="mt-1 text-sm text-navy-secondary">{t('home.subtitle')}</Text>
          </View>
          <LinearGradient
            colors={[colors.gold[300], colors.gold[500], colors.gold[700]]}
            style={{ width: 72, height: 72, borderRadius: 22, alignItems: 'center', justifyContent: 'center' }}
          >
            <ShieldCheck color={colors.white} size={32} />
          </LinearGradient>
        </View>

        {/* Quick actions */}
        <View className="mt-6 flex-row items-start rounded-2xl bg-white p-4">
          <QuickAction
            icon={Calendar}
            label={t('home.quickActions.bookAppointment')}
            onPress={() => router.push('/appointments')}
          />
          <QuickAction
            icon={ClipboardPlus}
            label={t('home.quickActions.healthRecords')}
            onPress={() => router.push('/(tabs)/records')}
          />
          <QuickAction
            icon={HeartPulse}
            label={t('home.quickActions.healthCheck')}
            onPress={() => router.push('/(tabs)/records')}
          />
          <QuickAction
            icon={Pill}
            label={t('home.quickActions.prescriptions')}
            onPress={() => router.push('/prescriptions')}
          />
          <QuickAction
            icon={LayoutGrid}
            label={t('home.quickActions.viewAllServices')}
            onPress={() => router.push('/(tabs)/records')}
          />
        </View>

        {/* Health ID card */}
        <LinearGradient
          colors={[colors.gold[600], colors.gold[500], colors.gold[300]]}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
          style={{ borderRadius: 20, marginTop: 16, padding: 20 }}
        >
          <View className="flex-row items-center justify-between">
            <View className="flex-1 flex-row items-center pr-3">
              <View className="h-11 w-11 items-center justify-center rounded-full bg-white/25">
                <Text className="text-base font-bold text-white">{initial}</Text>
              </View>
              <View className="ml-3 flex-1">
                <Text className="text-sm font-semibold text-white/90">{t('home.healthId.title')}</Text>
                {healthId.isLoading ? (
                  <ActivityIndicator color="white" style={{ marginTop: 6, alignSelf: 'flex-start' }} />
                ) : (
                  <Text className="mt-1 text-lg font-extrabold text-white" numberOfLines={1}>
                    {healthId.data?.health_id ?? '—'}
                  </Text>
                )}
              </View>
            </View>
            <View className="h-14 w-14 items-center justify-center rounded-2xl bg-white/20">
              <QrCode color="white" size={26} />
            </View>
          </View>

          {isVerified ? (
            <View
              className="mt-3 flex-row items-center self-start rounded-full px-3 py-1"
              style={{ backgroundColor: 'rgba(255,255,255,0.22)' }}
            >
              <CheckCircle2 size={14} color="white" />
              <Text className="ml-1 text-xs font-semibold text-white">{t('home.healthId.verified')}</Text>
            </View>
          ) : null}

          <View className="mt-4 flex-row justify-between">
            <VitalStat
              label={t('home.healthId.bloodGroup')}
              value={healthId.data?.blood_type ?? patient?.blood_group ?? '—'}
              light
            />
            <VitalStat
              label={t('home.healthId.allergies')}
              value={formatAllergies(patient?.allergies_count, t)}
              light
            />
            <VitalStat
              label={t('home.healthId.dateOfBirth')}
              value={formatDate(healthId.data?.dob ?? patient?.dob, i18n.language)}
              light
            />
          </View>

          <Pressable
            onPress={() => router.push('/(tabs)/health-id')}
            className="mt-4 flex-row items-center self-start"
            hitSlop={6}
          >
            <Text className="text-sm font-semibold text-white">{t('home.healthId.viewCard')}</Text>
            <ChevronRight size={16} color="white" style={{ marginLeft: 2 }} />
          </Pressable>
        </LinearGradient>

        {/* Upcoming appointment */}
        <Text className="mb-3 mt-6 text-base font-bold text-navy-text">{t('home.appointment.title')}</Text>
        <View className="rounded-2xl bg-white p-4">
          {appointments.isLoading ? (
            <ActivityIndicator color={colors.gold[500]} />
          ) : nextAppointment ? (
            <>
              <View className="flex-row items-start justify-between">
                <View className="flex-1 pr-3">
                  <Text className="text-base font-semibold text-navy-text">
                    {nextAppointment.provider_name ?? nextAppointment.appointment_type}
                  </Text>
                  {nextAppointment.provider_name ? (
                    <Text className="mt-0.5 text-xs text-navy-muted">{nextAppointment.appointment_type}</Text>
                  ) : null}
                  {nextAppointment.facility_name ? (
                    <Text className="mt-1 text-sm text-navy-secondary">{nextAppointment.facility_name}</Text>
                  ) : null}
                </View>
                {nextAppointment.scheduled_at ? (
                  <View className="items-center rounded-xl px-3 py-2" style={{ backgroundColor: colors.gold[50] }}>
                    <Text className="text-xs font-bold text-gold-600">
                      {formatMonthDay(nextAppointment.scheduled_at, i18n.language)}
                    </Text>
                    <Text className="text-[10px] text-navy-muted">
                      {formatWeekday(nextAppointment.scheduled_at, i18n.language)}
                    </Text>
                  </View>
                ) : null}
              </View>

              {nextAppointment.scheduled_at || nextAppointment.reason ? (
                <View
                  className="mt-3 flex-row items-center gap-4 pt-3"
                  style={{ borderTopWidth: 1, borderTopColor: colors.cream[300] }}
                >
                  {nextAppointment.scheduled_at ? (
                    <View className="flex-row items-center">
                      <Clock size={14} color={colors.navy.muted} />
                      <Text className="ml-1 text-xs text-navy-secondary">
                        {formatTime(nextAppointment.scheduled_at, i18n.language)}
                      </Text>
                    </View>
                  ) : null}
                  {nextAppointment.reason ? (
                    <View className="flex-1 flex-row items-center">
                      <MapPin size={14} color={colors.navy.muted} />
                      <Text className="ml-1 flex-1 text-xs text-navy-secondary" numberOfLines={1}>
                        {nextAppointment.reason}
                      </Text>
                    </View>
                  ) : null}
                </View>
              ) : null}

              <Pressable
                onPress={() => router.push('/appointments')}
                className="mt-3 flex-row items-center justify-between rounded-xl px-4 py-3"
                style={{ backgroundColor: colors.cream[100] }}
              >
                <Text className="text-sm font-semibold text-navy-text">{t('home.appointment.viewDetails')}</Text>
                <ChevronRight size={16} color={colors.navy.secondary} />
              </Pressable>
            </>
          ) : (
            <View className="items-center py-2">
              <Text className="text-sm text-navy-muted">{t('home.appointment.empty')}</Text>
              <Pressable onPress={() => router.push('/appointments')} className="mt-3" hitSlop={6}>
                <Text className="text-sm font-semibold text-gold-600">{t('home.appointment.emptyCta')}</Text>
              </Pressable>
            </View>
          )}
        </View>

        {/* Health insights */}
        <View className="mb-2 mt-6 rounded-2xl p-5" style={{ backgroundColor: colors.cream[200] }}>
          <View className="flex-row items-center">
            <Sparkles size={18} color={colors.gold[600]} />
            <Text className="ml-2 text-base font-bold text-navy-text">{t('home.insights.title')}</Text>
          </View>
          <Text className="mt-2 text-sm text-navy-secondary">{t('home.insights.body')}</Text>
          <Pressable
            onPress={() => router.push('/(tabs)/records')}
            className="mt-4 flex-row items-center self-start rounded-xl px-4 py-2.5"
            style={{ backgroundColor: colors.gold[500] }}
            hitSlop={4}
          >
            <Text className="text-sm font-bold text-white">{t('home.insights.cta')}</Text>
            <ChevronRight size={16} color="white" style={{ marginLeft: 4 }} />
          </Pressable>
        </View>

        <View className="h-10" />
      </ScrollView>
    </Screen>
  );
}

function QuickAction({ icon: Icon, label, onPress }: { icon: LucideIcon; label: string; onPress: () => void }) {
  return (
    <Pressable onPress={onPress} className="items-center px-0.5" style={{ flex: 1 }}>
      <Icon size={20} color={colors.gold[600]} />
      <Text className="mt-2 text-center text-[10px] font-medium text-navy-secondary" numberOfLines={2}>
        {label}
      </Text>
    </Pressable>
  );
}

function VitalStat({ label, value, light }: { label: string; value: string; light?: boolean }) {
  return (
    <View>
      <Text className={light ? 'text-xs text-white/80' : 'text-xs text-navy-muted'}>{label}</Text>
      <Text className={light ? 'text-base font-bold text-white' : 'text-base font-bold text-navy-text'}>
        {value}
      </Text>
    </View>
  );
}

function formatAllergies(count: number | null | undefined, t: (key: string) => string): string {
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

function formatMonthDay(value: string, language: string): string {
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '';
  return new Intl.DateTimeFormat(localeTag(language), { month: 'short', day: 'numeric' }).format(date);
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
