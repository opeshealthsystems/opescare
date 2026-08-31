import { useMemo, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  KeyboardAvoidingView,
  Modal,
  Platform,
  Pressable,
  RefreshControl,
  ScrollView,
  Text,
  TextInput,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import { ArrowLeft, ChevronRight, MessageCircle, Plus, Send, Video, X } from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { colors } from '../../theme/tokens';
import {
  useAppointmentsForMessaging,
  useMessageThread,
  useMessageThreads,
  useSendThreadMessage,
  useStartMessageThread,
  type MessageThreadSummary,
} from '../../lib/api/queries';

/** Messages tab — conversation list + inline thread view + a "start a new
 * conversation" composer, all in this one owned screen file (no separate
 * thread-detail route exists in this fan-out, so the thread view lives here
 * as a second state rather than a dead link). Also the entry point into the
 * Telemedicine screen (app/telemedicine), since neither the tab bar nor the
 * home screen are files this task owns. Real data throughout — see
 * lib/api/queries.ts (Messaging section) and
 * apps/api-laravel/app/Http/Controllers/Api/Mobile/MobileMessagingController.php. */
export default function MessagesScreen() {
  const { t } = useTranslation();
  const router = useRouter();

  const [activeThreadId, setActiveThreadId] = useState<number | null>(null);
  const [composeOpen, setComposeOpen] = useState(false);
  const [composeAppointmentId, setComposeAppointmentId] = useState<string | null>(null);
  const [composeBody, setComposeBody] = useState('');
  const [replyBody, setReplyBody] = useState('');

  const threadsQuery = useMessageThreads();
  const threadQuery = useMessageThread(activeThreadId);
  const appointmentsQuery = useAppointmentsForMessaging();
  const sendMessage = useSendThreadMessage(activeThreadId);
  const startThread = useStartMessageThread();

  const contactableAppointments = useMemo(
    () => (appointmentsQuery.data?.data ?? []).filter((a) => !!a.provider_name),
    [appointmentsQuery.data],
  );

  const closeThread = () => {
    setActiveThreadId(null);
    setReplyBody('');
  };

  const openCompose = () => {
    setComposeAppointmentId(null);
    setComposeBody('');
    setComposeOpen(true);
  };

  const submitReply = async () => {
    const body = replyBody.trim();
    if (!body || activeThreadId === null || sendMessage.isPending) return;
    setReplyBody('');
    try {
      await sendMessage.mutateAsync(body);
      threadQuery.refetch();
    } catch {
      setReplyBody(body);
    }
  };

  const submitCompose = async () => {
    const body = composeBody.trim();
    if (!body || !composeAppointmentId || startThread.isPending) return;
    try {
      const thread = await startThread.mutateAsync({ appointment_id: composeAppointmentId, body });
      setComposeOpen(false);
      setComposeBody('');
      setComposeAppointmentId(null);
      setActiveThreadId(thread.id);
    } catch {
      // startThread.isError renders the inline message below the form
    }
  };

  if (activeThreadId !== null) {
    return (
      <Screen className="px-0">
        <View className="flex-row items-center border-b px-6 pb-3 pt-2" style={{ borderColor: colors.cream[300] }}>
          <Pressable onPress={closeThread} hitSlop={8} className="mr-3">
            <ArrowLeft size={20} color={colors.gold[600]} />
          </Pressable>
          <Text className="flex-1 text-base font-bold text-navy-text" numberOfLines={1}>
            {threadQuery.data?.title ?? '…'}
          </Text>
        </View>

        {threadQuery.isLoading ? (
          <View className="flex-1 items-center justify-center">
            <ActivityIndicator color={colors.gold[500]} />
          </View>
        ) : threadQuery.isError ? (
          <View className="flex-1 items-center justify-center px-6">
            <Text className="text-center text-sm text-navy-muted">{t('messages.threadLoadError')}</Text>
          </View>
        ) : (
          <ScrollView className="flex-1 px-6" contentContainerStyle={{ paddingVertical: 16 }}>
            {threadQuery.data?.messages.length ? (
              threadQuery.data.messages.map((m) => (
                <View
                  key={m.id}
                  className="mb-3 max-w-[80%] rounded-2xl px-4 py-3"
                  style={{
                    alignSelf: m.is_mine ? 'flex-end' : 'flex-start',
                    backgroundColor: m.is_mine ? colors.gold[500] : colors.white,
                  }}
                >
                  <Text className={m.is_mine ? 'text-sm text-white' : 'text-sm text-navy-text'}>{m.body}</Text>
                  {m.created_at ? (
                    <Text
                      className="mt-1 text-[10px]"
                      style={{ color: m.is_mine ? 'rgba(255,255,255,0.7)' : colors.navy.muted }}
                    >
                      {formatTime(m.created_at)}
                    </Text>
                  ) : null}
                </View>
              ))
            ) : (
              <Text className="mt-10 text-center text-sm text-navy-muted">{t('messages.threadEmpty')}</Text>
            )}
          </ScrollView>
        )}

        {threadQuery.data?.status === 'closed' ? (
          <View className="border-t px-6 py-4" style={{ borderColor: colors.cream[300] }}>
            <Text className="text-center text-xs text-navy-muted">{t('messages.threadClosed')}</Text>
          </View>
        ) : (
          <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
            <View
              className="flex-row items-center gap-2 border-t px-6 py-3"
              style={{ borderColor: colors.cream[300] }}
            >
              <TextInput
                className="h-12 flex-1 rounded-full border px-4 text-base text-navy-text"
                style={{ borderColor: colors.cream[300], backgroundColor: colors.white }}
                placeholder={t('messages.replyPlaceholder')}
                placeholderTextColor={colors.navy.muted}
                value={replyBody}
                onChangeText={setReplyBody}
                multiline
              />
              <Pressable
                onPress={submitReply}
                disabled={!replyBody.trim() || sendMessage.isPending}
                className="h-12 w-12 items-center justify-center rounded-full bg-gold-500"
                style={{ opacity: !replyBody.trim() || sendMessage.isPending ? 0.5 : 1 }}
              >
                {sendMessage.isPending ? (
                  <ActivityIndicator color="white" size="small" />
                ) : (
                  <Send size={18} color="white" />
                )}
              </Pressable>
            </View>
          </KeyboardAvoidingView>
        )}
      </Screen>
    );
  }

  return (
    <Screen className="px-0">
      <View className="flex-row items-center justify-between px-6 pb-2 pt-2">
        <Text className="text-2xl font-extrabold text-navy-text">{t('messages.title')}</Text>
        <Pressable
          onPress={openCompose}
          className="h-10 w-10 items-center justify-center rounded-full bg-gold-500"
        >
          <Plus size={18} color="white" />
        </Pressable>
      </View>

      <Pressable
        onPress={() => router.push('/telemedicine')}
        className="mx-6 mb-4 flex-row items-center rounded-2xl bg-gold-500 p-4"
      >
        <View className="h-11 w-11 items-center justify-center rounded-full bg-white/20">
          <Video size={20} color="white" />
        </View>
        <View className="ml-3 flex-1">
          <Text className="text-sm font-bold text-white">{t('messages.videoVisits')}</Text>
          <Text className="text-xs text-white/80">{t('messages.videoVisitsBody')}</Text>
        </View>
        <ChevronRight size={18} color="white" />
      </Pressable>

      {threadsQuery.isLoading ? (
        <View className="flex-1 items-center justify-center">
          <ActivityIndicator color={colors.gold[500]} />
        </View>
      ) : threadsQuery.isError ? (
        <View className="flex-1 items-center justify-center px-6">
          <Text className="mb-3 text-center text-sm text-navy-muted">{t('messages.loadError')}</Text>
          <Pressable onPress={() => threadsQuery.refetch()}>
            <Text className="text-sm font-semibold text-gold-600">{t('messages.retry')}</Text>
          </Pressable>
        </View>
      ) : threadsQuery.data && threadsQuery.data.length > 0 ? (
        <FlatList
          data={threadsQuery.data}
          keyExtractor={(item) => String(item.id)}
          contentContainerStyle={{ paddingHorizontal: 24, paddingBottom: 24 }}
          refreshControl={
            <RefreshControl
              refreshing={threadsQuery.isRefetching}
              onRefresh={() => threadsQuery.refetch()}
              tintColor={colors.gold[500]}
            />
          }
          renderItem={({ item }) => <ThreadRow item={item} onPress={() => setActiveThreadId(item.id)} />}
        />
      ) : (
        <View className="flex-1 items-center justify-center px-8">
          <View className="mb-4 h-16 w-16 items-center justify-center rounded-full bg-gold-100">
            <MessageCircle size={26} color={colors.gold[500]} />
          </View>
          <Text className="mb-2 text-center text-lg font-bold text-navy-text">{t('messages.emptyTitle')}</Text>
          <Text className="mb-6 text-center text-sm text-navy-secondary">{t('messages.emptyBody')}</Text>
          <Button
            label={t('messages.startFirstConversation')}
            onPress={openCompose}
            showChevron={false}
            leftIcon={Plus}
          />
        </View>
      )}

      <Modal visible={composeOpen} animationType="slide" transparent onRequestClose={() => setComposeOpen(false)}>
        <View className="flex-1 justify-end" style={{ backgroundColor: 'rgba(0,0,0,0.4)' }}>
          <View className="max-h-[85%] rounded-t-3xl bg-cream-100 px-6 pb-8 pt-5">
            <View className="mb-4 flex-row items-center justify-between">
              <Text className="text-lg font-bold text-navy-text">{t('messages.composeTitle')}</Text>
              <Pressable onPress={() => setComposeOpen(false)} hitSlop={8}>
                <X size={20} color={colors.navy.muted} />
              </Pressable>
            </View>

            <Text className="mb-2 text-sm font-semibold text-navy-text">{t('messages.composeAbout')}</Text>
            {appointmentsQuery.isLoading ? (
              <ActivityIndicator color={colors.gold[500]} />
            ) : contactableAppointments.length === 0 ? (
              <Text className="mb-4 text-sm text-navy-muted">{t('messages.composeNoAppointments')}</Text>
            ) : (
              <ScrollView className="mb-4" style={{ maxHeight: 160 }} showsVerticalScrollIndicator={false}>
                {contactableAppointments.map((a) => (
                  <Pressable
                    key={a.id}
                    onPress={() => setComposeAppointmentId(a.id)}
                    className="mb-2 rounded-xl border px-4 py-3"
                    style={{
                      borderColor: composeAppointmentId === a.id ? colors.gold[500] : colors.cream[300],
                      backgroundColor: composeAppointmentId === a.id ? colors.gold[50] : colors.white,
                    }}
                  >
                    <Text className="text-sm font-semibold text-navy-text">{a.provider_name}</Text>
                    <Text className="mt-0.5 text-xs text-navy-muted">
                      {a.facility_name ?? a.appointment_type}
                      {a.scheduled_at ? ` · ${formatDate(a.scheduled_at)}` : ''}
                    </Text>
                  </Pressable>
                ))}
              </ScrollView>
            )}

            <TextInput
              className="mb-4 rounded-2xl border px-4 py-3 text-base text-navy-text"
              style={{ borderColor: colors.cream[300], backgroundColor: colors.white, minHeight: 90 }}
              placeholder={t('messages.composeMessagePlaceholder')}
              placeholderTextColor={colors.navy.muted}
              value={composeBody}
              onChangeText={setComposeBody}
              multiline
              textAlignVertical="top"
            />

            {startThread.isError ? (
              <Text className="mb-3 text-center text-xs text-danger">{t('messages.threadLoadError')}</Text>
            ) : null}

            <Button
              label={t('messages.composeSend')}
              onPress={submitCompose}
              loading={startThread.isPending}
              disabled={!composeBody.trim() || !composeAppointmentId}
              showChevron={false}
              leftIcon={Send}
            />
          </View>
        </View>
      </Modal>
    </Screen>
  );
}

function ThreadRow({ item, onPress }: { item: MessageThreadSummary; onPress: () => void }) {
  const { t } = useTranslation();
  return (
    <Pressable onPress={onPress} className="mb-3 flex-row items-center rounded-2xl bg-white p-4">
      <View className="h-12 w-12 items-center justify-center rounded-full bg-gold-100">
        <MessageCircle size={20} color={colors.gold[600]} />
      </View>
      <View className="ml-3 flex-1">
        <View className="flex-row items-center justify-between">
          <Text className="flex-1 text-sm font-bold text-navy-text" numberOfLines={1}>
            {item.title}
          </Text>
          {item.updated_at ? (
            <Text className="ml-2 text-[10px] text-navy-muted">{formatDate(item.updated_at)}</Text>
          ) : null}
        </View>
        <Text className="mt-0.5 text-xs text-navy-secondary" numberOfLines={1}>
          {item.last_message
            ? `${item.last_message.is_mine ? `${t('messages.you')}: ` : ''}${item.last_message.body}`
            : '—'}
        </Text>
      </View>
      {item.unread ? <View className="ml-2 h-2.5 w-2.5 rounded-full bg-gold-500" /> : null}
    </Pressable>
  );
}

function formatDate(iso: string) {
  try {
    return new Date(iso).toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
  } catch {
    return '';
  }
}

function formatTime(iso: string) {
  try {
    return new Date(iso).toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
  } catch {
    return '';
  }
}
