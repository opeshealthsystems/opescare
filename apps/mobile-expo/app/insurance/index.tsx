import { ActivityIndicator, Pressable, ScrollView, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import { ArrowLeft, ChevronRight, FileQuestion, ShieldCheck, Store } from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { useInsurancePolicies, type InsurancePolicy, type InsurancePolicyStatus } from '../../lib/api/queries';
import { colors } from '../../theme/tokens';

const STATUS_STYLES: Record<InsurancePolicyStatus, { text: string; surface: string }> = {
  active: { text: colors.semantic.success, surface: colors.semantic.successSurface },
  pending: { text: colors.semantic.warning, surface: colors.semantic.warningSurface },
  inactive: { text: colors.navy.secondary, surface: colors.cream[200] },
  expired: { text: colors.semantic.danger, surface: colors.semantic.dangerSurface },
  cancelled: { text: colors.semantic.danger, surface: colors.semantic.dangerSurface },
};

/** "My Insurance" — the patient's existing policies (GET /mobile/insurance),
 * plus an entry point into the marketplace to browse/enroll in new plans. */
export default function InsurancePoliciesScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const { data, isLoading, isError, refetch } = useInsurancePolicies();
  const policies = data?.data ?? [];

  return (
    <Screen className="px-0">
      <Header
        onBack={() => router.back()}
        onBrowse={() => router.push('/insurance/marketplace')}
        title={t('insurance.title')}
      />

      <ScrollView className="flex-1 px-6" showsVerticalScrollIndicator={false}>
        {isLoading ? (
          <View className="mt-16 items-center">
            <ActivityIndicator color={colors.gold[500]} />
          </View>
        ) : isError ? (
          <View className="mt-16 items-center px-4">
            <FileQuestion size={40} color={colors.navy.muted} />
            <Text className="mt-3 text-center text-sm text-navy-secondary">
              {t('insurance.loadError')}
            </Text>
            <Pressable
              onPress={() => refetch()}
              className="mt-4 rounded-full bg-gold-50 px-5 py-2"
            >
              <Text className="text-sm font-semibold text-gold-600">{t('insurance.retry')}</Text>
            </Pressable>
          </View>
        ) : policies.length === 0 ? (
          <View className="mt-10 items-center px-2">
            <View className="h-16 w-16 items-center justify-center rounded-full bg-gold-50">
              <ShieldCheck size={28} color={colors.gold[500]} />
            </View>
            <Text className="mt-4 text-center text-base font-bold text-navy-text">
              {t('insurance.emptyTitle')}
            </Text>
            <Text className="mt-2 text-center text-sm text-navy-secondary">
              {t('insurance.emptyBody')}
            </Text>
            <View className="mt-6 w-full">
              <Button
                label={t('insurance.emptyCta')}
                onPress={() => router.push('/insurance/marketplace')}
              />
            </View>
          </View>
        ) : (
          <>
            <Text className="mb-3 mt-5 text-sm text-navy-secondary">{t('insurance.subtitle')}</Text>
            {policies.map((policy) => (
              <PolicyCard key={policy.id} policy={policy} />
            ))}
            <Pressable
              onPress={() => router.push('/insurance/marketplace')}
              className="mb-4 flex-row items-center justify-between rounded-2xl border border-gold-300 bg-gold-50 p-4"
            >
              <View className="flex-1 flex-row items-center">
                <Store size={18} color={colors.gold[600]} />
                <Text className="ml-3 flex-1 text-sm font-semibold text-gold-600">
                  {t('insurance.browseMarketplace')}
                </Text>
              </View>
              <ChevronRight size={18} color={colors.gold[600]} />
            </Pressable>
          </>
        )}
        <View className="h-10" />
      </ScrollView>
    </Screen>
  );
}

function Header({
  title,
  onBack,
  onBrowse,
}: {
  title: string;
  onBack: () => void;
  onBrowse: () => void;
}) {
  return (
    <View className="flex-row items-center justify-between px-6 pt-2">
      <Pressable
        onPress={onBack}
        hitSlop={8}
        className="h-11 w-11 items-center justify-center rounded-full border border-gold-300"
      >
        <ArrowLeft size={18} color={colors.gold[600]} />
      </Pressable>
      <Text className="text-lg font-bold text-navy-text">{title}</Text>
      <Pressable
        onPress={onBrowse}
        hitSlop={8}
        className="h-11 w-11 items-center justify-center rounded-full border border-gold-300"
      >
        <Store size={18} color={colors.gold[600]} />
      </Pressable>
    </View>
  );
}

function PolicyCard({ policy }: { policy: InsurancePolicy }) {
  const { t } = useTranslation();
  const style = STATUS_STYLES[policy.status] ?? STATUS_STYLES.inactive;

  return (
    <View className="mb-3 rounded-2xl bg-white p-4">
      <View className="flex-row items-start justify-between">
        <View className="flex-1 pr-3">
          <Text className="text-base font-bold text-navy-text">{policy.plan?.name ?? '—'}</Text>
          <Text className="mt-1 text-sm text-navy-secondary">
            {policy.plan?.provider_name ?? '—'}
          </Text>
          <Text className="mt-1 text-xs text-navy-muted">
            {t(`insurance.relationshipToPrimary.${policy.relationship_to_primary}`, {
              defaultValue: policy.relationship_to_primary,
            })}
            {policy.member_id ? ` • ${t('insurance.memberId')}: ${policy.member_id}` : ''}
          </Text>
        </View>
        <View className="rounded-full px-3 py-1" style={{ backgroundColor: style.surface }}>
          <Text className="text-xs font-semibold" style={{ color: style.text }}>
            {t(`insurance.status.${policy.status}`)}
          </Text>
        </View>
      </View>

      <View className="mt-4 h-px bg-cream-300" />

      <View className="mt-4 flex-row justify-between">
        <View>
          <Text className="text-xs text-navy-muted">{t('insurance.policyNumber')}</Text>
          <Text className="mt-1 text-sm font-semibold text-navy-text">
            {policy.policy_number}
          </Text>
        </View>
        <View>
          <Text className="text-xs text-navy-muted">{t('insurance.effectiveDate')}</Text>
          <Text className="mt-1 text-sm font-semibold text-navy-text">
            {policy.effective_date ?? '—'}
          </Text>
        </View>
        <View>
          <Text className="text-xs text-navy-muted">{t('insurance.expiryDate')}</Text>
          <Text className="mt-1 text-sm font-semibold text-navy-text">
            {policy.expiry_date ?? '—'}
          </Text>
        </View>
      </View>
    </View>
  );
}
