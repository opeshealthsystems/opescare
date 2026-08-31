import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
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

// ---------------------------------------------------------------------------
// Insurance — policies + marketplace (screens: app/insurance/*)
// ---------------------------------------------------------------------------

export type InsurancePolicyStatus = 'pending' | 'active' | 'inactive' | 'expired' | 'cancelled';

export interface InsurancePolicy {
  id: string;
  policy_number: string;
  member_id: string | null;
  group_number: string | null;
  status: InsurancePolicyStatus;
  relationship_to_primary: string;
  effective_date: string | null;
  expiry_date: string | null;
  is_active: boolean;
  is_expired: boolean;
  plan: {
    id: string;
    name: string;
    provider_name: string | null;
    plan_type: string | null;
  } | null;
}

export interface InsurancePlanSummary {
  id: string;
  name: string;
  plan_type: string | null;
  description: string | null;
  monthly_premium: string | null;
  annual_premium: string | null;
  deductible: string | null;
  copay_percentage: string | null;
  cashless_available: boolean;
  requires_preauthorization: boolean;
}

export interface InsurancePlanDetail extends InsurancePlanSummary {
  covered_services: string | null;
  provider: {
    id: string;
    name: string;
    contact_email: string | null;
    contact_phone: string | null;
  } | null;
}

export interface InsuranceMarketplaceProvider {
  id: string;
  name: string;
  code: string | null;
  logo_url: string | null;
  contact_email: string | null;
  contact_phone: string | null;
  plans: InsurancePlanSummary[];
}

export interface PurchaseInsurancePlanPayload {
  payment_method: 'mobile_money' | 'card' | 'bank_transfer';
  payment_reference?: string;
}

export interface PurchaseInsurancePlanResponse {
  message: string;
  policy_id: string;
  policy_number: string;
  status: string;
  effective_date: string;
  expiry_date: string;
}

export function useInsurancePolicies() {
  return useQuery({
    queryKey: ['insurance', 'policies'],
    queryFn: async () =>
      (await apiClient.get<{ data: InsurancePolicy[] }>(endpoints.insurance)).data,
  });
}

export function useInsuranceMarketplace() {
  return useQuery({
    queryKey: ['insurance', 'marketplace'],
    queryFn: async () =>
      (
        await apiClient.get<{ data: InsuranceMarketplaceProvider[] }>(
          endpoints.insuranceMarketplace,
        )
      ).data,
  });
}

export function useInsurancePlanDetail(planId: string | undefined) {
  return useQuery({
    queryKey: ['insurance', 'marketplace', 'plan', planId],
    queryFn: async () =>
      (
        await apiClient.get<{ data: InsurancePlanDetail }>(
          endpoints.insuranceMarketplacePlan(planId as string),
        )
      ).data,
    enabled: !!planId,
  });
}

export function usePurchaseInsurancePlan(planId: string | undefined) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (payload: PurchaseInsurancePlanPayload) =>
      (
        await apiClient.post<PurchaseInsurancePlanResponse>(
          endpoints.insurancePurchasePlan(planId as string),
          payload,
        )
      ).data,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['insurance', 'policies'] });
    },
  });
}
