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

/** Broader appointment list — used by the Messages "new conversation" picker
 * (any appointment can be the care-relationship proof for starting a thread
 * with its provider, not just the next 3 upcoming ones). */
export function useAppointmentsForMessaging() {
  return useQuery({
    queryKey: ['appointments', 'all', 'messaging'],
    queryFn: async () =>
      (
        await apiClient.get<PaginatedAppointments>(endpoints.appointments, {
          params: { scope: 'all', limit: 20 },
        })
      ).data,
  });
}

// ── Messaging ────────────────────────────────────────────────────────────
// Backend: routes/mobile_telehealth.php + MobileMessagingController — the
// real, additive patient-facing entry point onto the Messaging module.

export interface MessageThreadSummary {
  id: number;
  title: string;
  status: string;
  priority: string;
  thread_type: string;
  updated_at: string | null;
  unread: boolean;
  last_message: { body: string; is_mine: boolean; created_at: string | null } | null;
}

export interface ThreadMessage {
  id: number;
  is_mine: boolean;
  body: string;
  status: string;
  created_at: string | null;
}

export interface MessageThreadDetail {
  id: number;
  title: string;
  status: string;
  messages: ThreadMessage[];
}

export function useMessageThreads() {
  return useQuery({
    queryKey: ['message-threads'],
    queryFn: async () =>
      (await apiClient.get<{ data: MessageThreadSummary[] }>(endpoints.messageThreads)).data.data,
  });
}

export function useMessageThread(id: number | null) {
  return useQuery({
    queryKey: ['message-threads', id],
    queryFn: async () =>
      (await apiClient.get<{ data: MessageThreadDetail }>(endpoints.messageThread(id as number))).data
        .data,
    enabled: id !== null,
  });
}

export function useSendThreadMessage(threadId: number | null) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (body: string) =>
      (
        await apiClient.post<{ data: ThreadMessage }>(
          endpoints.sendThreadMessage(threadId as number),
          { body },
        )
      ).data.data,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['message-threads'] });
    },
  });
}

export function useStartMessageThread() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (payload: { appointment_id: string; body: string }) =>
      (await apiClient.post<{ data: MessageThreadDetail }>(endpoints.messageThreads, payload)).data
        .data,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['message-threads'] });
    },
  });
}

// ── Telemedicine ─────────────────────────────────────────────────────────
// Backend: routes/mobile_telehealth.php + MobileTelemedicineController — the
// real, additive patient-facing entry point onto the Telemedicine module.

export interface TeleconsultationSummary {
  id: string;
  status: 'scheduled' | 'waiting' | 'active' | 'completed' | 'cancelled' | 'failed';
  platform: string | null;
  provider_id: string | null;
  provider_name: string | null;
  scheduled_at: string | null;
  started_at: string | null;
  ended_at: string | null;
  duration_seconds: number | null;
}

export interface TeleconsultationDetail extends TeleconsultationSummary {
  facility_name: string | null;
  consent: { consented: boolean; method: string | null; consented_at: string | null; revoked_at: string | null } | null;
  waiting_room: { status: string; estimated_wait_minutes: number | null } | null;
  call_session: { status: string; started_at: string | null; video_enabled: boolean; audio_enabled: boolean } | null;
}

export function useTeleconsultations(scope: 'upcoming' | 'past' = 'upcoming') {
  return useQuery({
    queryKey: ['teleconsultations', scope],
    queryFn: async () =>
      (
        await apiClient.get<{ data: TeleconsultationSummary[] }>(endpoints.teleconsultations, {
          params: { scope },
        })
      ).data.data,
  });
}

export function useTeleconsultation(id: string | null) {
  return useQuery({
    queryKey: ['teleconsultations', id],
    queryFn: async () =>
      (await apiClient.get<{ data: TeleconsultationDetail }>(endpoints.teleconsultation(id as string)))
        .data.data,
    enabled: id !== null,
    refetchInterval: (query) =>
      query.state.data?.call_session?.status === 'active' ? 5000 : false,
  });
}

export function useGrantTelemedicineConsent(id: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (recordingConsent: boolean) =>
      (
        await apiClient.post(endpoints.teleconsultationConsent(id), {
          recording_consent: recordingConsent,
        })
      ).data,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['teleconsultations', id] }),
  });
}

export function useJoinTeleconsultation(id: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async () => (await apiClient.post(endpoints.teleconsultationJoin(id))).data,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['teleconsultations', id] }),
  });
}

export function useEndTeleconsultation(id: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async () => (await apiClient.post(endpoints.teleconsultationEnd(id))).data,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['teleconsultations', id] });
      queryClient.invalidateQueries({ queryKey: ['teleconsultations', 'upcoming'] });
    },
  });
}
