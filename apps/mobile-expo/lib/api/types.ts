export interface Patient {
  health_id: string;
  display_name: string;
  first_name: string;
  last_name: string;
  phone: string | null;
  email: string | null;
  dob: string | null;
  sex: string | null;
  blood_group: string | null;
  status: string;
  allergies_count: number;
  conditions_count: number;
}

export interface AuthTokenResponse {
  status: 'authenticated' | 'refreshed';
  access_token: string;
  token_type: 'Bearer';
  expires_in: number;
  patient_id: string;
}

export interface ApiErrorBody {
  message: string;
}

export interface Appointment {
  id: string;
  appointment_type: string;
  status: string;
  facility_name: string | null;
  provider_name: string | null;
  scheduled_at: string | null;
  checked_in_at: string | null;
  reason: string | null;
}

export interface PaginatedAppointments {
  data: Appointment[];
  pagination: { total: number; per_page: number; current_page: number; last_page: number };
}

export interface HealthIdCard {
  health_id: string;
  display_name: string;
  sex: string | null;
  dob: string | null;
  blood_type: string | null;
  qr_payload: string;
  status: string;
}

/** One row of GET /mobile/timeline — a visit, resulted lab, or issued prescription. */
export interface TimelineEvent {
  event_type: 'visit' | 'lab_result' | 'prescription';
  id: string;
  facility_name: string | null;
  occurred_at: string;
  summary: string;
}

export interface TimelineResponse {
  timeline: TimelineEvent[];
}

export interface Allergy {
  id: string;
  substance: string;
  severity: string | null;
  status: string;
  recorded: string | null;
}

export interface AllergiesResponse {
  blood_group: string | null;
  allergies: Allergy[];
}

export interface ClinicalCondition {
  id: string;
  display_name: string;
  code: string | null;
  code_system: string | null;
  status: string;
  recorded: string | null;
}

export interface ClinicalResponse {
  conditions: ClinicalCondition[];
}

export interface Immunization {
  id: string;
  vaccine_name: string;
  lot_number: string | null;
  dose_number: number | null;
  administered_at: string | null;
  status: string;
}

export interface ImmunizationsResponse {
  immunizations: Immunization[];
}
