import { useState } from 'react';
import { Pressable, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  Apple,
  Chrome,
  Check,
  Lock,
  Mail,
  ShieldCheck,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { TextField } from '../../components/ui/TextField';
import { Logo } from '../../components/ui/Logo';
import { useAuthStore } from '../../lib/store/auth';
import { colors } from '../../theme/tokens';

export default function LoginScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const loginWithEmail = useAuthStore((s) => s.loginWithEmail);
  const storeError = useAuthStore((s) => s.error);

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [rememberMe, setRememberMe] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<{ email?: string; password?: string }>({});

  const handleSignIn = async () => {
    const errors: typeof fieldErrors = {};
    if (!email.trim()) errors.email = t('auth.invalidEmail');
    if (!password) errors.password = t('auth.invalidPassword');
    setFieldErrors(errors);
    if (Object.keys(errors).length > 0) return;

    setSubmitting(true);
    try {
      await loginWithEmail(email.trim(), password);
    } catch {
      // storeError already reflects the failure; nothing further to do here.
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
        <Logo size={80} />
      </View>

      <Text className="text-center text-2xl font-extrabold text-navy-text">
        {t('auth.welcomeBack')}
      </Text>
      <Text className="mt-2 text-center text-sm text-navy-secondary">
        {t('auth.signInSubtitle')}
      </Text>

      <View className="mt-6">
        <TextField
          label={t('auth.emailOrHealthId')}
          placeholder={t('auth.emailPlaceholder')}
          icon={Mail}
          autoCapitalize="none"
          keyboardType="email-address"
          value={email}
          onChangeText={setEmail}
          error={fieldErrors.email ?? (storeError ? ' ' : undefined)}
        />

        <View className="mb-2 flex-row items-center justify-between">
          <Text className="text-sm font-semibold text-navy-text">{t('auth.password')}</Text>
          <Text className="text-sm font-semibold text-gold-500">{t('auth.forgotPassword')}</Text>
        </View>
        <TextField
          placeholder={t('auth.passwordPlaceholder')}
          icon={Lock}
          secureToggle
          secureTextEntry
          value={password}
          onChangeText={setPassword}
          error={fieldErrors.password}
        />

        <Pressable
          onPress={() => setRememberMe((v) => !v)}
          className="mb-5 flex-row items-center"
        >
          <View
            className="mr-2 h-5 w-5 items-center justify-center rounded border"
            style={{
              borderColor: colors.gold[500],
              backgroundColor: rememberMe ? colors.gold[500] : 'transparent',
            }}
          >
            {rememberMe && <Check size={13} color="white" />}
          </View>
          <Text className="text-sm text-navy-secondary">{t('auth.rememberMe')}</Text>
        </Pressable>

        {storeError ? (
          <Text className="mb-3 text-center text-sm text-danger">{storeError}</Text>
        ) : null}

        <Button label={t('auth.signIn')} onPress={handleSignIn} loading={submitting} />

        <View className="my-6 flex-row items-center">
          <View className="h-px flex-1 bg-cream-300" />
          <Text className="mx-3 text-xs font-semibold text-navy-muted">{t('auth.or')}</Text>
          <View className="h-px flex-1 bg-cream-300" />
        </View>

        <SocialButton icon={Chrome} label={t('auth.continueWithGoogle')} />
        <View className="h-3" />
        <SocialButton icon={Apple} label={t('auth.continueWithApple')} />
        <View className="h-3" />
        <SocialButton
          icon={ShieldCheck}
          label={t('auth.signInWithHealthId')}
          badge={t('auth.recommended')}
          onPress={() => router.push('/(auth)/otp')}
        />

        <View className="mt-5 flex-row items-start rounded-2xl bg-gold-50 p-4">
          <ShieldCheck size={16} color={colors.gold[600]} />
          <Text className="ml-3 flex-1 text-xs text-navy-secondary">{t('auth.encryptionNote')}</Text>
        </View>

        <View className="mt-6 flex-row justify-center pb-4">
          <Text className="text-sm text-navy-secondary">{t('auth.noAccount')} </Text>
          <Text className="text-sm font-semibold text-gold-500">{t('auth.createAccount')}</Text>
        </View>
      </View>
    </Screen>
  );
}

function SocialButton({
  icon: Icon,
  label,
  badge,
  onPress,
}: {
  icon: typeof Chrome;
  label: string;
  badge?: string;
  onPress?: () => void;
}) {
  return (
    <Pressable
      onPress={onPress}
      className="h-14 flex-row items-center rounded-2xl border border-cream-300 bg-white px-4"
    >
      <Icon size={18} color={colors.navy.text} />
      <Text className="ml-3 flex-1 text-sm font-semibold text-navy-text">{label}</Text>
      {badge ? (
        <View className="rounded-full bg-gold-50 px-2 py-1">
          <Text className="text-[10px] font-semibold text-gold-600">{badge}</Text>
        </View>
      ) : null}
    </Pressable>
  );
}
