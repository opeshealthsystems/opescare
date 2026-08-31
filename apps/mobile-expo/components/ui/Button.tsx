import { ActivityIndicator, Pressable, Text, View } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { ArrowRight, type LucideIcon } from 'lucide-react-native';
import { colors } from '../../theme/tokens';

type ButtonVariant = 'primary' | 'outline';

interface ButtonProps {
  label: string;
  onPress: () => void;
  variant?: ButtonVariant;
  loading?: boolean;
  disabled?: boolean;
  leftIcon?: LucideIcon;
  showChevron?: boolean;
}

/** Primary CTA — gold gradient, circular left icon badge, chevron right. Matches
 * the "Get Started" / "Sign In" buttons across the onboarding + login references. */
export function Button({
  label,
  onPress,
  variant = 'primary',
  loading = false,
  disabled = false,
  leftIcon: LeftIcon = ArrowRight,
  showChevron = true,
}: ButtonProps) {
  const isDisabled = disabled || loading;

  if (variant === 'outline') {
    return (
      <Pressable
        onPress={onPress}
        disabled={isDisabled}
        className="h-14 flex-row items-center justify-center rounded-2xl border border-gold-500 bg-transparent px-4"
        style={{ opacity: isDisabled ? 0.5 : 1 }}
      >
        <Text className="text-base font-semibold text-gold-600">{label}</Text>
      </Pressable>
    );
  }

  return (
    <Pressable onPress={onPress} disabled={isDisabled} style={{ opacity: isDisabled ? 0.6 : 1 }}>
      <LinearGradient
        colors={[colors.gold[600], colors.gold[500], colors.gold[300]]}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 0 }}
        style={{ borderRadius: 16, height: 56, alignItems: 'center' }}
        className="flex-row justify-between px-2"
      >
        <View className="h-10 w-10 items-center justify-center rounded-full bg-white/90">
          {loading ? (
            <ActivityIndicator color={colors.gold[600]} size="small" />
          ) : (
            <LeftIcon color={colors.gold[600]} size={18} />
          )}
        </View>
        <Text
          className="flex-1 text-center text-base font-bold text-white"
          style={{ lineHeight: 20 }}
        >
          {label}
        </Text>
        {showChevron ? (
          <View className="h-10 w-10 items-center justify-center">
            <ArrowRight color="white" size={18} />
          </View>
        ) : (
          <View className="h-10 w-10" />
        )}
      </LinearGradient>
    </Pressable>
  );
}
