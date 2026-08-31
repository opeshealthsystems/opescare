import { useState } from 'react';
import { ActivityIndicator, Pressable, ScrollView, Text, View } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  Check,
  CheckCircle2,
  Circle,
  FileQuestion,
  Info,
  Smartphone,
  X,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import {
  useInsurancePlanDetail,
  usePurchaseInsurancePlan,
  type InsurancePlanDetail,
} from '../../lib/api/queries';
import { colors } from '../../theme/tokens';

type Stage = 'detail' | 'confirm' | 'success';
type PaymentOptionId = 'mtn' | 'orange';

/** Formats a decimal-string/number premium as a grouped XAF amount. Duplicated
 * from marketplace.tsx (screen-local, no shared util file to keep this
 * agent's file-ownership additive-only). */
function formatXaf(value: string | number | null | undefined, suffix: string): string {
  if (value === null || value === undefined) return '—';
  const num = typeof value === 'string' ? parseFloat(value) : value;
  if (Number.isNaN(num)) return '—';
  const grouped = Math.round(num)
    .toString()
    .replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
  return `${grouped} ${suffix}`;
}

/** covered_services comes back from the API as a raw text column (JSON-encoded
 * list, or possibly a plain comma-separated string) — parse defensively. */
function parseCoveredServices(raw: string | null): string[] {
  if (!raw) return [];
  try {
    const parsed = JSON.parse(raw);
    if (Array.isArray(parsed)) return parsed.map((item) => String(item));
  } catch {
    // Not JSON — fall through to comma-splitting.
  }
  return raw
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean);
}

function extractErrorMessage(err: unknown, fallback: string): string {
  const anyErr = err as any;
  return anyErr?.response?.data?.message ?? fallback;
}

/** Insurance plan detail + purchase flow (GET .../plans/{id}, POST
 * .../plans/{id}/purchase). Enrollment is a deliberate two-step flow — the
 * "Enroll" action only reveals a confirmation screen; the purchase call only
 * fires when the patient explicitly taps "Confirm & Enroll" there. */
export default function InsurancePlanDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const { t } = useTranslation();
  const router = useRouter();
  const { data, isLoading, isError, refetch } = useInsurancePlanDetail(id);
  const purchase = usePurchaseInsurancePlan(id);

  const [stage, setStage] = useState<Stage>('detail');
  const [paymentOption, setPaymentOption] = useState<PaymentOptionId>('mtn');
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [policyNumber, setPolicyNumber] = useState<string | null>(null);

  const plan = data?.data;
  const currencySuffix = t('insurance.currencySuffix');

  const handleBack = () => {
    if (stage === 'confirm') {
      setErrorMessage(null);
      setStage('detail');
      return;
    }
    router.back();
  };

  const handleConfirmPurchase = async () => {
    setErrorMessage(null);
    try {
      const result = await purchase.mutateAsync({
        payment_method: 'mobile_money',
        payment_reference:
          paymentOption === 'mtn' ? t('insurance.purchase.mtn') : t('insurance.purchase.orange'),
      });
      setPolicyNumber(result.policy_number);
      setStage('success');
    } catch (err) {
      setErrorMessage(extractErrorMessage(err, t('insurance.purchase.errorGeneric')));
    }
  };

  return (
    <Screen className="px-0">
      {stage !== 'success' ? (
        <View className="flex-row items-center px-6 pt-2">
          <Pressable
            onPress={handleBack}
            hitSlop={8}
            className="h-11 w-11 items-center justify-center rounded-full border border-gold-300"
          >
            <ArrowLeft size={18} color={colors.gold[600]} />
          </Pressable>
          <Text className="ml-4 text-lg font-bold text-navy-text">
            {stage === 'confirm' ? t('insurance.purchase.confirmTitle') : t('insurance.plan.title')}
          </Text>
        </View>
      ) : null}

      {isLoading ? (
        <View className="mt-16 items-center">
          <ActivityIndicator color={colors.gold[500]} />
        </View>
      ) : isError || !plan ? (
        <View className="mt-16 items-center px-6">
          <FileQuestion size={40} color={colors.navy.muted} />
          <Text className="mt-3 text-center text-sm text-navy-secondary">
            {t('insurance.plan.loadError')}
          </Text>
          <Pressable onPress={() => refetch()} className="mt-4 rounded-full bg-gold-50 px-5 py-2">
            <Text className="text-sm font-semibold text-gold-600">{t('insurance.retry')}</Text>
          </Pressable>
        </View>
      ) : stage === 'success' ? (
        <SuccessView
          policyNumber={policyNumber}
          onDone={() => router.replace('/insurance')}
        />
      ) : stage === 'confirm' ? (
        <ConfirmView
          planName={plan.name}
          providerName={plan.provider?.name ?? null}
          monthlyPremium={formatXaf(plan.monthly_premium, currencySuffix)}
          paymentOption={paymentOption}
          onSelectPaymentOption={setPaymentOption}
          errorMessage={errorMessage}
          submitting={purchase.isPending}
          onConfirm={handleConfirmPurchase}
          onCancel={() => setStage('detail')}
        />
      ) : (
        <DetailView
          plan={plan}
          currencySuffix={currencySuffix}
          onEnroll={() => setStage('confirm')}
        />
      )}
    </Screen>
  );
}

