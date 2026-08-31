import { useEffect, useRef, useState } from 'react';
import { Pressable, ScrollView, Text, TextInput, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  CheckCircle2,
  ChevronRight,
  Lock,
  Mail,
  ShieldCheck,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { TextField } from '../../components/ui/TextField';
import { Logo } from '../../components/ui/Logo';
import { useForgotPassword, useResetPassword } from '../../lib/api/queries';
import { colors } from '../../theme/tokens';

const CODE_LENGTH = 6;
const CODE_VALIDITY_SECONDS = 15 * 60; // matches the backend's PasswordResetCode expiry
const RESEND_COOLDOWN_SECONDS = 45;
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

/** mm:ss countdown formatter — mirrors app/(auth)/otp.tsx's formatter. */
function formatCountdown(totalSeconds: number, padMinutes: boolean): string {
  const clamped = Math.max(0, totalSeconds);
  const minutes = Math.floor(clamped / 60);
  const seconds = clamped % 60;
  const minuteLabel = padMinutes ? String(minutes).padStart(2, '0') : String(minutes);
  return `${minuteLabel}:${String(seconds).padStart(2, '0')}`;
}

function extractErrorMessage(err: unknown, fallback: string): string {
  const anyErr = err as any;
  return anyErr?.response?.data?.message ?? fallback;
}

/**
 * Forgot Password — reached from the "Forgot password?" link on login.tsx.
 * Three steps in one screen (email -> code + new password -> success),
 * mirroring app/(auth)/otp.tsx's structure and the boxed-code-entry pattern.
 * Talks to POST /mobile/auth/forgot-password + /mobile/auth/reset-password
 * (real endpoints — see routes/mobile_password_reset.php on the backend),
 * not a WebView to the (non-functional) web forgot-password route.
 */
export default function ForgotPasswordScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const forgotPassword = useForgotPassword();
  const resetPassword = useResetPassword();

  const [step, setStep] = useState<'email' | 'code' | 'success'>('email');
  const [email, setEmail] = useState('');
  const [code, setCode] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [codeFocused, setCodeFocused] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<{
    email?: string;
    code?: string;
    newPassword?: string;
    confirmPassword?: string;
  }>({});
  const [apiError, setApiError] = useState<string | null>(null);
  const [resending, setResending] = useState(false);
  const [resendError, setResendError] = useState<string | null>(null);
  const [expirySeconds, setExpirySeconds] = useState(CODE_VALIDITY_SECONDS);
  const [resendCooldown, setResendCooldown] = useState(RESEND_COOLDOWN_SECONDS);

  const codeInputRef = useRef<TextInput>(null);
  const isExpired = expirySeconds <= 0;

  // Tick both countdowns while on the code step, and focus the hidden input.
  useEffect(() => {
    if (step !== 'code') return;
    codeInputRef.current?.focus();
    const id = setInterval(() => {
      setExpirySeconds((s) => (s > 0 ? s - 1 : 0));
      setResendCooldown((s) => (s > 0 ? s - 1 : 0));
    }, 1000);
    return () => clearInterval(id);
  }, [step]);

  const goToEmailStep = () => {
    setStep('email');
    setCode('');
    setNewPassword('');
    setConfirmPassword('');
    setFieldErrors({});
    setApiError(null);
    setResendError(null);
  };

  const handleSendCode = async () => {
    const trimmedEmail = email.trim();
    if (!trimmedEmail || !EMAIL_PATTERN.test(trimmedEmail)) {
      setFieldErrors({ email: t('forgotPassword.invalidEmail') });
      return;
    }
    setFieldErrors({});
    setApiError(null);
    try {
      await forgotPassword.mutateAsync(trimmedEmail);
      setExpirySeconds(CODE_VALIDITY_SECONDS);
      setResendCooldown(RESEND_COOLDOWN_SECONDS);
      setStep('code');
    } catch (err) {
      setApiError(extractErrorMessage(err, t('forgotPassword.genericError')));
    }
  };

  const handleResend = async () => {
    if (resendCooldown > 0 || resending) return;
    setResending(true);
    setResendError(null);
    try {
      await forgotPassword.mutateAsync(email.trim());
      setCode('');
      setExpirySeconds(CODE_VALIDITY_SECONDS);
      setResendCooldown(RESEND_COOLDOWN_SECONDS);
      codeInputRef.current?.focus();
    } catch {
      setResendError(t('forgotPassword.resendFailed'));
    } finally {
      setResending(false);
    }
  };

  const handleResetPassword = async () => {
    const errors: typeof fieldErrors = {};
    if (code.trim().length !== CODE_LENGTH) errors.code = t('forgotPassword.invalidCode');
    if (newPassword.length < 8) errors.newPassword = t('forgotPassword.passwordTooShort');
    else if (newPassword !== confirmPassword) errors.confirmPassword = t('forgotPassword.passwordMismatch');
    setFieldErrors(errors);
    if (Object.keys(errors).length > 0) return;

    setApiError(null);
    try {
      await resetPassword.mutateAsync({
        email: email.trim(),
        code: code.trim(),
        password: newPassword,
        password_confirmation: confirmPassword,
      });
      setStep('success');
    } catch (err) {
      setApiError(extractErrorMessage(err, t('forgotPassword.genericError')));
    }
  };

  const handleBack = () => {
    if (step === 'code') {
      goToEmailStep();
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
        {step !== 'success' ? (
          <View className="mt-2 h-11 w-11 items-center justify-center rounded-full border border-gold-300">
            <Pressable onPress={handleBack} hitSlop={8}>
              <ArrowLeft size={18} color={colors.gold[600]} />
            </Pressable>
          </View>
        ) : null}

        <View className="items-center py-4">
          <Logo size={80} markOnly />
        </View>

        {step === 'email' ? (
          <>
            <Text className="text-center text-2xl font-extrabold text-navy-text">
              {t('forgotPassword.title')}
            </Text>
            <Text className="mt-2 text-center text-sm leading-5 text-navy-secondary">
              {t('forgotPassword.emailSubtitle')}
            </Text>

            <View className="mt-6">
              <TextField
                label={t('forgotPassword.emailLabel')}
                placeholder={t('forgotPassword.emailPlaceholder')}
                icon={Mail}
                autoCapitalize="none"
                keyboardType="email-address"
                textContentType="emailAddress"
                value={email}
                onChangeText={setEmail}
                error={fieldErrors.email}
              />

              {apiError ? (
                <Text className="mb-3 text-center text-sm text-danger">{apiError}</Text>
              ) : null}

              <Button
                label={t('forgotPassword.sendCode')}
                onPress={handleSendCode}
                loading={forgotPassword.isPending}
              />
            </View>
          </>
        ) : null}

        {step === 'code' ? (
          <>
            <Text className="text-center text-2xl font-extrabold text-navy-text">
              {t('forgotPassword.codeTitle')}
            </Text>
            <Text className="mt-2 text-center text-sm leading-5 text-navy-secondary">
              {t('forgotPassword.codeSubtitle')}
              {'\n'}
              <Text className="font-semibold text-gold-600">{email.trim()}</Text>
            </Text>

            <View className="mt-8">
              <Pressable onPress={() => codeInputRef.current?.focus()}>
                <View className="flex-row justify-between">
                  {Array.from({ length: CODE_LENGTH }).map((_, index) => {
                    const digit = code[index];
                    const isActive = codeFocused && index === Math.min(code.length, CODE_LENGTH - 1);
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
                ref={codeInputRef}
                value={code}
                onChangeText={(v) => setCode(v.replace(/\D/g, '').slice(0, CODE_LENGTH))}
                onFocus={() => setCodeFocused(true)}
                onBlur={() => setCodeFocused(false)}
                keyboardType="number-pad"
                maxLength={CODE_LENGTH}
                textContentType="oneTimeCode"
                autoComplete="sms-otp"
                caretHidden
                style={{ position: 'absolute', opacity: 0, height: 1, width: 1 }}
              />
              {fieldErrors.code ? (
                <Text className="mt-2 text-center text-xs text-danger">{fieldErrors.code}</Text>
              ) : null}

              <View className="mt-4 flex-row items-center justify-center">
                <ShieldCheck size={14} color={isExpired ? colors.semantic.danger : colors.gold[600]} />
                {isExpired ? (
                  <Text className="ml-2 text-xs font-semibold text-danger">
                    {t('forgotPassword.codeExpired')}
                  </Text>
                ) : (
                  <Text className="ml-2 text-xs text-navy-secondary">
                    {t('forgotPassword.codeExpiresIn')}{' '}
                    <Text className="font-bold text-gold-600">
                      {formatCountdown(expirySeconds, true)}
                    </Text>
                  </Text>
                )}
              </View>

              <View className="mt-5">
                <TextField
                  label={t('forgotPassword.newPassword')}
                  placeholder={t('forgotPassword.newPasswordPlaceholder')}
                  icon={Lock}
                  secureToggle
                  secureTextEntry
                  textContentType="newPassword"
                  value={newPassword}
                  onChangeText={setNewPassword}
                  error={fieldErrors.newPassword}
                />
                <TextField
                  label={t('forgotPassword.confirmPassword')}
                  placeholder={t('forgotPassword.confirmPasswordPlaceholder')}
                  icon={Lock}
                  secureToggle
                  secureTextEntry
                  textContentType="newPassword"
                  value={confirmPassword}
                  onChangeText={setConfirmPassword}
                  error={fieldErrors.confirmPassword}
                />
              </View>

              {apiError ? (
                <Text className="mb-1 mt-1 text-center text-sm text-danger">{apiError}</Text>
              ) : null}

              <View className="mt-2">
                <Button
                  label={t('forgotPassword.resetPassword')}
                  onPress={handleResetPassword}
                  loading={resetPassword.isPending}
                  disabled={code.length !== CODE_LENGTH || isExpired}
                />
              </View>

              <Pressable
                onPress={handleResend}
                disabled={resendCooldown > 0 || resending}
                className="mt-3 h-14 flex-row items-center justify-center rounded-2xl border border-gold-500 bg-transparent px-4"
                style={{ opacity: resendCooldown > 0 || resending ? 0.6 : 1 }}
              >
                <Text className="text-base font-semibold text-gold-600">
                  {t('forgotPassword.resendCode')}
                </Text>
                {resendCooldown > 0 ? (
                  <Text className="ml-2 text-sm font-semibold text-gold-600">
                    ({formatCountdown(resendCooldown, false)})
                  </Text>
                ) : null}
              </Pressable>
              {resendError ? (
                <Text className="mt-2 text-center text-sm text-danger">{resendError}</Text>
              ) : null}

              <Pressable onPress={goToEmailStep} className="mt-5 flex-row items-center justify-center">
                <Text className="text-sm font-semibold text-gold-500">
                  {t('forgotPassword.changeEmail')}
                </Text>
                <ChevronRight size={16} color={colors.gold[500]} style={{ marginLeft: 2 }} />
              </Pressable>
            </View>
          </>
        ) : null}

        {step === 'success' ? (
          <View className="mt-6 items-center">
            <View
              className="h-20 w-20 items-center justify-center rounded-full"
              style={{ backgroundColor: colors.semantic.successSurface }}
            >
              <CheckCircle2 size={40} color={colors.semantic.success} />
            </View>
            <Text className="mt-6 text-center text-2xl font-extrabold text-navy-text">
              {t('forgotPassword.successTitle')}
            </Text>
            <Text className="mt-2 text-center text-sm leading-5 text-navy-secondary">
              {t('forgotPassword.successBody')}
            </Text>
            <View className="mt-8 w-full">
              <Button
                label={t('forgotPassword.backToSignIn')}
                onPress={() => router.replace('/(auth)/login')}
              />
            </View>
          </View>
        ) : null}
      </ScrollView>
    </Screen>
  );
}
