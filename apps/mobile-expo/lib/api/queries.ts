import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { apiClient } from './client';
import { endpoints } from './endpoints';
import type {
  AllergiesResponse,
  Appointment,
  ClinicalResponse,
  HealthIdCard,
  ImmunizationsResponse,
  OfficialDocumentDetail,
  PaginatedAppointments,
  PaginatedDocuments,
  TemporaryQrCode,
  TimelineResponse,
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

// ---------------------------------------------------------------------------
// Referrals (app/referrals.tsx)
// ---------------------------------------------------------------------------

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

// ---------------------------------------------------------------------------
// Records / Timeline (app/(tabs)/records.tsx)
// ---------------------------------------------------------------------------

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
