import { Pressable, Text, View } from 'react-native';
import { Screen } from '../../components/ui/Screen';
import { useAuthStore } from '../../lib/store/auth';
import { colors } from '../../theme/tokens';

export default function ProfileScreen() {
  const patient = useAuthStore((s) => s.patient);
  const logout = useAuthStore((s) => s.logout);

  return (
    <Screen>
      <View className="mt-8 items-center">
        <View className="h-20 w-20 items-center justify-center rounded-full bg-gold-100">
          <Text className="text-2xl font-bold text-gold-600">{patient?.first_name?.[0] ?? '?'}</Text>
        </View>
        <Text className="mt-3 text-lg font-bold text-navy-text">{patient?.display_name ?? '—'}</Text>
        <Text className="text-sm text-navy-secondary">{patient?.email ?? patient?.phone ?? ''}</Text>
      </View>

      <Pressable
        onPress={() => logout()}
        className="mt-10 h-14 items-center justify-center rounded-2xl border border-danger"
      >
        <Text className="font-semibold" style={{ color: colors.semantic.danger }}>
          Sign out
        </Text>
      </Pressable>
    </Screen>
  );
}
