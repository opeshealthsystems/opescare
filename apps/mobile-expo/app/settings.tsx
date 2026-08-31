import { useCallback, useEffect, useState, type ReactNode } from 'react';
import { ActivityIndicator, Platform, Pressable, ScrollView, Switch, Text, View } from 'react-native';
import * as SecureStore from 'expo-secure-store';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  AlertCircle,
  Bell,
  BellRing,
  Calendar,
  ChevronLeft,
  ChevronRight,
  CreditCard,
  FlaskConical,
  Globe,
  Pill,
  ShieldCheck,
  Smartphone,
  Users,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../components/ui/Screen';
import {
  useMobileSettings,
  useRegisterPushToken,
  useRevokePushToken,
  useUpdateMobileSettings,
  type MobileSettings,
} from '../lib/api/queries';
import { colors } from '../theme/tokens';

/**
 * Locally persisted record of this device's push registration. The backend
 * (`PushDeviceToken`) is keyed on patient + device_fingerprint + platform, so
 * the fingerprint and the token_id it registered under need to survive app
 * restarts on THIS device — that's inherently local state, not part of
 * GET /mobile/settings (which only covers language/notification/theme
 * preferences shared across a patient's devices).
 */
const DEVICE_FINGERPRINT_KEY = 'opescare_device_fingerprint';
const PUSH_TOKEN_ID_KEY = 'opescare_push_token_id';

const deviceStore =
  Platform.OS === 'web'
    ? {
        get: async (key: string) => (typeof localStorage !== 'undefined' ? localStorage.getItem(key) : null),
        set: async (key: string, value: string) => localStorage.setItem(key, value),
        remove: async (key: string) => localStorage.removeItem(key),
      }
    : {
        get: (key: string) => SecureStore.getItemAsync(key),
        set: (key: string, value: string) => SecureStore.setItemAsync(key, value),
        remove: (key: string) => SecureStore.deleteItemAsync(key),
      };

function generateFingerprint(): string {
  const random = Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2);
  return `${Platform.OS}-${Date.now().toString(36)}-${random}`.slice(0, 128);
}

async function getOrCreateFingerprint(): Promise<string> {
  const existing = await deviceStore.get(DEVICE_FINGERPRINT_KEY);
  if (existing) return existing;
  const created = generateFingerprint();
  await deviceStore.set(DEVICE_FINGERPRINT_KEY, created);
  return created;
}

function resolvePlatform(): 'ios' | 'android' | 'web' {
  if (Platform.OS === 'ios') return 'ios';
  if (Platform.OS === 'android') return 'android';
  return 'web';
}

