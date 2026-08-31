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

// -- Prescriptions ----------------------------------------------------------
// Read-only view of medications a care team has prescribed. Distinct from
// pharmacy/medicine search (a separate, not-yet-built catalog feature).

export interface PrescriptionSummary {
  id: string;
  facility_name: string | null;
  status: string;
  status_color: 'success' | 'info' | 'warning' | 'default';
  item_count: number;
  prescribed_at: string | null;
  dispensed_at: string | null;
  expires_at: string | null;
}

export interface PrescriptionItemDetail {
  id: string;
  drug_name: string;
  drug_code: string | null;
  dose: string | null;
  frequency: string | null;
  route: string | null;
  duration_days: number | null;
  quantity: number | null;
  status: string;
  is_dispensed: boolean;
  dispensed_at: string | null;
  dispense_notes: string | null;
}

export interface PrescriptionDetail extends PrescriptionSummary {
  notes: string | null;
  items: PrescriptionItemDetail[];
}

export interface PaginatedPrescriptions {
  data: PrescriptionSummary[];
  pagination: { total: number; per_page: number; current_page: number; last_page: number };
}

/** List of the patient's prescriptions, optionally filtered by status
 * ('active' | 'dispensed' | 'partially_dispensed' | 'expired' | 'cancelled'). */
export function usePrescriptions(status?: string) {
  return useQuery({
    queryKey: ['prescriptions', status ?? 'all'],
    queryFn: async () =>
      (
        await apiClient.get<PaginatedPrescriptions>(endpoints.prescriptions, {
          params: status ? { status } : undefined,
        })
      ).data,
  });
}

/** Single prescription with its full medication list. */
export function usePrescriptionDetail(id: string | undefined) {
  return useQuery({
    queryKey: ['prescriptions', id],
    queryFn: async () =>
      (await apiClient.get<{ data: PrescriptionDetail }>(endpoints.prescription(id as string))).data.data,
    enabled: !!id,
  });
}
