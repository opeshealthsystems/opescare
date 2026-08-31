import { useState } from 'react';
import {
  ActivityIndicator,
  KeyboardAvoidingView,
  Linking,
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
import {
  AlertTriangle,
  Calendar,
  ChevronLeft,
  ChevronRight,
  FileText,
  Headset,
  Inbox,
  LifeBuoy,
  Mail,
  Phone,
  QrCode,
  Send,
  ShieldCheck,
  Users,
  X,
  type LucideIcon,
} from 'lucide-react-native';
import { Screen } from '../components/ui/Screen';
import { Button } from '../components/ui/Button';
import { colors } from '../theme/tokens';
import {
  useCreateSupportTicket,
  useSendSupportTicketMessage,
  useSupportContact,
  useSupportTicket,
  useSupportTickets,
  type SupportTicketCategory,
  type SupportTicketSummary,
} from '../lib/api/queries';

const CATEGORIES: SupportTicketCategory[] = [
  'technical_issue',
  'appointment_issue',
  'billing_question',
  'account_access',
  'medical_records',
  'prescription_pharmacy',
  'other',
];

/** Help & Support — real contact channels (config-driven, never fabricated),
 * a real "Submit a Request" form, and the patient's own ticket history +
 * threaded replies, all backed by MobileSupportController (see
 * routes/mobile_support.php), a new patient-facing entry point onto the
 * platform's existing Support/helpdesk module. Reachable from Profile. */
export default function HelpScreen() {
  const { t, i18n } = useTranslation();
  const router = useRouter();

  const contactQuery = useSupportContact();
  const ticketsQuery = useSupportTickets();
  const createTicket = useCreateSupportTicket();

  const [category, setCategory] = useState<SupportTicketCategory>('technical_issue');
  const [subject, setSubject] = useState('');
  const [description, setDescription] = useState('');
  const [submitted, setSubmitted] = useState(false);
  const [activeTicketId, setActiveTicketId] = useState<string | null>(null);

  const quickLinks: { icon: LucideIcon; label: string; onPress: () => void }[] = [
    { icon: QrCode, label: t('help.quickLink.healthId'), onPress: () => router.push('/(tabs)/health-id') },
    { icon: Calendar, label: t('help.quickLink.appointments'), onPress: () => router.push('/appointments') },
    { icon: FileText, label: t('help.quickLink.records'), onPress: () => router.push('/(tabs)/records') },
    { icon: ShieldCheck, label: t('help.quickLink.privacy'), onPress: () => router.push('/privacy') },
    { icon: Users, label: t('help.quickLink.family'), onPress: () => router.push('/family') },
  ];

  const canSubmit = subject.trim().length > 0 && description.trim().length > 0 && !createTicket.isPending;

  const submitRequest = async () => {
    if (!canSubmit) return;
    setSubmitted(false);
    try {
      await createTicket.mutateAsync({
        category,
        subject: subject.trim(),
        description: description.trim(),
      });
      setSubject('');
      setDescription('');
      setCategory('technical_issue');
      setSubmitted(true);
    } catch {
      // createTicket.isError renders the inline error below the form
    }
  };

  return (
    <Screen className="px-0">
      <View className="flex-row items-center justify-between px-6 pt-2">
        <Pressable onPress={() => router.back()} hitSlop={10} className="h-10 w-10 items-center justify-center">
          <ChevronLeft size={24} color={colors.navy.text} />
        </Pressable>
        <Text className="text-lg font-bold text-navy-text">{t('help.title')}</Text>
        <View className="h-10 w-10" />
      </View>

      <ScrollView
        className="flex-1 px-6"
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={ticketsQuery.isRefetching}
            onRefresh={() => {
              ticketsQuery.refetch();
              contactQuery.refetch();
            }}
            tintColor={colors.gold[500]}
          />
        }
      >
        <Text className="mb-4 mt-1 text-sm text-navy-secondary">{t('help.subtitle')}</Text>

        {/* Quick links */}
        <ScrollView horizontal showsHorizontalScrollIndicator={false} className="-mx-1 mb-5">
          {quickLinks.map((link) => (
            <Pressable
              key={link.label}
              onPress={link.onPress}
              className="mx-1 w-24 items-center rounded-2xl bg-white px-2 py-4"
            >
              <View className="mb-2 h-10 w-10 items-center justify-center rounded-full bg-gold-50">
                <link.icon size={18} color={colors.gold[600]} />
              </View>
              <Text className="text-center text-xs font-semibold text-navy-text" numberOfLines={2}>
                {link.label}
              </Text>
            </Pressable>
          ))}
        </ScrollView>

        {/* Contact us */}
        <View className="mb-5 rounded-2xl bg-white p-4">
          <View className="mb-3 flex-row items-center">
            <View className="h-9 w-9 items-center justify-center rounded-full bg-gold-100">
              <Headset size={18} color={colors.gold[600]} />
            </View>
            <View className="ml-3 flex-1">
              <Text className="text-base font-bold text-navy-text">{t('help.contact.title')}</Text>
              <Text className="text-xs text-navy-secondary">{t('help.contact.body')}</Text>
            </View>
          </View>

          {contactQuery.isLoading ? (
            <ActivityIndicator color={colors.gold[500]} />
          ) : (
            <View style={{ gap: 10 }}>
              {contactQuery.data?.email ? (
                <Pressable
                  onPress={() => Linking.openURL(`mailto:${contactQuery.data!.email}`).catch(() => {})}
                  className="flex-row items-center rounded-xl px-3 py-3"
                  style={{ backgroundColor: colors.cream[100] }}
                >
                  <View className="h-9 w-9 items-center justify-center rounded-full bg-white">
                    <Mail size={16} color={colors.gold[600]} />
                  </View>
                  <View className="ml-3 flex-1">
                    <Text className="text-sm font-semibold text-navy-text">{t('help.contact.email')}</Text>
                    <Text className="text-xs text-navy-secondary" numberOfLines={1}>
                      {contactQuery.data.email}
                    </Text>
                  </View>
                  <ChevronRight size={16} color={colors.navy.muted} />
                </Pressable>
              ) : null}

              {contactQuery.data?.phone ? (
                <Pressable
                  onPress={() =>
                    Linking.openURL(`tel:${contactQuery.data!.phone!.replace(/[^+\d]/g, '')}`).catch(() => {})
                  }
                  className="flex-row items-center rounded-xl px-3 py-3"
                  style={{ backgroundColor: colors.cream[100] }}
                >
                  <View className="h-9 w-9 items-center justify-center rounded-full bg-white">
                    <Phone size={16} color={colors.gold[600]} />
                  </View>
                  <View className="ml-3 flex-1">
                    <Text className="text-sm font-semibold text-navy-text">{t('help.contact.phone')}</Text>
                    <Text className="text-xs text-navy-secondary" numberOfLines={1}>
                      {contactQuery.data.phone}
                    </Text>
                  </View>
                  <ChevronRight size={16} color={colors.navy.muted} />
                </Pressable>
              ) : null}

              {!contactQuery.data?.email && !contactQuery.data?.phone ? (
                <Text className="text-xs text-navy-secondary">{t('help.contact.unavailable')}</Text>
              ) : null}
            </View>
          )}
        </View>

        {/* Submit a request */}
        <View className="mb-5 rounded-2xl bg-white p-4">
          <View className="mb-3 flex-row items-center">
            <View className="h-9 w-9 items-center justify-center rounded-full bg-gold-100">
              <LifeBuoy size={18} color={colors.gold[600]} />
            </View>
            <View className="ml-3 flex-1">
              <Text className="text-base font-bold text-navy-text">{t('help.requestForm.title')}</Text>
              <Text className="text-xs text-navy-secondary">{t('help.requestForm.body')}</Text>
            </View>
          </View>

          <Text className="mb-2 text-xs font-semibold text-navy-secondary">{t('help.requestForm.category')}</Text>
          <View className="mb-4 flex-row flex-wrap" style={{ gap: 8 }}>
            {CATEGORIES.map((c) => (
              <Pressable
                key={c}
                onPress={() => setCategory(c)}
                className="rounded-full border px-3 py-2"
                style={{
                  borderColor: category === c ? colors.gold[500] : colors.cream[300],
                  backgroundColor: category === c ? colors.gold[50] : 'transparent',
                }}
              >
                <Text
                  className="text-xs font-semibold"
                  style={{ color: category === c ? colors.gold[600] : colors.navy.secondary }}
                >
                  {t(`help.category.${c}`)}
                </Text>
              </Pressable>
            ))}
          </View>

          <Text className="mb-2 text-xs font-semibold text-navy-secondary">{t('help.requestForm.subject')}</Text>
          <TextInput
            className="mb-4 h-12 rounded-xl border px-4 text-sm text-navy-text"
            style={{ borderColor: colors.cream[300], backgroundColor: colors.white }}
            placeholder={t('help.requestForm.subjectPlaceholder')}
            placeholderTextColor={colors.navy.muted}
            value={subject}
            onChangeText={setSubject}
            maxLength={255}
          />

          <Text className="mb-2 text-xs font-semibold text-navy-secondary">{t('help.requestForm.description')}</Text>
          <TextInput
            className="mb-4 rounded-xl border px-4 py-3 text-sm text-navy-text"
            style={{ borderColor: colors.cream[300], backgroundColor: colors.white, minHeight: 90 }}
            placeholder={t('help.requestForm.descriptionPlaceholder')}
            placeholderTextColor={colors.navy.muted}
            value={description}
            onChangeText={setDescription}
            multiline
            textAlignVertical="top"
            maxLength={5000}
          />

          {createTicket.isError ? (
            <Text className="mb-3 text-xs" style={{ color: colors.semantic.danger }}>
              {t('help.requestForm.submitError')}
            </Text>
          ) : null}
          {submitted ? (
            <Text className="mb-3 text-xs font-semibold" style={{ color: colors.semantic.success }}>
              {t('help.requestForm.submitted')}
            </Text>
          ) : null}

          <Button
            label={t('help.requestForm.submit')}
            onPress={submitRequest}
            loading={createTicket.isPending}
            disabled={!canSubmit}
            showChevron={false}
            leftIcon={Send}
          />
        </View>

        {/* My support requests */}
        <View className="mb-5 rounded-2xl bg-white p-4">
          <View className="mb-1 flex-row items-center justify-between">
            <Text className="text-base font-bold text-navy-text">{t('help.myRequests.title')}</Text>
          </View>
          <Text className="mb-3 text-xs text-navy-secondary">{t('help.myRequests.body')}</Text>

          {ticketsQuery.isLoading ? (
            <ActivityIndicator color={colors.gold[500]} />
          ) : ticketsQuery.isError ? (
            <View className="items-center py-4">
              <Text className="mb-2 text-center text-xs text-navy-secondary">{t('help.myRequests.loadError')}</Text>
              <Pressable onPress={() => ticketsQuery.refetch()}>
                <Text className="text-xs font-semibold text-gold-600">{t('help.myRequests.retry')}</Text>
              </Pressable>
            </View>
          ) : !ticketsQuery.data?.length ? (
            <View className="items-center py-6">
              <Inbox size={22} color={colors.navy.muted} />
              <Text className="mt-2 text-center text-xs text-navy-secondary">{t('help.myRequests.empty')}</Text>
            </View>
          ) : (
            <View>
              {ticketsQuery.data.map((ticket, index) => (
                <TicketRow
                  key={ticket.id}
                  ticket={ticket}
                  isLast={index === ticketsQuery.data!.length - 1}
                  locale={i18n.language}
                  onPress={() => setActiveTicketId(ticket.id)}
                />
              ))}
            </View>
          )}
        </View>

        {/* Emergency notice */}
        <View
          className="mb-6 flex-row items-start rounded-2xl p-4"
          style={{ backgroundColor: colors.semantic.dangerSurface }}
        >
          <AlertTriangle size={18} color={colors.semantic.danger} />
          <View className="ml-3 flex-1">
            <Text className="text-sm font-bold" style={{ color: colors.semantic.danger }}>
              {t('help.emergency.title')}
            </Text>
            <Text className="mt-1 text-xs text-navy-secondary">{t('help.emergency.body')}</Text>
          </View>
        </View>
      </ScrollView>

      <TicketDetailModal
        ticketId={activeTicketId}
        visible={activeTicketId !== null}
        onClose={() => setActiveTicketId(null)}
        locale={i18n.language}
      />
    </Screen>
  );
}

