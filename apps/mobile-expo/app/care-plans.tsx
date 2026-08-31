import { useState } from 'react';
import { ActivityIndicator, Pressable, ScrollView, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useTranslation } from 'react-i18next';
import {
  Activity,
  AlertCircle,
  BookOpen,
  Calendar,
  CheckCircle2,
  ChevronDown,
  ChevronLeft,
  ChevronUp,
  Circle,
  ClipboardList,
  Pill,
  Share2,
  Stethoscope,
  Target,
  Utensils,
  XCircle,
} from 'lucide-react-native';
import { Screen } from '../components/ui/Screen';
import { Button } from '../components/ui/Button';
import {
  useCarePlan,
  useCarePlans,
  type CarePlan,
  type CarePlanGoal,
  type CarePlanIntervention,
} from '../lib/api/queries';
import { colors } from '../theme/tokens';

type Tone = 'success' | 'warning' | 'danger' | 'info';

const TONE_COLORS: Record<Tone, { bg: string; fg: string }> = {
  success: { bg: colors.semantic.successSurface, fg: colors.semantic.success },
  warning: { bg: colors.semantic.warningSurface, fg: colors.semantic.warning },
  danger: { bg: colors.semantic.dangerSurface, fg: colors.semantic.danger },
  info: { bg: colors.semantic.infoSurface, fg: colors.semantic.info },
};

const PLAN_STATUS_TONE: Record<CarePlan['status'], Tone> = {
  active: 'success',
  completed: 'info',
  on_hold: 'warning',
  cancelled: 'danger',
};

const GOAL_STATUS_TONE: Record<CarePlanGoal['status'], Tone> = {
  pending: 'warning',
  in_progress: 'info',
  achieved: 'success',
  abandoned: 'danger',
};

const INTERVENTION_STATUS_TONE: Record<CarePlanIntervention['status'], Tone> = {
  active: 'success',
  completed: 'info',
  discontinued: 'danger',
};

const INTERVENTION_ICON: Record<CarePlanIntervention['intervention_type'], typeof Pill> = {
  medication: Pill,
  exercise: Activity,
  diet: Utensils,
  monitoring: Stethoscope,
  referral: Share2,
  education: BookOpen,
  other: ClipboardList,
};

const GOAL_ICON: Record<CarePlanGoal['status'], typeof Circle> = {
  pending: Circle,
  in_progress: Target,
  achieved: CheckCircle2,
  abandoned: XCircle,
};

function StatusPill({ label, tone }: { label: string; tone: Tone }) {
  const c = TONE_COLORS[tone];
  return (
    <View
      className="rounded-full px-3 py-1"
      style={{ backgroundColor: c.bg }}
    >
      <Text className="text-xs font-semibold" style={{ color: c.fg }}>
        {label}
      </Text>
    </View>
  );
}

