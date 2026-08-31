import { Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import { ShieldCheck, UserRound } from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { Logo } from '../../components/ui/Logo';
import { colors } from '../../theme/tokens';

export default function WelcomeScreen() {
  const { t } = useTranslation();
  const router = useRouter();

  return (
    <Screen className="justify-between py-8">
      <View className="items-center pt-6">
        <Logo />
      </View>

      <View>
        <Text className="text-3xl font-extrabold leading-tight text-navy-text">
          {t('auth.welcomeHeadline1')}
        </Text>
        <Text className="text-3xl font-extrabold leading-tight text-navy-text">
          {t('auth.welcomeHeadline2')}
        </Text>
        <Text className="text-3xl font-extrabold leading-tight text-gold-500">
          {t('auth.welcomeHeadline3')}
        </Text>
        <View className="my-3 h-1 w-12 rounded-full bg-gold-500" />
        <Text className="text-base text-navy-secondary">{t('auth.welcomeBody')}</Text>
      </View>

      <View>
        <Button
          label={t('auth.getStarted')}
          onPress={() => router.push('/(auth)/login')}
        />
        <View className="h-3" />
        <Button
          label={t('auth.haveAccount')}
          variant="outline"
          leftIcon={UserRound}
          onPress={() => router.push('/(auth)/login')}
        />
        <View className="mt-5 flex-row items-center justify-center">
          <ShieldCheck size={14} color={colors.gold[600]} />
          <Text className="ml-2 text-center text-xs text-navy-muted">{t('auth.securityNote')}</Text>
        </View>
      </View>
    </Screen>
  );
}
