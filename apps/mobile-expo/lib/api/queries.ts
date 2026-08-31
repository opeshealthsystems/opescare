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

/** Shape of GET/PATCH /mobile/settings — see MobileSettingsController::formatSettings(). */
export interface MobileSettings {
  push_appointments: boolean;
  push_lab_results: boolean;
  push_prescriptions: boolean;
  push_billing: boolean;
  push_consent_requests: boolean;
  preferred_language: string;
  preferred_theme: 'light' | 'dark' | 'system';
  biometric_login_enabled: boolean;
}

export function useMobileSettings() {
  return useQuery({
    queryKey: ['mobile-settings'],
    queryFn: async () =>
      (await apiClient.get<{ data: MobileSettings }>(endpoints.settings)).data.data,
  });
}

export function useUpdateMobileSettings() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (patch: Partial<MobileSettings>) =>
      (await apiClient.patch<{ data: MobileSettings }>(endpoints.settings, patch)).data.data,
    onSuccess: (data) => {
      queryClient.setQueryData(['mobile-settings'], data);
    },
  });
}

interface RegisterPushTokenPayload {
  device_fingerprint: string;
  platform: 'ios' | 'android' | 'web';
  push_token: string;
}

export function useRegisterPushToken() {
  return useMutation({
    mutationFn: async (payload: RegisterPushTokenPayload) =>
      (
        await apiClient.post<{ status: string; token_id: string; platform: string }>(
          endpoints.pushTokens,
          payload,
        )
      ).data,
  });
}

export function useRevokePushToken() {
  return useMutation({
    mutationFn: async (tokenId: string) =>
      (await apiClient.delete<{ status: string }>(endpoints.pushToken(tokenId))).data,
  });
}
