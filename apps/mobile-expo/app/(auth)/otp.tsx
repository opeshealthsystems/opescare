import { useEffect, useRef, useState } from 'react';
import { ActivityIndicator, Pressable, ScrollView, Text, TextInput, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  ChevronRight,
  CircleQuestionMark,
  KeyRound,
  Phone,
  ShieldCheck,
  Smartphone,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { TextField } from '../../components/ui/TextField';
import { Logo } from '../../components/ui/Logo';
import { useAuthStore } from '../../lib/store/auth';
import { colors } from '../../theme/tokens';

const OTP_LENGTH = 6;
const CODE_VALIDITY_SECONDS = 5 * 60;
const RESEND_COOLDOWN_SECONDS = 45;

/** mm:ss countdown formatter. `padMinutes` pads the minute to 2 digits
 * ("04:59", matching the expiry timer); the resend cooldown reference shows
 * an unpadded minute ("0:45"). */
function formatCountdown(totalSeconds: number, padMinutes: boolean): string {
  const clamped = Math.max(0, totalSeconds);
  const minutes = Math.floor(clamped / 60);
  const seconds = clamped % 60;
  const minuteLabel = padMinutes ? String(minutes).padStart(2, '0') : String(minutes);
  return `${minuteLabel}:${String(seconds).padStart(2, '0')}`;
}

/** Legacy phone + PIN -> OTP flow, reached via "Sign in with Health ID" on the
 * login screen. Two steps in one screen, gated on auth store status — mirrors
 * apps/mobile-patient's login_screen.dart + otp_screen.dart split. The OTP
 * step matches the "verification screen" reference: boxed 6-digit input,
 * expiry + resend countdowns, change-number row, and a secure/private note. */
export default function OtpScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const status = useAuthStore((s) => s.status);
  const storeError = useAuthStore((s) => s.error);
  const loginWithPhone = useAuthStore((s) => s.loginWithPhone);
  const verifyOtp = useAuthStore((s) => s.verifyOtp);
  const resendOtp = useAuthStore((s) => s.resendOtp);
  const clearError = useAuthStore((s) => s.clearError);
  const pendingPhone = useAuthStore((s) => s.pendingPhone);

  const [phoneNumber, setPhoneNumber] = useState('');
  const [pin, setPin] = useState('');
  const [otp, setOtp] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [resending, setResending] = useState(false);
  const [resendError, setResendError] = useState<string | null>(null);
  const [otpFocused, setOtpFocused] = useState(false);
  // Overrides the store-driven step so "back" / "change phone number" can
  // return to the phone form without discarding the store's otp_pending
  // status (there's no store action to un-pend an OTP request).
  const [manualStep, setManualStep] = useState<'phone' | 'otp' | null>(null);
  const [expirySeconds, setExpirySeconds] = useState(CODE_VALIDITY_SECONDS);
  const [resendCooldown, setResendCooldown] = useState(RESEND_COOLDOWN_SECONDS);

  const otpInputRef = useRef<TextInput>(null);
  const prevStepRef = useRef<'phone' | 'otp'>('phone');

  const step: 'phone' | 'otp' = manualStep ?? (status === 'otp_pending' ? 'otp' : 'phone');
  const isExpired = expirySeconds <= 0;

  // Reset both countdowns whenever the user newly lands on the OTP step.
  useEffect(() => {
    if (step === 'otp' && prevStepRef.current !== 'otp') {
      setExpirySeconds(CODE_VALIDITY_SECONDS);
      setResendCooldown(RESEND_COOLDOWN_SECONDS);
      setResendError(null);
    }
    prevStepRef.current = step;
  }, [step]);

  // Tick both countdowns while on the OTP step, and focus the hidden input.
  useEffect(() => {
    if (step !== 'otp') return;
    otpInputRef.current?.focus();
    const id = setInterval(() => {
      setExpirySeconds((s) => (s > 0 ? s - 1 : 0));
      setResendCooldown((s) => (s > 0 ? s - 1 : 0));
    }, 1000);
    return () => clearInterval(id);
  }, [step]);

  const submitPhone = async () => {
    if (!phoneNumber.trim() || !pin.trim()) return;
    setSubmitting(true);
    try {
      await loginWithPhone(phoneNumber.trim(), pin.trim());
      setManualStep(null); // follow the store back to the OTP step
    } catch {
      // storeError reflects the failure
    } finally {
      setSubmitting(false);
    }
  };

  const submitOtp = async () => {
    if (otp.trim().length !== OTP_LENGTH || isExpired || submitting) return;
    setSubmitting(true);
    try {
      await verifyOtp(otp.trim());
    } catch {
      // storeError reflects the failure
    } finally {
      setSubmitting(false);
    }
  };

  const handleResend = async () => {
    if (resendCooldown > 0 || resending) return;
    setResending(true);
    setResendError(null);
    try {
      await resendOtp();
      setOtp('');
      setExpirySeconds(CODE_VALIDITY_SECONDS);
      setResendCooldown(RESEND_COOLDOWN_SECONDS);
      otpInputRef.current?.focus();
    } catch {
      setResendError(t('auth.resendFailed'));
    } finally {
      setResending(false);
    }
  };

  const goToPhoneStep = () => {
    setManualStep('phone');
    setOtp('');
    setPin('');
    clearError();
  };

  const handleBack = () => {
    if (step === 'otp') {
      goToPhoneStep();
    } else {
      router.back();
    }
  };

  return (
    <Screen>
      <ScrollView
        style={{ flex: 1 }}
        showsVerticalScrollIndicator={false}
        keyboardShouldPersistTaps="handled"
        contentContainerStyle={{ paddingBottom: 32 }}
      >
        <View className="mt-2 h-11 w-11 items-center justify-center rounded-full border border-gold-300">
          <Pressable onPress={handleBack} hitSlop={8}>
            <ArrowLeft size={18} color={colors.gold[600]} />
          </Pressable>
        </View>

        <View className="items-center py-4">
          <Logo size={80} markOnly />
        </View>

        {step === 'phone' ? (
          <>
            <Text className="text-center text-2xl font-extrabold text-navy-text">
              {t('auth.welcomeBack')}
            </Text>
            <View className="mt-6">
              <TextField
                label={t('auth.phoneNumber')}
                placeholder={t('auth.phoneNumberPlaceholder')}
                icon={Phone}
                keyboardType="phone-pad"
                textContentType="telephoneNumber"
                value={phoneNumber}
                onChangeText={setPhoneNumber}
              />
              <TextField
                label={t('auth.pin')}
                placeholder={t('auth.pinPlaceholder')}
                icon={KeyRound}
                secureToggle
                secureTextEntry
                keyboardType="number-pad"
                value={pin}
                onChangeText={setPin}
              />
              {storeError ? (
                <Text className="mb-3 text-center text-sm text-danger">{storeError}</Text>
              ) : null}
              <Button label={t('auth.signIn')} onPress={submitPhone} loading={submitting} />
            </View>
          </>
        ) : (
          <>
            <Text className="text-center text-2xl font-extrabold text-navy-text">
              {t('auth.otpTitle')}
            </Text>
            <Text className="mt-2 text-center text-sm leading-5 text-navy-secondary">
              {t('auth.otpSubtitle')}
              {'\n'}
              <Text className="font-semibold text-gold-600">{pendingPhone}</Text>
            </Text>

            <View className="mt-8">
              <Pressable onPress={() => otpInputRef.current?.focus()}>
                <View className="flex-row justify-between">
                  {Array.from({ length: OTP_LENGTH }).map((_, index) => {
                    const digit = otp[index];
                    const isActive = otpFocused && index === Math.min(otp.length, OTP_LENGTH - 1);
                    const borderColor = isActive
                      ? colors.gold[500]
                      : digit
                        ? colors.gold[300]
                        : colors.cream[300];
                    return (
                      <View
                        key={index}
                        className="h-16 items-center justify-center rounded-2xl border-2 bg-white"
                        style={{
                          width: 46,
                          borderColor,
                          shadowColor: colors.gold[500],
                          shadowOpacity: isActive ? 0.3 : 0,
                          shadowRadius: 6,
                          shadowOffset: { width: 0, height: 0 },
                          elevation: isActive ? 3 : 0,
                        }}
                      >
                        {digit ? (
                          <Text className="text-xl font-bold text-navy-text">{digit}</Text>
                        ) : isActive ? (
                          <View style={{ width: 2, height: 22, backgroundColor: colors.gold[500] }} />
                        ) : null}
                      </View>
                    );
                  })}
                </View>
              </Pressable>
              <TextInput
                ref={otpInputRef}
                value={otp}
                onChangeText={(v) => setOtp(v.replace(/\D/g, '').slice(0, OTP_LENGTH))}
                onFocus={() => setOtpFocused(true)}
                onBlur={() => setOtpFocused(false)}
                keyboardType="number-pad"
                maxLength={OTP_LENGTH}
                textContentType="oneTimeCode"
                autoComplete="sms-otp"
                caretHidden
                style={{ position: 'absolute', opacity: 0, height: 1, width: 1 }}
              />

              <View className="mt-4 flex-row items-center justify-center">
                <ShieldCheck size={14} color={isExpired ? colors.semantic.danger : colors.gold[600]} />
                {isExpired ? (
                  <Text className="ml-2 text-xs font-semibold text-danger">
                    {t('auth.codeExpired')}
                  </Text>
                ) : (
                  <Text className="ml-2 text-xs text-navy-secondary">
                    {t('auth.codeExpiresIn')}{' '}
                    <Text className="font-bold text-gold-600">
                      {formatCountdown(expirySeconds, true)}
                    </Text>
                  </Text>
                )}
              </View>

              {storeError ? (
                <Text className="mb-1 mt-3 text-center text-sm text-danger">{storeError}</Text>
              ) : null}
              {resendError ? (
                <Text className="mb-1 mt-3 text-center text-sm text-danger">{resendError}</Text>
              ) : null}

              <View className="mt-5">
                <Button
                  label={t('auth.verifyAndContinue')}
                  onPress={submitOtp}
                  loading={submitting}
                  disabled={otp.length !== OTP_LENGTH || isExpired}
                />
              </View>

              <Pressable
                onPress={handleResend}
                disabled={resendCooldown > 0 || resending}
                className="mt-3 h-14 flex-row items-center justify-between rounded-2xl border border-gold-500 bg-transparent px-4"
                style={{ opacity: resendCooldown > 0 || resending ? 0.6 : 1 }}
              >
                {resending ? (
                  <ActivityIndicator color={colors.gold[600]} size="small" />
                ) : (
                  <>
                    <Text className="text-base font-semibold text-gold-600">
                      {t('auth.resendCode')}
                    </Text>
                    {resendCooldown > 0 ? (
                      <Text className="text-sm font-semibold text-gold-600">
                        ({formatCountdown(resendCooldown, false)})
                      </Text>
                    ) : null}
                  </>
                )}
              </Pressable>

              <View className="my-6 flex-row items-center">
                <View className="h-px flex-1 bg-cream-300" />
                <Text className="mx-3 text-xs font-semibold text-navy-muted">{t('auth.or')}</Text>
                <View className="h-px flex-1 bg-cream-300" />
              </View>

              <Pressable
                onPress={goToPhoneStep}
                className="h-14 flex-row items-center rounded-2xl border border-cream-300 bg-white px-4"
              >
                <Smartphone size={18} color={colors.navy.text} />
                <Text className="ml-3 flex-1 text-sm font-semibold text-navy-text">
                  {t('auth.changePhoneNumber')}
                </Text>
                <ChevronRight size={18} color={colors.navy.muted} />
              </Pressable>

              <View className="mt-5 flex-row items-start rounded-2xl bg-gold-50 p-4">
                <ShieldCheck size={16} color={colors.gold[600]} />
                <View className="ml-3 flex-1">
                  <Text className="text-sm font-semibold text-navy-text">
                    {t('auth.securePrivateTitle')}
                  </Text>
                  <Text className="mt-1 text-xs text-navy-secondary">
                    {t('auth.securePrivateBody')}
                  </Text>
                </View>
              </View>

              <View className="mt-6 items-center pb-4">
                <View className="flex-row items-center">
                  <CircleQuestionMark size={14} color={colors.gold[600]} />
                  <Text className="ml-2 text-xs text-navy-secondary">
                    {t('auth.otpHelpPrompt')}{' '}
                    <Text className="font-semibold text-gold-600">{t('auth.otpHelpTip')}</Text>
                  </Text>
                </View>
                <Text className="mt-2 text-xs text-navy-secondary">
                  {t('auth.otpStillTrouble')}{' '}
                  <Text className="font-semibold text-gold-600">{t('auth.contactSupport')}</Text>
                </Text>
              </View>
            </View>
          </>
        )}
      </ScrollView>
    </Screen>
  );
}
