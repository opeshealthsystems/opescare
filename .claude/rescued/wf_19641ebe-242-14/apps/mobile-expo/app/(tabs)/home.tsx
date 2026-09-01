import { ActivityIndicator, Pressable, ScrollView, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import {
  Calendar,
  ClipboardPlus,
  HeartPulse,
  Menu,
  Bell,
  Pill,
  Plus,
  ShieldCheck,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { useAuthStore } from '../../lib/store/auth';
import { useHealthIdCard, useUpcomingAppointments } from '../../lib/api/queries';
import { colors } from '../../theme/tokens';

export default function HomeScreen() {
  const patient = useAuthStore((s) => s.patient);
  const router = useRouter();
  const healthId = useHealthIdCard();
  const appointments = useUpcomingAppointments();
  const nextAppointment = appointments.data?.data?.[0];

  return (
    <Screen className="px-0">
      <ScrollView className="flex-1 px-6" showsVerticalScrollIndicator={false}>
        <View className="mt-2 flex-row items-center justify-between">
          <Menu size={22} color={colors.gold[600]} />
          <View className="flex-row items-center">
            <Text className="text-lg font-extrabold text-navy-text">Opes</Text>
            <Text className="text-lg font-extrabold text-gold-500">Care</Text>
          </View>
          <View className="flex-row items-center gap-4">
            <Bell size={20} color={colors.navy.text} />
            <View className="h-9 w-9 items-center justify-center rounded-full bg-gold-100">
              <Text className="font-bold text-gold-600">
                {patient?.first_name?.[0] ?? '?'}
              </Text>
            </View>
          </View>
        </View>

        <View className="mt-6">
          <Text className="text-2xl font-extrabold text-navy-text">
            Welcome back, <Text className="text-gold-500">{patient?.first_name ?? '...'}</Text> 👋
          </Text>
          <Text className="mt-1 text-sm text-navy-secondary">
            Here&apos;s your health overview for today.
          </Text>
        </View>

        <View className="mt-6 flex-row justify-between rounded-2xl bg-white p-4">
          <QuickAction icon={Calendar} label="Book Appointment" onPress={() => router.push('/(tabs)/records')} />
          <QuickAction icon={ClipboardPlus} label="Health Records" onPress={() => router.push('/(tabs)/records')} />
          <QuickAction icon={HeartPulse} label="Health Check" onPress={() => {}} />
          <QuickAction icon={Pill} label="Prescriptions" onPress={() => {}} />
          <QuickAction icon={Plus} label="More" onPress={() => {}} />
        </View>

        <View className="mt-4 rounded-2xl bg-gold-500 p-5">
          <View className="flex-row items-center justify-between">
            <Text className="text-sm font-semibold text-white/90">OpesCare Health ID</Text>
            <ShieldCheck size={18} color="white" />
          </View>
          {healthId.isLoading ? (
            <ActivityIndicator color="white" className="mt-4" />
          ) : (
            <>
              <Text className="mt-2 text-xl font-extrabold text-white">
                {healthId.data?.health_id ?? '—'}
              </Text>
              <View className="mt-4 flex-row justify-between">
                <VitalStat label="Blood Group" value={healthId.data?.blood_type ?? '—'} light />
                <VitalStat label="Date of Birth" value={healthId.data?.dob ?? '—'} light />
              </View>
            </>
          )}
        </View>

        <Text className="mb-3 mt-6 text-base font-bold text-navy-text">Upcoming Appointment</Text>
        <View className="rounded-2xl bg-white p-4">
          {appointments.isLoading ? (
            <ActivityIndicator color={colors.gold[500]} />
          ) : nextAppointment ? (
            <>
              <Text className="text-base font-semibold text-navy-text">
                {nextAppointment.provider_name ?? nextAppointment.appointment_type}
              </Text>
              <Text className="mt-1 text-sm text-navy-secondary">{nextAppointment.facility_name}</Text>
              <Text className="mt-1 text-sm text-navy-muted">
                {nextAppointment.scheduled_at
                  ? new Date(nextAppointment.scheduled_at).toLocaleString()
                  : ''}
              </Text>
            </>
          ) : (
            <Text className="text-sm text-navy-muted">No upcoming appointments.</Text>
          )}
        </View>

        <View className="h-10" />
      </ScrollView>
    </Screen>
  );
}

function QuickAction({
  icon: Icon,
  label,
  onPress,
}: {
  icon: typeof Calendar;
  label: string;
  onPress: () => void;
}) {
  return (
    <Pressable onPress={onPress} className="items-center" style={{ width: 56 }}>
      <Icon size={20} color={colors.gold[600]} />
      <Text className="mt-2 text-center text-[10px] font-medium text-navy-secondary" numberOfLines={2}>
        {label}
      </Text>
    </Pressable>
  );
}

function VitalStat({ label, value, light }: { label: string; value: string; light?: boolean }) {
  return (
    <View>
      <Text className={light ? 'text-xs text-white/80' : 'text-xs text-navy-muted'}>{label}</Text>
      <Text className={light ? 'text-base font-bold text-white' : 'text-base font-bold text-navy-text'}>
        {value}
      </Text>
    </View>
  );
}
