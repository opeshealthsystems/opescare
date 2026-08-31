import { useMemo, useState } from 'react';
import { ActivityIndicator, Linking, Pressable, ScrollView, Text, View } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import { isAxiosError } from 'axios';
import { LinearGradient } from 'expo-linear-gradient';
import {
  ArrowLeft,
  BadgeCheck,
  Check,
  CheckCircle2,
  Circle,
  Hourglass,
  Mail,
  Phone,
  ShieldAlert,
  ShieldCheck,
  Square,
  SquareCheckBig,
  Store,
  TriangleAlert,
  Wallet,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import {
  useInsurancePlanDetail,
  useInsurancePolicies,
  usePurchaseInsurancePlan,
  type InsurancePlanDetail,
  type PurchaseInsurancePlanResponse,
} from '../../lib/api/queries';
import { colors } from '../../theme/tokens';

type Stage = 'detail' | 'review' | 'success';

/** The only rails this product settles premiums on. Both post
 * `payment_method: 'mobile_money'` — the wallet choice travels in
 * `payment_reference` as a stable, locale-independent token so the insurer's
 * back office can read it off the policy note. */
const PAYMENT_OPTIONS = [
  { id: 'mtn', reference: 'MTN MoMo', labelKey: 'insurance.purchase.mtn' },
  { id: 'orange', reference: 'Orange Money', labelKey: 'insurance.purchase.orange' },
] as const;

type PaymentOptionId = (typeof PAYMENT_OPTIONS)[number]['id'];

const PREMIUM_GRADIENT = [colors.brand[600], colors.brand[500], colors.brand[300]] as const;

/** See marketplace.tsx — same locale-aware grouping, duplicated deliberately so
 * each insurance screen stays self-contained (no shared file is owned here). */
function groupDigits(value: string | number | null | undefined, language: string): string | null {
  if (value === null || value === undefined || value === '') return null;
  const num = typeof value === 'string' ? parseFloat(value) : value;
  if (!Number.isFinite(num)) return null;
  const separator = language.startsWith('fr') ? ' ' : ',';
  return Math.round(num)
    .toString()
    .replace(/\B(?=(\d{3})+(?!\d))/g, separator);
}

/** `covered_services` is a raw text column on `insurance_plans` (documented as a
 * JSON list, but not enforced) — parse defensively. */
function parseCoveredServices(raw: string | null): string[] {
  if (!raw) return [];
  try {
    const parsed = JSON.parse(raw);
    if (Array.isArray(parsed)) return parsed.map((item) => String(item)).filter(Boolean);
  } catch {
    // Not JSON — fall through to comma-splitting.
  }
  return raw
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean);
}

