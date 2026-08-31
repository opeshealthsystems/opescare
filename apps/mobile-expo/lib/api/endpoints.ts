/**
 * OpesCare Mobile API endpoints — mirrors apps/mobile-patient/lib/core/api/api_endpoints.dart
 * 1:1, since Expo talks to the exact same live /mobile/* surface as the Flutter app.
 * Base URL is overridable via EXPO_PUBLIC_API_BASE_URL; defaults to the live
 * staging/production host confirmed reachable during development.
 */
export const API_BASE_URL =
  process.env.EXPO_PUBLIC_API_BASE_URL ?? 'https://opescare.cloud/api';

export const endpoints = {
  loginEmail: '/mobile/auth/login-email',
  loginPhone: '/mobile/auth/login',
  verifyOtp: '/mobile/auth/otp/verify',
  resendOtp: '/mobile/auth/otp/resend',
  refresh: '/mobile/auth/refresh',
  appConfig: '/mobile/app-config',

  me: '/mobile/me',
  timeline: '/mobile/timeline',
  healthIdCard: '/mobile/health-id-card',
  generateTemporaryQr: '/mobile/qr/temporary',
  allergies: '/mobile/allergies',
  clinical: '/mobile/clinical',
  immunizations: '/mobile/immunizations',

  labs: '/mobile/labs',
  lab: (id: string) => `/mobile/labs/${id}`,

  prescriptions: '/mobile/prescriptions',
  prescription: (id: string) => `/mobile/prescriptions/${id}`,

  appointments: '/mobile/appointments',
  appointment: (id: string) => `/mobile/appointments/${id}`,
  cancelAppointment: (id: string) => `/mobile/appointments/${id}/cancel`,

  facilities: '/mobile/facilities',
  facility: (id: string) => `/mobile/facilities/${id}`,
  facilitySlots: (id: string) => `/mobile/facilities/${id}/slots`,

  documents: '/mobile/documents',
  document: (id: string) => `/mobile/documents/${id}`,

  settings: '/mobile/settings',
  pushTokens: '/mobile/push-tokens',
  pushToken: (id: string) => `/mobile/push-tokens/${id}`,

  carePlans: '/mobile/care-plans',
  carePlan: (id: string) => `/mobile/care-plans/${id}`,

  surveys: '/mobile/surveys',
  survey: (id: string) => `/mobile/surveys/${id}`,
  submitSurvey: (id: string) => `/mobile/surveys/${id}/submit`,

  exportRecordsPdf: '/mobile/medical-records/export/pdf',
  exportRecordsFhir: '/mobile/medical-records/export/fhir',

  referrals: '/mobile/referrals',

  insurance: '/mobile/insurance',
  insuranceMarketplace: '/mobile/insurance/marketplace',
  insuranceMarketplacePlan: (id: string) => `/mobile/insurance/marketplace/plans/${id}`,
  insurancePurchasePlan: (id: string) => `/mobile/insurance/marketplace/plans/${id}/purchase`,

  family: '/mobile/family',
  familyInvitations: '/mobile/family/invitations',
  familyMember: (id: string) => `/mobile/family/members/${id}`,
  familyInvitation: (id: string) => `/mobile/family/invitations/${id}`,

  consentRequests: '/mobile/consent-requests',
  approveConsent: (id: string) => `/mobile/consent-requests/${id}/approve`,
  denyConsent: (id: string) => `/mobile/consent-requests/${id}/deny`,
  revokeConsent: (id: string) => `/mobile/consents/${id}/revoke`,
  accessLogs: '/mobile/access-logs',

  notifications: '/mobile/notifications',
  notificationUnreadCount: '/mobile/notifications/unread-count',
  markNotificationRead: (id: string) => `/mobile/notifications/${id}/read`,
  markAllNotificationsRead: '/mobile/notifications/mark-all-read',

  // Not yet implemented on the backend — added in Phase 2 (see design spec §3, §6).
  pharmacyNearby: '/mobile/pharmacy/nearby',
  medicineSearch: '/mobile/pharmacy/medicines',
} as const;
