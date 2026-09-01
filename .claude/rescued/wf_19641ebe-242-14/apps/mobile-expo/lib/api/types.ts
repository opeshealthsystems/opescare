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
