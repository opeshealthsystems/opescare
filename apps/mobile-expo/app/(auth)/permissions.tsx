import { useCallback, useEffect, useState } from 'react';
import { Linking, Platform, Pressable, ScrollView, Text, View } from 'react-native';
import * as SecureStore from 'expo-secure-store';
import * as Notifications from 'expo-notifications';
import * as Location from 'expo-location';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import { Bell, Check, MapPin, ShieldCheck, type LucideIcon } from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { Logo } from '../../components/ui/Logo';
import { useAuthStore } from '../../lib/store/auth';
import { useRegisterPushToken } from '../../lib/api/queries';
import { colors } from '../../theme/tokens';

/**
 * One-time priming screen shown right after signup / first successful login
 * (see lib/store/auth.ts's `permissions_pending` status) — explains why
 * OpesCare wants notification + location access before asking the OS for
 * them, then hands off to /(tabs)/home. Fully skippable: nothing here ever
 * blocks the user from reaching the app.
 */

type PermissionState = 'checking' | 'undetermined' | 'granted' | 'denied';

/** Mirrors app/settings.tsx's device-registration keys exactly, so a push
 * token registered here is recognized by the Settings toggle later instead
 * of being registered twice under a different identity. */
const DEVICE_FINGERPRINT_KEY = 'opescare_device_fingerprint';
const PUSH_TOKEN_ID_KEY = 'opescare_push_token_id';

const deviceStore =
  Platform.OS === 'web'
    ? {
        get: async (key: string) =>
          typeof localStorage !== 'undefined' ? localStorage.getItem(key) : null,
        set: async (key: string, value: string) => localStorage.setItem(key, value),
      }
    : {
        get: (key: string) => SecureStore.getItemAsync(key),
        set: (key: string, value: string) => SecureStore.setItemAsync(key, value),
      };

function generateFingerprint(): string {
  const random = Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2);
  return `${Platform.OS}-${Date.now().toString(36)}-${random}`.slice(0, 128);
}

function resolvePlatform(): 'ios' | 'android' | 'web' {
  if (Platform.OS === 'ios') return 'ios';
  if (Platform.OS === 'android') return 'android';
  return 'web';
}

function toState(status: string | undefined): PermissionState {
  if (status === 'granted') return 'granted';
  if (status === 'denied') return 'denied';
  return 'undetermined';
}

