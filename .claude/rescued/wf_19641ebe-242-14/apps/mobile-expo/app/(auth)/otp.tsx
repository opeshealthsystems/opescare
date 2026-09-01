import { useState } from 'react';
import { Pressable, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import { ArrowLeft, Phone, KeyRound } from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { TextField } from '../../components/ui/TextField';
import { Logo } from '../../components/ui/Logo';
import { useAuthStore } from '../../lib/store/auth';
import { colors } from '../../theme/tokens';

/** Legacy phone + PIN -> OTP flow, reached via "Sign in with Health ID" on the
 * login screen. Two steps in one screen, gated on auth store status — mirrors
 * apps/mobile-patient's login_screen.dart + otp_screen.dart split. */
export default function OtpScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const status = useAuthStore((s) => s.status);
  const storeError = useAuthStore((s) => s.error);
  const loginWithPhone = useAuthStore((s) => s.loginWithPhone);
  const verifyOtp = useAuthStore((s) => s.verifyOtp);
  const resendOtp = useAuthStore((s) => s.resendOtp);
  const pendingPhone = useAuthStore((s) => s.pendingPhone);

  const [phoneNumber, setPhoneNumber] = useState('');
  const [pin, setPin] = useState('');
  const [otp, setOtp] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const step: 'phone' | 'otp' = status === 'otp_pending' ? 'otp' : 'phone';

  const submitPhone = async () => {
    if (!phoneNumber.trim() || !pin.trim()) return;
    setSubmitting(true);
    try {
      await loginWithPhone(phoneNumber.trim(), pin.trim());
    } catch {
      // storeError reflects the failure
    } finally {
      setSubmitting(false);
    }
  };

  const submitOtp = async () => {
    if (otp.trim().length !== 6) return;
    setSubmitting(true);
    try {
      await verifyOtp(otp.trim());
    } catch {
      // storeError reflects the failure
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <Screen>
      <View className="mt-2 h-11 w-11 items-center justify-center rounded-full border border-gold-300">
        <Pressable onPress={() => router.back()} hitSlop={8}>
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
              label="Phone number"
              placeholder="+237 6XX XXX XXX"
              icon={Phone}
              keyboardType="phone-pad"
              value={phoneNumber}
              onChangeText={setPhoneNumber}
            />
            <TextField
              label="PIN"
              placeholder="••••••"
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
          <Text className="mt-2 text-center text-sm text-navy-secondary">
            {t('auth.otpSubtitle', { phone: pendingPhone })}
          </Text>
          <View className="mt-6">
            <TextField
              placeholder="000000"
              keyboardType="number-pad"
              maxLength={6}
              value={otp}
              onChangeText={setOtp}
            />
            {storeError ? (
              <Text className="mb-3 text-center text-sm text-danger">{storeError}</Text>
            ) : null}
            <Button label={t('auth.verify')} onPress={submitOtp} loading={submitting} />
            <Pressable onPress={() => resendOtp()} className="mt-4">
              <Text className="text-center text-sm font-semibold text-gold-500">
                {t('auth.otpResend')}
              </Text>
            </Pressable>
          </View>
        </>
      )}
    </Screen>
  );
}