export default function SettingsScreen() {
  const { t, i18n } = useTranslation();
  const router = useRouter();

  const settingsQuery = useMobileSettings();
  const updateSettings = useUpdateMobileSettings();
  const registerPushToken = useRegisterPushToken();
  const revokePushToken = useRevokePushToken();

  const [pushEnabled, setPushEnabled] = useState(false);
  const [pushBusy, setPushBusy] = useState(false);
  const [pushError, setPushError] = useState<string | null>(null);

  useEffect(() => {
    deviceStore.get(PUSH_TOKEN_ID_KEY).then((id) => setPushEnabled(!!id));
  }, []);

  const setLanguage = useCallback(
    (lng: 'en' | 'fr') => {
      if (i18n.language === lng) return;
      i18n.changeLanguage(lng);
      updateSettings.mutate({ preferred_language: lng });
    },
    [i18n, updateSettings],
  );

  const toggleNotification = useCallback(
    (key: keyof MobileSettings, value: boolean) => {
      updateSettings.mutate({ [key]: value } as Partial<MobileSettings>);
    },
    [updateSettings],
  );

  const togglePush = useCallback(
    async (value: boolean) => {
      setPushError(null);
      setPushBusy(true);
      try {
        if (value) {
          const fingerprint = await getOrCreateFingerprint();
          const result = await registerPushToken.mutateAsync({
            device_fingerprint: fingerprint,
            platform: resolvePlatform(),
            push_token: fingerprint,
          });
          await deviceStore.set(PUSH_TOKEN_ID_KEY, result.token_id);
          setPushEnabled(true);
        } else {
          const tokenId = await deviceStore.get(PUSH_TOKEN_ID_KEY);
          if (tokenId) {
            await revokePushToken.mutateAsync(tokenId);
            await deviceStore.remove(PUSH_TOKEN_ID_KEY);
          }
          setPushEnabled(false);
        }
      } catch {
        setPushError(value ? t('settings.device.registerError') : t('settings.device.revokeError'));
      } finally {
        setPushBusy(false);
      }
    },
    [registerPushToken, revokePushToken, t],
  );

  const settings = settingsQuery.data;

  return (
    <Screen className="px-0">
      <View className="flex-row items-center justify-between px-6 pt-2">
        <Pressable onPress={() => router.back()} hitSlop={10} className="h-10 w-10 items-center justify-center">
          <ChevronLeft size={24} color={colors.navy.text} />
        </Pressable>
        <Text className="text-lg font-bold text-navy-text">{t('settings.title')}</Text>
        <View className="h-10 w-10" />
      </View>

      <ScrollView className="flex-1 px-6" showsVerticalScrollIndicator={false}>
        <Text className="mb-4 mt-1 text-sm text-navy-secondary">{t('settings.subtitle')}</Text>

        {settingsQuery.isLoading ? (
          <View className="mt-10 items-center">
            <ActivityIndicator color={colors.gold[500]} size="large" />
          </View>
        ) : settingsQuery.isError || !settings ? (
          <View className="mt-6 items-center rounded-2xl bg-white p-6">
            <AlertCircle size={28} color={colors.semantic.danger} />
            <Text className="mt-3 text-center text-sm text-navy-secondary">{t('settings.loadError')}</Text>
            <Pressable
              onPress={() => settingsQuery.refetch()}
              className="mt-4 rounded-full border border-gold-500 px-5 py-2"
            >
              <Text className="text-sm font-semibold text-gold-600">{t('settings.retry')}</Text>
            </Pressable>
          </View>
        ) : (
          <>
            {/* Language */}
            <SectionCard icon={Globe} title={t('settings.language.title')} body={t('settings.language.body')}>
              <View className="mt-4 flex-row rounded-2xl bg-cream-200 p-1">
                <LanguagePill
                  label={t('settings.language.en')}
                  active={i18n.language?.startsWith('en')}
                  onPress={() => setLanguage('en')}
                />
                <LanguagePill
                  label={t('settings.language.fr')}
                  active={i18n.language?.startsWith('fr')}
                  onPress={() => setLanguage('fr')}
                />
              </View>
            </SectionCard>

            {/* Notification preferences */}
            <SectionCard
              icon={Bell}
              title={t('settings.notifications.title')}
              body={t('settings.notifications.body')}
            >
              <View className="mt-2">
                <ToggleRow
                  icon={Calendar}
                  label={t('settings.notifications.appointments')}
                  body={t('settings.notifications.appointmentsBody')}
                  value={settings.push_appointments}
                  onChange={(v) => toggleNotification('push_appointments', v)}
                />
                <ToggleRow
                  icon={FlaskConical}
                  label={t('settings.notifications.labResults')}
                  body={t('settings.notifications.labResultsBody')}
                  value={settings.push_lab_results}
                  onChange={(v) => toggleNotification('push_lab_results', v)}
                />
                <ToggleRow
                  icon={Pill}
                  label={t('settings.notifications.prescriptions')}
                  body={t('settings.notifications.prescriptionsBody')}
                  value={settings.push_prescriptions}
                  onChange={(v) => toggleNotification('push_prescriptions', v)}
                />
                <ToggleRow
                  icon={CreditCard}
                  label={t('settings.notifications.billing')}
                  body={t('settings.notifications.billingBody')}
                  value={settings.push_billing}
                  onChange={(v) => toggleNotification('push_billing', v)}
                />
                <ToggleRow
                  icon={ShieldCheck}
                  label={t('settings.notifications.consentRequests')}
                  body={t('settings.notifications.consentRequestsBody')}
                  value={settings.push_consent_requests}
                  onChange={(v) => toggleNotification('push_consent_requests', v)}
                  isLast
                />
              </View>
              {updateSettings.isError ? (
                <Text className="mt-3 text-xs" style={{ color: colors.semantic.danger }}>
                  {t('settings.notifications.updateError')}
                </Text>
              ) : null}
            </SectionCard>

            {/* This device */}
            <SectionCard icon={Smartphone} title={t('settings.device.title')} body={t('settings.device.body')}>
              <View className="mt-2">
                <ToggleRow
                  icon={BellRing}
                  label={t('settings.device.pushLabel')}
                  body={t('settings.device.pushBody')}
                  value={pushEnabled}
                  onChange={togglePush}
                  busy={pushBusy}
                  isLast
                />
              </View>
              {pushError ? (
                <Text className="mt-3 text-xs" style={{ color: colors.semantic.danger }}>
                  {pushError}
                </Text>
              ) : null}
            </SectionCard>

            {/* Links to related settings that live on their own screens */}
            <View className="mb-4 overflow-hidden rounded-2xl bg-white">
              <NavRow
                icon={ShieldCheck}
                label={t('privacy.title')}
                body={t('privacy.subtitle')}
                onPress={() => router.push('/privacy')}
              />
              <View className="h-px bg-cream-300" style={{ marginLeft: 60 }} />
              <NavRow
                icon={Users}
                label={t('family.title')}
                body={t('family.subtitle')}
                onPress={() => router.push('/family')}
              />
            </View>
          </>
        )}

        <View className="h-10" />
      </ScrollView>
    </Screen>
  );
}

