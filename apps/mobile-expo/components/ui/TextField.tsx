import { useState } from 'react';
import { Text, TextInput, View, Pressable, type TextInputProps } from 'react-native';
import { Eye, EyeOff, type LucideIcon } from 'lucide-react-native';
import { colors } from '../../theme/tokens';

interface TextFieldProps extends TextInputProps {
  label?: string;
  icon?: LucideIcon;
  error?: string;
  secureToggle?: boolean;
}

/** Bordered input matching the login/onboarding reference: gold-tinted border,
 * rounded corners, optional leading icon, optional password-visibility toggle. */
export function TextField({ label, icon: Icon, error, secureToggle, secureTextEntry, ...props }: TextFieldProps) {
  const [hidden, setHidden] = useState(!!secureTextEntry);

  return (
    <View className="mb-4">
      {label ? (
        <Text className="mb-2 text-sm font-semibold text-navy-text">{label}</Text>
      ) : null}
      <View
        className="h-14 flex-row items-center rounded-2xl border bg-white px-4"
        style={{ borderColor: error ? colors.semantic.danger : colors.cream[300] }}
      >
        {Icon ? <Icon size={18} color={colors.navy.muted} style={{ marginRight: 8 }} /> : null}
        <TextInput
          className="flex-1 text-base text-navy-text"
          placeholderTextColor={colors.navy.muted}
          secureTextEntry={secureToggle ? hidden : secureTextEntry}
          {...props}
        />
        {secureToggle ? (
          <Pressable onPress={() => setHidden((h) => !h)} hitSlop={8}>
            {hidden ? <EyeOff size={18} color={colors.navy.muted} /> : <Eye size={18} color={colors.navy.muted} />}
          </Pressable>
        ) : null}
      </View>
      {error ? <Text className="mt-1 text-xs text-danger">{error}</Text> : null}
    </View>
  );
}
