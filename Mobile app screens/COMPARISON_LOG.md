# Screen parity log

One row per reference image. A reference only moves into `built/` after a
**side-by-side done in the running app** — not on an agent's word, and not on a
typecheck. Agents were barred from starting Expo (20 of them share one machine),
so every visual verdict here is mine.

## Verdicts

| Verdict | Meaning |
|---|---|
| `BUILT` | Compared side-by-side in the browser at 375px. Matches, or differs only where the difference is deliberate and justified below. Reference moved to `built/`. |
| `GAPS` | Compared, real differences found. Stays out of `built/` until closed. |
| `RENDER-OK` | Renders cleanly (no clipped text, no horizontal overflow, no console errors) but not yet compared against its reference. |
| `NO-REF` | No reference image exists for this screen. Built in the established visual language. |

## Standing exclusions

Some reference elements cannot be built without inventing data or faking a
capability. These are deliberate, and are NOT counted as gaps:

- **Patient photography** — no photo-upload endpoint exists. Monogram avatars
  are used wherever a reference shows a face.
- **Named clinicians in access logs** — the endpoint returns `actor_id` /
  `actor_type`, never a name.
- **Document upload, file size/type** — no upload endpoint; the list returns
  neither.
- **Ratings / review counts / years of experience** on clinicians — not in the
  schema.
- **Map canvas** — `react-native-maps` is not a dependency; care-map uses a
  nearest-facility spotlight and directions deep links instead of a faked map.

## Log

| Screen | Reference | Verdict | Notes |
|---|---|---|---|
| Home | `a_clean_mobile_app_home_dashboard_ui_screenshot.png` (+3 dashboard variants) | **GAPS** | Quick actions are a 3x2 tile grid; reference is a single row of 5 in one card with dividers. **Health Vitals card missing entirely** (heart rate / BP / blood sugar / SpO2) — no vitals endpoint existed, so this is being built end to end, backend included. Appointment + Vitals should be a 2-column row. Health ID details should be 3 inline columns, not a 2x2 icon grid; needs a copy-to-clipboard control. Fixed already: all text clipping, and three dead links (Health Check, View All Services, Health Insights all pointed nowhere real). |
| Welcome | `a_clean_modern_mobile_app_welcome_landing_screen.png`, `..._onboarding_welcome_scre.png` | **RENDER-OK** | Renders full-width and clean. Both buttons previously pushed `/(auth)/login`; Get Started now goes to signup. Not yet compared element-by-element. |
| Login | `a_clean_mobile_app_login_screen_ui_iphone_like_t.png` | **RENDER-OK** | Google/Apple were dead controls with no OAuth endpoint — now honestly disabled. "Remember me" was fake state, now really persists the identifier. |
| Health ID | 3 card treatments (no dedicated screen reference) | **RENDER-OK** | Clipping fixed; renders clean once entry animation settles. |
| Records | `a_clean_modern_mobile_app_ui_screenshot_health_r.png` | **RENDER-OK** | Month-grouped timeline. Renders clean. |
| Profile / Edit / Family | `a_clean_mobile_app_profile_screen_ios_style_ui_s.png` | **RENDER-OK** | Renders clean. Verified badge bug fixed (`identity_status` is `verified`, not `active`). |
| Appointments | `a_clean_mobile_app_screenshot_ui_smartphone_portr.png` | **RENDER-OK** | Renders clean. |
| Booking wizard | `a_screenshot_of_a_mobile_app_ui_appointment_booki.png` | **RENDER-OK** | Confirm was a silent no-op until facilities were linked to slots. Now books for real (verified 201). |
| Pharmacy / Medicine | `a_clean_mobile_app_ui_screenshot_of_a_medicine_fi.png` | **RENDER-OK** | Renders clean; real medicines and XAF pricing. |
| Care map / Facility | `a_clean_flat_promotional_mockup_image_of_an_app_de.png` panel 08 | **RENDER-OK** | Default ordering fixed (no `ORDER BY` server-side). List cards had no navigation at all. |
| Blood finder | *(none — medicine finder used as archetype)* | **RENDER-OK** | Renders clean. |
| Prescriptions | *(no list reference)* | **RENDER-OK** | Renders clean. |
| Labs / Surveys | *(no reference)* | **NO-REF** | Renders clean. |
| Insurance | *(no reference)* | **NO-REF** | Renders clean. |
| Privacy / Documents | `a_tall_smartphone_app_screenshot_ui_mobile_health.png`, `a_bright_clean_..._health_r.png` | **RENDER-OK** | `Alert.alert` confirmations were silently no-ops on web — replaced with in-screen confirms. |
| Settings / Help / Notifications / Offline | `a_bright_clean_white_mobile_app_settings_screen.png` | *pending* | Agent in flight. |
| Referrals / Care plans / Doctor | *(none)* | **NO-REF** | `/referrals` and `/care-plans` are still URL-only — no nav link anywhere. |

## Sweep results (375px, browser, after animations settle)

14 routes checked for clipped text and horizontal overflow: **all clean**.
An earlier reading showed clipping on health-id, records and pharmacy — that was
measurement taken mid-animation, not a real defect, and re-measuring at rest
returned zero. Worth remembering: measure after the entry transition, or the
numbers lie.
