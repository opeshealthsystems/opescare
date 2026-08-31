import { create } from 'zustand';
import { apiClient, setSessionExpiredHandler } from '../api/client';
import { endpoints } from '../api/endpoints';
import { tokenStorage } from '../api/tokenStorage';
import type { AuthTokenResponse, Patient } from '../api/types';

type AuthStatus = 'booting' | 'unauthenticated' | 'otp_pending' | 'authenticated';

interface AuthState {
  status: AuthStatus;
  patient: Patient | null;
  pendingPhone: string | null;
  error: string | null;

  bootstrap: () => Promise<void>;
  loginWithEmail: (email: string, password: string) => Promise<void>;
  loginWithPhone: (phoneNumber: string, pin: string, dateOfBirth?: string) => Promise<void>;
  verifyOtp: (otp: string) => Promise<void>;
  resendOtp: () => Promise<void>;
  fetchMe: () => Promise<void>;
  logout: () => Promise<void>;
  clearError: () => void;
}

function extractErrorMessage(err: unknown): string {
  const anyErr = err as any;
  return anyErr?.response?.data?.message ?? 'Something went wrong. Please try again.';
}

export const useAuthStore = create<AuthState>((set, get) => ({
  status: 'booting',
  patient: null,
  pendingPhone: null,
  error: null,

  bootstrap: async () => {
    const token = await tokenStorage.get();
    if (!token) {
      set({ status: 'unauthenticated' });
      return;
    }
    try {
      await get().fetchMe();
      set({ status: 'authenticated' });
    } catch {
      await tokenStorage.clear();
      set({ status: 'unauthenticated' });
    }
  },

  loginWithEmail: async (email, password) => {
    set({ error: null });
    try {
      const { data } = await apiClient.post<AuthTokenResponse>(endpoints.loginEmail, {
        email,
        password,
      });
      await tokenStorage.set(data.access_token);
      await get().fetchMe();
      set({ status: 'authenticated' });
    } catch (err) {
      set({ error: extractErrorMessage(err) });
      throw err;
    }
  },

  loginWithPhone: async (phoneNumber, pin, dateOfBirth) => {
    set({ error: null });
    try {
      await apiClient.post(endpoints.loginPhone, {
        phone_number: phoneNumber,
        pin,
        ...(dateOfBirth ? { date_of_birth: dateOfBirth } : {}),
      });
      set({ status: 'otp_pending', pendingPhone: phoneNumber });
    } catch (err) {
      set({ error: extractErrorMessage(err) });
      throw err;
    }
  },

  verifyOtp: async (otp) => {
    const phone = get().pendingPhone;
    if (!phone) throw new Error('No pending phone login');
    set({ error: null });
    try {
      const { data } = await apiClient.post<AuthTokenResponse>(endpoints.verifyOtp, {
        phone_number: phone,
        otp,
      });
      await tokenStorage.set(data.access_token);
      await get().fetchMe();
      set({ status: 'authenticated', pendingPhone: null });
    } catch (err) {
      set({ error: extractErrorMessage(err) });
      throw err;
    }
  },

  resendOtp: async () => {
    const phone = get().pendingPhone;
    if (!phone) return;
    await apiClient.post(endpoints.resendOtp, { phone_number: phone });
  },

  fetchMe: async () => {
    const { data } = await apiClient.get<Patient>(endpoints.me);
    set({ patient: data });
  },

  logout: async () => {
    await tokenStorage.clear();
    set({ status: 'unauthenticated', patient: null, pendingPhone: null });
  },

  clearError: () => set({ error: null }),
}));

// Wired once, outside the component tree: if any request's token refresh fails,
// force the store back to unauthenticated so the router redirects to login.
setSessionExpiredHandler(() => {
  useAuthStore.setState({ status: 'unauthenticated', patient: null });
});