function DetailView({
  plan,
  currencySuffix,
  onEnroll,
}: {
  plan: InsurancePlanDetail;
  currencySuffix: string;
  onEnroll: () => void;
}) {
  const { t } = useTranslation();
  const coveredServices = parseCoveredServices(plan.covered_services);

  return (
    <ScrollView className="flex-1 px-6" showsVerticalScrollIndicator={false}>
      <View className="mt-4">
        {plan.provider?.name ? (
          <Text className="text-xs font-semibold uppercase text-navy-muted">
            {plan.provider.name}
          </Text>
        ) : null}
        <Text className="mt-1 text-2xl font-extrabold text-navy-text">{plan.name}</Text>
        {plan.plan_type ? (
          <View className="mt-2 self-start rounded-full bg-gold-50 px-3 py-1">
            <Text className="text-xs font-semibold text-gold-600">
              {t(`insurance.planType.${plan.plan_type}`, { defaultValue: plan.plan_type })}
            </Text>
          </View>
        ) : null}
        {plan.description ? (
          <Text className="mt-3 text-sm text-navy-secondary">{plan.description}</Text>
        ) : null}
      </View>

      <View className="mt-5 rounded-2xl bg-gold-500 p-5">
        <Text className="text-sm font-semibold text-white/90">
          {t('insurance.plan.monthlyPremium')}
        </Text>
        <Text className="mt-1 text-2xl font-extrabold text-white">
          {formatXaf(plan.monthly_premium, currencySuffix)}
        </Text>
        <View className="mt-4 h-px bg-white/25" />
        <View className="mt-4 flex-row justify-between">
          <View>
            <Text className="text-xs text-white/80">{t('insurance.plan.annualPremium')}</Text>
            <Text className="mt-1 text-base font-bold text-white">
              {formatXaf(plan.annual_premium, currencySuffix)}
            </Text>
          </View>
          <View>
            <Text className="text-xs text-white/80">{t('insurance.plan.deductible')}</Text>
            <Text className="mt-1 text-base font-bold text-white">
              {formatXaf(plan.deductible, currencySuffix)}
            </Text>
          </View>
        </View>
      </View>

      <View className="mt-4 rounded-2xl bg-white p-4">
        <DetailRow
          label={t('insurance.plan.copay')}
          value={plan.copay_percentage !== null ? `${plan.copay_percentage}%` : '—'}
        />
        <View className="my-3 h-px bg-cream-300" />
        <DetailRow
          label={t('insurance.plan.cashlessAvailable')}
          value={plan.cashless_available ? t('insurance.plan.yes') : t('insurance.plan.no')}
          positive={plan.cashless_available}
        />
        <View className="my-3 h-px bg-cream-300" />
        <DetailRow
          label={t('insurance.plan.preauthRequired')}
          value={plan.requires_preauthorization ? t('insurance.plan.yes') : t('insurance.plan.no')}
          positive={!plan.requires_preauthorization}
        />
      </View>

      <Text className="mb-3 mt-5 text-base font-bold text-navy-text">
        {t('insurance.plan.coveredServices')}
      </Text>
      <View className="mb-6 rounded-2xl bg-white p-4">
        {coveredServices.length > 0 ? (
          coveredServices.map((service, index) => (
            <View
              key={`${service}-${index}`}
              className={`flex-row items-center ${index > 0 ? 'mt-3' : ''}`}
            >
              <View className="h-6 w-6 items-center justify-center rounded-full bg-gold-50">
                <Check size={13} color={colors.gold[600]} />
              </View>
              <Text className="ml-3 flex-1 text-sm text-navy-text">{service}</Text>
            </View>
          ))
        ) : (
          <Text className="text-sm text-navy-secondary">{t('insurance.plan.noCoveredServices')}</Text>
        )}
      </View>

      <Button label={t('insurance.plan.enrollNow')} onPress={onEnroll} />
      <View className="h-10" />
    </ScrollView>
  );
}

