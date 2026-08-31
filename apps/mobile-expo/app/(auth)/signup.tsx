import { useState } from 'react';
import { Pressable, ScrollView, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  Calendar,
  Check,
  Lock,
  Mail,
  Phone,
  User,
  UserRound,
  Users,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { TextField } from '../../components/ui/TextField';
import { Logo } from '../../components/ui/Logo';
import { useAuthStore } from '../../lib/store/auth';
import { tokenStorage } from '../../lib/api/tokenStorage';
import { useRegisterPatient } from '../../lib/api/queries';
import type { ApiValidationErrorBody, RegisterPatientPayload } from '../../lib/api/types';
import { colors } from '../../theme/tokens';

type Sex = 'male' | 'female' | 'other' | 'unknown';
type StepNumber = 1 | 2 | 3;
const TOTAL_STEPS = 3;

/** Maps a server validation field name back to the step that collects it, so a
 * late (post-submit) server error — e.g. "email already taken" surfacing only
 * after the final POST — sends the patient back to the right step instead of
 * failing silently on step 3. */
const FIELD_STEP: Record<string, StepNumber> = {
  first_name: 1,
  last_name: 1,
  dob: 1,
  sex: 1,
  phone: 2,
  email: 2,
  password: 2,
  emergency_name: 3,
  emergency_relationship: 3,
  emergency_phone: 3,
};

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

/** Turns raw digit entry into "YYYY-MM-DD" as the patient types, without a
 * date-picker dependency this screen isn't allowed to add. */
function formatDobInput(raw: string): string {
  const digits = raw.replace(/\D/g, '').slice(0, 8);
  const y = digits.slice(0, 4);
  const m = digits.slice(4, 6);
  const d = digits.slice(6, 8);
  return [y, m, d].filter(Boolean).join('-');
}

function isValidPastDate(value: string): boolean {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return false;
  const [y, m, d] = value.split('-').map(Number);
  const date = new Date(Date.UTC(y, m - 1, d));
  if (date.getUTCFullYear() !== y || date.getUTCMonth() !== m - 1 || date.getUTCDate() !== d) {
    return false;
  }
  const today = new Date();
  const todayUtc = Date.UTC(today.getFullYear(), today.getMonth(), today.getDate());
  return date.getTime() < todayUtc;
}

function extractErrorMessage(err: unknown): { fieldErrors?: Record<string, string>; banner?: string } {
  const anyErr = err as { response?: { status?: number; data?: ApiValidationErrorBody } };
  const status = anyErr?.response?.status;
  const data = anyErr?.response?.data;

  if (status === 422 && data?.errors) {
    const fieldErrors: Record<string, string> = {};
    for (const key of Object.keys(data.errors)) {
      fieldErrors[key] = data.errors[key][0];
    }
    return { fieldErrors };
  }

  return { banner: data?.message };
}

/** Sign Up / Create Account — native registration flow (POST /mobile/auth/register).
 * Split into three short steps (personal / contact+security / emergency contact)
 * rather than one long form, matching the mobile app's other multi-part auth
 * screen (otp.tsx). Reached from login.tsx's "Create account" link. */
export default function SignupScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const registerPatient = useRegisterPatient();

  const [step, setStep] = useState<StepNumber>(1);

  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [dob, setDob] = useState('');
  const [sex, setSex] = useState<Sex | null>(null);

  const [phone, setPhone] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');

  const [emergencyName, setEmergencyName] = useState('');
  const [emergencyRelationship, setEmergencyRelationship] = useState('');
  const [emergencyPhone, setEmergencyPhone] = useState('');

  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [bannerError, setBannerError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  const clearFieldError = (key: string) => {
    setFieldErrors((prev) => {
      if (!(key in prev)) return prev;
      const next = { ...prev };
      delete next[key];
      return next;
    });
  };

  const validateStep1 = (): boolean => {
    const errors: Record<string, string> = {};
    if (!firstName.trim()) errors.first_name = t('signup.errors.firstNameRequired');
    if (!lastName.trim()) errors.last_name = t('signup.errors.lastNameRequired');
    if (!dob) errors.dob = t('signup.errors.dobRequired');
    else if (!isValidPastDate(dob)) {
      errors.dob = /^\d{4}-\d{2}-\d{2}$/.test(dob)
        ? t('signup.errors.dobFuture')
        : t('signup.errors.dobInvalid');
    }
    if (!sex) errors.sex = t('signup.errors.sexRequired');
    setFieldErrors((prev) => ({ ...prev, ...errors }));
    return Object.keys(errors).length === 0;
  };

  const validateStep2 = (): boolean => {
    const errors: Record<string, string> = {};
    if (!phone.trim()) errors.phone = t('signup.errors.phoneRequired');
    if (email.trim() && !EMAIL_RE.test(email.trim())) errors.email = t('signup.errors.emailInvalid');
    if (password.length < 8) errors.password = t('signup.errors.passwordTooShort');
    else if (confirmPassword !== password) errors.password = t('signup.errors.passwordMismatch');
    setFieldErrors((prev) => ({ ...prev, ...errors }));
    return Object.keys(errors).length === 0;
  };

  const validateStep3 = (): boolean => {
    const errors: Record<string, string> = {};
    if (!emergencyName.trim()) errors.emergency_name = t('signup.errors.emergencyNameRequired');
    if (!emergencyRelationship.trim()) {
      errors.emergency_relationship = t('signup.errors.emergencyRelationshipRequired');
    }
    if (!emergencyPhone.trim()) errors.emergency_phone = t('signup.errors.emergencyPhoneRequired');
    setFieldErrors((prev) => ({ ...prev, ...errors }));
    return Object.keys(errors).length === 0;
  };

  const goNext = () => {
    setBannerError(null);
    const ok = step === 1 ? validateStep1() : validateStep2();
    if (ok) setStep((s) => (s + 1) as StepNumber);
  };

  const goBack = () => {
    setBannerError(null);
    if (step === 1) {
      router.back();
    } else {
      setStep((s) => (s - 1) as StepNumber);
    }
  };

  const handleSubmit = async () => {
    setBannerError(null);
    if (!validateStep3()) return;

    const payload: RegisterPatientPayload = {
      first_name: firstName.trim(),
      last_name: lastName.trim(),
      dob,
      sex: sex as Sex,
      phone: phone.trim(),
      ...(email.trim() ? { email: email.trim() } : {}),
      emergency_name: emergencyName.trim(),
      emergency_relationship: emergencyRelationship.trim(),
      emergency_phone: emergencyPhone.trim(),
      password,
      password_confirmation: confirmPassword,
    };

    setSubmitting(true);
    try {
      const data = await registerPatient.mutateAsync(payload);
      await tokenStorage.set(data.access_token);
      await useAuthStore.getState().fetchMe();
      useAuthStore.setState({ status: 'authenticated' });
      // Root layout redirects to /(tabs)/home once status flips to authenticated.
    } catch (err) {
      const { fieldErrors: serverFieldErrors, banner } = extractErrorMessage(err);
      if (serverFieldErrors) {
        setFieldErrors((prev) => ({ ...prev, ...serverFieldErrors }));
        const erroredSteps = Object.keys(serverFieldErrors).map((k) => FIELD_STEP[k] ?? 3);
        setStep(Math.min(...erroredSteps) as StepNumber);
      } else {
        setBannerError(banner ?? t('signup.genericError'));
      }
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <Screen className="px-0">
      <ScrollView
        className="flex-1 px-6"
        showsVerticalScrollIndicator={false}
        keyboardShouldPersistTaps="handled"
        contentContainerStyle={{ paddingBottom: 24 }}
      >
        <View className="mt-2 h-11 w-11 items-center justify-center rounded-full border border-gold-300">
          <Pressable onPress={goBack} hitSlop={8}>
            <ArrowLeft size={18} color={colors.gold[600]} />
          </Pressable>
        </View>

        <View className="items-center py-4">
          <Logo size={72} markOnly />
        </View>

        <Text className="text-center text-2xl font-extrabold text-navy-text">
          {t('signup.title')}
        </Text>
        <Text className="mt-2 text-center text-sm text-navy-secondary">
          {t('signup.subtitle')}
        </Text>

        <StepProgress step={step} />

        <View className="mt-5">
          <Text className="text-lg font-bold text-navy-text">
            {t(`signup.step${step}Title` as const)}
          </Text>
          <Text className="mt-1 text-sm text-navy-secondary">
            {t(`signup.step${step}Subtitle` as const)}
          </Text>
        </View>

        <View className="mt-5">
          {step === 1 ? (
            <>
              <TextField
                label={t('signup.firstName')}
                placeholder={t('signup.firstNamePlaceholder')}
                icon={User}
                value={firstName}
                onChangeText={(v) => {
                  setFirstName(v);
                  clearFieldError('first_name');
                }}
                error={fieldErrors.first_name}
              />
              <TextField
                label={t('signup.lastName')}
                placeholder={t('signup.lastNamePlaceholder')}
                icon={User}
                value={lastName}
                onChangeText={(v) => {
                  setLastName(v);
                  clearFieldError('last_name');
                }}
                error={fieldErrors.last_name}
              />
              <TextField
                label={t('signup.dateOfBirth')}
                placeholder={t('signup.dateOfBirthPlaceholder')}
                icon={Calendar}
                keyboardType="number-pad"
                maxLength={10}
                value={dob}
                onChangeText={(v) => {
                  setDob(formatDobInput(v));
                  clearFieldError('dob');
                }}
                error={fieldErrors.dob}
              />
              <SexPicker
                value={sex}
                onChange={(v) => {
                  setSex(v);
                  clearFieldError('sex');
                }}
                error={fieldErrors.sex}
              />
            </>
          ) : null}

          {step === 2 ? (
            <>
              <TextField
                label={t('auth.phoneNumber')}
                placeholder={t('auth.phoneNumberPlaceholder')}
                icon={Phone}
                keyboardType="phone-pad"
                textContentType="telephoneNumber"
                value={phone}
                onChangeText={(v) => {
                  setPhone(v);
                  clearFieldError('phone');
                }}
                error={fieldErrors.phone}
              />
              <TextField
                label={t('signup.email')}
                placeholder={t('signup.emailPlaceholder')}
                icon={Mail}
                autoCapitalize="none"
                keyboardType="email-address"
                textContentType="emailAddress"
                value={email}
                onChangeText={(v) => {
                  setEmail(v);
                  clearFieldError('email');
                }}
                error={fieldErrors.email}
              />
              <TextField
                label={t('auth.password')}
                placeholder={t('auth.passwordPlaceholder')}
                icon={Lock}
                secureToggle
                secureTextEntry
                textContentType="newPassword"
                value={password}
                onChangeText={(v) => {
                  setPassword(v);
                  clearFieldError('password');
                }}
                error={fieldErrors.password}
              />
              <TextField
                label={t('signup.confirmPassword')}
                placeholder={t('signup.confirmPasswordPlaceholder')}
                icon={Lock}
                secureToggle
                secureTextEntry
                textContentType="newPassword"
                value={confirmPassword}
                onChangeText={(v) => {
                  setConfirmPassword(v);
                  clearFieldError('password');
                }}
              />
            </>
          ) : null}

          {step === 3 ? (
            <>
              <TextField
                label={t('signup.emergencyName')}
                placeholder={t('signup.emergencyNamePlaceholder')}
                icon={UserRound}
                value={emergencyName}
                onChangeText={(v) => {
                  setEmergencyName(v);
                  clearFieldError('emergency_name');
                }}
                error={fieldErrors.emergency_name}
              />
              <TextField
                label={t('signup.emergencyRelationship')}
                placeholder={t('signup.emergencyRelationshipPlaceholder')}
                icon={Users}
                value={emergencyRelationship}
                onChangeText={(v) => {
                  setEmergencyRelationship(v);
                  clearFieldError('emergency_relationship');
                }}
                error={fieldErrors.emergency_relationship}
              />
              <TextField
                label={t('signup.emergencyPhone')}
                placeholder={t('signup.emergencyPhonePlaceholder')}
                icon={Phone}
                keyboardType="phone-pad"
                value={emergencyPhone}
                onChangeText={(v) => {
                  setEmergencyPhone(v);
                  clearFieldError('emergency_phone');
                }}
                error={fieldErrors.emergency_phone}
              />
            </>
          ) : null}

          {bannerError ? (
            <Text className="mb-3 text-center text-sm text-danger">{bannerError}</Text>
          ) : null}

          {step < 3 ? (
            <Button label={t('signup.continue')} onPress={goNext} />
          ) : (
            <Button label={t('signup.createAccount')} onPress={handleSubmit} loading={submitting} />
          )}

          {step === 1 ? (
            <View className="mt-6 flex-row items-center justify-center pb-4">
              <Text className="text-sm text-navy-secondary">{t('signup.alreadyHaveAccount')} </Text>
              <Pressable onPress={() => router.push('/(auth)/login')}>
                <Text className="text-sm font-semibold text-gold-500">{t('auth.signIn')}</Text>
              </Pressable>
            </View>
          ) : null}
        </View>
      </ScrollView>
    </Screen>
  );
}

/** Three-segment progress bar + "Step X of 3" label above the active step's form. */
function StepProgress({ step }: { step: StepNumber }) {
  const { t } = useTranslation();
  return (
    <View className="mt-6">
      <View className="flex-row" style={{ gap: 6 }}>
        {Array.from({ length: TOTAL_STEPS }).map((_, index) => (
          <View
            key={index}
            className="h-1.5 flex-1 rounded-full"
            style={{ backgroundColor: index < step ? colors.gold[500] : colors.cream[300] }}
          />
        ))}
      </View>
      <Text className="mt-2 text-xs font-semibold text-navy-muted">
        {t('signup.stepIndicator', { current: step, total: TOTAL_STEPS })}
      </Text>
    </View>
  );
}

function SexPicker({
  value,
  onChange,
  error,
}: {
  value: Sex | null;
  onChange: (v: Sex) => void;
  error?: string;
}) {
  const { t } = useTranslation();
  const options: { value: Sex; label: string }[] = [
    { value: 'male', label: t('signup.sexMale') },
    { value: 'female', label: t('signup.sexFemale') },
    { value: 'other', label: t('signup.sexOther') },
    { value: 'unknown', label: t('signup.sexUnknown') },
  ];

  return (
    <View className="mb-4">
      <Text className="mb-2 text-sm font-semibold text-navy-text">{t('signup.sex')}</Text>
      <View className="flex-row flex-wrap" style={{ gap: 8 }}>
        {options.map((opt) => {
          const selected = value === opt.value;
          return (
            <Pressable
              key={opt.value}
              onPress={() => onChange(opt.value)}
              className="flex-row items-center rounded-2xl border px-4 py-3"
              style={{
                borderColor: selected ? colors.gold[500] : colors.cream[300],
                backgroundColor: selected ? colors.gold[50] : colors.white,
              }}
            >
              {selected ? (
                <Check size={14} color={colors.gold[600]} style={{ marginRight: 6 }} />
              ) : null}
              <Text
                className="text-sm font-semibold"
                style={{ color: selected ? colors.gold[600] : colors.navy.text }}
              >
                {opt.label}
              </Text>
            </Pressable>
          );
        })}
      </View>
      {error ? <Text className="mt-1 text-xs text-danger">{error}</Text> : null}
    </View>
  );
}
