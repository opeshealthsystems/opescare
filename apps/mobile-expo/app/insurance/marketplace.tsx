import { useMemo } from 'react';
import { Pressable, RefreshControl, ScrollView, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  BadgeCheck,
  Building2,
  ChevronRight,
  Percent,
  ShieldAlert,
  ShieldCheck,
  Store,
  Wallet,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import {
  useInsuranceMarketplace,
  useInsurancePolicies,
  type InsuranceMarketplaceProvider,
  type InsurancePlanSummary,
} from '../../lib/api/queries';
import { colors } from '../../theme/tokens';

/** Groups an integer with the separator the active locale expects (comma in
 * EN, non-breaking space in FR) — Intl is not guaranteed in the Hermes
 * runtime, so the grouping is done by hand. Returns null when the insurer has
 * not published a figure, so callers can say "on request" rather than render a
 * bare dash next to a currency code. */
function groupDigits(value: string | number | null | undefined, language: string): string | null {
  if (value === null || value === undefined || value === '') return null;
  const num = typeof value === 'string' ? parseFloat(value) : value;
  if (!Number.isFinite(num)) return null;
  const separator = language.startsWith('fr') ? ' ' : ',';
  return Math.round(num)
    .toString()
    .replace(/\B(?=(\d{3})+(?!\d))/g, separator);
}

/** Insurance Marketplace — accredited providers and their purchasable plans
 * (GET /mobile/insurance/marketplace). Every plan card exposes the same three
 * comparison slots in the same order so tiers can actually be compared, and
 * plans the patient already holds are flagged rather than left to fail with a
 * 409 at the end of the purchase flow. */
export default function InsuranceMarketplaceScreen() {
  const { t, i18n } = useTranslation();
  const router = useRouter();
  const marketplace = useInsuranceMarketplace();
  const policies = useInsurancePolicies();

  const providers = marketplace.data?.data ?? [];

  /** Plan ids the patient already holds a live-or-pending policy for. The
   * purchase endpoint rejects those with a 409, so the card must say so up
   * front. */
  const enrolledPlanIds = useMemo(() => {
    const ids = new Set<string>();
    for (const policy of policies.data?.data ?? []) {
      if (policy.plan?.id && (policy.status === 'active' || policy.status === 'pending')) {
        ids.add(policy.plan.id);
      }
    }
    return ids;
  }, [policies.data]);

  const formatAmount = (value: string | number | null | undefined): string | null => {
    const grouped = groupDigits(value, i18n.language);
    return grouped === null ? null : t('insurance.amountFormat', { amount: grouped });
  };

  return (
    <Screen className="px-0">
      <View className="flex-row items-center justify-between px-6 pt-2">
        <Pressable
          onPress={() => router.back()}
          hitSlop={8}
          accessibilityRole="button"
          accessibilityLabel={t('insurance.back')}
          className="h-11 w-11 items-center justify-center rounded-full border border-brand-300"
        >
          <ArrowLeft size={18} color={colors.brand[600]} />
        </Pressable>
        <Pressable
          onPress={() => router.push('/insurance')}
          hitSlop={8}
          accessibilityRole="button"
          accessibilityLabel={t('insurance.title')}
          className="h-11 w-11 items-center justify-center rounded-full border border-brand-300"
        >
          <ShieldCheck size={18} color={colors.brand[600]} />
        </Pressable>
      </View>

      <ScrollView
        className="flex-1 px-6"
        showsVerticalScrollIndicator={false}
        contentContainerStyle={{ paddingBottom: 40 }}
        refreshControl={
          <RefreshControl
            refreshing={marketplace.isRefetching}
            onRefresh={() => marketplace.refetch()}
            tintColor={colors.brand[500]}
          />
        }
      >
        <View className="mt-4">
          <Text className="text-2xl font-extrabold text-navy-text">
            {t('insurance.marketplace.title')}
          </Text>
          <Text className="mt-1 text-sm leading-5 text-navy-secondary">
            {t('insurance.marketplace.subtitle')}
          </Text>
        </View>

        {marketplace.isLoading ? (
          <View className="mt-6">
            <PlanSkeleton />
            <PlanSkeleton />
            <PlanSkeleton />
          </View>
        ) : marketplace.isError ? (
          <View className="mt-8 items-center rounded-3xl bg-white p-6">
            <View
              className="h-14 w-14 items-center justify-center rounded-full"
              style={{ backgroundColor: colors.semantic.dangerSurface }}
            >
              <ShieldAlert size={26} color={colors.semantic.danger} />
            </View>
            <Text className="mt-4 text-center text-base font-bold text-navy-text">
              {t('insurance.marketplace.loadError')}
            </Text>
            <Text className="mt-2 text-center text-sm text-navy-secondary">
              {t('insurance.loadErrorBody')}
            </Text>
            <Pressable
              onPress={() => marketplace.refetch()}
              className="mt-5 rounded-full bg-brand-50 px-6 py-3"
              accessibilityRole="button"
            >
              <Text className="text-sm font-semibold text-brand-600">{t('insurance.retry')}</Text>
            </Pressable>
          </View>
        ) : providers.length === 0 ? (
          <View className="mt-8 items-center rounded-3xl bg-white p-6">
            <View className="h-16 w-16 items-center justify-center rounded-full bg-brand-50">
              <Store size={30} color={colors.brand[500]} />
            </View>
            <Text className="mt-4 text-center text-base font-bold text-navy-text">
              {t('insurance.marketplace.empty')}
            </Text>
            <Text className="mt-2 text-center text-sm leading-5 text-navy-secondary">
              {t('insurance.marketplace.emptyBody')}
            </Text>
          </View>
        ) : (
          <>
            <View className="mt-4 flex-row items-center rounded-2xl bg-brand-50 px-4 py-3">
              <Wallet size={15} color={colors.brand[600]} />
              <Text className="ml-2.5 flex-1 text-xs text-navy-secondary">
                {t('insurance.currencyNote')}
              </Text>
            </View>

            <View className="mt-6">
              {providers.map((provider) => (
                <ProviderSection
                  key={provider.id}
                  provider={provider}
                  enrolledPlanIds={enrolledPlanIds}
                  formatAmount={formatAmount}
                  onSelectPlan={(planId) => router.push(`/insurance/${planId}`)}
                  onOpenCoverage={() => router.push('/insurance')}
                />
              ))}
            </View>
          </>
        )}
      </ScrollView>
    </Screen>
  );
}

function ProviderSection({
  provider,
  enrolledPlanIds,
  formatAmount,
  onSelectPlan,
  onOpenCoverage,
}: {
  provider: InsuranceMarketplaceProvider;
  enrolledPlanIds: Set<string>;
  formatAmount: (value: string | number | null | undefined) => string | null;
  onSelectPlan: (planId: string) => void;
  onOpenCoverage: () => void;
}) {
  const { t } = useTranslation();

  return (
    <View className="mb-7">
      <View className="mb-4 flex-row items-center">
        <View className="h-11 w-11 items-center justify-center rounded-full bg-brand-100">
          <Building2 size={18} color={colors.brand[600]} />
        </View>
        <View className="ml-3 flex-1">
          <Text className="text-base font-extrabold text-navy-text" numberOfLines={2}>
            {provider.name}
          </Text>
          <Text className="mt-0.5 text-xs text-navy-muted">
            {provider.plans.length > 1
              ? t('insurance.marketplace.plansAvailable', { n: provider.plans.length })
              : t('insurance.marketplace.singlePlan')}
          </Text>
        </View>
      </View>

      {provider.plans.map((plan) => (
        <PlanCard
          key={plan.id}
          plan={plan}
          enrolled={enrolledPlanIds.has(plan.id)}
          formatAmount={formatAmount}
          onPress={() => (enrolledPlanIds.has(plan.id) ? onOpenCoverage() : onSelectPlan(plan.id))}
        />
      ))}
    </View>
  );
}

function PlanCard({
  plan,
  enrolled,
  formatAmount,
  onPress,
}: {
  plan: InsurancePlanSummary;
  enrolled: boolean;
  formatAmount: (value: string | number | null | undefined) => string | null;
  onPress: () => void;
}) {
  const { t } = useTranslation();
  const monthly = formatAmount(plan.monthly_premium);
  const annual = formatAmount(plan.annual_premium);
  const deductible = formatAmount(plan.deductible);
  const copay =
    plan.copay_percentage === null || plan.copay_percentage === undefined
      ? null
      : `${parseFloat(String(plan.copay_percentage))}%`;

  return (
    <Pressable
      onPress={onPress}
      accessibilityRole="button"
      className="mb-3 rounded-3xl border border-cream-300 bg-white p-5"
    >
      <View className="flex-row items-start justify-between">
        <View className="flex-1 pr-3">
          <Text className="text-base font-extrabold text-navy-text">{plan.name}</Text>
          {plan.plan_type ? (
            <View className="mt-2 self-start rounded-full bg-cream-200 px-3 py-1">
              <Text className="text-[11px] font-semibold text-navy-secondary">
                {t(`insurance.planType.${plan.plan_type}`, { defaultValue: plan.plan_type })}
              </Text>
            </View>
          ) : null}
        </View>
        {enrolled ? (
          <View
            className="flex-row items-center rounded-full px-3 py-1"
            style={{ backgroundColor: colors.semantic.successSurface }}
          >
            <BadgeCheck size={12} color={colors.semantic.success} />
            <Text
              className="ml-1.5 text-[11px] font-bold"
              style={{ color: colors.semantic.success }}
            >
              {t('insurance.marketplace.enrolled')}
            </Text>
          </View>
        ) : (
          <ChevronRight size={18} color={colors.navy.muted} />
        )}
      </View>

      {plan.description ? (
        <Text className="mt-3 text-xs leading-4 text-navy-secondary" numberOfLines={3}>
          {plan.description}
        </Text>
      ) : null}

      <View className="mt-4 flex-row items-end">
        <View className="flex-1">
          <Text className="text-[10px] font-bold uppercase tracking-widest text-navy-muted">
            {t('insurance.marketplace.monthlyPremium')}
          </Text>
          {monthly ? (
            <View className="mt-1 flex-row items-baseline">
              <Text className="text-xl font-extrabold text-brand-600">{monthly}</Text>
              <Text className="ml-1 text-xs text-navy-muted">
                {t('insurance.marketplace.perMonth')}
              </Text>
            </View>
          ) : (
            <Text className="mt-1 text-sm font-bold text-navy-secondary">
              {t('insurance.marketplace.premiumOnRequest')}
            </Text>
          )}
        </View>
        {annual ? (
          <View className="items-end">
            <Text className="text-[10px] text-navy-muted">
              {t('insurance.marketplace.annualPremium')}
            </Text>
            <Text className="mt-1 text-sm font-bold text-navy-text">{annual}</Text>
          </View>
        ) : null}
      </View>

      <View className="my-4 h-px bg-cream-300" />

      {/* Fixed comparison slots — same three facts, same order, on every card,
       * so tiers line up visually when a provider publishes more than one. */}
      <View className="flex-row">
        <ComparisonSlot
          label={t('insurance.marketplace.deductible')}
          value={deductible ?? t('insurance.notListed')}
          muted={deductible === null}
        />
        <ComparisonSlot
          label={t('insurance.marketplace.copay')}
          value={copay ?? t('insurance.notListed')}
          muted={copay === null}
        />
        <ComparisonSlot
          label={t('insurance.marketplace.cashless')}
          value={
            plan.cashless_available
              ? t('insurance.marketplace.cashlessYes')
              : t('insurance.marketplace.cashlessNo')
          }
          positive={plan.cashless_available}
        />
      </View>

      {plan.requires_preauthorization ? (
        <View
          className="mt-4 flex-row items-center rounded-2xl px-3 py-2"
          style={{ backgroundColor: colors.semantic.warningSurface }}
        >
          <Percent size={13} color={colors.semantic.warning} />
          <Text
            className="ml-2 flex-1 text-[11px] font-semibold"
            style={{ color: colors.semantic.warning }}
          >
            {t('insurance.marketplace.preauthRequired')}
          </Text>
        </View>
      ) : null}

      {enrolled ? (
        <Text className="mt-4 text-[11px] text-navy-muted">
          {t('insurance.marketplace.enrolledHint')}
        </Text>
      ) : null}
    </Pressable>
  );
}

function ComparisonSlot({
  label,
  value,
  muted,
  positive,
}: {
  label: string;
  value: string;
  muted?: boolean;
  positive?: boolean;
}) {
  const color = muted
    ? colors.navy.muted
    : positive === undefined
      ? colors.navy.text
      : positive
        ? colors.semantic.success
        : colors.navy.secondary;

  return (
    <View className="flex-1 pr-2">
      <Text className="text-[10px] text-navy-muted" numberOfLines={1}>
        {label}
      </Text>
      <Text className="mt-1 text-xs font-bold" style={{ color }} numberOfLines={1}>
        {value}
      </Text>
    </View>
  );
}

/** Static placeholder card — keeps the marketplace's rhythm while the request
 * is in flight instead of collapsing to a bare spinner. */
function PlanSkeleton() {
  return (
    <View className="mb-3 rounded-3xl border border-cream-300 bg-white p-5">
      <View className="h-4 w-2/3 rounded-full bg-cream-200" />
      <View className="mt-3 h-3 w-1/3 rounded-full bg-cream-200" />
      <View className="mt-5 h-6 w-1/2 rounded-full bg-cream-200" />
      <View className="my-4 h-px bg-cream-300" />
      <View className="flex-row">
        <View className="mr-2 h-3 flex-1 rounded-full bg-cream-200" />
        <View className="mr-2 h-3 flex-1 rounded-full bg-cream-200" />
        <View className="h-3 flex-1 rounded-full bg-cream-200" />
      </View>
    </View>
  );
}