function formatDate(value: string | null | undefined, language: string): string {
  if (!value) return '—';
  const date = new Date(`${value}T00:00:00`);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString(language.startsWith('fr') ? 'fr-FR' : 'en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

/**
 * Insurance plan detail + enrollment (GET .../plans/{id}, POST
 * .../plans/{id}/purchase).
 *
 * Enrollment is a money-adjacent commitment, so it is deliberately three taps
 * apart: "Enroll" only opens a review screen, the review screen's Confirm stays
 * disabled until the patient ticks an acknowledgement naming the plan and the
 * premium, and only that Confirm fires the POST. A 409 (the patient already
 * holds a pending/active policy for this plan) is surfaced as a calm
 * "you're already covered" outcome, not an error.
 */
export default function InsurancePlanDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const { t, i18n } = useTranslation();
  const router = useRouter();

  const { data, isLoading, isError, refetch } = useInsurancePlanDetail(id);
  const policies = useInsurancePolicies();
  const purchase = usePurchaseInsurancePlan(id);

  const [stage, setStage] = useState<Stage>('detail');
  const [paymentOption, setPaymentOption] = useState<PaymentOptionId>('mtn');
  const [acknowledged, setAcknowledged] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [duplicateMessage, setDuplicateMessage] = useState<string | null>(null);
  /** The enrolment outcome, snapshotted with the plan/insurer names so the
   * success screen never depends on the plan query still being healthy. */
  const [receipt, setReceipt] = useState<
    (PurchaseInsurancePlanResponse & { planName: string; providerName: string | null }) | null
  >(null);

  const plan = data?.data;

  const formatAmount = (value: string | number | null | undefined): string | null => {
    const grouped = groupDigits(value, i18n.language);
    return grouped === null ? null : t('insurance.amountFormat', { amount: grouped });
  };

  /** A pending or active policy on this plan means the purchase endpoint will
   * answer 409 — so never offer the enroll CTA in that state. */
  const alreadyEnrolled = useMemo(
    () =>
      (policies.data?.data ?? []).some(
        (policy) =>
          policy.plan?.id === id && (policy.status === 'active' || policy.status === 'pending'),
      ),
    [policies.data, id],
  );

  const monthlyPremium = formatAmount(plan?.monthly_premium);

  const openReview = () => {
    setErrorMessage(null);
    setDuplicateMessage(null);
    setAcknowledged(false);
    setStage('review');
  };

  const handleBack = () => {
    if (stage === 'review') {
      setErrorMessage(null);
      setDuplicateMessage(null);
      setStage('detail');
      return;
    }
    router.back();
  };

  const handleConfirm = async () => {
    if (!plan) return;
    setErrorMessage(null);
    setDuplicateMessage(null);
    const option = PAYMENT_OPTIONS.find((entry) => entry.id === paymentOption) ?? PAYMENT_OPTIONS[0];
    try {
      const result = await purchase.mutateAsync({
        payment_method: 'mobile_money',
        payment_reference: option.reference,
      });
      setReceipt({
        ...result,
        planName: plan.name,
        providerName: plan.provider?.name ?? null,
      });
      setStage('success');
    } catch (err) {
      if (isAxiosError(err) && err.response?.status === 409) {
        // The API's message is already translated server-side.
        const message = (err.response.data as { message?: string } | undefined)?.message;
        setDuplicateMessage(message ?? t('insurance.purchase.duplicateBody'));
        policies.refetch();
        return;
      }
      const apiMessage = isAxiosError(err)
        ? (err.response?.data as { message?: string } | undefined)?.message
        : undefined;
      setErrorMessage(apiMessage ?? t('insurance.purchase.errorGeneric'));
    }
  };

  return (
    <Screen className="px-0">
      {stage !== 'success' ? (
        <View className="flex-row items-center px-6 pt-2">
          <Pressable
            onPress={handleBack}
            hitSlop={8}
            accessibilityRole="button"
            accessibilityLabel={t('insurance.back')}
            className="h-11 w-11 items-center justify-center rounded-full border border-brand-300"
          >
            <ArrowLeft size={18} color={colors.brand[600]} />
          </Pressable>
          <Text className="ml-4 flex-1 text-lg font-bold text-navy-text" numberOfLines={1}>
            {stage === 'review' ? t('insurance.purchase.reviewTitle') : t('insurance.plan.title')}
          </Text>
        </View>
      ) : null}

      {stage === 'success' && receipt ? (
        <SuccessView
          receipt={receipt}
          language={i18n.language}
          onViewCoverage={() => router.replace('/insurance')}
          onBackToMarketplace={() => router.replace('/insurance/marketplace')}
        />
      ) : isLoading ? (
        <View className="mt-20 items-center">
          <ActivityIndicator color={colors.brand[500]} size="large" />
        </View>
      ) : isError || !plan ? (
        <View className="mt-8 px-6">
          <View className="items-center rounded-3xl bg-white p-6">
            <View
              className="h-14 w-14 items-center justify-center rounded-full"
              style={{ backgroundColor: colors.semantic.dangerSurface }}
            >
              <ShieldAlert size={26} color={colors.semantic.danger} />
            </View>
            <Text className="mt-4 text-center text-base font-bold text-navy-text">
              {t('insurance.plan.loadError')}
            </Text>
            <Text className="mt-2 text-center text-sm text-navy-secondary">
              {t('insurance.loadErrorBody')}
            </Text>
            <Pressable
              onPress={() => refetch()}
              className="mt-5 rounded-full bg-brand-50 px-6 py-3"
              accessibilityRole="button"
            >
              <Text className="text-sm font-semibold text-brand-600">{t('insurance.retry')}</Text>
            </Pressable>
          </View>
        </View>
      ) : stage === 'review' ? (
        <ReviewView
          plan={plan}
          monthlyPremium={monthlyPremium}
          formatAmount={formatAmount}
          paymentOption={paymentOption}
          onSelectPaymentOption={setPaymentOption}
          acknowledged={acknowledged}
          onToggleAcknowledged={() => setAcknowledged((value) => !value)}
          errorMessage={errorMessage}
          duplicateMessage={duplicateMessage}
          submitting={purchase.isPending}
          onConfirm={handleConfirm}
          onCancel={() => {
            setErrorMessage(null);
            setDuplicateMessage(null);
            setStage('detail');
          }}
          onViewCoverage={() => router.replace('/insurance')}
        />
      ) : (
        <DetailView
          plan={plan}
          monthlyPremium={monthlyPremium}
          formatAmount={formatAmount}
          alreadyEnrolled={alreadyEnrolled}
          onEnroll={openReview}
          onViewCoverage={() => router.push('/insurance')}
        />
      )}
    </Screen>
  );
}

/* -------------------------------------------------------------------------- */
/* Detail                                                                      */
/* -------------------------------------------------------------------------- */

function DetailView({
  plan,
  monthlyPremium,
  formatAmount,
  alreadyEnrolled,
  onEnroll,
  onViewCoverage,
}: {
  plan: InsurancePlanDetail;
  monthlyPremium: string | null;
  formatAmount: (value: string | number | null | undefined) => string | null;
  alreadyEnrolled: boolean;
  onEnroll: () => void;
  onViewCoverage: () => void;
}) {
  const { t } = useTranslation();
  const coveredServices = parseCoveredServices(plan.covered_services);
  const annualPremium = formatAmount(plan.annual_premium);
  const deductible = formatAmount(plan.deductible);
  const copay =
    plan.copay_percentage === null || plan.copay_percentage === undefined
      ? null
      : `${parseFloat(String(plan.copay_percentage))}%`;

  const phone = plan.provider?.contact_phone ?? null;
  const email = plan.provider?.contact_email ?? null;

  return (
    <ScrollView
      className="flex-1 px-6"
      showsVerticalScrollIndicator={false}
      contentContainerStyle={{ paddingBottom: 40 }}
    >
      <View className="mt-4">
        {plan.provider?.name ? (
          <Text className="text-[10px] font-bold uppercase tracking-widest text-brand-600">
            {plan.provider.name}
          </Text>
        ) : null}
        <Text className="mt-2 text-2xl font-extrabold text-navy-text">{plan.name}</Text>
        {plan.plan_type ? (
          <View className="mt-3 self-start rounded-full bg-cream-200 px-3 py-1">
            <Text className="text-[11px] font-semibold text-navy-secondary">
              {t(`insurance.planType.${plan.plan_type}`, { defaultValue: plan.plan_type })}
            </Text>
          </View>
        ) : null}
        {plan.description ? (
          <Text className="mt-3 text-sm leading-5 text-navy-secondary">{plan.description}</Text>
        ) : null}
      </View>

      {/* Premium hero. LinearGradient ignores className (no cssInterop) —
       * inline style only. */}
      <LinearGradient
        colors={PREMIUM_GRADIENT}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={{
          borderRadius: 24,
          padding: 20,
          marginTop: 20,
          overflow: 'hidden',
          shadowColor: colors.brand[700],
          shadowOpacity: 0.22,
          shadowRadius: 16,
          shadowOffset: { width: 0, height: 8 },
          elevation: 4,
        }}
      >
        <View className="flex-row items-center">
          <Wallet size={14} color={colors.white} />
          <Text className="ml-2 text-[10px] font-bold uppercase tracking-widest text-white/80">
            {t('insurance.plan.premiumHeading')}
          </Text>
        </View>

        {monthlyPremium ? (
          <View className="mt-2 flex-row items-baseline">
            <Text className="text-3xl font-extrabold text-white">{monthlyPremium}</Text>
            <Text className="ml-2 text-sm text-white/80">{t('insurance.plan.perMonth')}</Text>
          </View>
        ) : (
          <>
            <Text className="mt-2 text-xl font-extrabold text-white">
              {t('insurance.plan.premiumOnRequest')}
            </Text>
            <Text className="mt-2 text-xs leading-4 text-white/85">
              {t('insurance.plan.premiumOnRequestBody')}
            </Text>
          </>
        )}

        <View className="mt-5 h-px bg-white/25" />

        <View className="mt-4 flex-row justify-between">
          <HeroStat label={t('insurance.plan.annualPremium')} value={annualPremium} />
          <HeroStat label={t('insurance.plan.deductible')} value={deductible} />
          <HeroStat label={t('insurance.plan.copay')} value={copay} />
        </View>

        <Text className="mt-4 text-[10px] text-white/70">{t('insurance.currencyNote')}</Text>
      </LinearGradient>

      <Text className="mb-3 mt-7 text-base font-extrabold text-navy-text">
        {t('insurance.plan.coverageHeading')}
      </Text>
      <View className="rounded-3xl border border-cream-300 bg-white p-5">
        <FactRow
          label={t('insurance.plan.cashlessAvailable')}
          value={plan.cashless_available ? t('insurance.plan.yes') : t('insurance.plan.no')}
          positive={plan.cashless_available}
        />
        <View className="my-3.5 h-px bg-cream-300" />
        <FactRow
          label={t('insurance.plan.preauthRequired')}
          value={
            plan.requires_preauthorization
              ? t('insurance.plan.preauthRequiredValue')
              : t('insurance.plan.preauthNotRequired')
          }
          positive={!plan.requires_preauthorization}
        />
      </View>

      <Text className="mb-3 mt-7 text-base font-extrabold text-navy-text">
        {t('insurance.plan.coveredServices')}
      </Text>
      <View className="rounded-3xl border border-cream-300 bg-white p-5">
        {coveredServices.length > 0 ? (
          coveredServices.map((service, index) => (
            <View
              key={`${service}-${index}`}
              className={`flex-row items-center ${index > 0 ? 'mt-3.5' : ''}`}
            >
              <View className="h-6 w-6 items-center justify-center rounded-full bg-brand-50">
                <Check size={13} color={colors.brand[600]} />
              </View>
              <Text className="ml-3 flex-1 text-sm text-navy-text">{service}</Text>
            </View>
          ))
        ) : (
          <View className="flex-row items-start">
            <ShieldAlert size={16} color={colors.navy.muted} />
            <Text className="ml-3 flex-1 text-sm leading-5 text-navy-secondary">
              {t('insurance.plan.noCoveredServices')}
            </Text>
          </View>
        )}
      </View>

      {plan.provider && (phone || email) ? (
        <>
          <Text className="mb-3 mt-7 text-base font-extrabold text-navy-text">
            {t('insurance.plan.providerHeading')}
          </Text>
          <View className="rounded-3xl border border-cream-300 bg-white p-5">
            <Text className="text-sm font-bold text-navy-text">{plan.provider.name}</Text>
            <View className="mt-4 flex-row">
              {phone ? (
                <ContactAction
                  icon={Phone}
                  label={t('insurance.plan.callProvider')}
                  value={phone}
                  url={`tel:${phone.replace(/\s/g, '')}`}
                />
              ) : null}
              {email ? (
                <ContactAction
                  icon={Mail}
                  label={t('insurance.plan.emailProvider')}
                  value={email}
                  url={`mailto:${email}`}
                />
              ) : null}
            </View>
          </View>
        </>
      ) : null}

      <View className="mt-8">
        {alreadyEnrolled ? (
          <>
            <View
              className="mb-4 flex-row items-start rounded-3xl p-5"
              style={{ backgroundColor: colors.semantic.successSurface }}
            >
              <BadgeCheck size={20} color={colors.semantic.success} />
              <View className="ml-3 flex-1">
                <Text
                  className="text-sm font-bold"
                  style={{ color: colors.semantic.success }}
                >
                  {t('insurance.plan.alreadyEnrolledTitle')}
                </Text>
                <Text className="mt-1 text-xs leading-4 text-navy-secondary">
                  {t('insurance.plan.alreadyEnrolledBody')}
                </Text>
              </View>
            </View>
            <Button
              label={t('insurance.plan.viewMyCoverage')}
              onPress={onViewCoverage}
              leftIcon={ShieldCheck}
            />
          </>
        ) : (
          <>
            <Button label={t('insurance.plan.enrollNow')} onPress={onEnroll} leftIcon={ShieldCheck} />
            <Text className="mt-3 text-center text-[11px] text-navy-muted">
              {t('insurance.plan.enrollHint')}
            </Text>
          </>
        )}
      </View>
    </ScrollView>
  );
}

function HeroStat({ label, value }: { label: string; value: string | null }) {
  const { t } = useTranslation();
  return (
    <View className="flex-1 pr-2">
      <Text className="text-[10px] text-white/75" numberOfLines={1}>
        {label}
      </Text>
      <Text className="mt-1 text-sm font-bold text-white" numberOfLines={1}>
        {value ?? t('insurance.notListed')}
      </Text>
    </View>
  );
}

function FactRow({
  label,
  value,
  positive,
}: {
  label: string;
  value: string;
  positive?: boolean;
}) {
  const color =
    positive === undefined
      ? colors.navy.text
      : positive
        ? colors.semantic.success
        : colors.navy.secondary;
  return (
    <View className="flex-row items-center justify-between">
      <Text className="flex-1 pr-3 text-sm text-navy-secondary">{label}</Text>
      <Text className="text-sm font-bold" style={{ color }}>
        {value}
      </Text>
    </View>
  );
}

function ContactAction({
  icon: Icon,
  label,
  value,
  url,
}: {
  icon: typeof Phone;
  label: string;
  value: string;
  url: string;
}) {
  return (
    <Pressable
      onPress={() => {
        // Rejects when no handler is installed (e.g. a tablet with no dialler).
        Linking.openURL(url).catch(() => undefined);
      }}
      accessibilityRole="button"
      accessibilityLabel={`${label} ${value}`}
      className="mr-2 flex-1 flex-row items-center rounded-2xl border border-brand-300 bg-brand-50 px-3 py-3"
    >
      <Icon size={15} color={colors.brand[600]} />
      <View className="ml-2.5 flex-1">
        <Text className="text-[10px] text-navy-muted">{label}</Text>
        <Text className="text-xs font-semibold text-brand-600" numberOfLines={1}>
          {value}
        </Text>
      </View>
    </Pressable>
  );
}

/* -------------------------------------------------------------------------- */
/* Review + confirm                                                            */
/* -------------------------------------------------------------------------- */

function ReviewView({
  plan,
  monthlyPremium,
  formatAmount,
  paymentOption,
  onSelectPaymentOption,
  acknowledged,
  onToggleAcknowledged,
  errorMessage,
  duplicateMessage,
  submitting,
  onConfirm,
  onCancel,
  onViewCoverage,
}: {
  plan: InsurancePlanDetail;
  monthlyPremium: string | null;
  formatAmount: (value: string | number | null | undefined) => string | null;
  paymentOption: PaymentOptionId;
  onSelectPaymentOption: (option: PaymentOptionId) => void;
  acknowledged: boolean;
  onToggleAcknowledged: () => void;
  errorMessage: string | null;
  duplicateMessage: string | null;
  submitting: boolean;
  onConfirm: () => void;
  onCancel: () => void;
  onViewCoverage: () => void;
}) {
  const { t } = useTranslation();
  const annualPremium = formatAmount(plan.annual_premium);
  const deductible = formatAmount(plan.deductible);
  const copay =
    plan.copay_percentage === null || plan.copay_percentage === undefined
      ? null
      : `${parseFloat(String(plan.copay_percentage))}%`;

  const acknowledgement = monthlyPremium
    ? t('insurance.purchase.acknowledge', { plan: plan.name, amount: monthlyPremium })
    : t('insurance.purchase.acknowledgeOnRequest', { plan: plan.name });

  return (
    <ScrollView
      className="flex-1 px-6"
      showsVerticalScrollIndicator={false}
      contentContainerStyle={{ paddingBottom: 40 }}
    >
      <Text className="mb-5 mt-3 text-sm leading-5 text-navy-secondary">
        {t('insurance.purchase.reviewSubtitle')}
      </Text>

      <Text className="mb-3 text-[10px] font-bold uppercase tracking-widest text-navy-muted">
        {t('insurance.purchase.summaryHeading')}
      </Text>
      <View className="rounded-3xl border border-cream-300 bg-white p-5">
        <SummaryRow label={t('insurance.purchase.plan')} value={plan.name} emphasise />
        {plan.provider?.name ? (
          <SummaryRow label={t('insurance.purchase.provider')} value={plan.provider.name} />
        ) : null}
        {plan.plan_type ? (
          <SummaryRow
            label={t('insurance.purchase.tier')}
            value={t(`insurance.planType.${plan.plan_type}`, { defaultValue: plan.plan_type })}
          />
        ) : null}

        <View className="my-4 h-px bg-cream-300" />

        <View className="flex-row items-end justify-between">
          <Text className="flex-1 pr-3 text-sm text-navy-secondary">
            {t('insurance.purchase.monthlyPremium')}
          </Text>
          {monthlyPremium ? (
            <View className="flex-row items-baseline">
              <Text className="text-xl font-extrabold text-brand-600">{monthlyPremium}</Text>
              <Text className="ml-1 text-xs text-navy-muted">
                {t('insurance.purchase.perMonth')}
              </Text>
            </View>
          ) : (
            <Text className="text-sm font-bold text-navy-secondary">
              {t('insurance.purchase.premiumOnRequest')}
            </Text>
          )}
        </View>

        <View className="my-4 h-px bg-cream-300" />

        <SummaryRow
          label={t('insurance.purchase.annualPremium')}
          value={annualPremium ?? t('insurance.notListed')}
        />
        <SummaryRow
          label={t('insurance.purchase.deductible')}
          value={deductible ?? t('insurance.notListed')}
        />
        <SummaryRow
          label={t('insurance.purchase.copay')}
          value={copay ?? t('insurance.notListed')}
        />
        <SummaryRow
          label={t('insurance.purchase.cashless')}
          value={plan.cashless_available ? t('insurance.plan.yes') : t('insurance.plan.no')}
        />
      </View>
      <Text className="mt-2 text-[11px] text-navy-muted">{t('insurance.currencyNote')}</Text>

      <Text className="mb-1 mt-7 text-base font-extrabold text-navy-text">
        {t('insurance.purchase.paymentHeading')}
      </Text>
      <Text className="mb-4 text-xs leading-4 text-navy-secondary">
        {t('insurance.purchase.paymentSubtitle')}
      </Text>
      {PAYMENT_OPTIONS.map((option) => (
        <PaymentOptionRow
          key={option.id}
          label={t(option.labelKey)}
          selected={paymentOption === option.id}
          onPress={() => onSelectPaymentOption(option.id)}
        />
      ))}

      <Text className="mb-3 mt-6 text-base font-extrabold text-navy-text">
        {t('insurance.purchase.nextStepsHeading')}
      </Text>
      <View className="rounded-3xl border border-cream-300 bg-white p-5">
        <NextStep index={1} text={t('insurance.purchase.step1')} />
        <NextStep index={2} text={t('insurance.purchase.step2')} />
        <NextStep index={3} text={t('insurance.purchase.step3')} last />
      </View>

      <View className="mt-4 flex-row items-start rounded-2xl bg-brand-50 p-4">
        <Wallet size={16} color={colors.brand[600]} />
        <Text className="ml-3 flex-1 text-xs leading-4 text-navy-secondary">
          {t('insurance.purchase.paymentNote')}
        </Text>
      </View>

      {duplicateMessage ? (
        <View
          className="mt-5 rounded-3xl p-5"
          style={{ backgroundColor: colors.semantic.successSurface }}
        >
          <View className="flex-row items-start">
            <BadgeCheck size={20} color={colors.semantic.success} />
            <View className="ml-3 flex-1">
              <Text className="text-sm font-bold" style={{ color: colors.semantic.success }}>
                {t('insurance.purchase.duplicateTitle')}
              </Text>
              <Text className="mt-1 text-xs leading-4 text-navy-secondary">
                {duplicateMessage}
              </Text>
              <Text className="mt-2 text-xs leading-4 text-navy-secondary">
                {t('insurance.purchase.duplicateBody')}
              </Text>
            </View>
          </View>
          <View className="mt-4">
            <Button
              label={t('insurance.purchase.viewPolicies')}
              onPress={onViewCoverage}
              leftIcon={ShieldCheck}
            />
          </View>
        </View>
      ) : (
        <>
          {errorMessage ? (
            <View
              className="mt-5 flex-row items-start rounded-2xl p-4"
              style={{ backgroundColor: colors.semantic.dangerSurface }}
            >
              <TriangleAlert size={16} color={colors.semantic.danger} />
              <View className="ml-3 flex-1">
                <Text className="text-xs font-bold" style={{ color: colors.semantic.danger }}>
                  {t('insurance.purchase.errorTitle')}
                </Text>
                <Text className="mt-1 text-xs leading-4 text-navy-secondary">{errorMessage}</Text>
              </View>
            </View>
          ) : null}

          <Pressable
            onPress={onToggleAcknowledged}
            disabled={submitting}
            accessibilityRole="checkbox"
            accessibilityState={{ checked: acknowledged, disabled: submitting }}
            className="mt-6 flex-row items-start rounded-2xl border bg-white p-4"
            style={{
              borderColor: acknowledged ? colors.brand[500] : colors.cream[300],
              borderWidth: acknowledged ? 2 : 1,
            }}
          >
            {acknowledged ? (
              <SquareCheckBig size={20} color={colors.brand[500]} />
            ) : (
              <Square size={20} color={colors.navy.muted} />
            )}
            <Text className="ml-3 flex-1 text-xs leading-4 text-navy-text">{acknowledgement}</Text>
          </Pressable>

          <View className="mt-5">
            <Button
              label={
                submitting
                  ? t('insurance.purchase.submitting')
                  : t('insurance.purchase.confirmButton')
              }
              onPress={onConfirm}
              loading={submitting}
              disabled={!acknowledged || submitting}
              leftIcon={ShieldCheck}
            />
            <View className="mt-3">
              <Button
                label={t('insurance.purchase.cancel')}
                onPress={onCancel}
                variant="outline"
                disabled={submitting}
                showChevron={false}
              />
            </View>
          </View>
        </>
      )}
    </ScrollView>
  );
}

function SummaryRow({
  label,
  value,
  emphasise,
}: {
  label: string;
  value: string;
  emphasise?: boolean;
}) {
  return (
    <View className="flex-row items-start justify-between py-1">
      <Text className="flex-1 pr-4 text-sm text-navy-secondary">{label}</Text>
      <Text
        className={`flex-1 text-right text-sm text-navy-text ${emphasise ? 'font-extrabold' : 'font-semibold'}`}
      >
        {value}
      </Text>
    </View>
  );
}

function PaymentOptionRow({
  label,
  selected,
  onPress,
}: {
  label: string;
  selected: boolean;
  onPress: () => void;
}) {
  return (
    <Pressable
      onPress={onPress}
      accessibilityRole="radio"
      accessibilityState={{ selected }}
      className="mb-3 flex-row items-center justify-between rounded-2xl bg-white p-4"
      style={{
        borderWidth: selected ? 2 : 1,
        borderColor: selected ? colors.brand[500] : colors.cream[300],
      }}
    >
      <View className="flex-1 flex-row items-center">
        <View className="h-9 w-9 items-center justify-center rounded-full bg-brand-50">
          <Wallet size={16} color={colors.brand[600]} />
        </View>
        <Text className="ml-3 flex-1 text-sm font-semibold text-navy-text">{label}</Text>
      </View>
      {selected ? (
        <CheckCircle2 size={20} color={colors.brand[500]} />
      ) : (
        <Circle size={20} color={colors.cream[300]} />
      )}
    </Pressable>
  );
}

function NextStep({ index, text, last }: { index: number; text: string; last?: boolean }) {
  return (
    <View className={`flex-row items-start ${last ? '' : 'mb-4'}`}>
      <View className="h-6 w-6 items-center justify-center rounded-full bg-brand-50">
        <Text className="text-[11px] font-extrabold text-brand-600">{index}</Text>
      </View>
      <Text className="ml-3 flex-1 text-xs leading-4 text-navy-secondary">{text}</Text>
    </View>
  );
}

/* -------------------------------------------------------------------------- */
/* Success                                                                     */
/* -------------------------------------------------------------------------- */

function SuccessView({
  receipt,
  language,
  onViewCoverage,
  onBackToMarketplace,
}: {
  receipt: PurchaseInsurancePlanResponse & { planName: string; providerName: string | null };
  language: string;
  onViewCoverage: () => void;
  onBackToMarketplace: () => void;
}) {
  const { t } = useTranslation();
  const { planName, providerName } = receipt;

  return (
    <ScrollView
      className="flex-1 px-6"
      showsVerticalScrollIndicator={false}
      contentContainerStyle={{ paddingBottom: 40, paddingTop: 24 }}
    >
      <View className="rounded-3xl bg-white p-6">
        <View className="items-center">
          <View className="h-20 w-20 items-center justify-center rounded-full bg-brand-50">
            <BadgeCheck size={38} color={colors.brand[500]} />
          </View>
          <Text className="mt-5 text-center text-xl font-extrabold text-navy-text">
            {t('insurance.purchase.successTitle')}
          </Text>
          <Text className="mt-2 text-center text-sm leading-5 text-navy-secondary">
            {t('insurance.purchase.successBody')}
          </Text>
        </View>

        <View className="my-6 h-px bg-cream-300" />

        <SummaryRow label={t('insurance.purchase.plan')} value={planName} emphasise />
        {providerName ? (
          <SummaryRow label={t('insurance.purchase.provider')} value={providerName} />
        ) : null}
        <SummaryRow label={t('insurance.purchase.policyNumber')} value={receipt.policy_number} />
        <SummaryRow
          label={t('insurance.purchase.coverPeriod')}
          value={`${formatDate(receipt.effective_date, language)} — ${formatDate(receipt.expiry_date, language)}`}
        />

        <View
          className="mt-5 flex-row items-center rounded-2xl px-4 py-3"
          style={{ backgroundColor: colors.semantic.warningSurface }}
        >
          <Hourglass size={15} color={colors.semantic.warning} />
          <Text
            className="ml-2.5 flex-1 text-xs font-semibold"
            style={{ color: colors.semantic.warning }}
          >
            {t(`insurance.status.${receipt.status}`, { defaultValue: receipt.status })}
          </Text>
        </View>
      </View>

      <View className="mt-5 flex-row items-start rounded-2xl bg-brand-50 p-4">
        <ShieldCheck size={16} color={colors.brand[600]} />
        <Text className="ml-3 flex-1 text-xs leading-4 text-navy-secondary">
          {t('insurance.purchase.successNote')}
        </Text>
      </View>

      <View className="mt-7">
        <Button
          label={t('insurance.purchase.viewPolicies')}
          onPress={onViewCoverage}
          leftIcon={ShieldCheck}
        />
        <View className="mt-3">
          <Button
            label={t('insurance.purchase.backToMarketplace')}
            onPress={onBackToMarketplace}
            variant="outline"
            showChevron={false}
            leftIcon={Store}
          />
        </View>
      </View>
    </ScrollView>
  );
}
