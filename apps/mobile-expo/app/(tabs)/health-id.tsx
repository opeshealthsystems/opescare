import { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Pressable,
  RefreshControl,
  ScrollView,
  Share,
  Text,
  View,
} from 'react-native';
import { useTranslation } from 'react-i18next';
import QRCode from 'react-native-qrcode-svg';
import {
  AlertCircle,
  Clock,
  Droplet,
  RefreshCw,
  Share2,
  ShieldCheck,
  Sparkles,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { useHealthIdCard, useGenerateTemporaryQr } from '../../lib/api/queries';
import { colors } from '../../theme/tokens';
import type { TemporaryQrCode } from '../../lib/api/types';

/** Backend `Patient::identity_status` groupings (see app/Enums/IdentityStatus.php) —
 * mirrored here only for badge tone, never compared with `===` on the backend side. */
const ACTIVE_STATUSES = new Set(['active', 'verified', 'verified_by_facility']);
const BLOCKED_STATUSES = new Set(['suspended', 'deceased', 'entered_in_error', 'merged', 'erasure_pending']);

type Tone = 'success' | 'warning' | 'danger';

const TONE_STYLES: Record<Tone, { surface: string; text: string }> = {
  success: { surface: 'rgba(255,255,255,0.24)', text: colors.white },
  warning: { surface: colors.semantic.warningSurface, text: colors.semantic.warning },
  danger: { surface: colors.semantic.dangerSurface, text: colors.semantic.danger },
};

function toneFor(statusKey: string): Tone {
  if (ACTIVE_STATUSES.has(statusKey)) return 'success';
  if (BLOCKED_STATUSES.has(statusKey)) return 'danger';
  return 'warning';
}

function titleCase(value: string): string {
  return value
    .split('_')
    .filter(Boolean)
    .map((word) => word[0].toUpperCase() + word.slice(1))
    .join(' ');
}

function formatDob(dob: string | null, language: string): string {
  if (!dob) return '—';
  const date = new Date(dob);
  if (Number.isNaN(date.getTime())) return dob;
  return date.toLocaleDateString(language === 'fr' ? 'fr-FR' : 'en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

function formatCountdown(totalSeconds: number): string {
  const clamped = Math.max(0, totalSeconds);
  const minutes = Math.floor(clamped / 60)
    .toString()
    .padStart(2, '0');
  const seconds = Math.floor(clamped % 60)
    .toString()
    .padStart(2, '0');
  return `${minutes}:${seconds}`;
}

export default function HealthIdScreen() {
  const { t, i18n } = useTranslation();
  const healthId = useHealthIdCard();
  const temporaryQr = useGenerateTemporaryQr();

  const [tempQr, setTempQr] = useState<TemporaryQrCode | null>(null);
  const [secondsLeft, setSecondsLeft] = useState(0);

  useEffect(() => {
    if (!tempQr) return;
    const expiresAtMs = new Date(tempQr.expires_at).getTime();

    const tick = () => {
      const remaining = Math.round((expiresAtMs - Date.now()) / 1000);
      if (remaining <= 0) {
        setSecondsLeft(0);
        setTempQr(null);
        return;
      }
      setSecondsLeft(remaining);
    };

    tick();
    const intervalId = setInterval(tick, 1000);
    return () => clearInterval(intervalId);
  }, [tempQr]);

  const handleGenerate = async () => {
    try {
      const data = await temporaryQr.mutateAsync();
      setTempQr(data);
    } catch {
      // temporaryQr.isError drives the inline error message below.
    }
  };

  const handleShare = async () => {
    if (!tempQr) return;
    try {
      await Share.share({
        message: t('healthId.shareMessage', {
          url: tempQr.verify_url,
          time: formatCountdown(secondsLeft),
        }),
      });
    } catch {
      // Share sheet dismissed/cancelled — nothing to surface.
    }
  };

  const card = healthId.data;
  const statusKey = (card?.status ?? '').toLowerCase();
  const tone = toneFor(statusKey);
  const statusLabel = statusKey
    ? t(`healthId.statusValues.${statusKey}`, { defaultValue: titleCase(statusKey) })
    : '—';
  const sexKey = (card?.sex ?? '').toLowerCase();
  const sexLabel = sexKey ? t(`healthId.sexValues.${sexKey}`, { defaultValue: titleCase(sexKey) }) : '—';

  return (
    <Screen className="px-0">
      <ScrollView
        className="flex-1 px-6"
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={healthId.isRefetching}
            onRefresh={() => healthId.refetch()}
            tintColor={colors.gold[500]}
          />
        }
      >
        <View className="mt-2">
          <Text className="text-2xl font-extrabold text-navy-text">{t('healthId.title')}</Text>
          <Text className="mt-1 text-sm text-navy-secondary">{t('healthId.subtitle')}</Text>
        </View>

        {healthId.isLoading ? (
          <View className="mt-10 items-center justify-center">
            <ActivityIndicator color={colors.gold[500]} size="large" />
          </View>
        ) : healthId.isError || !card ? (
          <View className="mt-8 items-center rounded-2xl bg-white p-6">
            <AlertCircle size={28} color={colors.semantic.danger} />
            <Text className="mt-3 text-center text-sm text-navy-secondary">{t('healthId.loadError')}</Text>
            <Pressable
              onPress={() => healthId.refetch()}
              className="mt-4 rounded-full bg-gold-50 px-5 py-2.5"
            >
              <Text className="text-sm font-semibold text-gold-600">{t('healthId.retry')}</Text>
            </Pressable>
          </View>
        ) : (
          <>
            <View
              className="mt-6 overflow-hidden rounded-3xl bg-gold-500 p-5"
              style={{
                shadowColor: colors.gold[700],
                shadowOpacity: 0.25,
                shadowRadius: 14,
                shadowOffset: { width: 0, height: 8 },
                elevation: 4,
              }}
            >
              <View className="flex-row items-center justify-between">
                <Text className="text-sm font-bold text-white">{t('healthId.cardLabel')}</Text>
                <View
                  className="flex-row items-center rounded-full px-3 py-1"
                  style={{ backgroundColor: TONE_STYLES[tone].surface }}
                >
                  <ShieldCheck size={12} color={TONE_STYLES[tone].text} />
                  <Text className="ml-1 text-[11px] font-bold" style={{ color: TONE_STYLES[tone].text }}>
                    {statusLabel}
                  </Text>
                </View>
              </View>

              <Text className="mt-6 text-xs font-semibold uppercase tracking-widest text-white/70">
                {t('healthId.idLabel')}
              </Text>
              <Text className="mt-1 text-2xl font-extrabold tracking-wider text-white">{card.health_id}</Text>
              <Text className="mt-1 text-base font-semibold text-white/90">{card.display_name}</Text>

              <View className="mt-5 h-px bg-white/25" />

              <View className="mt-4 flex-row justify-between">
                <CardStat label={t('healthId.sex')} value={sexLabel} />
                <CardStat label={t('healthId.dob')} value={formatDob(card.dob, i18n.language)} />
                <CardStat label={t('healthId.bloodType')} value={card.blood_type ?? '—'} icon={Droplet} />
              </View>
            </View>

            <View className="mt-6 items-center rounded-2xl bg-white p-6">
              <View className="flex-row items-center">
                <ShieldCheck size={16} color={colors.gold[600]} />
                <Text className="ml-2 text-sm font-bold text-navy-text">{t('healthId.qrSectionTitle')}</Text>
              </View>
              <View className="mt-4 rounded-2xl border border-cream-300 p-4">
                <QRCode
                  value={card.qr_payload}
                  size={176}
                  color={colors.navy.text}
                  backgroundColor={colors.white}
                />
              </View>
              <Text className="mt-4 text-center text-xs text-navy-muted">{t('healthId.qrSectionBody')}</Text>
            </View>

            <View className="mb-10 mt-6 rounded-2xl bg-white p-6">
              <Text className="text-sm font-bold text-navy-text">{t('healthId.temporaryQrTitle')}</Text>
              <Text className="mt-1 text-xs text-navy-secondary">{t('healthId.temporaryQrBody')}</Text>

              {tempQr ? (
                <View className="mt-5 items-center">
                  <View className="rounded-2xl border border-gold-300 p-4">
                    <QRCode
                      value={tempQr.qr_payload}
                      size={176}
                      color={colors.navy.text}
                      backgroundColor={colors.white}
                    />
                  </View>
                  <View className="mt-3 flex-row items-center rounded-full bg-gold-50 px-3 py-1">
                    <Clock size={13} color={colors.gold[600]} />
                    <Text className="ml-1.5 text-xs font-semibold text-gold-600">
                      {t('healthId.expiresIn', { time: formatCountdown(secondsLeft) })}
                    </Text>
                  </View>
                  <View className="mt-4 flex-row" style={{ gap: 12 }}>
                    <Pressable
                      onPress={handleShare}
                      className="flex-row items-center rounded-full border border-cream-300 px-4 py-2.5"
                    >
                      <Share2 size={14} color={colors.navy.text} />
                      <Text className="ml-1.5 text-xs font-semibold text-navy-text">{t('healthId.share')}</Text>
                    </Pressable>
                    <Pressable
                      onPress={handleGenerate}
                      disabled={temporaryQr.isPending}
                      className="flex-row items-center rounded-full border border-gold-300 px-4 py-2.5"
                      style={{ opacity: temporaryQr.isPending ? 0.6 : 1 }}
                    >
                      {temporaryQr.isPending ? (
                        <ActivityIndicator size="small" color={colors.gold[600]} />
                      ) : (
                        <RefreshCw size={14} color={colors.gold[600]} />
                      )}
                      <Text className="ml-1.5 text-xs font-semibold text-gold-600">
                        {t('healthId.regenerate')}
                      </Text>
                    </Pressable>
                  </View>
                </View>
              ) : (
                <View className="mt-5">
                  {temporaryQr.isError ? (
                    <Text className="mb-3 text-center text-xs text-danger">{t('healthId.generateError')}</Text>
                  ) : null}
                  <Button
                    label={temporaryQr.isPending ? t('healthId.generating') : t('healthId.generateButton')}
                    onPress={handleGenerate}
                    loading={temporaryQr.isPending}
                    leftIcon={Sparkles}
                    showChevron={false}
                  />
                </View>
              )}
            </View>
          </>
        )}
      </ScrollView>
    </Screen>
  );
}

function CardStat({ label, value, icon: Icon }: { label: string; value: string; icon?: LucideIcon }) {
  return (
    <View className="items-start">
      <Text className="text-[11px] text-white/70">{label}</Text>
      <View className="mt-1 flex-row items-center">
        {Icon ? <Icon size={12} color={colors.white} style={{ marginRight: 4 }} /> : null}
        <Text className="text-sm font-bold text-white">{value}</Text>
      </View>
    </View>
  );
}
