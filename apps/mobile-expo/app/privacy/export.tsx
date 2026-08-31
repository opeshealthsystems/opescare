import { useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  Pressable,
  ScrollView,
  Share,
  Text,
  TextInput,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  Ban,
  CheckCircle2,
  Download,
  Hourglass,
  Inbox,
  Send,
  Share2,
  XCircle,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { TextField } from '../../components/ui/TextField';
import { colors } from '../../theme/tokens';
import {
  type CorrectionRequestItem,
  type DataExportRequestItem,
  type DataExportType,
  extractApiErrorMessage,
  useCreateCorrectionRequest,
  useCreateDataExportRequest,
  useDataExportRequests,
  useDownloadDataExport,
} from '../../lib/api/queries';

const EXPORT_TYPES: DataExportType[] = ['full_record', 'encounters', 'prescriptions', 'lab_results', 'imaging'];
const RECORD_TYPES = ['clinical_note', 'diagnosis', 'allergy', 'prescription', 'lab_result', 'immunization', 'other'];
const UUID_RE = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;

const EXPORT_STATUS_TONE: Record<string, 'warning' | 'success' | 'danger' | 'muted'> = {
  pending: 'warning',
  approved: 'success',
  downloaded: 'muted',
  rejected: 'danger',
  expired: 'muted',
};

export default function PrivacyExportScreen() {
  const { t } = useTranslation();
  const router = useRouter();

  return (
    <Screen className="px-0">
      <ScrollView
        className="flex-1 px-6"
        showsVerticalScrollIndicator={false}
        keyboardShouldPersistTaps="handled"
        contentContainerStyle={{ paddingBottom: 32 }}
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
            <Text className="text-xl font-extrabold text-navy-text">{t('privacy.exportTitle')}</Text>
          </View>
        </View>

        <ExportSection t={t} />

        <View className="my-8 h-px bg-cream-300" />

        <CorrectionSection t={t} />
      </ScrollView>
    </Screen>
  );
}

function ExportSection({ t }: { t: (key: string, opts?: Record<string, unknown>) => string }) {
  const [selectedType, setSelectedType] = useState<DataExportType>('full_record');

  const requestsQuery = useDataExportRequests();
  const createMutation = useCreateDataExportRequest();
  const downloadMutation = useDownloadDataExport();

  const requests = requestsQuery.data ?? [];

  const handleRequest = () => {
    createMutation.mutate(selectedType, {
      onSuccess: () => Alert.alert(t('privacy.exportSectionTitle'), t('privacy.exportRequested')),
      onError: (err) => Alert.alert(t('privacy.actionFailed'), extractApiErrorMessage(err)),
    });
  };

  const handleDownload = (item: DataExportRequestItem) => {
    downloadMutation.mutate(item.id, {
      onSuccess: async (result) => {
        try {
          await Share.share({
            title: `opescare-export-${result.export_type}-${result.id}`,
            message: JSON.stringify(result.content, null, 2),
          });
        } catch {
          // User dismissed the share sheet — nothing to do.
        }
      },
      onError: (err) => Alert.alert(t('privacy.downloadFailed'), extractApiErrorMessage(err)),
    });
  };

  return (
    <View className="mt-6">
      <Text className="text-lg font-bold text-navy-text">{t('privacy.exportSectionTitle')}</Text>
      <Text className="mt-1 text-sm text-navy-secondary">{t('privacy.exportSectionSubtitle')}</Text>

      <View className="mt-4 flex-row flex-wrap gap-2">
        {EXPORT_TYPES.map((type) => {
          const selected = type === selectedType;
          return (
            <Pressable
              key={type}
              onPress={() => setSelectedType(type)}
              className="rounded-full border px-4 py-2"
              style={{
                borderColor: selected ? colors.gold[500] : colors.cream[300],
                backgroundColor: selected ? colors.gold[500] : colors.white,
              }}
            >
              <Text
                className="text-xs font-semibold"
                style={{ color: selected ? colors.white : colors.navy.secondary }}
              >
                {t(`privacy.exportType.${type}`)}
              </Text>
            </Pressable>
          );
        })}
      </View>

      <View className="mt-4">
        <Button
          label={t('privacy.requestExport')}
          leftIcon={Download}
          showChevron={false}
          loading={createMutation.isPending}
          onPress={handleRequest}
        />
      </View>

      <Text className="mb-3 mt-6 text-sm font-bold text-navy-text">{t('privacy.myRequests')}</Text>
      {requestsQuery.isLoading ? (
        <ActivityIndicator color={colors.gold[500]} />
      ) : requests.length === 0 ? (
        <View className="items-center rounded-2xl bg-white p-6">
          <Inbox size={20} color={colors.navy.muted} />
          <Text className="mt-2 text-sm text-navy-muted">{t('privacy.noExportRequests')}</Text>
        </View>
      ) : (
        requests.map((item) => (
          <View key={item.id} className="mb-3 rounded-2xl bg-white p-4">
            <View className="flex-row items-center justify-between">
              <Text className="text-sm font-bold text-navy-text">
                {t(`privacy.exportType.${item.export_type}`, { defaultValue: item.export_type })}
              </Text>
              <ExportStatusBadge status={item.status} t={t} />
            </View>
            <Text className="mt-1 text-xs text-navy-muted">
              {t('privacy.requestedOn', { date: new Date(item.created_at).toLocaleDateString() })}
            </Text>
            {item.status === 'approved' ? (
              <Pressable
                onPress={() => handleDownload(item)}
                disabled={downloadMutation.isPending && downloadMutation.variables === item.id}
                className="mt-3 flex-row items-center justify-center rounded-xl py-2.5"
                style={{ backgroundColor: colors.gold[50] }}
              >
                {downloadMutation.isPending && downloadMutation.variables === item.id ? (
                  <ActivityIndicator size="small" color={colors.gold[600]} />
                ) : (
                  <>
                    <Share2 size={14} color={colors.gold[600]} />
                    <Text className="ml-1.5 text-xs font-bold text-gold-600">{t('privacy.download')}</Text>
                  </>
                )}
              </Pressable>
            ) : null}
          </View>
        ))
      )}
    </View>
  );
}

function ExportStatusBadge({
  status,
  t,
}: {
  status: string;
  t: (key: string, opts?: Record<string, unknown>) => string;
}) {
  const tone = EXPORT_STATUS_TONE[status] ?? 'muted';
  const bg =
    tone === 'success'
      ? colors.semantic.successSurface
      : tone === 'danger'
        ? colors.semantic.dangerSurface
        : tone === 'warning'
          ? colors.semantic.warningSurface
          : colors.cream[200];
  const fg =
    tone === 'success'
      ? colors.semantic.success
      : tone === 'danger'
        ? colors.semantic.danger
        : tone === 'warning'
          ? colors.semantic.warning
          : colors.navy.secondary;
  const Icon =
    status === 'approved'
      ? CheckCircle2
      : status === 'rejected'
        ? XCircle
        : status === 'expired'
          ? Ban
          : status === 'pending'
            ? Hourglass
            : CheckCircle2;
  return (
    <View className="flex-row items-center rounded-full px-2.5 py-1" style={{ backgroundColor: bg }}>
      <Icon size={11} color={fg} />
      <Text className="ml-1 text-[10px] font-bold" style={{ color: fg }}>
        {t(`privacy.exportStatus.${status}`, { defaultValue: status })}
      </Text>
    </View>
  );
}

function CorrectionSection({ t }: { t: (key: string, opts?: Record<string, unknown>) => string }) {
  const [recordType, setRecordType] = useState<string | null>(null);
  const [resourceId, setResourceId] = useState('');
  const [reason, setReason] = useState('');
  const [errors, setErrors] = useState<{ recordType?: string; resourceId?: string; reason?: string }>({});
  const [submitted, setSubmitted] = useState<CorrectionRequestItem | null>(null);

  const createMutation = useCreateCorrectionRequest();

  const resetForm = () => {
    setRecordType(null);
    setResourceId('');
    setReason('');
    setErrors({});
    setSubmitted(null);
  };

  const handleSubmit = () => {
    const nextErrors: typeof errors = {};
    if (!recordType) nextErrors.recordType = t('privacy.errorSelectType');
    if (!UUID_RE.test(resourceId.trim())) nextErrors.resourceId = t('privacy.errorInvalidId');
    if (reason.trim().length < 10) nextErrors.reason = t('privacy.errorReasonTooShort');
    setErrors(nextErrors);
    if (Object.keys(nextErrors).length > 0 || !recordType) return;

    createMutation.mutate(
      { resource_type: recordType, resource_id: resourceId.trim(), reason: reason.trim() },
      {
        onSuccess: (item) => setSubmitted(item),
        onError: (err) => Alert.alert(t('privacy.actionFailed'), extractApiErrorMessage(err)),
      },
    );
  };

  if (submitted) {
    return (
      <View className="mt-2">
        <Text className="text-lg font-bold text-navy-text">{t('privacy.correctionSectionTitle')}</Text>
        <View className="mt-4 items-center rounded-2xl bg-white p-6">
          <CheckCircle2 size={24} color={colors.semantic.success} />
          <Text className="mt-2 text-center text-sm font-semibold text-navy-text">
            {t('privacy.correctionSubmitted')}
          </Text>
          <Text className="mt-1 text-center text-xs text-navy-muted">
            {t('privacy.correctionReference', { id: submitted.id })}
          </Text>
          <Pressable onPress={resetForm} className="mt-4">
            <Text className="text-sm font-semibold text-gold-500">{t('privacy.correctionAnother')}</Text>
          </Pressable>
        </View>
      </View>
    );
  }

  return (
    <View className="mt-2">
      <Text className="text-lg font-bold text-navy-text">{t('privacy.correctionSectionTitle')}</Text>
      <Text className="mt-1 text-sm text-navy-secondary">{t('privacy.correctionSectionSubtitle')}</Text>

      <Text className="mb-2 mt-4 text-sm font-semibold text-navy-text">{t('privacy.recordTypeLabel')}</Text>
      <View className="flex-row flex-wrap gap-2">
        {RECORD_TYPES.map((type) => {
          const selected = type === recordType;
          return (
            <Pressable
              key={type}
              onPress={() => setRecordType(type)}
              className="rounded-full border px-4 py-2"
              style={{
                borderColor: selected ? colors.gold[500] : colors.cream[300],
                backgroundColor: selected ? colors.gold[500] : colors.white,
              }}
            >
              <Text
                className="text-xs font-semibold"
                style={{ color: selected ? colors.white : colors.navy.secondary }}
              >
                {t(`privacy.recordType.${type}`)}
              </Text>
            </Pressable>
          );
        })}
      </View>
      {errors.recordType ? <Text className="mt-1 text-xs text-danger">{errors.recordType}</Text> : null}

      <View className="mt-4">
        <TextField
          label={t('privacy.recordIdLabel')}
          placeholder={t('privacy.recordIdPlaceholder')}
          autoCapitalize="none"
          value={resourceId}
          onChangeText={setResourceId}
          error={errors.resourceId}
        />
        <Text className="-mt-3 mb-4 text-xs text-navy-muted">{t('privacy.recordIdHint')}</Text>
      </View>

      <Text className="mb-2 text-sm font-semibold text-navy-text">{t('privacy.reasonLabel')}</Text>
      <View
        className="rounded-2xl border bg-white p-4"
        style={{ borderColor: errors.reason ? colors.semantic.danger : colors.cream[300] }}
      >
        <TextInput
          className="text-base text-navy-text"
          placeholder={t('privacy.reasonPlaceholder')}
          placeholderTextColor={colors.navy.muted}
          multiline
          numberOfLines={4}
          textAlignVertical="top"
          style={{ minHeight: 90 }}
          value={reason}
          onChangeText={setReason}
        />
      </View>
      {errors.reason ? <Text className="mt-1 text-xs text-danger">{errors.reason}</Text> : null}

      <View className="mt-5">
        <Button
          label={t('privacy.submitCorrection')}
          leftIcon={Send}
          showChevron={false}
          loading={createMutation.isPending}
          onPress={handleSubmit}
        />
      </View>
    </View>
  );
}
