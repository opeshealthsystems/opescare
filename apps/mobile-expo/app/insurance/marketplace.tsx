import { ActivityIndicator, Pressable, ScrollView, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import { ArrowLeft, Building2, ChevronRight, FileQuestion, ShieldCheck } from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import {
  useInsuranceMarketplace,
  type InsuranceMarketplaceProvider,
  type InsurancePlanSummary,
} from '../../lib/api/queries';
import { colors } from '../../theme/tokens';

/** Formats a decimal-string/number premium as a grouped XAF amount. Avoids
 * relying on Intl (not guaranteed in the Hermes runtime) for a simple
 * thousands-space grouping consistent with FCFA display conventions. */
function formatXaf(value: string | number | null | undefined, suffix: string): string {
  if (value === null || value === undefined) return '—';
  const num = typeof value === 'string' ? parseFloat(value) : value;
  if (Number.isNaN(num)) return '—';
  const grouped = Math.round(num)
    .toString()
    .replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
  return `${grouped} ${suffix}`;
}

/** Insurance Marketplace — active providers and their purchasable plans
 * (GET /mobile/insurance/marketplace). Tapping a plan opens its detail +
 * purchase flow at /insurance/[id]. */
export default function InsuranceMarketplaceScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const { data, isLoading, isError, refetch } = useInsuranceMarketplace();
  const providers = data?.data ?? [];
  const currencySuffix = t('insurance.currencySuffix');

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
        <Text className="ml-4 text-lg font-bold text-navy-text">{t('insurance.marketplace.title')}</Text>
      </View>

      <ScrollView className="flex-1 px-6" showsVerticalScrollIndicator={false}>
        <Text className="mb-4 mt-2 text-sm text-navy-secondary">
          {t('insurance.marketplace.subtitle')}
        </Text>

        {isLoading ? (
          <View className="mt-16 items-center">
            <ActivityIndicator color={colors.gold[500]} />
          </View>
        ) : isError ? (
          <View className="mt-16 items-center px-4">
            <FileQuestion size={40} color={colors.navy.muted} />
            <Text className="mt-3 text-center text-sm text-navy-secondary">
              {t('insurance.marketplace.loadError')}
            </Text>
            <Pressable onPress={() => refetch()} className="mt-4 rounded-full bg-gold-50 px-5 py-2">
              <Text className="text-sm font-semibold text-gold-600">{t('insurance.retry')}</Text>
            </Pressable>
          </View>
        ) : providers.length === 0 ? (
          <View className="mt-10 items-center px-2">
            <View className="h-16 w-16 items-center justify-center rounded-full bg-gold-50">
              <ShieldCheck size={28} color={colors.gold[500]} />
            </View>
            <Text className="mt-4 text-center text-sm text-navy-secondary">
              {t('insurance.marketplace.empty')}
            </Text>
          </View>
        ) : (
          providers.map((provider) => (
            <ProviderSection
              key={provider.id}
              provider={provider}
              currencySuffix={currencySuffix}
              onSelectPlan={(planId) => router.push(`/insurance/${planId}`)}
            />
          ))
        )}
        <View className="h-10" />
      </ScrollView>
    </Screen>
  );
}

function ProviderSection({
  provider,
  currencySuffix,
  onSelectPlan,
}: {
  provider: InsuranceMarketplaceProvider;
  currencySuffix: string;
  onSelectPlan: (planId: string) => void;
}) {
  return (
    <View className="mb-5">
      <View className="mb-3 flex-row items-center">
        <View className="h-9 w-9 items-center justify-center rounded-full bg-gold-100">
          <Building2 size={16} color={colors.gold[600]} />
        </View>
        <Text className="ml-3 text-base font-bold text-navy-text">{provider.name}</Text>
      </View>

      {provider.plans.map((plan) => (
        <PlanCard
          key={plan.id}
          plan={plan}
          currencySuffix={currencySuffix}
          onPress={() => onSelectPlan(plan.id)}
        />
      ))}
    </View>
  );
}

function PlanCard({
  plan,
  currencySuffix,
  onPress,
}: {
  plan: InsurancePlanSummary;
  currencySuffix: string;
  onPress: () => void;
}) {
  const { t } = useTranslation();
  return (
    <Pressable onPress={onPress} className="mb-3 rounded-2xl bg-white p-4">
      <View className="flex-row items-start justify-between">
        <View className="flex-1 pr-3">
          <Text className="text-base font-semibold text-navy-text">{plan.name}</Text>
          {plan.plan_type ? (
            <Text className="mt-1 text-xs text-navy-muted">
              {t(`insurance.planType.${plan.plan_type}`, { defaultValue: plan.plan_type })}
            </Text>
          ) : null}
        </View>
        <ChevronRight size={18} color={colors.navy.muted} />
      </View>

      <View className="mt-3 flex-row items-end justify-between">
        <View>
          <Text className="text-xs text-navy-muted">{t('insurance.marketplace.monthlyPremium')}</Text>
          <Text className="mt-1 text-base font-bold text-gold-600">
            {formatXaf(plan.monthly_premium, currencySuffix)}
            <Text className="text-xs font-normal text-navy-muted">
              {' '}
              {t('insurance.marketplace.fromPerMonth')}
            </Text>
          </Text>
        </View>
        <View className="flex-row flex-wrap justify-end" style={{ maxWidth: 160 }}>
          {plan.cashless_available ? (
            <Badge label={t('insurance.marketplace.cashless')} />
          ) : null}
          {plan.requires_preauthorization ? (
            <Badge label={t('insurance.marketplace.preauthRequired')} />
          ) : null}
        </View>
      </View>
    </Pressable>
  );
}

function Badge({ label }: { label: string }) {
  return (
    <View className="ml-2 mt-1 rounded-full bg-gold-50 px-2 py-1">
      <Text className="text-[10px] font-semibold text-gold-600">{label}</Text>
    </View>
  );
}
