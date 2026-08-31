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

/** GET /mobile/referrals — the authenticated patient's referral cases, newest
 * first. Read-only from the mobile app; consumed by app/referrals.tsx. */
export type ReferralStatus =
  | 'draft'
  | 'sent'
  | 'accepted'
  | 'rejected'
  | 'cancelled'
  | 'completed'
  | 'expired';

export type ReferralUrgency = 'routine' | 'urgent' | 'emergency';

export interface Referral {
  id: string;
  status: ReferralStatus;
  reason: string;
  notes: string | null;
  urgency: ReferralUrgency;
  referring_facility: string;
  receiving_facility: string;
  referred_at: string | null;
  accepted_at: string | null;
  completed_at: string | null;
}

export interface ReferralsResponse {
  data: Referral[];
}

export function useReferrals() {
  return useQuery({
    queryKey: ['referrals'],
    queryFn: async () => (await apiClient.get<ReferralsResponse>(endpoints.referrals)).data,
  });
}
