import { useQuery } from '@tanstack/react-query';
import { apiClient } from './client';
import { endpoints } from './endpoints';
import type { HealthIdCard, PaginatedAppointments } from './types';

export function useHealthIdCard() {
  return useQuery({
    queryKey: ['health-id-card'],
    queryFn: async () => (await apiClient.get<HealthIdCard>(endpoints.healthIdCard)).data,
  });
}

export function useUpcomingAppointments() {
  return useQuery({
    queryKey: ['appointments', 'upcoming'],
    queryFn: async () =>
      (
        await apiClient.get<PaginatedAppointments>(endpoints.appointments, {
          params: { scope: 'upcoming', limit: 3 },
        })
      ).data,
  });
}

// --- Care Plans (owned by the Care Plans & Surveys screen agent — additive only) ---
import { useMutation, useQueryClient } from '@tanstack/react-query';

export interface CarePlanGoal {
  id: string;
  care_plan_id: string;
  goal_text: string;
  target_date: string | null;
  status: 'pending' | 'in_progress' | 'achieved' | 'abandoned';
  achieved_at: string | null;
  notes: string | null;
}

export interface CarePlanIntervention {
  id: string;
  care_plan_id: string;
  intervention_type: 'medication' | 'exercise' | 'diet' | 'monitoring' | 'referral' | 'education' | 'other';
  description: string | null;
  frequency: string | null;
  responsible_party: string | null;
  status: 'active' | 'completed' | 'discontinued';
}

export interface CarePlan {
  id: string;
  patient_id: string;
  facility_id: string;
  title: string;
  description: string | null;
  start_date: string | null;
  end_date: string | null;
  status: 'active' | 'completed' | 'on_hold' | 'cancelled';
  visit_id: string | null;
  goals: CarePlanGoal[];
  interventions: CarePlanIntervention[];
}

export interface CarePlanSummary {
  plan: CarePlan;
  goals: CarePlanGoal[];
  interventions: CarePlanIntervention[];
  progress_pct: number;
}

export function useCarePlans() {
  return useQuery({
    queryKey: ['care-plans'],
    queryFn: async () => (await apiClient.get<{ data: CarePlan[] }>(endpoints.carePlans)).data.data,
  });
}

export function useCarePlan(id: string | null) {
  return useQuery({
    queryKey: ['care-plans', id],
    queryFn: async () =>
      (await apiClient.get<{ data: CarePlanSummary }>(endpoints.carePlan(id as string))).data.data,
    enabled: !!id,
  });
}

// --- Surveys (owned by the Care Plans & Surveys screen agent — additive only) ---
export type SurveyQuestionType = 'rating_5' | 'rating_10' | 'yes_no' | 'text';

export interface SurveyQuestion {
  key: string;
  text: string;
  type: SurveyQuestionType;
}

export interface Survey {
  id: string;
  patient_id: string;
  facility_id: string;
  visit_id: string | null;
  template_key: string;
  status: 'pending' | 'sent' | 'completed' | 'expired';
  sent_at: string | null;
  completed_at: string | null;
  expires_at: string | null;
}

export interface SurveyWithTemplate {
  survey: Survey;
  template: SurveyQuestion[];
}

export type SurveyResponseValue = number | boolean | string;

export function useSurveys() {
  return useQuery({
    queryKey: ['surveys'],
    queryFn: async () => (await apiClient.get<{ data: Survey[] }>(endpoints.surveys)).data.data,
  });
}

export function useSurvey(id: string | null) {
  return useQuery({
    queryKey: ['surveys', id],
    queryFn: async (): Promise<SurveyWithTemplate> => {
      const { data } = await apiClient.get<{ data: Survey; template: SurveyQuestion[] }>(
        endpoints.survey(id as string),
      );
      return { survey: data.data, template: data.template };
    },
    enabled: !!id,
  });
}

export function useSubmitSurvey(id: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (responses: Record<string, SurveyResponseValue>) =>
      (await apiClient.post<{ data: Survey }>(endpoints.submitSurvey(id), { responses })).data.data,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['surveys'] });
      queryClient.invalidateQueries({ queryKey: ['surveys', id] });
    },
  });
}
