import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
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

// ── Privacy & Data (consent, access logs, exports, corrections) ────────────
// Backing screens: app/privacy/index.tsx, app/privacy/access-logs.tsx, app/privacy/export.tsx.

/** Pulls a readable message out of an axios error, falling back to a generic one. */
export function extractApiErrorMessage(err: unknown): string {
  const anyErr = err as any;
  return anyErr?.response?.data?.message ?? 'Something went wrong. Please try again.';
}

export interface ConsentRequestItem {
  id: string;
  patient_id: string;
  requesting_facility_id: string | null;
  requesting_facility_name: string | null;
  requesting_facility_type: string | null;
  requesting_user_id: string | null;
  purpose: string;
  requested_scope: string[];
  duration_minutes: number;
  /** pending_patient_approval | approved | denied | expired */
  status: string;
  /** The active ConsentGrant this request produced, once approved — the id `revoke` targets. */
  grant_id: string | null;
  grant_status: string | null;
  grant_expires_at: string | null;
  created_at: string | null;
  updated_at: string | null;
}

export function useConsentRequests() {
  return useQuery({
    queryKey: ['consent-requests'],
    queryFn: async () =>
      (await apiClient.get<ConsentRequestItem[]>(endpoints.consentRequests)).data,
  });
}

export function useApproveConsent() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (requestId: string) =>
      (await apiClient.post(endpoints.approveConsent(requestId))).data,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['consent-requests'] }),
  });
}

export function useDenyConsent() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (requestId: string) =>
      (await apiClient.post(endpoints.denyConsent(requestId))).data,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['consent-requests'] }),
  });
}

export function useRevokeConsent() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (grantId: string) =>
      (await apiClient.post(endpoints.revokeConsent(grantId))).data,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['consent-requests'] }),
  });
}

export interface AccessLogFacility {
  id: string;
  name: string;
  type: string;
}

export interface AccessLogItem {
  id: string;
  patient_id: string | null;
  actor_id: string;
  actor_type: string;
  organization_id: string | null;
  facility_id: string | null;
  facility: AccessLogFacility | null;
  purpose: string;
  data_category: string;
  resource_type: string;
  resource_id: string | null;
  access_type: string;
  emergency_access: boolean;
  ip_address: string | null;
  user_agent: string | null;
  created_at: string;
}

export interface PaginatedAccessLogs {
  data: AccessLogItem[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export function useAccessLogs(page: number) {
  return useQuery({
    queryKey: ['access-logs', page],
    queryFn: async () =>
      (
        await apiClient.get<PaginatedAccessLogs>(endpoints.accessLogs, {
          params: { page },
        })
      ).data,
    placeholderData: keepPreviousData,
  });
}

/** full_record | encounters | prescriptions | lab_results | imaging */
export type DataExportType = 'full_record' | 'encounters' | 'prescriptions' | 'lab_results' | 'imaging';

export interface DataExportRequestItem {
  id: string;
  patient_id: string;
  requested_by_user_id: string;
  export_type: DataExportType;
  scope_json: string[] | null;
  /** pending | approved | rejected | expired | downloaded */
  status: string;
  approved_by: string | null;
  file_path: string | null;
  expires_at: string | null;
  created_at: string;
  updated_at: string;
}

export function useDataExportRequests() {
  return useQuery({
    queryKey: ['data-export-requests'],
    queryFn: async () =>
      (await apiClient.get<DataExportRequestItem[]>(endpoints.dataExportRequests)).data,
  });
}

export function useCreateDataExportRequest() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (exportType: DataExportType) =>
      (
        await apiClient.post<DataExportRequestItem>(endpoints.dataExportRequests, {
          export_type: exportType,
        })
      ).data,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['data-export-requests'] }),
  });
}

export interface DataExportDownload {
  id: string;
  export_type: DataExportType;
  status: string;
  generated_at: string;
  expires_at: string | null;
  content: {
    patient: {
      health_id: string | null;
      display_name: string;
      date_of_birth: string | null;
      sex: string | null;
    } | null;
    sections: Record<string, Record<string, unknown>[]>;
  };
}

export function useDownloadDataExport() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (id: string) =>
      (
        await apiClient.get<{ status: string; export: DataExportDownload; message: string }>(
          endpoints.downloadDataExport(id),
        )
      ).data.export,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['data-export-requests'] }),
  });
}

export interface CorrectionRequestPayload {
  resource_type: string;
  resource_id: string;
  reason: string;
}

export interface CorrectionRequestItem {
  id: string;
  patient_id: string;
  requested_by_user_id: string;
  resource_type: string;
  resource_id: string;
  reason: string;
  supporting_document_id: string | null;
  status: string;
  reviewed_by: string | null;
  reviewed_at: string | null;
  created_at: string;
  updated_at: string;
}

export function useCreateCorrectionRequest() {
  return useMutation({
    mutationFn: async (payload: CorrectionRequestPayload) =>
      (await apiClient.post<CorrectionRequestItem>(endpoints.correctionRequests, payload)).data,
  });
}
