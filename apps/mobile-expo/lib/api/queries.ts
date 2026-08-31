import { useMutation, useQuery } from '@tanstack/react-query';
import { apiClient } from './client';
import { endpoints } from './endpoints';
import type { HealthIdCard, PaginatedAppointments, TemporaryQrCode } from './types';

export function useHealthIdCard() {
  return useQuery({
    queryKey: ['health-id-card'],
    queryFn: async () => (await apiClient.get<HealthIdCard>(endpoints.healthIdCard)).data,
  });
}

/** POST /mobile/qr/temporary — issues a fresh 15-minute temporary-access QR on each call. */
export function useGenerateTemporaryQr() {
  return useMutation({
    mutationFn: async () =>
      (await apiClient.post<TemporaryQrCode>(endpoints.generateTemporaryQr)).data,
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
