import { useMemo, useState } from 'react';
import { ActivityIndicator, Pressable, RefreshControl, ScrollView, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  Bell,
  Calendar,
  CheckCheck,
  ChevronRight,
  FlaskConical,
  HeartPulse,
  Lock,
  MessageCircle,
  Pill,
  RotateCcw,
  Settings,
  ShieldAlert,
  Sparkles,
  Users,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../components/ui/Screen';
import {
  useMarkAllNotificationsRead,
  useMarkNotificationRead,
  useNotifications,
  type NotificationItem,
} from '../lib/api/queries';
import { colors } from '../theme/tokens';

type Category = 'all' | 'appointments' | 'health' | 'messages' | 'system';

/** Category → the one tab route we know is real today. `system` has no
 * dedicated in-app destination yet, so its cards render without a tap target
 * rather than linking somewhere wrong. */
const CATEGORY_ROUTE: Partial<Record<Category, string>> = {
  appointments: '/(tabs)/records',
  health: '/(tabs)/health-id',
  messages: '/(tabs)/messages',
};

function iconForType(type: string): LucideIcon {
  if (type.includes('emergency') || type.includes('security')) return ShieldAlert;
  if (type.includes('health_id')) return HeartPulse;
  if (type.includes('appointment')) return Calendar;
  if (type.includes('lab')) return FlaskConical;
  if (type.includes('prescription')) return Pill;
  if (type.includes('family')) return Users;
  if (type.includes('message')) return MessageCircle;
  if (type.includes('tip') || type.includes('health')) return Sparkles;
  return Bell;
}

function iconForCategory(category: Category): LucideIcon {
  switch (category) {
    case 'appointments':
      return Calendar;
    case 'health':
      return HeartPulse;
    case 'messages':
      return MessageCircle;
    case 'system':
      return Settings;
    default:
      return Bell;
  }
}

function severityPalette(severity: string): { bg: string; fg: string } {
  if (severity === 'high') return { bg: colors.semantic.dangerSurface, fg: colors.semantic.danger };
  if (severity === 'medium') return { bg: colors.semantic.warningSurface, fg: colors.semantic.warning };
  return { bg: colors.gold[50], fg: colors.gold[600] };
}

function isSameDay(a: Date, b: Date): boolean {
  return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
}

type SectionKey = 'today' | 'yesterday' | 'earlier';

function timestampLabel(iso: string | null, sectionKey: SectionKey, sectionLabel: string): string {
  if (!iso) return '';
  const d = new Date(iso);
  const time = d.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
  if (sectionKey === 'today') return time;
  if (sectionKey === 'yesterday') return `${sectionLabel}, ${time}`;
  return d.toLocaleString(undefined, {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  });
}

export default function NotificationsScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const [filter, setFilter] = useState<Category>('all');

  const { data, isLoading, isError, refetch, isRefetching } = useNotifications();
  const markRead = useMarkNotificationRead();
  const markAllRead = useMarkAllNotificationsRead();

  const items = data?.data ?? [];
  const unreadCount = data?.unread_count ?? 0;

  const counts = useMemo(() => {
    const base: Record<Category, number> = { all: items.length, appointments: 0, health: 0, messages: 0, system: 0 };
    for (const item of items) {
      if (item.category in base) base[item.category as Category] += 1;
    }
    return base;
  }, [items]);

  const filtered = filter === 'all' ? items : items.filter((n) => n.category === filter);

  const sectionLabels: Record<SectionKey, string> = {
    today: t('notifications.sectionToday'),
    yesterday: t('notifications.sectionYesterday'),
    earlier: t('notifications.sectionEarlier'),
  };

  const groups = useMemo(() => {
    const now = new Date();
    const yesterdayRef = new Date(now);
    yesterdayRef.setDate(yesterdayRef.getDate() - 1);

    const byKey = new Map<SectionKey, NotificationItem[]>();

    for (const item of filtered) {
      let key: SectionKey = 'earlier';
      if (item.created_at) {
        const d = new Date(item.created_at);
        if (isSameDay(d, now)) key = 'today';
        else if (isSameDay(d, yesterdayRef)) key = 'yesterday';
      }
      if (!byKey.has(key)) byKey.set(key, []);
      byKey.get(key)!.push(item);
    }

    // Today / Yesterday / Earlier, in that fixed order, skipping empty ones —
    // items already arrive newest-first from the API within each bucket.
    return (['today', 'yesterday', 'earlier'] as const)
      .filter((key) => byKey.has(key))
      .map((key) => ({ key, label: sectionLabels[key], items: byKey.get(key)! }));
  }, [filtered, sectionLabels.today, sectionLabels.yesterday, sectionLabels.earlier]);

  const chips: { key: Category; label: string }[] = [
    { key: 'all', label: t('notifications.filterAll') },
    { key: 'appointments', label: t('notifications.filterAppointments') },
    { key: 'health', label: t('notifications.filterHealth') },
    { key: 'messages', label: t('notifications.filterMessages') },
    { key: 'system', label: t('notifications.filterSystem') },
  ];

  const handleCardPress = (item: NotificationItem) => {
    if (!item.read) markRead.mutate(item.id);
    const route = CATEGORY_ROUTE[item.category];
    if (route) router.push(route);
  };

  return (
    <Screen className="px-0">
      <View className="px-6">
        <View className="mt-2 flex-row items-center justify-between">
          <Pressable
            onPress={() => router.back()}
            hitSlop={8}
            className="h-11 w-11 items-center justify-center rounded-full border border-gold-300"
          >
            <ArrowLeft size={18} color={colors.gold[600]} />
          </Pressable>
          <Pressable
            onPress={() => markAllRead.mutate()}
            disabled={unreadCount === 0 || markAllRead.isPending}
            className="flex-row items-center gap-1.5"
            style={{ opacity: unreadCount === 0 ? 0.4 : markAllRead.isPending ? 0.6 : 1 }}
          >
            <Text className="text-sm font-semibold text-success">{t('notifications.markAllRead')}</Text>
            <CheckCheck size={16} color={colors.semantic.success} />
          </Pressable>
        </View>

        <View className="mt-4 flex-row items-center">
          <Text className="text-2xl font-extrabold text-navy-text">{t('notifications.title')}</Text>
          {unreadCount > 0 ? (
            <View className="ml-2 h-6 min-w-[24px] items-center justify-center rounded-full bg-gold-500 px-2">
              <Text className="text-xs font-bold text-white">{unreadCount}</Text>
            </View>
          ) : null}
        </View>
        <Text className="mt-1 text-sm text-navy-secondary">{t('notifications.subtitle')}</Text>
      </View>

      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        className="mt-4 flex-grow-0"
        contentContainerStyle={{ paddingHorizontal: 24, gap: 8 }}
      >
        {chips.map((chip) => {
          const active = filter === chip.key;
          const ChipIcon = iconForCategory(chip.key);
          return (
            <Pressable
              key={chip.key}
              onPress={() => setFilter(chip.key)}
              className="flex-row items-center gap-1.5 rounded-full border px-3.5 py-2"
              style={{
                borderColor: active ? colors.gold[500] : colors.cream[300],
                backgroundColor: active ? colors.gold[50] : colors.white,
              }}
            >
              <ChipIcon size={14} color={active ? colors.gold[700] : colors.navy.muted} />
              <Text
                className="text-xs font-semibold"
                style={{ color: active ? colors.gold[700] : colors.navy.secondary }}
              >
                {chip.label} ({counts[chip.key]})
              </Text>
            </Pressable>
          );
        })}
      </ScrollView>

      <ScrollView
        className="mt-4 flex-1 px-6"
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={isRefetching} onRefresh={refetch} tintColor={colors.gold[500]} />}
      >
        {isLoading ? (
          <View className="items-center py-16">
            <ActivityIndicator color={colors.gold[500]} />
          </View>
        ) : isError ? (
          <View className="items-center rounded-2xl bg-white px-6 py-12">
            <Bell size={28} color={colors.navy.muted} />
            <Text className="mt-3 text-center text-sm font-semibold text-navy-text">
              {t('notifications.errorTitle')}
            </Text>
            <Pressable onPress={() => refetch()} className="mt-4 flex-row items-center gap-1.5">
              <RotateCcw size={14} color={colors.gold[600]} />
              <Text className="text-sm font-semibold text-gold-600">{t('notifications.retry')}</Text>
            </Pressable>
          </View>
        ) : filtered.length === 0 ? (
          <View className="items-center rounded-2xl bg-white px-6 py-16">
            <View className="h-14 w-14 items-center justify-center rounded-full bg-gold-50">
              <Bell size={24} color={colors.gold[500]} />
            </View>
            <Text className="mt-4 text-center text-base font-bold text-navy-text">
              {t('notifications.emptyTitle')}
            </Text>
            <Text className="mt-1 text-center text-sm text-navy-secondary">
              {t('notifications.emptySubtitle')}
            </Text>
          </View>
        ) : (
          <>
            {groups.map((group) => (
              <View key={group.key} className="mb-2">
                <Text className="mb-2 mt-2 text-sm font-bold text-navy-secondary">{group.label}</Text>
                <View className="overflow-hidden rounded-2xl bg-white">
                  {group.items.map((item, idx) => {
                    const Icon = iconForType(item.type);
                    const palette = severityPalette(item.severity);
                    const route = CATEGORY_ROUTE[item.category];
                    return (
                      <Pressable
                        key={item.id}
                        onPress={() => handleCardPress(item)}
                        className="flex-row px-4 py-4"
                        style={{
                          borderTopWidth: idx === 0 ? 0 : 1,
                          borderTopColor: colors.cream[200],
                        }}
                      >
                        <View
                          className="mr-3 h-11 w-11 items-center justify-center rounded-xl"
                          style={{ backgroundColor: palette.bg }}
                        >
                          <Icon size={20} color={palette.fg} />
                        </View>
                        <View className="flex-1">
                          <View className="flex-row items-start justify-between">
                            <Text className="flex-1 pr-2 text-base font-bold text-navy-text">{item.title}</Text>
                            <Text className="text-xs text-navy-muted">
                              {timestampLabel(item.created_at, group.key, group.label)}
                            </Text>
                          </View>
                          <Text className="mt-1 text-sm text-navy-secondary">{item.message}</Text>
                          {item.action_label && route ? (
                            <View className="mt-2 flex-row items-center gap-0.5">
                              <Text className="text-sm font-semibold text-gold-600">{item.action_label}</Text>
                              <ChevronRight size={14} color={colors.gold[600]} />
                            </View>
                          ) : null}
                        </View>
                        <View
                          className="ml-2 mt-1.5 h-2 w-2 rounded-full"
                          style={{ backgroundColor: item.read ? colors.cream[300] : colors.gold[500] }}
                        />
                      </Pressable>
                    );
                  })}
                </View>
              </View>
            ))}

            <View className="mb-8 mt-2 flex-row items-center rounded-2xl bg-cream-200 px-4 py-3.5">
              <Lock size={18} color={colors.gold[600]} />
              <View className="ml-3 flex-1">
                <Text className="text-sm font-semibold text-navy-text">{t('notifications.privacyTitle')}</Text>
                <Text className="mt-0.5 text-xs text-navy-secondary">{t('notifications.privacySubtitle')}</Text>
              </View>
            </View>
          </>
        )}
      </ScrollView>
    </Screen>
  );
}