function SectionCard({
  icon: Icon,
  title,
  body,
  children,
}: {
  icon: typeof Globe;
  title: string;
  body: string;
  children: ReactNode;
}) {
  return (
    <View className="mb-4 rounded-2xl bg-white p-4">
      <View className="flex-row items-center">
        <View className="h-9 w-9 items-center justify-center rounded-full bg-gold-100">
          <Icon size={18} color={colors.gold[600]} />
        </View>
        <View className="ml-3 flex-1">
          <Text className="text-base font-bold text-navy-text">{title}</Text>
          <Text className="text-xs text-navy-secondary">{body}</Text>
        </View>
      </View>
      {children}
    </View>
  );
}

function LanguagePill({ label, active, onPress }: { label: string; active?: boolean; onPress: () => void }) {
  return (
    <Pressable
      onPress={onPress}
      className="flex-1 items-center rounded-xl py-2.5"
      style={{ backgroundColor: active ? colors.white : 'transparent' }}
    >
      <Text
        className="text-sm font-semibold"
        style={{ color: active ? colors.gold[600] : colors.navy.secondary }}
      >
        {label}
      </Text>
    </Pressable>
  );
}

function NavRow({
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
    <Pressable onPress={onPress} className="flex-row items-center px-4 py-4">
      <View className="h-9 w-9 items-center justify-center rounded-full bg-gold-50">
        <Icon size={17} color={colors.gold[600]} />
      </View>
      <View className="ml-3 flex-1">
        <Text className="text-sm font-semibold text-navy-text">{label}</Text>
        <Text className="text-xs text-navy-secondary">{body}</Text>
      </View>
      <ChevronRight size={18} color={colors.navy.muted} />
    </Pressable>
  );
}

function ToggleRow({
  icon: Icon,
  label,
  body,
  value,
  onChange,
  busy,
  isLast,
}: {
  icon: typeof Bell;
  label: string;
  body: string;
  value: boolean;
  onChange: (value: boolean) => void;
  busy?: boolean;
  isLast?: boolean;
}) {
  return (
    <View
      className="flex-row items-center py-3"
      style={!isLast ? { borderBottomWidth: 1, borderBottomColor: colors.cream[300] } : undefined}
    >
      <Icon size={18} color={colors.navy.secondary} />
      <View className="ml-3 flex-1">
        <Text className="text-sm font-semibold text-navy-text">{label}</Text>
        <Text className="text-xs text-navy-secondary">{body}</Text>
      </View>
      {busy ? (
        <ActivityIndicator color={colors.gold[500]} />
      ) : (
        <Switch
          value={value}
          onValueChange={onChange}
          trackColor={{ false: colors.cream[300], true: colors.gold[300] }}
          thumbColor={value ? colors.gold[600] : colors.white}
        />
      )}
    </View>
  );
}