function DetailRow({
  label,
  value,
  positive,
}: {
  label: string;
  value: string;
  positive?: boolean;
}) {
  const valueColor =
    positive === undefined ? colors.navy.text : positive ? colors.semantic.success : colors.semantic.danger;
  return (
    <View className="flex-row items-center justify-between">
      <Text className="text-sm text-navy-secondary">{label}</Text>
      <Text className="text-sm font-semibold" style={{ color: valueColor }}>
        {value}
      </Text>
    </View>
  );
}

function ConfirmView({
  planName,
  providerName,
  monthlyPremium,
  paymentOption,
  onSelectPaymentOption,
  errorMessage,
  submitting,
  onConfirm,
  onCancel,
}: {
  planName: string;
  providerName: string | null;
  monthlyPremium: string;
  paymentOption: PaymentOptionId;
  onSelectPaymentOption: (option: PaymentOptionId) => void;
  errorMessage: string | null;
  submitting: boolean;
  onConfirm: () => void;
  onCancel: () => void;
}) {
  const { t } = useTranslation();

  return (
    <ScrollView className="flex-1 px-6" showsVerticalScrollIndicator={false}>
      <Text className="mb-4 mt-2 text-sm text-navy-secondary">
        {t('insurance.purchase.reviewSubtitle')}
      </Text>

      <View className="rounded-2xl bg-white p-4">
        <DetailRow label={t('insurance.purchase.plan')} value={planName} />
        {providerName ? (
          <>
            <View className="my-3 h-px bg-cream-300" />
            <DetailRow label={t('insurance.purchase.provider')} value={providerName} />
          </>
        ) : null}
        <View className="my-3 h-px bg-cream-300" />
        <DetailRow
          label={t('insurance.purchase.amountDue')}
          value={`${monthlyPremium} ${t('insurance.purchase.perMonth')}`}
        />
      </View>

      <Text className="mb-3 mt-5 text-base font-bold text-navy-text">
        {t('insurance.purchase.paymentMethod')}
      </Text>
      <PaymentOption
        label={t('insurance.purchase.mtn')}
        selected={paymentOption === 'mtn'}
        onPress={() => onSelectPaymentOption('mtn')}
      />
      <PaymentOption
        label={t('insurance.purchase.orange')}
        selected={paymentOption === 'orange'}
        onPress={() => onSelectPaymentOption('orange')}
      />

      <View className="mt-2 flex-row items-start rounded-2xl bg-gold-50 p-4">
        <Info size={16} color={colors.gold[600]} />
        <Text className="ml-3 flex-1 text-xs text-navy-secondary">
          {t('insurance.purchase.paymentNote')}
        </Text>
      </View>

      {errorMessage ? (
        <View className="mt-4 flex-row items-start rounded-2xl p-4" style={{ backgroundColor: colors.semantic.dangerSurface }}>
          <X size={16} color={colors.semantic.danger} />
          <Text className="ml-3 flex-1 text-xs text-danger">{errorMessage}</Text>
        </View>
      ) : null}

      <View className="mt-6">
        <Button
          label={submitting ? t('insurance.purchase.submitting') : t('insurance.purchase.confirmButton')}
          onPress={onConfirm}
          loading={submitting}
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
      <View className="h-10" />
    </ScrollView>
  );
}

function PaymentOption({
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
      className="mb-3 flex-row items-center justify-between rounded-2xl bg-white p-4"
      style={{ borderWidth: selected ? 2 : 1, borderColor: selected ? colors.gold[500] : colors.cream[300] }}
    >
      <View className="flex-row items-center">
        <Smartphone size={18} color={colors.gold[600]} />
        <Text className="ml-3 text-sm font-semibold text-navy-text">{label}</Text>
      </View>
      {selected ? (
        <CheckCircle2 size={20} color={colors.gold[500]} />
      ) : (
        <Circle size={20} color={colors.cream[300]} />
      )}
    </Pressable>
  );
}

function SuccessView({
  policyNumber,
  onDone,
}: {
  policyNumber: string | null;
  onDone: () => void;
}) {
  const { t } = useTranslation();
  return (
    <View className="flex-1 items-center justify-center px-6">
      <View className="h-20 w-20 items-center justify-center rounded-full bg-gold-50">
        <CheckCircle2 size={40} color={colors.gold[500]} />
      </View>
      <Text className="mt-5 text-center text-xl font-extrabold text-navy-text">
        {t('insurance.purchase.successTitle')}
      </Text>
      <Text className="mt-2 text-center text-sm text-navy-secondary">
        {t('insurance.purchase.successBody', { policyNumber: policyNumber ?? '' })}
      </Text>
      <View className="mt-8 w-full">
        <Button label={t('insurance.purchase.viewPolicies')} onPress={onDone} />
      </View>
    </View>
  );
}
