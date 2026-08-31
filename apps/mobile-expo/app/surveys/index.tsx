import { ActivityIndicator, Pressable, ScrollView, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import { AlertCircle, CheckCircle2, ChevronLeft, ChevronRight, ClipboardCheck, Clock } from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { useSurveys } from '../../lib/api/queries';
import { colors } from '../../theme/tokens';

function formatDate(value: string | null): string | null {
  if (!value) return null;
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return value;
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

export default function SurveysScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const surveysQuery = useSurveys();

  return (
    <Screen className="px-0">
      <View className="mt-2 flex-row items-center px-6">
        <Pressable
          onPress={() => router.back()}
          hitSlop={8}
          className="h-9 w-9 items-center justify-center rounded-full bg-white"
        >
          <ChevronLeft size={20} color={colors.navy.text} />
        </Pressable>
        <Text className="ml-3 text-xl font-extrabold text-navy-text">{t('surveys.title')}</Text>
      </View>
      <Text className="mt-1 px-6 text-sm text-navy-secondary">{t('surveys.listSubtitle')}</Text>

      <ScrollView className="mt-4 flex-1 px-6" showsVerticalScrollIndicator={false}>
        {surveysQuery.isLoading ? (
          <View className="mt-16 items-center">
            <ActivityIndicator color={colors.gold[500]} />
          </View>
        ) : surveysQuery.isError ? (
          <View className="mt-16 items-center">
            <AlertCircle size={32} color={colors.semantic.danger} />
            <Text className="mt-3 text-center text-sm text-navy-secondary">{t('surveys.loadError')}</Text>
            <View className="mt-4">
              <Button
                label={t('surveys.retry')}
                variant="outline"
                showChevron={false}
                onPress={() => surveysQuery.refetch()}
              />
            </View>
          </View>
        ) : !surveysQuery.data || surveysQuery.data.length === 0 ? (
          <View className="mt-16 items-center px-4">
            <View className="h-16 w-16 items-center justify-center rounded-full bg-gold-100">
              <CheckCircle2 size={26} color={colors.gold[600]} />
            </View>
            <Text className="mt-4 text-base font-bold text-navy-text">{t('surveys.emptyTitle')}</Text>
            <Text className="mt-1 text-center text-sm text-navy-secondary">{t('surveys.emptyBody')}</Text>
          </View>
        ) : (
          surveysQuery.data.map((survey) => {
            const templateLabel = t(`surveys.templates.${survey.template_key}`, { defaultValue: survey.template_key });
            const sent = formatDate(survey.sent_at);
            const expires = formatDate(survey.expires_at);

            return (
              <Pressable
                key={survey.id}
                onPress={() => router.push(`/surveys/${survey.id}`)}
                className="mb-4 rounded-2xl bg-white p-4"
              >
                <View className="flex-row items-center">
                  <View className="mr-3 h-10 w-10 items-center justify-center rounded-full bg-gold-100">
                    <ClipboardCheck size={18} color={colors.gold[600]} />
                  </View>
                  <View className="flex-1">
                    <Text className="text-base font-bold text-navy-text">{templateLabel}</Text>
                    {sent ? (
                      <Text className="mt-1 text-xs text-navy-muted">{t('surveys.sentOn', { date: sent })}</Text>
                    ) : null}
                  </View>
                  <ChevronRight size={18} color={colors.navy.muted} />
                </View>

                {expires ? (
                  <View className="mt-3 flex-row items-center rounded-xl bg-cream-100 px-3 py-2">
                    <Clock size={14} color={colors.semantic.warning} />
                    <Text className="ml-2 text-xs font-medium" style={{ color: colors.semantic.warning }}>
                      {t('surveys.expiresOn', { date: expires })}
                    </Text>
                  </View>
                ) : null}

                <View className="mt-3">
                  <Button
                    label={t('surveys.takeSurvey')}
                    onPress={() => router.push(`/surveys/${survey.id}`)}
                  />
                </View>
              </Pressable>
            );
          })
        )}
        <View className="h-10" />
      </ScrollView>
    </Screen>
  );
}
