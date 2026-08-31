import { Fragment } from 'react';
import { Pressable, ScrollView, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  Activity,
  AlertTriangle,
  BadgeCheck,
  Calendar,
  ChevronRight,
  Droplet,
  FileText,
  FolderOpen,
  LogOut,
  Mail,
  MessageCircle,
  Phone,
  QrCode,
  Settings as SettingsIcon,
  ShieldCheck,
  User,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { useAuthStore } from '../../lib/store/auth';
import { colors } from '../../theme/tokens';

/** Formats an ISO date string ("1985-03-14") into a locale-aware short date,
 * falling back to the not-provided copy when missing/unparsable. */
function formatDate(value: string | null, locale: string, fallback: string): string {
  if (!value) return fallback;
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return value;
  return parsed.toLocaleDateString(locale === 'fr' ? 'fr-FR' : 'en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

export default function ProfileScreen() {
  const { t, i18n } = useTranslation();
  const router = useRouter();
  const patient = useAuthStore((s) => s.patient);
  const logout = useAuthStore((s) => s.logout);

  const initials =
    `${patient?.first_name?.[0] ?? ''}${patient?.last_name?.[0] ?? ''}`.toUpperCase() || '?';

  const isActive = (patient?.status ?? 'active').toLowerCase() === 'active';
  const statusLabel = isActive
    ? t('profile.verified')
    : patient?.status
      ? patient.status.charAt(0).toUpperCase() + patient.status.slice(1)
      : t('profile.active');

  const genderLabel = (() => {
    switch (patient?.sex?.toLowerCase()) {
      case 'male':
        return t('profile.genderMale');
      case 'female':
        return t('profile.genderFemale');
      case null:
      case undefined:
      case '':
        return t('profile.notProvided');
      default:
        return t('profile.genderOther');
    }
  })();

  const menuItems: { icon: LucideIcon; label: string; onPress: () => void }[] = [
    {
      icon: QrCode,
      label: t('profile.healthIdCard'),
      onPress: () => router.push('/(tabs)/health-id'),
    },
    {
      icon: FileText,
      label: t('profile.recordsTimeline'),
      onPress: () => router.push('/(tabs)/records'),
    },
    {
      icon: FolderOpen,
      label: t('documents.title'),
      onPress: () => router.push('/documents'),
    },
    {
      icon: MessageCircle,
      label: t('profile.messages'),
      onPress: () => router.push('/(tabs)/messages'),
    },
    {
      icon: SettingsIcon,
      label: t('profile.settings'),
      onPress: () => router.push('/settings'),
    },
  ];

  return (
    <Screen className="px-0">
      <ScrollView className="flex-1 px-6" showsVerticalScrollIndicator={false}>
        <View className="mt-2">
          <Text className="text-2xl font-extrabold text-navy-text">{t('profile.title')}</Text>
          <Text className="mt-1 text-sm text-navy-secondary">{t('profile.subtitle')}</Text>
        </View>

        {/* Profile card */}
        <View className="mt-5 rounded-2xl bg-white p-5">
          <View className="flex-row items-center">
            <View className="h-20 w-20 items-center justify-center rounded-full bg-gold-100">
              <Text className="text-2xl font-bold text-gold-600">{initials}</Text>
            </View>
            <View className="ml-4 flex-1">
              <Text className="text-lg font-bold text-navy-text" numberOfLines={1}>
                {patient?.display_name ?? '—'}
              </Text>
              <View
                className="mt-2 flex-row items-center self-start rounded-full px-2 py-1"
                style={{
                  backgroundColor: isActive
                    ? colors.semantic.successSurface
                    : colors.semantic.warningSurface,
                }}
              >
                <BadgeCheck
                  size={12}
                  color={isActive ? colors.semantic.success : colors.semantic.warning}
                />
                <Text
                  className="ml-1 text-xs font-semibold"
                  style={{ color: isActive ? colors.semantic.success : colors.semantic.warning }}
                >
                  {statusLabel}
                </Text>
              </View>
            </View>
          </View>

          <View className="mt-4 border-t border-cream-300 pt-4">
            {patient?.email ? (
              <View className="mb-2 flex-row items-center">
                <Mail size={16} color={colors.navy.muted} />
                <Text className="ml-2 text-sm text-navy-secondary">{patient.email}</Text>
              </View>
            ) : null}
            {patient?.phone ? (
              <View className="flex-row items-center">
                <Phone size={16} color={colors.navy.muted} />
                <Text className="ml-2 text-sm text-navy-secondary">{patient.phone}</Text>
              </View>
            ) : null}
          </View>

          <View className="mt-4 flex-row items-center justify-between rounded-xl bg-cream-100 px-4 py-3">
            <Text className="text-sm font-semibold text-navy-text">{t('profile.healthId')}</Text>
            <Text className="text-sm font-bold text-gold-600">{patient?.health_id ?? '—'}</Text>
          </View>

          <View className="mt-4 flex-row flex-wrap">
            <InfoItem
              icon={Calendar}
              label={t('profile.dateOfBirth')}
              value={formatDate(patient?.dob ?? null, i18n.language, t('profile.notProvided'))}
            />
            <InfoItem icon={User} label={t('profile.gender')} value={genderLabel} />
            <InfoItem
              icon={Droplet}
              label={t('profile.bloodGroup')}
              value={patient?.blood_group ?? t('profile.notProvided')}
            />
          </View>
        </View>

        {/* Quick stats */}
        <View className="mt-4 flex-row" style={{ gap: 12 }}>
          <StatTile
            icon={AlertTriangle}
            label={t('profile.allergies')}
            value={patient?.allergies_count ?? 0}
          />
          <StatTile
            icon={Activity}
            label={t('profile.conditions')}
            value={patient?.conditions_count ?? 0}
          />
        </View>

        {/* Menu list */}
        <Text className="mt-6 text-xs font-bold uppercase tracking-wide text-navy-muted">
          {t('profile.quickLinks')}
        </Text>
        <View className="mt-2 overflow-hidden rounded-2xl bg-white">
          {menuItems.map((item, index) => (
            <Fragment key={item.label}>
              <Pressable
                onPress={item.onPress}
                className="flex-row items-center px-4 py-4"
                style={{ opacity: 1 }}
              >
                <View className="h-9 w-9 items-center justify-center rounded-full bg-gold-50">
                  <item.icon size={17} color={colors.gold[600]} />
                </View>
                <Text className="ml-3 flex-1 text-sm font-semibold text-navy-text">
                  {item.label}
                </Text>
                <ChevronRight size={18} color={colors.navy.muted} />
              </Pressable>
              {index < menuItems.length - 1 ? (
                <View className="h-px bg-cream-200" style={{ marginLeft: 60 }} />
              ) : null}
            </Fragment>
          ))}
        </View>

        {/* Privacy note */}
        <View className="mt-5 flex-row items-start rounded-2xl bg-gold-50 p-4">
          <ShieldCheck size={16} color={colors.gold[600]} />
          <View className="ml-3 flex-1">
            <Text className="text-sm font-semibold text-navy-text">
              {t('profile.privacyTitle')}
            </Text>
            <Text className="mt-1 text-xs text-navy-secondary">{t('profile.privacyBody')}</Text>
          </View>
        </View>

        {/* Sign out */}
        <Pressable
          onPress={() => logout()}
          className="mt-6 h-14 flex-row items-center justify-center rounded-2xl border"
          style={{ borderColor: colors.semantic.danger }}
        >
          <LogOut size={17} color={colors.semantic.danger} />
          <Text className="ml-2 font-semibold" style={{ color: colors.semantic.danger }}>
            {t('profile.signOut')}
          </Text>
        </Pressable>

        <View className="h-10" />
      </ScrollView>
    </Screen>
  );
}

function InfoItem({ icon: Icon, label, value }: { icon: LucideIcon; label: string; value: string }) {
  return (
    <View className="mb-3" style={{ width: '50%' }}>
      <View className="flex-row items-center">
        <Icon size={13} color={colors.navy.muted} />
        <Text className="ml-1.5 text-xs text-navy-muted">{label}</Text>
      </View>
      <Text className="mt-1 text-sm font-semibold text-navy-text">{value}</Text>
    </View>
  );
}

function StatTile({ icon: Icon, label, value }: { icon: LucideIcon; label: string; value: number }) {
  return (
    <View className="flex-1 rounded-2xl bg-white p-4">
      <View className="h-9 w-9 items-center justify-center rounded-full bg-gold-50">
        <Icon size={16} color={colors.gold[600]} />
      </View>
      <Text className="mt-3 text-lg font-extrabold text-navy-text">{value}</Text>
      <Text className="text-xs text-navy-secondary">{label}</Text>
    </View>
  );
}