function statusColors(status: string) {
  switch (status) {
    case 'resolved':
      return { fg: colors.semantic.success, bg: colors.semantic.successSurface };
    case 'escalated':
      return { fg: colors.semantic.danger, bg: colors.semantic.dangerSurface };
    case 'assigned':
      return { fg: colors.semantic.warning, bg: colors.semantic.warningSurface };
    default:
      return { fg: colors.semantic.info, bg: colors.semantic.infoSurface };
  }
}

function TicketRow({
  ticket,
  isLast,
  locale,
  onPress,
}: {
  ticket: SupportTicketSummary;
  isLast: boolean;
  locale: string;
  onPress: () => void;
}) {
  const { t } = useTranslation();
  const sc = statusColors(ticket.status);

  return (
    <Pressable
      onPress={onPress}
      className="flex-row items-center py-3"
      style={!isLast ? { borderBottomWidth: 1, borderBottomColor: colors.cream[300] } : undefined}
    >
      <View className="flex-1 pr-2">
        <Text className="text-sm font-semibold text-navy-text" numberOfLines={1}>
          {ticket.subject}
        </Text>
        <Text className="mt-0.5 text-xs text-navy-muted">
          {t(`help.category.${ticket.category}`)} · {formatDate(ticket.created_at, locale)}
        </Text>
      </View>
      <View className="rounded-full px-2 py-1" style={{ backgroundColor: sc.bg }}>
        <Text className="text-[10px] font-semibold" style={{ color: sc.fg }}>
          {t(`help.status.${ticket.status}`, ticket.status)}
        </Text>
      </View>
      <ChevronRight size={16} color={colors.navy.muted} style={{ marginLeft: 8 }} />
    </Pressable>
  );
}

