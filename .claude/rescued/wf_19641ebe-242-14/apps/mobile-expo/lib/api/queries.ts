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

// ── Family: members + invitations ──────────────────────────────────────────
// Backend: App\Http\Controllers\Api\Mobile\MobileFamilyController.

export interface FamilyMemberPatient {
  id: string;
  health_id: string;
  full_name: string;
  date_of_birth: string | null;
  age: number | null;
}

export interface FamilyMember {
  id: string;
  relationship: string;
  access_level: 'view_only' | 'guardian' | 'full' | string;
  status: 'active' | 'pending_invite' | 'cancelled' | string;
  is_pending: boolean;
  patient: FamilyMemberPatient | null;
}

export interface FamilyInvitation {
  id: string;
  contact: string;
  relationship: string;
  method: string;
  sent_at: string | null;
  expires_at: string | null;
}

export function useFamilyMembers() {
  return useQuery({
    queryKey: ['family', 'members'],
    queryFn: async () =>
      (await apiClient.get<{ data: FamilyMember[] }>(endpoints.family)).data.data,
  });
}

export function useFamilyInvitations() {
  return useQuery({
    queryKey: ['family', 'invitations'],
    queryFn: async () =>
      (await apiClient.get<{ data: FamilyInvitation[] }>(endpoints.familyInvitations)).data.data,
  });
}

export interface SendFamilyInvitationInput {
  contact: string;
  relationship: string;
  method?: 'phone' | 'email' | 'qr';
  access_level?: 'view_only' | 'guardian' | 'full';
}

export function useSendFamilyInvitation() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (input: SendFamilyInvitationInput) =>
      (
        await apiClient.post<{ message: string; data: FamilyInvitation }>(
          endpoints.familyInvitations,
          input,
        )
      ).data,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['family'] });
    },
  });
}

export function useCancelFamilyInvitation() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (id: string) =>
      (await apiClient.delete<{ message: string }>(endpoints.familyInvitation(id))).data,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['family'] });
    },
  });
}