function formatDate(value: string | null): string | null {
  if (!value) return null;
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return value;
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

export default function CarePlansScreen() {
  const { t } = useTranslation();
  const router = useRouter();
  const [expandedId, setExpandedId] = useState<string | null>(null);
  const plansQuery = useCarePlans();
  const detailQuery = useCarePlan(expandedId);

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
        <Text className="ml-3 text-xl font-extrabold text-navy-text">{t('carePlans.title')}</Text>
      </View>
      <Text className="mt-1 px-6 text-sm text-navy-secondary">{t('carePlans.subtitle')}</Text>

      <ScrollView className="mt-4 flex-1 px-6" showsVerticalScrollIndicator={false}>
        {plansQuery.isLoading ? (
          <View className="mt-16 items-center">
            <ActivityIndicator color={colors.gold[500]} />
          </View>
        ) : plansQuery.isError ? (
          <View className="mt-16 items-center">
            <AlertCircle size={32} color={colors.semantic.danger} />
            <Text className="mt-3 text-center text-sm text-navy-secondary">{t('carePlans.loadError')}</Text>
            <View className="mt-4">
              <Button
                label={t('carePlans.retry')}
                variant="outline"
                showChevron={false}
                onPress={() => plansQuery.refetch()}
              />
            </View>
          </View>
        ) : !plansQuery.data || plansQuery.data.length === 0 ? (
          <View className="mt-16 items-center px-4">
            <View className="h-16 w-16 items-center justify-center rounded-full bg-gold-100">
              <ClipboardList size={26} color={colors.gold[600]} />
            </View>
            <Text className="mt-4 text-base font-bold text-navy-text">{t('carePlans.emptyTitle')}</Text>
            <Text className="mt-1 text-center text-sm text-navy-secondary">{t('carePlans.emptyBody')}</Text>
          </View>
        ) : (
          plansQuery.data.map((plan) => {
            const isExpanded = expandedId === plan.id;
            const summary = isExpanded ? detailQuery.data : undefined;
            const goals = summary?.goals ?? plan.goals ?? [];
            const interventions = summary?.interventions ?? plan.interventions ?? [];
            const progressPct = summary?.progress_pct;
            const start = formatDate(plan.start_date);
            const end = plan.end_date ? formatDate(plan.end_date) : t('carePlans.ongoing');

            return (
              <Pressable
                key={plan.id}
                onPress={() => setExpandedId(isExpanded ? null : plan.id)}
                className="mb-4 rounded-2xl bg-white p-4"
              >
                <View className="flex-row items-start justify-between">
                  <View className="flex-1 flex-row items-start">
                    <View className="mr-3 h-10 w-10 items-center justify-center rounded-full bg-gold-100">
                      <ClipboardList size={18} color={colors.gold[600]} />
                    </View>
                    <View className="flex-1">
                      <Text className="text-base font-bold text-navy-text">{plan.title}</Text>
                      {plan.description ? (
                        <Text className="mt-1 text-sm text-navy-secondary" numberOfLines={isExpanded ? undefined : 2}>
                          {plan.description}
                        </Text>
                      ) : null}
                    </View>
                  </View>
                  {isExpanded ? (
                    <ChevronUp size={18} color={colors.navy.muted} />
                  ) : (
                    <ChevronDown size={18} color={colors.navy.muted} />
                  )}
                </View>

                <View className="mt-3 flex-row items-center justify-between">
                  <View className="flex-row items-center">
                    <Calendar size={14} color={colors.navy.muted} />
                    <Text className="ml-2 text-xs text-navy-muted">
                      {start} — {end}
                    </Text>
                  </View>
                  <StatusPill label={t(`carePlans.status.${plan.status}`)} tone={PLAN_STATUS_TONE[plan.status]} />
                </View>

                <Text className="mt-2 text-xs text-navy-muted">
                  {t('carePlans.goalsCount', { count: goals.length })}
                  {'  ·  '}
                  {t('carePlans.interventionsCount', { count: interventions.length })}
                </Text>

                {isExpanded ? (
                  <View className="mt-4 border-t border-cream-300 pt-4">
                    {typeof progressPct === 'number' ? (
                      <View className="mb-4">
                        <View className="flex-row items-center justify-between">
                          <Text className="text-xs font-semibold text-navy-text">{t('carePlans.progress')}</Text>
                          <Text className="text-xs font-semibold text-gold-600">{progressPct}%</Text>
                        </View>
                        <View className="mt-2 h-2 overflow-hidden rounded-full bg-cream-200">
                          <View
                            className="h-2 rounded-full bg-gold-500"
                            style={{ width: `${Math.min(100, Math.max(0, progressPct))}%` }}
                          />
                        </View>
                      </View>
                    ) : detailQuery.isLoading ? (
                      <View className="mb-4 items-center">
                        <ActivityIndicator color={colors.gold[500]} size="small" />
                      </View>
                    ) : null}

                    <Text className="mb-2 text-sm font-bold text-navy-text">{t('carePlans.goals')}</Text>
                    {goals.length === 0 ? (
                      <Text className="mb-3 text-xs text-navy-muted">{t('carePlans.noGoals')}</Text>
                    ) : (
                      goals.map((goal) => {
                        const GoalIcon = GOAL_ICON[goal.status];
                        const tone = GOAL_STATUS_TONE[goal.status];
                        return (
                          <View key={goal.id} className="mb-3 flex-row items-start">
                            <GoalIcon size={16} color={TONE_COLORS[tone].fg} style={{ marginTop: 2 }} />
                            <View className="ml-2 flex-1">
                              <Text className="text-sm text-navy-text">{goal.goal_text}</Text>
                              <View className="mt-1 flex-row items-center">
                                <StatusPill label={t(`carePlans.goalStatus.${goal.status}`)} tone={tone} />
                                {goal.target_date ? (
                                  <Text className="ml-2 text-xs text-navy-muted">
                                    {t('carePlans.target')}: {formatDate(goal.target_date)}
                                  </Text>
                                ) : null}
                              </View>
                            </View>
                          </View>
                        );
                      })
                    )}

                    <Text className="mb-2 mt-2 text-sm font-bold text-navy-text">
                      {t('carePlans.interventions')}
                    </Text>
                    {interventions.length === 0 ? (
                      <Text className="text-xs text-navy-muted">{t('carePlans.noInterventions')}</Text>
                    ) : (
                      interventions.map((intervention) => {
                        const InterventionIcon = INTERVENTION_ICON[intervention.intervention_type];
                        const tone = INTERVENTION_STATUS_TONE[intervention.status];
                        return (
                          <View key={intervention.id} className="mb-3 flex-row items-start">
                            <InterventionIcon size={16} color={colors.gold[600]} style={{ marginTop: 2 }} />
                            <View className="ml-2 flex-1">
                              <Text className="text-sm text-navy-text">
                                {t(`carePlans.interventionType.${intervention.intervention_type}`)}
                                {intervention.description ? ` — ${intervention.description}` : ''}
                              </Text>
                              <View className="mt-1 flex-row flex-wrap items-center">
                                <StatusPill label={t(`carePlans.interventionStatus.${intervention.status}`)} tone={tone} />
                                {intervention.frequency ? (
                                  <Text className="ml-2 text-xs text-navy-muted">{intervention.frequency}</Text>
                                ) : null}
                                {intervention.responsible_party ? (
                                  <Text className="ml-2 text-xs text-navy-muted">
                                    · {intervention.responsible_party}
                                  </Text>
                                ) : null}
                              </View>
                            </View>
                          </View>
                        );
                      })
                    )}
                  </View>
                ) : null}
              </Pressable>
            );
          })
        )}
        <View className="h-10" />
      </ScrollView>
    </Screen>
  );
}