function TicketDetailModal({
  ticketId,
  visible,
  onClose,
  locale,
}: {
  ticketId: string | null;
  visible: boolean;
  onClose: () => void;
  locale: string;
}) {
  const { t } = useTranslation();
  const ticketQuery = useSupportTicket(ticketId);
  const sendMessage = useSendSupportTicketMessage(ticketId);
  const [reply, setReply] = useState('');

  const sc = statusColors(ticketQuery.data?.status ?? 'open');

  const submitReply = async () => {
    const body = reply.trim();
    if (!body || sendMessage.isPending) return;
    setReply('');
    try {
      await sendMessage.mutateAsync(body);
    } catch {
      setReply(body);
    }
  };

  return (
    <Modal visible={visible} animationType="slide" transparent onRequestClose={onClose}>
      <View className="flex-1 justify-end" style={{ backgroundColor: 'rgba(0,0,0,0.4)' }}>
        <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
          <View className="max-h-[85%] rounded-t-3xl bg-cream-100 px-6 pb-6 pt-5">
            <View className="mb-3 flex-row items-center justify-between">
              <Text className="flex-1 pr-3 text-base font-bold text-navy-text" numberOfLines={1}>
                {ticketQuery.data?.subject ?? '…'}
              </Text>
              <Pressable onPress={onClose} hitSlop={8}>
                <X size={20} color={colors.navy.muted} />
              </Pressable>
            </View>

            {ticketQuery.isLoading ? (
              <View className="items-center py-8">
                <ActivityIndicator color={colors.gold[500]} />
              </View>
            ) : !ticketQuery.data ? (
              <Text className="py-8 text-center text-sm text-navy-secondary">{t('help.myRequests.loadError')}</Text>
            ) : (
              <>
                <View className="mb-3 flex-row items-center" style={{ gap: 8 }}>
                  <View className="rounded-full px-2 py-1" style={{ backgroundColor: sc.bg }}>
                    <Text className="text-[10px] font-semibold" style={{ color: sc.fg }}>
                      {t(`help.status.${ticketQuery.data.status}`, ticketQuery.data.status)}
                    </Text>
                  </View>
                  <Text className="text-xs text-navy-muted">
                    {t(`help.category.${ticketQuery.data.category}`)} ·{' '}
                    {formatDate(ticketQuery.data.created_at, locale)}
                  </Text>
                </View>

                <ScrollView style={{ maxHeight: 320 }} showsVerticalScrollIndicator={false}>
                  <Text className="mb-1 text-xs font-semibold text-navy-secondary">
                    {t('help.detail.description')}
                  </Text>
                  <Text className="mb-4 text-sm text-navy-text">{ticketQuery.data.description}</Text>

                  {ticketQuery.data.resolution_note ? (
                    <View className="mb-4 rounded-xl p-3" style={{ backgroundColor: colors.semantic.successSurface }}>
                      <Text className="mb-1 text-xs font-semibold" style={{ color: colors.semantic.success }}>
                        {t('help.detail.resolutionNote')}
                      </Text>
                      <Text className="text-sm text-navy-text">{ticketQuery.data.resolution_note}</Text>
                    </View>
                  ) : null}

                  <Text className="mb-2 text-xs font-semibold text-navy-secondary">
                    {t('help.detail.conversation')}
                  </Text>
                  {ticketQuery.data.messages.length === 0 ? (
                    <Text className="mb-2 text-xs text-navy-muted">{t('help.detail.empty')}</Text>
                  ) : (
                    ticketQuery.data.messages.map((m) => (
                      <View
                        key={m.id}
                        className="mb-2 max-w-[85%] rounded-2xl px-3 py-2"
                        style={{
                          alignSelf: m.is_mine ? 'flex-end' : 'flex-start',
                          backgroundColor: m.is_mine ? colors.gold[500] : colors.white,
                        }}
                      >
                        <Text className={m.is_mine ? 'text-xs text-white' : 'text-xs text-navy-text'}>
                          {m.body}
                        </Text>
                      </View>
                    ))
                  )}
                </ScrollView>

                <View className="mt-3 flex-row items-center gap-2">
                  <TextInput
                    className="h-11 flex-1 rounded-full border px-4 text-sm text-navy-text"
                    style={{ borderColor: colors.cream[300], backgroundColor: colors.white }}
                    placeholder={t('help.detail.replyPlaceholder')}
                    placeholderTextColor={colors.navy.muted}
                    value={reply}
                    onChangeText={setReply}
                  />
                  <Pressable
                    onPress={submitReply}
                    disabled={!reply.trim() || sendMessage.isPending}
                    className="h-11 w-11 items-center justify-center rounded-full bg-gold-500"
                    style={{ opacity: !reply.trim() || sendMessage.isPending ? 0.5 : 1 }}
                  >
                    {sendMessage.isPending ? (
                      <ActivityIndicator color="white" size="small" />
                    ) : (
                      <Send size={16} color="white" />
                    )}
                  </Pressable>
                </View>
              </>
            )}
          </View>
        </KeyboardAvoidingView>
      </View>
    </Modal>
  );
}

function formatDate(iso: string, locale: string) {
  try {
    return new Date(iso).toLocaleDateString(locale === 'fr' ? 'fr-FR' : 'en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  } catch {
    return iso;
  }
}
