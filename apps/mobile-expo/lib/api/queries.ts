import { useQuery } from '@tanstack/react-query';
import { apiClient } from './client';
import { endpoints } from './endpoints';
import type {
  AllergiesResponse,
  ClinicalResponse,
  HealthIdCard,
  ImmunizationsResponse,
  PaginatedAppointments,
  TimelineResponse,
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

/** Records/Timeline screen — chronological feed of visits, resulted labs, prescriptions. */
export function useTimeline(limit = 50) {
  return useQuery({
    queryKey: ['timeline', limit],
    queryFn: async () =>
      (await apiClient.get<TimelineResponse>(endpoints.timeline, { params: { limit } })).data,
  });
}

export function useAllergies() {
  return useQuery({
    queryKey: ['allergies'],
    queryFn: async () => (await apiClient.get<AllergiesResponse>(endpoints.allergies)).data,
  });
}

export function useClinical() {
  return useQuery({
    queryKey: ['clinical'],
    queryFn: async () => (await apiClient.get<ClinicalResponse>(endpoints.clinical)).data,
  });
}

export function useImmunizations() {
  return useQuery({
    queryKey: ['immunizations'],
    queryFn: async () =>
      (await apiClient.get<ImmunizationsResponse>(endpoints.immunizations)).data,
  });
}
