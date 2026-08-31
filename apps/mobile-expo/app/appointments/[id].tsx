import { useState } from 'react';
import { ActivityIndicator, Alert, Pressable, ScrollView, Text, View } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  Building2,
  Calendar,
  Clock,
  FileText,
  Stethoscope,
  XCircle,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { useAppointmentDetail, useCancelAppointment } from '../../lib/api/queries';
import { colors } from '../../theme/tokens';

const CANCELLABLE_STATUSES = ['booked', 'confirmed'];

function statusStyle(status: string) {
  switch (status) {
    case 'completed':
      return { bg: colors.semantic.successSurface, fg: colors.semantic.success };
    case 'cancelled':
      return { bg: colors.semantic.dangerSurface, fg: colors.semantic.danger };
    case 'no_show':
      return { bg: colors.semantic.warningSurface, fg: colors.semantic.warning };
    case 'checked_in':
      return { bg: colors.gold[50], fg: colors.gold[600] };
    default:
      return { bg: colors.semantic.infoSurface, fg: colors.semantic.info };
  }
}

function formatDateTime(iso: string | null, locale: string) {
  if (!iso) return '—';
  const date = new Date(iso);
  const localeTag = locale === 'fr' ? 'fr-FR' : 'en-US';
  const day = date.toLocaleDateString(localeTag, {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
  const time = date.toLocaleTimeString(localeTag, { hour: '2-digit', minute: '2-digit' });
  return `${day}, ${time}`;
}

function InfoRow({
  icon: Icon,
  label,
  value,
}: {
  icon: typeof Calendar;
  label: string;
  value: string;
}) {
  return (
    <View
      className="flex-row items-start py-3"
      style={{ borderTopWidth: 1, borderTopColor: colors.cream[300] }}
    >
      <View className="mr-3 h-9 w-9 items-center justify-center rounded-full bg-gold-50">
        <Icon size={16} color={colors.gold[600]} />
      </View>
      <View className="flex-1">
        <Text className="text-xs text-navy-muted">{label}</Text>
        <Text className="mt-0.5 text-sm font-semibold text-navy-text">{value}</Text>
      </View>
    </View>
  );
}

export default function AppointmentDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const { t, i18n } = useTranslation();
  const router = useRouter();
  const { data: appointment, isLoading, isError } = useAppointmentDetail(id);
  const cancelMutation = useCancelAppointment();
  const [cancelError, setCancelError] = useState<string | null>(null);

  const confirmCancel = () => {
    if (!appointment) return;
    Alert.alert(
      t('appointments.detail.cancelConfirmTitle'),
      t('appointments.detail.cancelConfirmBody'),
      [
        { text: t('appointments.detail.cancelConfirmNo'), style: 'cancel' },
        {
          text: t('appointments.detail.cancelConfirmYes'),
          style: 'destructive',
          onPress: () => {
            setCancelError(null);
            cancelMutation.mutate(
              { id: appointment.id },
              { onError: () => setCancelError(t('appointments.detail.cancelError')) },
            );
          },
        },
      ],
    );
  };

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
        <Text className="ml-4 text-lg font-extrabold text-navy-text">{t('appointments.detail.title')}</Text>
      </View>

      {isLoading ? (
        <View className="flex-1 items-center justify-center">
          <ActivityIndicator color={colors.gold[500]} />
        </View>
      ) : isError || !appointment ? (
        <View className="flex-1 items-center justify-center px-10">
          <Text className="text-center text-sm text-navy-secondary">
            {isError ? t('appointments.detail.loadError') : t('appointments.detail.notFound')}
          </Text>
        </View>
      ) : (
        <ScrollView className="flex-1 px-6" showsVerticalScrollIndicator={false} contentContainerStyle={{ paddingTop: 16, paddingBottom: 40 }}>
          <View className="rounded-2xl bg-white p-4">
            <View className="flex-row items-center justify-between">
              <Text className="text-xl font-extrabold text-navy-text">{appointment.appointment_type}</Text>
              <View
                className="rounded-full px-3 py-1"
                style={{ backgroundColor: statusStyle(appointment.status).bg }}
              >
                <Text className="text-xs font-semibold" style={{ color: statusStyle(appointment.status).fg }}>
                  {t(`appointments.status.${appointment.status}`, { defaultValue: appointment.status })}
                </Text>
              </View>
            </View>

            <View className="mt-1">
              <InfoRow
                icon={Calendar}
                label={t('appointments.detail.dateTime')}
                value={formatDateTime(appointment.scheduled_at, i18n.language)}
              />
              {appointment.facility_name ? (
                <InfoRow icon={Building2} label={t('appointments.detail.facility')} value={appointment.facility_name} />
              ) : null}
              {appointment.provider_name ? (
                <InfoRow icon={Stethoscope} label={t('appointments.detail.provider')} value={appointment.provider_name} />
              ) : null}
              <InfoRow
                icon={FileText}
                label={t('appointments.detail.reason')}
                value={appointment.reason || t('appointments.detail.noReason')}
              />
              {appointment.checked_in_at ? (
                <InfoRow
                  icon={Clock}
                  label={t('appointments.detail.checkedInAt')}
                  value={formatDateTime(appointment.checked_in_at, i18n.language)}
                />
              ) : null}
            </View>
          </View>

          {appointment.status === 'cancelled' ? (
            <View className="mt-4 rounded-2xl bg-white p-4">
              <View className="flex-row items-center">
                <XCircle size={16} color={colors.semantic.danger} />
                <Text className="ml-2 text-sm font-bold text-navy-text">
                  {t('appointments.detail.cancellationReason')}
                </Text>
              </View>
              <Text className="mt-2 text-sm text-navy-secondary">
                {appointment.cancellation_reason || t('appointments.detail.noReason')}
              </Text>
              {appointment.cancelled_at ? (
                <Text className="mt-2 text-xs text-navy-muted">
                  {t('appointments.detail.cancelledAt')}: {formatDateTime(appointment.cancelled_at, i18n.language)}
                </Text>
              ) : null}
            </View>
          ) : null}

          {cancelError ? (
            <Text className="mt-4 text-center text-sm text-danger">{cancelError}</Text>
          ) : null}

          {CANCELLABLE_STATUSES.includes(appointment.status) ? (
            <Pressable
              onPress={confirmCancel}
              disabled={cancelMutation.isPending}
              className="mt-6 h-14 flex-row items-center justify-center rounded-2xl border"
              style={{
                borderColor: colors.semantic.danger,
                opacity: cancelMutation.isPending ? 0.6 : 1,
              }}
            >
              {cancelMutation.isPending ? (
                <ActivityIndicator color={colors.semantic.danger} />
              ) : (
                <>
                  <XCircle size={18} color={colors.semantic.danger} />
                  <Text className="ml-2 text-base font-semibold" style={{ color: colors.semantic.danger }}>
                    {t('appointments.detail.cancelButton')}
                  </Text>
                </>
              )}
            </Pressable>
          ) : null}
        </ScrollView>
      )}
    </Screen>
  );
}
