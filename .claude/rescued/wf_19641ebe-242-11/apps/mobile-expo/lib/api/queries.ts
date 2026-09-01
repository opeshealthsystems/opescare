import { useMutation, useQuery } from '@tanstack/react-query';
import { apiClient } from './client';
import { endpoints } from './endpoints';
import type {
  HealthIdCard,
  OfficialDocumentDetail,
  PaginatedAppointments,
  PaginatedDocuments,
} from './types';

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

export function useDocuments() {
  return useQuery({
    queryKey: ['documents'],
    queryFn: async () => (await apiClient.get<PaginatedDocuments>(endpoints.documents)).data,
  });
}

/** Fetches a document's detail on demand — used to mint a fresh `verify_url`
 * right before opening it, since the backend only issues a token per-view. */
export function useDocumentViewer() {
  return useMutation({
    mutationFn: async (id: string) =>
      (await apiClient.get<{ data: OfficialDocumentDetail }>(endpoints.document(id))).data.data,
  });
}
