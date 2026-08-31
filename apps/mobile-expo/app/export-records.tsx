import { useState } from 'react';
import { ActivityIndicator, Alert, Pressable, ScrollView, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import * as FileSystem from 'expo-file-system/legacy';
import * as Sharing from 'expo-sharing';
import {
  AlertTriangle,
  ArrowLeft,
  Check,
  FileCode,
  FileText,
  FlaskConical,
  Pill,
  ShieldCheck,
  Stethoscope,
  Syringe,
  User,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../components/ui/Screen';
import {
  extractApiErrorMessage,
  useExportMedicalRecordsFhir,
  useExportMedicalRecordsPdf,
} from '../lib/api/queries';
import { colors } from '../theme/tokens';

type ExportFormat = 'pdf' | 'fhir';

/**
 * Direct, immediate export of the patient's full medical record as a PDF or
 * FHIR R4 bundle — distinct from the Privacy hub's data-export-requests flow
 * (app/privacy/export.tsx), which is a moderated, approval-gated request for
 * a scoped export. This screen calls POST /mobile/medical-records/export/pdf
 * or /export/fhir directly and hands the result to the OS share sheet.
 *
 * No reference image was identified for this screen; it follows the app's
 * established header + white-card list pattern (see app/documents.tsx,
 * app/privacy/export.tsx).
 */
export default function ExportRecordsScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const [activeFormat, setActiveFormat] = useState<ExportFormat | null>(null);

  const exportPdf = useExportMedicalRecordsPdf();
  const exportFhir = useExportMedicalRecordsFhir();

  /** Writes the export to app storage. Throws on failure — the caller treats
   * that as a real export failure. */
  const saveExport = async (
    filename: string,
    content: string,
    encoding: 'base64' | 'utf8',
  ): Promise<string> => {
    const uri = `${FileSystem.documentDirectory}${filename}`;
    await FileSystem.writeAsStringAsync(uri, content, {
      encoding:
        encoding === 'base64' ? FileSystem.EncodingType.Base64 : FileSystem.EncodingType.UTF8,
    });
    return uri;
  };

  /** Opens the OS share sheet. The file is already saved at this point, so a
   * cancelled/dismissed share sheet is not treated as a failure — matches
   * the pattern in app/privacy/export.tsx. */
  const presentShareSheet = async (uri: string, mimeType: string) => {
    try {
      const canShare = await Sharing.isAvailableAsync();
      if (!canShare) {
        Alert.alert(t('exportRecords.successTitle'), t('exportRecords.sharingUnavailable'));
        return;
      }
      await Sharing.shareAsync(uri, {
        mimeType,
        dialogTitle: t('exportRecords.shareTitle'),
        UTI: mimeType === 'application/pdf' ? 'com.adobe.pdf' : 'public.json',
      });
    } catch {
      // User dismissed the share sheet — nothing to do, the file is saved.
    }
  };

  const handleExport = async (format: ExportFormat) => {
    if (activeFormat) return;
    setActiveFormat(format);
    try {
      if (format === 'pdf') {
        const result = await exportPdf.mutateAsync();
        const uri = await saveExport(result.filename, result.file_base64, 'base64');
        await presentShareSheet(uri, result.mime_type);
      } else {
        const bundle = await exportFhir.mutateAsync();
        const filename = `medical-record-fhir-${Date.now()}.json`;
        const uri = await saveExport(filename, JSON.stringify(bundle, null, 2), 'utf8');
        await presentShareSheet(uri, 'application/fhir+json');
      }
    } catch (err) {
      Alert.alert(t('exportRecords.errorTitle'), extractApiErrorMessage(err));
    } finally {
      setActiveFormat(null);
    }
  };

  return (
    <Screen className="px-0">
      <ScrollView
        className="flex-1 px-6"
        showsVerticalScrollIndicator={false}
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
          <Text className="ml-3 flex-1 text-xl font-extrabold text-navy-text">
            {t('exportRecords.title')}
          </Text>
        </View>
        <Text className="mb-5 mt-2 text-sm text-navy-secondary">
          {t('exportRecords.subtitle')}
        </Text>

        {/* What's included */}
        <View className="rounded-2xl bg-white p-4">
          <Text className="mb-2 text-sm font-bold text-navy-text">
            {t('exportRecords.includedTitle')}
          </Text>
          <IncludedRow icon={User} label={t('exportRecords.includedProfile')} />
          <IncludedRow icon={AlertTriangle} label={t('exportRecords.includedAllergies')} />
          <IncludedRow icon={Stethoscope} label={t('exportRecords.includedDiagnoses')} />
          <IncludedRow icon={Pill} label={t('exportRecords.includedMedications')} />
          <IncludedRow icon={FlaskConical} label={t('exportRecords.includedLabs')} />
          <IncludedRow
            icon={Syringe}
            label={t('exportRecords.includedImmunizations')}
            isLast
          />
        </View>

        {/* Format choices */}
        <View className="mt-5" style={{ gap: 12 }}>
          <FormatCard
            icon={FileText}
            title={t('exportRecords.pdfTitle')}
            body={t('exportRecords.pdfBody')}
            actionLabel={t('exportRecords.pdfAction')}
            loading={activeFormat === 'pdf'}
            disabled={activeFormat !== null}
            onPress={() => handleExport('pdf')}
          />
          <FormatCard
            icon={FileCode}
            title={t('exportRecords.fhirTitle')}
            body={t('exportRecords.fhirBody')}
            actionLabel={t('exportRecords.fhirAction')}
            loading={activeFormat === 'fhir'}
            disabled={activeFormat !== null}
            onPress={() => handleExport('fhir')}
          />
        </View>

        {/* Privacy note */}
        <View className="mt-5 flex-row items-start rounded-2xl bg-gold-50 p-4">
          <ShieldCheck size={16} color={colors.gold[600]} />
          <Text className="ml-3 flex-1 text-xs text-navy-secondary">
            {t('exportRecords.privacyNote')}
          </Text>
        </View>
      </ScrollView>
    </Screen>
  );
}

function IncludedRow({
  icon: Icon,
  label,
  isLast,
}: {
  icon: LucideIcon;
  label: string;
  isLast?: boolean;
}) {
  return (
    <View
      className="flex-row items-center py-2"
      style={
        !isLast ? { borderBottomWidth: 1, borderBottomColor: colors.cream[200] } : undefined
      }
    >
      <View className="h-8 w-8 items-center justify-center rounded-full bg-gold-50">
        <Icon size={15} color={colors.gold[600]} />
      </View>
      <Text className="ml-3 flex-1 text-sm text-navy-text">{label}</Text>
      <Check size={15} color={colors.semantic.success} />
    </View>
  );
}

function FormatCard({
  icon: Icon,
  title,
  body,
  actionLabel,
  loading,
  disabled,
  onPress,
}: {
  icon: LucideIcon;
  title: string;
  body: string;
  actionLabel: string;
  loading: boolean;
  disabled: boolean;
  onPress: () => void;
}) {
  const { t } = useTranslation();
  const isBlocked = disabled && !loading;

  return (
    <View className="rounded-2xl bg-white p-4">
      <View className="flex-row items-center">
        <View className="h-11 w-11 items-center justify-center rounded-full bg-gold-100">
          <Icon size={20} color={colors.gold[600]} />
        </View>
        <View className="ml-3 flex-1">
          <Text className="text-base font-bold text-navy-text">{title}</Text>
          <Text className="mt-0.5 text-xs text-navy-secondary">{body}</Text>
        </View>
      </View>

      <Pressable
        onPress={onPress}
        disabled={disabled}
        className="mt-4 h-12 flex-row items-center justify-center rounded-xl bg-gold-500"
        style={{ opacity: isBlocked ? 0.5 : 1 }}
      >
        {loading ? (
          <>
            <ActivityIndicator color={colors.white} size="small" />
            <Text className="ml-2 text-sm font-bold text-white">
              {t('exportRecords.generating')}
            </Text>
          </>
        ) : (
          <Text className="text-sm font-bold text-white">{actionLabel}</Text>
        )}
      </Pressable>
    </View>
  );
}
