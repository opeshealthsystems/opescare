import { useState } from 'react';
import {
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
  Pressable,
  ScrollView,
  Text,
  TextInput,
  View,
} from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  AlertCircle,
  AlertTriangle,
  CheckCircle2,
  ChevronLeft,
  Star,
  ThumbsDown,
  ThumbsUp,
  TimerOff,
} from 'lucide-react-native';
import { Screen } from '../../components/ui/Screen';
import { Button } from '../../components/ui/Button';
import { useSurvey, useSubmitSurvey, type SurveyQuestion, type SurveyResponseValue } from '../../lib/api/queries';
import { colors } from '../../theme/tokens';

function formatDate(value: string | null): string | null {
  if (!value) return null;
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return value;
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

function isRequired(type: SurveyQuestion['type']): boolean {
  return type === 'rating_5' || type === 'rating_10' || type === 'yes_no';
}

function RatingQuestion({
  scale,
  value,
  onChange,
}: {
  scale: number;
  value: number | undefined;
  onChange: (value: number) => void;
}) {
  const stars = Array.from({ length: scale }, (_, i) => i + 1);
  return (
    <View className="mt-3 flex-row flex-wrap" style={{ gap: 8 }}>
      {stars.map((n) => {
        const active = value !== undefined && n <= value;
        return (
          <Pressable key={n} onPress={() => onChange(n)} hitSlop={4}>
            <Star
              size={28}
              color={active ? colors.gold[500] : colors.cream[300]}
              fill={active ? colors.gold[500] : 'transparent'}
            />
          </Pressable>
        );
      })}
    </View>
  );
}

function YesNoQuestion({
  value,
  onChange,
}: {
  value: boolean | undefined;
  onChange: (value: boolean) => void;
}) {
  const { t } = useTranslation();
  return (
    <View className="mt-3 flex-row" style={{ gap: 12 }}>
      <Pressable
        onPress={() => onChange(true)}
        className="flex-1 flex-row items-center justify-center rounded-2xl border py-3"
        style={{
          borderColor: value === true ? colors.gold[500] : colors.cream[300],
          backgroundColor: value === true ? colors.gold[50] : colors.white,
        }}
      >
        <ThumbsUp size={16} color={value === true ? colors.gold[600] : colors.navy.muted} />
        <Text
          className="ml-2 text-sm font-semibold"
          style={{ color: value === true ? colors.gold[600] : colors.navy.secondary }}
        >
          {t('surveys.yes')}
        </Text>
      </Pressable>
      <Pressable
        onPress={() => onChange(false)}
        className="flex-1 flex-row items-center justify-center rounded-2xl border py-3"
        style={{
          borderColor: value === false ? colors.gold[500] : colors.cream[300],
          backgroundColor: value === false ? colors.gold[50] : colors.white,
        }}
      >
        <ThumbsDown size={16} color={value === false ? colors.gold[600] : colors.navy.muted} />
        <Text
          className="ml-2 text-sm font-semibold"
          style={{ color: value === false ? colors.gold[600] : colors.navy.secondary }}
        >
          {t('surveys.no')}
        </Text>
      </Pressable>
    </View>
  );
}

export default function SurveyDetailScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const { id } = useLocalSearchParams<{ id: string }>();
  const surveyQuery = useSurvey(id ?? null);
  const submitMutation = useSubmitSurvey(id ?? '');

  const [answers, setAnswers] = useState<Record<string, SurveyResponseValue>>({});
  const [showValidation, setShowValidation] = useState(false);
  const [submitted, setSubmitted] = useState(false);

  const setAnswer = (key: string, value: SurveyResponseValue) => {
    setAnswers((prev) => ({ ...prev, [key]: value }));
  };

  const handleSubmit = () => {
    const template = surveyQuery.data?.template ?? [];
    const missing = template.some((q) => isRequired(q.type) && answers[q.key] === undefined);
    if (missing) {
      setShowValidation(true);
      return;
    }
    setShowValidation(false);

    const payload: Record<string, SurveyResponseValue> = {};
    for (const q of template) {
      const v = answers[q.key];
      if (v === undefined) continue;
      if (typeof v === 'string' && v.trim().length === 0) continue;
      payload[q.key] = v;
    }

    submitMutation.mutate(payload, {
      onSuccess: () => setSubmitted(true),
    });
  };

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
        <Text className="ml-3 flex-1 text-xl font-extrabold text-navy-text" numberOfLines={1}>
          {surveyQuery.data
            ? t(`surveys.templates.${surveyQuery.data.survey.template_key}`, {
                defaultValue: surveyQuery.data.survey.template_key,
              })
            : t('surveys.title')}
        </Text>
      </View>

      {surveyQuery.isLoading ? (
        <View className="mt-20 items-center">
          <ActivityIndicator color={colors.gold[500]} />
        </View>
      ) : surveyQuery.isError || !surveyQuery.data ? (
        <View className="mt-20 items-center px-6">
          <AlertCircle size={32} color={colors.semantic.danger} />
          <Text className="mt-3 text-center text-sm text-navy-secondary">{t('surveys.loadError')}</Text>
          <View className="mt-4">
            <Button
              label={t('surveys.retry')}
              variant="outline"
              showChevron={false}
              onPress={() => surveyQuery.refetch()}
            />
          </View>
        </View>
      ) : submitted || surveyQuery.data.survey.status === 'completed' ? (
        <View className="mt-20 items-center px-6">
          <View className="h-16 w-16 items-center justify-center rounded-full bg-gold-100">
            <CheckCircle2 size={28} color={colors.gold[600]} />
          </View>
          <Text className="mt-4 text-center text-lg font-bold text-navy-text">
            {submitted ? t('surveys.submittedTitle') : t('surveys.completedTitle')}
          </Text>
          <Text className="mt-2 text-center text-sm text-navy-secondary">
            {submitted
              ? t('surveys.submittedBody')
              : t('surveys.completedBody', { date: formatDate(surveyQuery.data.survey.completed_at) })}
          </Text>
          <View className="mt-6 w-full">
            <Button label={t('surveys.done')} onPress={() => router.replace('/surveys')} />
          </View>
        </View>
      ) : surveyQuery.data.survey.status === 'expired' ? (
        <View className="mt-20 items-center px-6">
          <View className="h-16 w-16 items-center justify-center rounded-full bg-cream-200">
            <TimerOff size={26} color={colors.navy.muted} />
          </View>
          <Text className="mt-4 text-center text-lg font-bold text-navy-text">{t('surveys.expiredTitle')}</Text>
          <Text className="mt-2 text-center text-sm text-navy-secondary">{t('surveys.expiredBody')}</Text>
          <View className="mt-6 w-full">
            <Button
              label={t('surveys.backToSurveys')}
              variant="outline"
              showChevron={false}
              onPress={() => router.replace('/surveys')}
            />
          </View>
        </View>
      ) : (
        <KeyboardAvoidingView
          className="flex-1"
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
          keyboardVerticalOffset={16}
        >
          <ScrollView className="mt-4 flex-1 px-6" showsVerticalScrollIndicator={false}>
            {showValidation ? (
              <View
                className="mb-4 flex-row items-center rounded-2xl p-3"
                style={{ backgroundColor: colors.semantic.dangerSurface }}
              >
                <AlertTriangle size={16} color={colors.semantic.danger} />
                <Text className="ml-2 flex-1 text-xs font-medium" style={{ color: colors.semantic.danger }}>
                  {t('surveys.requiredError')}
                </Text>
              </View>
            ) : null}

            {submitMutation.isError ? (
              <View
                className="mb-4 flex-row items-center rounded-2xl p-3"
                style={{ backgroundColor: colors.semantic.dangerSurface }}
              >
                <AlertTriangle size={16} color={colors.semantic.danger} />
                <Text className="ml-2 flex-1 text-xs font-medium" style={{ color: colors.semantic.danger }}>
                  {t('surveys.submitError')}
                </Text>
              </View>
            ) : null}

            {surveyQuery.data.template.map((question, index) => {
              const missing = showValidation && isRequired(question.type) && answers[question.key] === undefined;
              return (
                <View
                  key={question.key}
                  className="mb-4 rounded-2xl bg-white p-4"
                  style={missing ? { borderWidth: 1, borderColor: colors.semantic.danger } : undefined}
                >
                  <Text className="text-xs font-semibold text-gold-600">
                    {t('surveys.questionOf', { current: index + 1, total: surveyQuery.data!.template.length })}
                  </Text>
                  <Text className="mt-1 text-sm font-semibold text-navy-text">{question.text}</Text>

                  {question.type === 'rating_5' || question.type === 'rating_10' ? (
                    <RatingQuestion
                      scale={question.type === 'rating_5' ? 5 : 10}
                      value={typeof answers[question.key] === 'number' ? (answers[question.key] as number) : undefined}
                      onChange={(v) => setAnswer(question.key, v)}
                    />
                  ) : question.type === 'yes_no' ? (
                    <YesNoQuestion
                      value={typeof answers[question.key] === 'boolean' ? (answers[question.key] as boolean) : undefined}
                      onChange={(v) => setAnswer(question.key, v)}
                    />
                  ) : (
                    <TextInput
                      className="mt-3 rounded-2xl border bg-cream-50 p-3 text-sm text-navy-text"
                      style={{ borderColor: colors.cream[300], minHeight: 90, textAlignVertical: 'top' }}
                      placeholder={t('surveys.commentsPlaceholder') ?? undefined}
                      placeholderTextColor={colors.navy.muted}
                      multiline
                      value={typeof answers[question.key] === 'string' ? (answers[question.key] as string) : ''}
                      onChangeText={(v) => setAnswer(question.key, v)}
                    />
                  )}
                </View>
              );
            })}

            <Button
              label={t('surveys.submit')}
              onPress={handleSubmit}
              loading={submitMutation.isPending}
              showChevron={false}
            />
            <View className="h-10" />
          </ScrollView>
        </KeyboardAvoidingView>
      )}
    </Screen>
  );
}