export default function PermissionsScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const completePermissionsPriming = useAuthStore((s) => s.completePermissionsPriming);
  const registerPushToken = useRegisterPushToken();

  const [notifState, setNotifState] = useState<PermissionState>('checking');
  const [locState, setLocState] = useState<PermissionState>('checking');
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    let cancelled = false;
    Notifications.getPermissionsAsync()
      .then((r) => !cancelled && setNotifState(toState(r.status)))
      .catch(() => !cancelled && setNotifState('undetermined'));
    Location.getForegroundPermissionsAsync()
      .then((r) => !cancelled && setLocState(toState(r.status)))
      .catch(() => !cancelled && setLocState('undetermined'));
    return () => {
      cancelled = true;
    };
  }, []);

  /** Best-effort: registers this device for push once notifications are
   * granted, reusing the same fingerprint convention as Settings so the two
   * screens agree on device state. Never blocks or fails the onboarding
   * flow — a failure here just means the user registers later from
   * Settings instead. */
  const registerDeviceForPush = useCallback(async () => {
    try {
      const existing = await deviceStore.get(PUSH_TOKEN_ID_KEY);
      if (existing) return;
      let fingerprint = await deviceStore.get(DEVICE_FINGERPRINT_KEY);
      if (!fingerprint) {
        fingerprint = generateFingerprint();
        await deviceStore.set(DEVICE_FINGERPRINT_KEY, fingerprint);
      }
      const result = await registerPushToken.mutateAsync({
        device_fingerprint: fingerprint,
        platform: resolvePlatform(),
        push_token: fingerprint,
      });
      await deviceStore.set(PUSH_TOKEN_ID_KEY, result.token_id);
    } catch {
      // Silent — Settings offers a retry, and this screen never gates on it.
    }
  }, [registerPushToken]);

  const requestNotifications = useCallback(async () => {
    try {
      const { status } = await Notifications.requestPermissionsAsync();
      const next = toState(status);
      setNotifState(next);
      if (next === 'granted') await registerDeviceForPush();
    } catch {
      setNotifState((prev) => (prev === 'checking' ? 'undetermined' : prev));
    }
  }, [registerDeviceForPush]);

  const requestLocation = useCallback(async () => {
    try {
      const { status } = await Location.requestForegroundPermissionsAsync();
      setLocState(toState(status));
    } catch {
      setLocState((prev) => (prev === 'checking' ? 'undetermined' : prev));
    }
  }, []);

  const handleRowPress = useCallback(
    (state: PermissionState, request: () => Promise<void>) => {
      if (state === 'undetermined') {
        request();
      } else if (state === 'denied' && Platform.OS !== 'web') {
        // Linking.openSettings() is a native-only API — it doesn't exist on
        // web at all (calling it throws synchronously, before any .catch()
        // can attach), so there's no "open OS settings" affordance on web.
        Linking.openSettings().catch(() => {});
      }
    },
    [],
  );

  const finish = useCallback(async () => {
    await completePermissionsPriming();
    router.replace('/(tabs)/home');
  }, [completePermissionsPriming, router]);

  const handleContinue = useCallback(async () => {
    setBusy(true);
    try {
      if (notifState === 'undetermined') await requestNotifications();
      if (locState === 'undetermined') await requestLocation();
    } finally {
      setBusy(false);
      finish();
    }
  }, [notifState, locState, requestNotifications, requestLocation, finish]);

  const handleSkip = useCallback(() => {
    finish();
  }, [finish]);

  return (
    <Screen className="px-0">
      <ScrollView
        className="flex-1 px-6"
        showsVerticalScrollIndicator={false}
        contentContainerStyle={{ paddingBottom: 24 }}
      >
        <View className="items-center pb-2 pt-4">
          <Logo size={72} markOnly />
        </View>

        <Text className="mt-4 text-center text-2xl font-extrabold text-navy-text">
          {t('permissions.title')}
        </Text>
        <Text className="mt-2 text-center text-sm text-navy-secondary">
          {t('permissions.subtitle')}
        </Text>

        <View className="mt-6">
          <PermissionRow
            icon={Bell}
            title={t('permissions.notificationsTitle')}
            body={t('permissions.notificationsBody')}
            state={notifState}
            t={t}
            onPress={() => handleRowPress(notifState, requestNotifications)}
          />
          <PermissionRow
            icon={MapPin}
            title={t('permissions.locationTitle')}
            body={t('permissions.locationBody')}
            state={locState}
            t={t}
            onPress={() => handleRowPress(locState, requestLocation)}
          />
        </View>

        <View className="mt-5 flex-row items-start rounded-2xl bg-gold-50 p-4">
          <ShieldCheck size={16} color={colors.gold[600]} />
          <Text className="ml-3 flex-1 text-xs text-navy-secondary">
            {t('permissions.privacyNote')}
          </Text>
        </View>

        <View className="mt-8">
          <Button label={t('permissions.continue')} onPress={handleContinue} loading={busy} />
        </View>

        <Pressable onPress={handleSkip} disabled={busy} className="mt-5 items-center py-2">
          <Text className="text-sm font-semibold text-gold-500">{t('permissions.skip')}</Text>
        </Pressable>
      </ScrollView>
    </Screen>
  );
}

function PermissionRow({
  icon: Icon,
  title,
  body,
  state,
  t,
  onPress,
}: {
  icon: LucideIcon;
  title: string;
  body: string;
  state: PermissionState;
  t: (key: string) => string;
  onPress: () => void;
}) {
  const badge =
    state === 'granted'
      ? { label: t('permissions.statusAllowed'), bg: colors.semantic.successSurface, fg: colors.semantic.success }
      : state === 'denied'
        ? { label: t('permissions.statusDenied'), bg: colors.semantic.dangerSurface, fg: colors.semantic.danger }
        : state === 'checking'
          ? { label: t('permissions.statusChecking'), bg: colors.cream[200], fg: colors.navy.muted }
          : { label: t('permissions.enable'), bg: colors.gold[50], fg: colors.gold[600] };

  return (
    <Pressable
      onPress={onPress}
      disabled={state === 'granted' || state === 'checking'}
      className="mb-3 flex-row items-center rounded-2xl border border-cream-300 bg-white p-4"
    >
      <View className="h-11 w-11 items-center justify-center rounded-full bg-gold-50">
        <Icon size={20} color={colors.gold[600]} />
      </View>
      <View className="ml-3 flex-1">
        <Text className="text-sm font-bold text-navy-text">{title}</Text>
        <Text className="mt-0.5 text-xs text-navy-secondary">{body}</Text>
      </View>
      <View className="ml-2 flex-row items-center rounded-full px-3 py-1.5" style={{ backgroundColor: badge.bg }}>
        {state === 'granted' ? <Check size={12} color={badge.fg} style={{ marginRight: 4 }} /> : null}
        <Text className="text-xs font-semibold" style={{ color: badge.fg }}>
          {state === 'denied' ? t('permissions.openSettings') : badge.label}
        </Text>
      </View>
    </Pressable>
  );
}
