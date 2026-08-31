import { View, type ViewProps } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

/** Base screen wrapper: cream background + safe-area padding, used by every screen. */
export function Screen({ children, className, ...props }: ViewProps & { className?: string }) {
  return (
    <SafeAreaView className="flex-1 bg-cream-100" edges={['top', 'bottom']}>
      <View className={`flex-1 px-6 ${className ?? ''}`} {...props}>
        {children}
      </View>
    </SafeAreaView>
  );
}
