import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { apiClient } from './client';
import { endpoints } from './endpoints';
import type {
  Appointment,
  HealthIdCard,
  OfficialDocumentDetail,
  PaginatedAppointments,
  PaginatedDocuments,
  TemporaryQrCode,
} from './types';

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

// ---------------------------------------------------------------------------
// Appointments: list / detail / cancel / book (facility directory + slots)
// Added for the appointments screen (app/appointments/*). Kept below the
// original hooks above to stay additive-only per FILE OWNERSHIP.
// ---------------------------------------------------------------------------

export type AppointmentScope = 'upcoming' | 'past';

export interface AppointmentDetail extends Appointment {
  cancellation_reason: string | null;
  cancelled_at: string | null;
  no_show_at: string | null;
  visit_id: string | null;
}

export interface CareFacilitySummary {
  id: string;
  facility_name: string;
  facility_type: string;
  ownership_type: string | null;
  city: string | null;
  region: string | null;
  address: string | null;
  latitude: number | null;
  longitude: number | null;
  phone_primary: string | null;
  phone_secondary: string | null;
  email: string | null;
  website: string | null;
  integration_status: string | null;
  listing_status: string;
}

export interface PaginatedFacilities {
  data: CareFacilitySummary[];
  pagination: { total: number; per_page: number; current_page: number; last_page: number };
}

export interface AppointmentSlotOption {
  id: string;
  starts_at: string;
  ends_at: string;
  available_count: number;
  provider_id: string | null;
}

export interface FacilitySlotsResponse {
  facility_id: string | null;
  data: AppointmentSlotOption[];
}

export interface BookAppointmentPayload {
  facility_id: string;
  appointment_slot_id: string;
  appointment_type: string;
  reason?: string;
}

/** List the patient's appointments (upcoming or past). */
export function useAppointmentsList(scope: AppointmentScope, limit = 20) {
  return useQuery({
    queryKey: ['appointments', 'list', scope, limit],
    queryFn: async () =>
      (
        await apiClient.get<PaginatedAppointments>(endpoints.appointments, {
          params: { scope, limit },
        })
      ).data,
  });
}

/** Fetch one appointment's full detail. */
export function useAppointmentDetail(id: string | undefined) {
  return useQuery({
    queryKey: ['appointments', 'detail', id],
    queryFn: async () =>
      (await apiClient.get<{ data: AppointmentDetail }>(endpoints.appointment(id as string))).data.data,
    enabled: !!id,
  });
}

/** Cancel one of the patient's own appointments. */
export function useCancelAppointment() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async ({ id, reason }: { id: string; reason?: string }) =>
      (
        await apiClient.post<{ data: AppointmentDetail }>(endpoints.cancelAppointment(id), {
          reason,
        })
      ).data.data,
    onSuccess: (data) => {
      queryClient.setQueryData(['appointments', 'detail', data.id], data);
      queryClient.invalidateQueries({ queryKey: ['appointments'] });
    },
  });
}

/** Browse the facility directory — used by the booking flow's facility-picker step. */
export function useFacilities(params: { q?: string; type?: string; city?: string; page?: number }) {
  return useQuery({
    queryKey: ['facilities', 'list', params],
    queryFn: async () =>
      (await apiClient.get<PaginatedFacilities>(endpoints.facilities, { params })).data,
  });
}

/** List a facility's open upcoming appointment slots — booking flow's slot-picker step. */
export function useFacilitySlots(facilityId: string | undefined, date?: string) {
  return useQuery({
    queryKey: ['facilities', facilityId, 'slots', date ?? null],
    queryFn: async () =>
      (
        await apiClient.get<FacilitySlotsResponse>(endpoints.facilitySlots(facilityId as string), {
          params: date ? { date } : undefined,
        })
      ).data,
    enabled: !!facilityId,
  });
}

/** Book a slot into a new appointment — booking flow's confirm step. */
export function useBookAppointment() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (payload: BookAppointmentPayload) =>
      (await apiClient.post<{ data: AppointmentDetail }>(endpoints.appointments, payload)).data.data,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['appointments'] });
    },
  });
}

// ---------------------------------------------------------------------------
// Settings + push token registration (app/settings.tsx)
// ---------------------------------------------------------------------------
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

// ---------------------------------------------------------------------------
// Documents (app/documents.tsx)
// ---------------------------------------------------------------------------

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
