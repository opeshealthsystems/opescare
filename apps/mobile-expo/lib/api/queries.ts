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

/** Notification Center — GET /mobile/notifications. Backed by the patient's +
 * their linked user's real notification records (see MobileNotificationController
 * on the API side); `category` is always one of appointments|health|messages|system. */
export interface NotificationItem {
  id: string;
  type: string;
  category: 'appointments' | 'health' | 'messages' | 'system';
  title: string;
  message: string;
  severity: 'high' | 'medium' | 'normal' | string;
  action_url: string | null;
  action_label: string | null;
  read: boolean;
  created_at: string | null;
}

export interface NotificationsResponse {
  data: NotificationItem[];
  unread_count: number;
}

export function useNotifications() {
  return useQuery({
    queryKey: ['notifications'],
    queryFn: async () =>
      (
        await apiClient.get<NotificationsResponse>(endpoints.notifications, {
          params: { limit: 50 },
        })
      ).data,
  });
}

export function useMarkNotificationRead() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (id: string) => (await apiClient.post(endpoints.markNotificationRead(id))).data,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['notifications'] }),
  });
}

export function useMarkAllNotificationsRead() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async () => (await apiClient.post(endpoints.markAllNotificationsRead)).data,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['notifications'] }),
  });
}
