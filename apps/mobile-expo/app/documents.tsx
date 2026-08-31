import { useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Pressable,
  RefreshControl,
  Text,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import * as Linking from 'expo-linking';
import {
  ArrowLeft,
  ExternalLink,
  FileText,
  FolderOpen,
  Hash,
  ShieldCheck,
} from 'lucide-react-native';
import { Screen } from '../components/ui/Screen';
import { useDocumentViewer, useDocuments } from '../lib/api/queries';
import type { OfficialDocument } from '../lib/api/types';
import { colors } from '../theme/tokens';

/** Documents — the patient's official, verifiable documents (discharge
 * summaries, lab reports, referral letters, vaccination cards, etc.).
 * Reached by push from other tabs; no direct reference image, so this
 * follows the app's established list-of-cards pattern. */
export default function DocumentsScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const documents = useDocuments();
  const viewer = useDocumentViewer();
  const [openingId, setOpeningId] = useState<string | null>(null);

  const rows = documents.data?.data ?? [];

  const handleView = (doc: OfficialDocument) => {
    setOpeningId(doc.id);
    viewer.mutate(doc.id, {
      onSuccess: async (detail) => {
        setOpeningId(null);
        if (!detail.verify_url) {
          Alert.alert(t('documents.viewError'));
          return;
        }
        try {
          await Linking.openURL(detail.verify_url);
        } catch {
          Alert.alert(t('documents.viewError'));
        }
      },
      onError: () => {
        setOpeningId(null);
        Alert.alert(t('documents.viewError'));
      },
    });
  };

  return (
    <Screen className="px-0">
      <View className="px-6">
        <View className="mt-2 flex-row items-center">
          <Pressable
            onPress={() => router.back()}
            hitSlop={8}
            className="h-11 w-11 items-center justify-center rounded-full border border-gold-300"
          >
            <ArrowLeft size={18} color={colors.gold[600]} />
          </Pressable>
          <Text className="ml-3 text-2xl font-extrabold text-navy-text">
            {t('documents.title')}
          </Text>
        </View>
        <Text className="mb-4 mt-2 text-sm text-navy-secondary">{t('documents.subtitle')}</Text>
      </View>

      {documents.isLoading ? (
        <View className="flex-1 items-center justify-center">
          <ActivityIndicator color={colors.gold[500]} size="large" />
        </View>
      ) : documents.isError ? (
        <View className="flex-1 items-center justify-center px-10">
          <Text className="text-center text-sm text-danger">{t('documents.loadError')}</Text>
          <Pressable
            onPress={() => documents.refetch()}
            className="mt-4 rounded-full border border-gold-500 px-5 py-2"
          >
            <Text className="text-sm font-semibold text-gold-600">{t('documents.retry')}</Text>
          </Pressable>
        </View>
      ) : (
        <FlatList
          data={rows}
          keyExtractor={(item) => item.id}
          contentContainerStyle={{ paddingHorizontal: 24, paddingBottom: 32, flexGrow: 1 }}
          refreshControl={
            <RefreshControl
              refreshing={documents.isRefetching}
              onRefresh={() => documents.refetch()}
              tintColor={colors.gold[500]}
            />
          }
          ItemSeparatorComponent={() => <View className="h-3" />}
          ListEmptyComponent={
            <View className="flex-1 items-center justify-center py-20">
              <View className="h-16 w-16 items-center justify-center rounded-full bg-gold-100">
                <FolderOpen size={26} color={colors.gold[600]} />
              </View>
              <Text className="mt-4 text-base font-bold text-navy-text">
                {t('documents.empty')}
              </Text>
              <Text className="mt-1 max-w-[260px] text-center text-sm text-navy-secondary">
                {t('documents.emptyBody')}
              </Text>
            </View>
          }
          renderItem={({ item }) => (
            <DocumentCard
              doc={item}
              onView={() => handleView(item)}
              loading={openingId === item.id}
            />
          )}
        />
      )}
    </Screen>
  );
}

function DocumentCard({
  doc,
  onView,
  loading,
}: {
  doc: OfficialDocument;
  onView: () => void;
  loading: boolean;
}) {
  const { t } = useTranslation();
  const typeLabel = t(`documents.type.${doc.document_type}`, {
    defaultValue: t('documents.type.other'),
  });
  const issuedLabel = doc.issued_at
    ? t('documents.issuedOn', { date: new Date(doc.issued_at).toLocaleDateString() })
    : null;

  return (
    <View className="rounded-2xl bg-white p-4">
      <View className="flex-row items-start">
        <View className="h-11 w-11 items-center justify-center rounded-full bg-gold-100">
          <FileText size={20} color={colors.gold[600]} />
        </View>
        <View className="ml-3 flex-1">
          <Text className="text-base font-bold text-navy-text" numberOfLines={2}>
            {doc.title}
          </Text>
          <Text className="mt-0.5 text-xs font-semibold uppercase tracking-wide text-gold-600">
            {typeLabel}
          </Text>
          {doc.facility_name ? (
            <Text className="mt-1 text-sm text-navy-secondary" numberOfLines={1}>
              {doc.facility_name}
            </Text>
          ) : null}
        </View>
      </View>

      <View className="mt-3 flex-row flex-wrap items-center">
        {issuedLabel ? (
          <Text className="mr-4 text-xs text-navy-muted">{issuedLabel}</Text>
        ) : null}
        {doc.document_number ? (
          <View className="flex-row items-center">
            <Hash size={11} color={colors.navy.muted} />
            <Text className="ml-0.5 text-xs text-navy-muted">
              {t('documents.referenceNumber', { number: doc.document_number })}
            </Text>
          </View>
        ) : null}
      </View>

      <View className="mt-3 h-px bg-cream-300" />

      <View className="mt-3 flex-row items-center justify-between">
        <View className="flex-row items-center">
          <ShieldCheck size={13} color={colors.gold[600]} />
          <Text className="ml-1.5 text-xs text-navy-secondary" numberOfLines={1}>
            {doc.verification_code}
          </Text>
        </View>
        <Pressable
          onPress={onView}
          disabled={loading}
          className="flex-row items-center rounded-full bg-gold-500 px-4 py-2"
          style={{ opacity: loading ? 0.7 : 1 }}
        >
          {loading ? (
            <ActivityIndicator size="small" color="white" />
          ) : (
            <>
              <Text className="mr-1.5 text-xs font-bold text-white">{t('documents.view')}</Text>
              <ExternalLink size={13} color="white" />
            </>
          )}
        </Pressable>
      </View>
    </View>
  );
}
