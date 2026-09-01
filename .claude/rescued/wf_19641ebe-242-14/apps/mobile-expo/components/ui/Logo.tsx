import { Text, View } from 'react-native';
import { HeartPulse } from 'lucide-react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { useTranslation } from 'react-i18next';
import { colors } from '../../theme/tokens';

/** Heartbeat-in-ring mark + "OpesCare" wordmark + tagline — the recurring brand
 * header seen on splash/onboarding/login. `size` scales the ring; wordmark
 * and tagline are hidden via `markOnly` for the splash screen variant. */
export function Logo({ size = 96, markOnly = false }: { size?: number; markOnly?: boolean }) {
  const { t } = useTranslation();
  return (
    <View className="items-center">
      <LinearGradient
        colors={[colors.gold[300], colors.gold[500], colors.gold[700]]}
        style={{
          width: size,
          height: size,
          borderRadius: size / 2,
          alignItems: 'center',
          justifyContent: 'center',
        }}
      >
        <View
          style={{
            width: size * 0.68,
            height: size * 0.68,
            borderRadius: (size * 0.68) / 2,
            backgroundColor: colors.cream[50],
            alignItems: 'center',
            justifyContent: 'center',
          }}
        >
          <HeartPulse color={colors.gold[500]} size={size * 0.32} />
        </View>
      </LinearGradient>

      {!markOnly && (
        <>
          <View className="mt-4 flex-row">
            <Text className="text-3xl font-extrabold text-navy-text">Opes</Text>
            <Text className="text-3xl font-extrabold text-gold-500">Care</Text>
          </View>
          <Text className="mt-1 text-sm text-navy-secondary">{t('auth.tagline')}</Text>
        </>
      )}
    </View>
  );
}
