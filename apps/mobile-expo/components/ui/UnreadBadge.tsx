import { Text, View } from 'react-native';
import { colors } from '../../theme/tokens';

interface UnreadBadgeProps {
  count: number;
  /** Counts above this render as "N+" so the pill can't grow unbounded. */
  max?: number;
  /** Accessibility label, e.g. "3 unread notifications". */
  label?: string;
}

/**
 * Small count pill for notification entry points. Renders nothing at 0, so
 * callers can pass a count straight through without a guard — including the
 * 0 that `useUnreadNotificationCount` returns while loading or on error.
 */
export function UnreadBadge({ count, max = 99, label }: UnreadBadgeProps) {
  if (count <= 0) return null;

  const text = count > max ? `${max}+` : String(count);

  return (
    <View
      accessibilityLabel={label}
      className="absolute -right-2 -top-1.5 h-[18px] min-w-[18px] items-center justify-center rounded-full px-1"
      style={{
        backgroundColor: colors.semantic.danger,
        borderWidth: 1.5,
        borderColor: colors.cream[100],
      }}
    >
      <Text className="text-[10px] font-bold text-white" style={{ lineHeight: 12 }}>
        {text}
      </Text>
    </View>
  );
}
