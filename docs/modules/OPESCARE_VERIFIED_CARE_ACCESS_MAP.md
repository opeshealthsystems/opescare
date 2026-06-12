# OpesCare Verified Care Access Map PRD

**Project:** OpesCare  
**Parent Company:** Opesware  
**Document Type:** Product Requirements + Technical Architecture + UI/UX + Governance Blueprint  
**Module Name:** OpesCare Verified Care Access Map  
**Alternative Name:** Health Services Directory & Access Map  
**Build Direction:** Build from scratch or safely extend existing directory/map modules  
**Core Backend:** Laravel  
**Database:** PostgreSQL + PostGIS recommended for geospatial search  
**Queue/Cache:** Redis  
**Search:** PostgreSQL full-text search initially; optional Meilisearch/Typesense/Elasticsearch later  
**Maps:** Provider-agnostic map layer; support Google Maps, Mapbox, OpenStreetMap, or other provider through abstraction  
**Mobile App:** Flutter recommended  
**Important Rule:** Do not use OpesHIS OS. Do not copy OpesHIS OS map, facility, directory, database, UI, or listing assumptions.  
**Safety Rule:** The map must not guarantee clinical service availability. It must show verified and recently updated information with clear safety disclaimers.

---

# 1. Purpose

The **OpesCare Verified Care Access Map** helps patients, families, healthcare workers, hospitals, clinics, pharmacies, insurers, public health officers, and partners find verified healthcare services.

It is not just a map. It is a **verified healthcare access directory** connected to OpesCare’s Health ID, partner governance, pharmacy stock, blood availability, lab test directory, insurance workflows, and public health modules.

The module helps users answer:

```text
Where is the nearest verified hospital?
Where can I get this medication?
Which pharmacy has this medicine in stock?
Where can I do this lab test?
Which lab offers this test?
Where is blood available?
Which hospital offers emergency care?
Which facilities accept my insurance?
Which specialist is available?
Which clinic is open now?
Which facility is verified by OpesCare?
```

The goal is to make OpesCare useful beyond records. It helps patients move from health information to actual care access.

---

# 2. Product Positioning

Correct positioning:

```text
Verified Care Access Map
Health Services Directory
Verified Healthcare Access Directory
Care Navigation Map
```

Avoid positioning it as:

```text
simple map
random business directory
unverified hospital list
guaranteed availability finder
emergency dispatch service unless formally built and approved
```

The core promise should be:

```text
Find verified healthcare services, check reported availability, and connect to the right care faster.
```

Not:

```text
We guarantee treatment, medicine, blood, or immediate service.
```

---

# 3. Why This Module Is Needed

OpesCare helps patients carry their medical information through a Health ID. But patients also need to know **where to go**.

The Care Access Map connects patient needs to service locations.

Examples:

```text
Patient has prescription → find pharmacies with reported stock
Patient needs lab test → find labs offering that test
Patient needs blood → find blood banks/hospitals with reported availability
Patient needs emergency care → find emergency-capable facilities
Patient has insurance → find facilities that accept that insurer
Patient needs specialist → find hospitals/clinics with that specialty
Patient has referral → find receiving facility and directions
```

This module increases the platform’s daily usefulness.

Without it, OpesCare may only be used during hospital visits. With it, patients can use OpesCare anytime they need care access.

---

# 4. Core Principle

Every listing on the map must answer:

1. What facility or service is this?
2. Is it verified?
3. Who owns or manages the listing?
4. What services are offered?
5. Is the information fresh?
6. When was availability last updated?
7. What can the patient safely rely on?
8. What must the patient confirm before travelling?
9. Is the facility linked to a verified OpesCare partner?
10. Is this facility integrated with OpesCare?
11. What data can be shown publicly?
12. What data requires login or partner authorization?
13. Who updated this listing?
14. Who verified it?
15. What happens if the information is wrong?

If these questions cannot be answered, the listing should be treated as unverified, limited, or hidden depending on risk.

---

# 5. Facility and Service Types

The map must support verified listings for:

```text
hospitals
teaching hospitals
specialist hospitals
clinics
health centers
medical centers
pharmacies
hospital pharmacies
laboratories
diagnostic centers
radiology/imaging centers
blood banks
ambulance/emergency services
dental clinics
mental health centers
maternal health centers
vaccination centers
rehabilitation centers
physiotherapy centers
specialist consultation centers
public health offices
insurance-supported facilities
telemedicine-supported facilities
medical equipment suppliers where relevant
```

---

# 6. Main User Groups

## 6.1 Patients and Guardians

Use the map to find:

```text
nearby facilities
medicines
lab tests
blood availability
emergency services
insurance-supported providers
specialists
vaccination centers
directions/contact info
```

## 6.2 Doctors and Nurses

Use the map to:

```text
refer patients
find available lab services
find blood availability
send patient to pharmacy with stock
locate specialist centers
coordinate care with other facilities
```

## 6.3 Pharmacies

Use the map to:

```text
publish stock availability
receive reservation requests where enabled
update stock freshness
show verified pharmacy profile
```

## 6.4 Labs and Imaging Centers

Use the map to:

```text
publish available tests/services
show operating hours
receive test requests where enabled
publish sample collection options
```

## 6.5 Hospitals and Clinics

Use the map to:

```text
show services
show departments
show emergency capability
show accepted insurance
show referral capacity
update blood/bed/service availability where enabled
```

## 6.6 Insurance Companies

Use the map to:

```text
show network facilities
show covered providers
support eligibility-based facility search
```

## 6.7 Public Health Users

Use the map to:

```text
monitor service coverage
view shortages
view medicine stock-out patterns
view blood shortages
view facility reporting completeness
```

## 6.8 OpesCare Administrators

Use the map to:

```text
verify listings
moderate corrections
resolve reports
approve facility claims
monitor freshness
audit updates
```

---

# 7. Listing Trust Model

Every listing must have a trust and verification status.

## 7.1 Listing Statuses

```text
draft
submitted
under_review
verified
active
limited
unverified
rejected
suspended
closed
archived
```

## 7.2 Verification Statuses

```text
unverified
self_reported
document_verified
partner_verified
facility_verified
license_verified
government_verified
suspended
expired
```

## 7.3 Data Freshness Statuses

```text
fresh
recent
stale
expired
unknown
```

Suggested freshness thresholds:

```text
pharmacy stock: fresh <= 24 hours, stale > 24 hours
blood availability: fresh <= 2 hours, stale > 6 hours
emergency service status: fresh <= 24 hours
facility profile: fresh <= 90 days
lab test availability: fresh <= 30 days
insurance acceptance: fresh <= 30 days
opening hours: fresh <= 90 days
```

These thresholds must be configurable.

## 7.4 Listing Visibility Rules

```text
verified listings: public by default
self-reported listings: public with warning or hidden based on policy
unverified listings: limited display or hidden
suspended listings: hidden from patient search
closed listings: hidden or shown as closed only
expired verification: show warning or hide depending risk
```

---

# 8. Facility Profile Requirements

Each facility listing should include:

```text
facility name
facility type
verification badge
license number where public/policy allows
facility ownership type
address
city/region/country
GPS coordinates
phone numbers
email
website
opening hours
emergency hours
services offered
departments
specialties
accepted insurance
payment methods
languages spoken where available
accessibility notes
parking/transport notes optional
photos optional
logo optional
integration status
last updated time
data freshness badge
report wrong information button
directions button
call button
save/share button
```

## 8.1 Required Internal Fields

```text
facility_id
partner_id nullable
organization_id nullable
verification_status
license_status
created_by
verified_by
last_verified_at
last_profile_update_at
last_availability_update_at
source_of_data
audit_status
```

---

# 9. Location and Geospatial Requirements

## 9.1 Coordinates

Every map listing must store:

```text
latitude
longitude
geocoding_accuracy
geocoding_source
geocoded_at
manual_override_allowed
```

## 9.2 Location Accuracy Levels

```text
exact
street_level
area_level
city_level
unknown
```

## 9.3 Geospatial Search

Users should search by:

```text
near me
city
region
facility name
service
specialty
medicine
lab test
blood group
insurance
open now
emergency now
```

## 9.4 Distance Calculation

Support:

```text
distance from current location
distance from selected address
distance from saved patient location
distance from referring facility
```

## 9.5 Map Provider Abstraction

Do not hard-code the map provider.

Use:

```text
MapProviderInterface
GeocodingProviderInterface
DirectionsProviderInterface
PlacesProviderInterface optional
```

Supported future providers:

```text
Google Maps
Mapbox
OpenStreetMap
Here Maps
local/regional geocoding provider
```

---

# 10. Patient Location Privacy

Location data is sensitive.

## 10.1 Patient Location Rules

```text
ask permission before using current location
allow manual location entry
do not store precise location unless user saves it
allow location history off by default
mask analytics
do not expose patient location to facilities without explicit action
```

## 10.2 Saved Locations

Patients may save:

```text
home area
work area
preferred city
care region
```

Avoid forcing exact home address.

## 10.3 Location Audit

Audit if precise location is used for sensitive workflows, such as emergency or blood requests.

---

# 11. Search and Filter Features

## 11.1 Basic Search

```text
facility name
facility type
city/region
service
specialty
```

## 11.2 Advanced Search

```text
medicine name
generic medicine name
lab test name
blood group
imaging service
insurance provider
open now
emergency available
verified only
distance
rating/quality indicator where allowed
accepts appointments
integrated with OpesCare
```

## 11.3 Sorting

Sort by:

```text
nearest
most recently updated
verified first
open now
service match
insurance match
availability confidence
```

Do not sort purely by paid promotion unless clearly labeled and policy allows.

## 11.4 No-Result Handling

If no result:

```text
show nearest alternatives
suggest broadening location
suggest related service type
show call emergency services for urgent cases
allow alert request where appropriate
```

---

# 12. Pharmacy Stock Finder

This feature connects to the medicine availability module.

## 12.1 Purpose

Patients can search for a medicine and see pharmacies with recently reported stock.

## 12.2 Pharmacy Stock Fields

```text
medicine_name
generic_name
brand_name optional
strength
form
quantity_available_range
availability_status
price optional
currency optional
batch/lot optional internal
expiry date optional internal/public policy
last_updated_at
updated_by
source_system
stock_freshness_status
reservation_enabled
```

## 12.3 Availability Statuses

```text
reported_available
low_stock
out_of_stock
unknown
stale
reserved_limited
```

## 12.4 Public Display Wording

Use:

```text
Reported available
Low stock reported
Out of stock reported
Availability not recently updated
Call to confirm before travelling
```

Avoid:

```text
Guaranteed available
Definitely in stock
```

## 12.5 Medicine Search Flow

1. Patient searches medicine name.
2. System normalizes medicine/generic name.
3. System checks available pharmacies.
4. Results sorted by distance, freshness, verification, and stock status.
5. Patient can call, get directions, reserve if enabled, or save.
6. Search event is logged anonymously/aggregated where appropriate.

## 12.6 Reservation Flow Optional

1. Patient selects pharmacy.
2. Patient requests reservation.
3. Pharmacy confirms or rejects.
4. Reservation expires after configured time.
5. Patient receives notification.
6. Audit event is created.

Reservation must not promise clinical appropriateness of medicine.

---

# 13. Blood Availability Finder

## 13.1 Purpose

Patients/providers can find reported blood availability or blood request support.

## 13.2 Blood Listing Types

```text
blood bank availability
hospital blood availability
urgent blood request
blood component availability
```

## 13.3 Blood Data Fields

```text
blood_group
component_type
units_available_range
availability_status
last_updated_at
source_facility
verification_status
contact_protocol
emergency_contact
```

## 13.4 Blood Components

```text
whole_blood
packed_red_cells
platelets
plasma
cryoprecipitate
other
```

## 13.5 Public Display Safety

Use:

```text
Reported availability
Recently updated
Contact facility immediately
Emergency cases should follow facility instructions
```

Avoid:

```text
Guaranteed blood available
Blood reserved automatically
```

## 13.6 Patient Safety Rule

Blood availability workflows should strongly encourage contact with authorized facilities.

Do not allow unsafe direct patient-to-donor workflows unless separately designed, legally reviewed, and approved.

## 13.7 Blood Search Flow

1. User selects blood group/component.
2. User selects location or uses current location.
3. System returns verified/recently updated facilities.
4. User can call facility or get directions.
5. Providers may initiate official blood request if permitted.
6. All urgent workflows are logged and may create alerts.

---

# 14. Lab Test Finder

## 14.1 Purpose

Patients/providers can find labs offering a particular test.

## 14.2 Lab Test Fields

```text
test_name
local_test_code
LOINC code where mapped
specimen_type
turnaround_time
price optional
sample_collection_available
home_sample_collection_available optional
requires_doctor_order
availability_status
last_updated_at
```

## 14.3 Lab Search Flow

1. User searches test name.
2. System maps local/common names.
3. Results show labs offering test.
4. Show distance, verification, opening hours, sample collection rules, and freshness.
5. Patient can call, get directions, or send request if enabled.

## 14.4 Public Safety

Show:

```text
Some tests may require a clinician’s request. Confirm requirements with the lab or your healthcare provider.
```

---

# 15. Emergency Care Finder

## 15.1 Purpose

Help users find emergency-capable facilities.

## 15.2 Emergency Capability Fields

```text
emergency_department_available
24_7_emergency
ambulance_available
trauma_capability
maternity_emergency
pediatric_emergency
ICU_available optional
oxygen_available optional
emergency_contact
last_updated_at
```

## 15.3 Emergency Safety Warning

Display clearly:

```text
If this is a life-threatening emergency, contact local emergency services or go to the nearest emergency facility immediately. Availability may change.
```

## 15.4 Emergency Search Flow

1. User selects emergency care or urgent care.
2. System shows nearest emergency-capable facilities.
3. Sort by distance, open/24-7, verification, and freshness.
4. Show call and directions prominently.
5. Avoid complex filters that slow urgent decisions.

---

# 16. Specialist and Service Finder

## 16.1 Specialties

Support:

```text
general medicine
pediatrics
obstetrics and gynecology
surgery
cardiology
neurology
orthopedics
dermatology
ophthalmology
ENT
dentistry
mental health
physiotherapy
nutrition
radiology
laboratory
emergency medicine
public health
```

## 16.2 Service Fields

```text
service_name
specialty
facility_id
provider_count optional
appointment_required
walk_in_allowed
telemedicine_available
price_range optional
last_updated_at
```

---

# 17. Insurance Network Finder

## 17.1 Purpose

Help patients find facilities that accept their insurer or plan.

## 17.2 Insurance Fields

```text
insurance_company
plan_name optional
facility_id
coverage_type
preauthorization_required
cashless_available
claim_supported
last_verified_at
```

## 17.3 Insurance Search Flow

1. Patient selects insurer/plan.
2. System shows accepted facilities.
3. Patient can filter by service/specialty/location.
4. System warns that coverage must be confirmed.

## 17.4 Safety Wording

```text
Insurance acceptance and coverage may vary by service and plan. Confirm with your insurer or facility before receiving care.
```

---

# 18. Facility Claiming and Ownership

Facilities need a controlled way to claim their profile.

## 18.1 Claim Flow

1. Facility representative finds listing.
2. Clicks “Claim this facility.”
3. Submits proof of authority.
4. Uploads documents.
5. OpesCare reviews.
6. Listing becomes partner-managed if approved.
7. Audit event created.

## 18.2 Claim Required Documents

```text
facility license
business/organization registration where applicable
authorized representative ID
professional/facility authorization letter
phone/email/domain verification
```

## 18.3 Claim Statuses

```text
submitted
under_review
more_information_required
approved
rejected
revoked
```

---

# 19. Data Contribution and Update Sources

Listing data can come from:

```text
OpesCare admin entry
verified partner self-update
facility system integration
pharmacy system integration
lab system integration
insurance network import
public health/government import
user report/correction
field verification
```

## 19.1 Source Trust Levels

```text
admin_verified
partner_verified
system_integrated
government_imported
self_reported
user_reported
unknown
```

## 19.2 Update Approval Rules

High-risk fields require review:

```text
facility type
license status
emergency capability
blood availability rules
insurance acceptance
public health role
controlled medicine availability
```

Low-risk fields may be updated by partner:

```text
opening hours
phone number
service description
photos
general service list
```

Depending on trust level, some changes may publish immediately or require approval.

---

# 20. User Reports and Corrections

Patients and users can report wrong info.

## 20.1 Report Types

```text
wrong location
wrong phone number
facility closed
wrong opening hours
service not available
medicine not available
blood not available
insurance not accepted
unsafe listing
duplicate listing
fraud/suspicious listing
```

## 20.2 Report Flow

1. User submits report.
2. System creates moderation case.
3. Facility/partner may be asked to confirm.
4. Admin reviews.
5. Listing is corrected, limited, suspended, or kept.
6. Reporter may receive status update where appropriate.

## 20.3 Report Statuses

```text
new
under_review
facility_contacted
confirmed
rejected
resolved
escalated
closed
```

---

# 21. Ratings, Reviews, and Quality Indicators

## 21.1 Recommendation

Do not launch with open public reviews immediately.

Healthcare reviews can become abusive, defamatory, or misleading.

Start with operational quality indicators:

```text
verified status
data freshness
integration status
response time where available
service availability
reporting completeness
```

Add patient feedback later with moderation.

## 21.2 If Reviews Are Added Later

Must support:

```text
moderation
abuse reporting
no diagnosis disclosure
no personal staff attacks
facility response
verified visit badge optional
privacy warnings
```

---

# 22. Public Health and Analytics

The map can support public health planning.

## 22.1 Aggregated Analytics

Show public health users:

```text
facility coverage by area
pharmacy stock-out patterns
blood shortage patterns
lab service distribution
insurance network gaps
emergency service coverage
reporting freshness
```

## 22.2 Privacy Rule

Do not expose patient-level movement or exact patient search behavior.

Use:

```text
aggregate
de-identified
pseudonymized where needed
```

---

# 23. Integration with Other OpesCare Modules

## 23.1 Partner Contribution & Governance

Facilities and organizations must be verified through partner governance.

## 23.2 Pharmacy Stock Module

Medicine availability comes from pharmacy stock sync.

## 23.3 Blood Availability Module

Blood availability comes from hospitals/blood banks.

## 23.4 Lab Module

Lab services and test availability come from lab profiles or integration.

## 23.5 Insurance Module

Insurance network search uses accepted insurer/facility mappings.

## 23.6 Medical ID

Patients can use Health ID to filter by care history or preferred facilities, but never expose patient info publicly.

## 23.7 Notifications

Availability updates, reservations, corrections, and emergency alerts can trigger notifications.

## 23.8 Documents

Facility profiles can show QR-verifiable facility certificate where applicable.

## 23.9 Public Health Reporting

Aggregated shortages and service availability can support public health reporting.

---

# 24. User Experience Flows

## 24.1 Find Nearby Hospital Flow

1. Patient opens Care Access Map.
2. Patient allows location or enters city.
3. Selects “Hospitals.”
4. Filters by open now/emergency/insurance.
5. Views verified results.
6. Opens facility profile.
7. Calls, gets directions, or saves.

## 24.2 Find Medicine Flow

1. Patient enters medicine name.
2. System shows matching medicines/generics.
3. Patient selects correct medicine.
4. System shows pharmacies with reported stock.
5. User filters by distance/open now/verified.
6. User calls/reserves/gets directions.
7. Warning displayed: “Availability may change. Confirm before travelling.”

## 24.3 Find Lab Test Flow

1. User searches test.
2. System shows labs offering test.
3. User checks opening hours and requirements.
4. User calls or requests test if enabled.
5. System warns some tests require clinician order.

## 24.4 Find Blood Flow

1. Provider/patient selects blood group/component.
2. System shows verified/recent facilities.
3. User calls facility or initiates official request where allowed.
4. Urgent workflow may create alert for facility.
5. Audit event created.

## 24.5 Find Insurance-Accepting Facility Flow

1. Patient selects insurance provider/plan.
2. System shows facilities that report acceptance.
3. Patient filters by service/location.
4. System warns coverage must be confirmed.

## 24.6 Report Wrong Information Flow

1. User clicks report.
2. Selects issue type.
3. Adds optional note/photo.
4. Submit.
5. Moderation case created.
6. Admin/facility reviews.
7. Listing updated or report closed.

---

# 25. UI Requirements

## 25.1 Main Map Page

Must include:

```text
search bar
current location/manual location selector
facility type filters
service filters
medicine/lab/blood search modes
map view
list view
verified badge
freshness badge
open/closed badge
distance
call button
directions button
save button
report wrong info button
```

## 25.2 Facility Detail Page

Sections:

```text
header with name/type/verification badge
address and map
contact buttons
opening hours
emergency status
services
specialties
insurance accepted
medicine availability if pharmacy
lab tests if lab
blood availability if blood bank/hospital
integration status
last updated
safety disclaimer
report correction
```

## 25.3 Pharmacy Detail Additions

```text
medicine search inside pharmacy
stock freshness
reservation button where enabled
last stock update
```

## 25.4 Lab Detail Additions

```text
test catalog
sample types
turnaround time
doctor order requirement
home collection where available
```

## 25.5 Emergency Mode UI

Emergency mode must be simplified:

```text
nearest emergency facilities
call button
directions button
24/7 badge
last updated
emergency disclaimer
```

Avoid too many filters in emergency mode.

## 25.6 Admin Map Dashboard

```text
pending listings
verification queue
claimed listings
reported issues
stale listings
high-risk listings
duplicate listings
freshness dashboard
facility coverage map
```

## 25.7 Partner Facility Dashboard

```text
edit profile
update services
update hours
sync availability
view reports/corrections
manage staff contacts
view listing analytics
```

---

# 26. Safety and Legal Disclaimers

## 26.1 General Availability Disclaimer

```text
Information may change. Please contact the facility before travelling or making medical decisions.
```

## 26.2 Medicine Disclaimer

```text
Medicine availability is reported by the pharmacy or connected system and may change. Always confirm with the pharmacy and follow guidance from a qualified healthcare professional.
```

## 26.3 Blood Disclaimer

```text
Blood availability may change quickly. Contact the hospital or blood bank immediately and follow official medical procedures.
```

## 26.4 Emergency Disclaimer

```text
If this is a life-threatening emergency, contact local emergency services or go to the nearest emergency facility immediately. OpesCare does not guarantee immediate treatment or availability.
```

## 26.5 Insurance Disclaimer

```text
Insurance acceptance and coverage may vary by service, plan, and authorization. Confirm with your insurer or facility before receiving care.
```

---

# 27. Data Models

## 27.1 care_facilities

```text
id
uuid
partner_id nullable
organization_id nullable
facility_name
facility_type
ownership_type nullable
license_number nullable
license_status
verification_status
listing_status
country_code
region
city
address
latitude
longitude
geocoding_accuracy
phone_primary
phone_secondary nullable
email nullable
website nullable
emergency_contact nullable
description nullable
logo_path nullable
cover_image_path nullable
integration_status
last_verified_at nullable
last_profile_update_at nullable
last_availability_update_at nullable
created_at
updated_at
```

## 27.2 care_facility_services

```text
id
facility_id
service_name
service_category
specialty nullable
service_code nullable
availability_status
appointment_required
walk_in_allowed
telemedicine_available
price_range nullable
last_updated_at
created_at
updated_at
```

## 27.3 care_facility_hours

```text
id
facility_id
day_of_week
opens_at nullable
closes_at nullable
is_closed
is_24_hours
service_context nullable
created_at
updated_at
```

## 27.4 care_facility_insurance

```text
id
facility_id
insurance_partner_id
insurance_name
plan_name nullable
coverage_type nullable
preauthorization_required
cashless_available
claim_supported
last_verified_at nullable
status
created_at
updated_at
```

## 27.5 pharmacy_stock_availability

```text
id
facility_id
medicine_name
generic_name nullable
brand_name nullable
strength nullable
form nullable
local_medicine_code nullable
gtin nullable
availability_status
quantity_available_range nullable
price nullable
currency nullable
reservation_enabled
last_updated_at
source_system nullable
freshness_status
created_at
updated_at
```

## 27.6 lab_test_availability

```text
id
facility_id
test_name
local_test_code nullable
loinc_code nullable
specimen_type nullable
turnaround_time nullable
price nullable
currency nullable
requires_doctor_order
sample_collection_available
home_sample_collection_available
availability_status
last_updated_at
freshness_status
created_at
updated_at
```

## 27.7 blood_availability

```text
id
facility_id
blood_group
component_type
units_available_range nullable
availability_status
last_updated_at
freshness_status
emergency_contact nullable
created_at
updated_at
```

## 27.8 facility_claims

```text
id
uuid
facility_id
claimant_user_id
claim_status
claim_reason
submitted_at
reviewed_by nullable
reviewed_at nullable
review_notes nullable
created_at
updated_at
```

## 27.9 facility_reports

```text
id
uuid
facility_id
reported_by_user_id nullable
report_type
description nullable
evidence_path nullable
status
reviewed_by nullable
reviewed_at nullable
resolution_notes nullable
created_at
updated_at
```

## 27.10 facility_update_audits

```text
id
facility_id
actor_id
actor_type
field_changed
old_value nullable
new_value nullable
source
requires_review
approved_by nullable
approved_at nullable
created_at
```

## 27.11 saved_facilities

```text
id
user_id
facility_id
label nullable
created_at
updated_at
```

## 27.12 medicine_reservation_requests

```text
id
uuid
patient_id nullable
facility_id
medicine_name
quantity_requested nullable
status
requested_at
expires_at nullable
confirmed_at nullable
cancelled_at nullable
created_at
updated_at
```

---

# 28. API Endpoints

## 28.1 Public/Patient Search

```text
GET  /api/v1/care-map/facilities
GET  /api/v1/care-map/facilities/{id}
GET  /api/v1/care-map/search
GET  /api/v1/care-map/nearby
GET  /api/v1/care-map/pharmacies/medicine-search
GET  /api/v1/care-map/labs/test-search
GET  /api/v1/care-map/blood/search
GET  /api/v1/care-map/emergency
GET  /api/v1/care-map/insurance-network
POST /api/v1/care-map/facilities/{id}/save
POST /api/v1/care-map/facilities/{id}/report
```

## 28.2 Partner Facility Management

```text
GET  /api/v1/partner/care-map/facilities
PUT  /api/v1/partner/care-map/facilities/{id}
POST /api/v1/partner/care-map/facilities/{id}/services
PUT  /api/v1/partner/care-map/facilities/{id}/hours
POST /api/v1/partner/care-map/facilities/{id}/stock-sync
POST /api/v1/partner/care-map/facilities/{id}/blood-availability
POST /api/v1/partner/care-map/facilities/{id}/lab-tests
POST /api/v1/partner/care-map/facilities/{id}/claim
```

## 28.3 Admin

```text
GET  /api/v1/admin/care-map/dashboard
GET  /api/v1/admin/care-map/facilities
POST /api/v1/admin/care-map/facilities
PUT  /api/v1/admin/care-map/facilities/{id}
POST /api/v1/admin/care-map/facilities/{id}/verify
POST /api/v1/admin/care-map/facilities/{id}/suspend
GET  /api/v1/admin/care-map/claims
POST /api/v1/admin/care-map/claims/{id}/approve
POST /api/v1/admin/care-map/claims/{id}/reject
GET  /api/v1/admin/care-map/reports
POST /api/v1/admin/care-map/reports/{id}/resolve
GET  /api/v1/admin/care-map/stale-listings
GET  /api/v1/admin/care-map/coverage-analytics
```

## 28.4 Reservations Optional

```text
POST /api/v1/care-map/medicine-reservations
GET  /api/v1/care-map/medicine-reservations/{id}
POST /api/v1/partner/care-map/medicine-reservations/{id}/confirm
POST /api/v1/partner/care-map/medicine-reservations/{id}/reject
POST /api/v1/care-map/medicine-reservations/{id}/cancel
```

---

# 29. Permissions

## 29.1 Patient/User Permissions

```text
care_map.view
care_map.search
care_map.save_facility
care_map.report_listing
care_map.request_medicine_reservation
```

## 29.2 Partner Permissions

```text
care_map.manage_own_facility
care_map.update_services
care_map.update_hours
care_map.update_pharmacy_stock
care_map.update_lab_tests
care_map.update_blood_availability
care_map.respond_to_reports
care_map.manage_reservations
```

## 29.3 Admin Permissions

```text
care_map.manage_all
care_map.verify_facilities
care_map.review_claims
care_map.resolve_reports
care_map.suspend_listings
care_map.view_analytics
care_map.manage_data_sources
```

## 29.4 Public Health Permissions

```text
care_map.view_aggregate_coverage
care_map.view_shortage_analytics
care_map.view_reporting_freshness
```

---

# 30. Data Freshness and Scheduled Jobs

## 30.1 Required Jobs

```text
mark_stale_pharmacy_stock
mark_stale_blood_availability
mark_stale_lab_tests
mark_expired_facility_verifications
send_update_reminders_to_partners
generate_shortage_analytics
detect_duplicate_listings
detect_suspicious_updates
```

## 30.2 Freshness Reminders

Notify partners when:

```text
stock data is stale
blood availability is stale
facility profile needs review
insurance acceptance needs reconfirmation
emergency capability update is overdue
```

---

# 31. Audit Events

```text
facility_listing_created
facility_listing_updated
facility_listing_verified
facility_listing_suspended
facility_claim_submitted
facility_claim_approved
facility_claim_rejected
facility_report_submitted
facility_report_resolved
facility_service_updated
facility_hours_updated
pharmacy_stock_updated
blood_availability_updated
lab_test_availability_updated
insurance_acceptance_updated
medicine_reservation_requested
medicine_reservation_confirmed
medicine_reservation_rejected
care_map_search_performed
directions_clicked
call_clicked
```

Audit fields:

```text
actor_id nullable
actor_type
facility_id nullable
action
old_value nullable
new_value nullable
source
ip_address
user_agent
timestamp
```

For patient searches, use privacy-preserving analytics. Do not expose patient identity unnecessarily.

---

# 32. Error Codes

```text
FACILITY_NOT_FOUND
FACILITY_NOT_VERIFIED
FACILITY_SUSPENDED
FACILITY_CLOSED
FACILITY_CLAIM_REQUIRED
FACILITY_CLAIM_ALREADY_EXISTS
FACILITY_UPDATE_REQUIRES_REVIEW
LOCATION_PERMISSION_DENIED
LOCATION_NOT_AVAILABLE
MAP_PROVIDER_UNAVAILABLE
GEOCODING_FAILED
MEDICINE_NOT_FOUND
STOCK_DATA_STALE
BLOOD_DATA_STALE
LAB_TEST_NOT_FOUND
INSURANCE_NETWORK_NOT_FOUND
RESERVATION_NOT_AVAILABLE
RESERVATION_EXPIRED
RESERVATION_REJECTED
REPORT_ALREADY_SUBMITTED
CARE_MAP_ACCESS_DENIED
```

---

# 33. Bilingual Requirements

All public labels must support English and French.

Examples:

```text
Verified Care Access Map → Carte vérifiée d’accès aux soins
Hospital → Hôpital
Clinic → Clinique
Pharmacy → Pharmacie
Laboratory → Laboratoire
Blood Bank → Banque de sang
Open Now → Ouvert maintenant
Emergency Services → Services d’urgence
Reported Available → Disponibilité signalée
Call to Confirm → Appeler pour confirmer
Get Directions → Obtenir l’itinéraire
Report Wrong Information → Signaler une information incorrecte
Verified Facility → Établissement vérifié
Last Updated → Dernière mise à jour
Availability May Change → La disponibilité peut changer
```

---

# 34. Security and Privacy Rules

## 34.1 Required

```text
location permission before precise location
no unnecessary storage of patient exact location
facility updates audited
stock updates audited
verification status controlled by admin/governance
public health analytics aggregated
patient search behavior privacy-protected
integration API authenticated
rate limits on public search endpoints
```

## 34.2 Blocked

Do not allow:

```text
unverified facility claiming without review
suspended facility visible as active
guaranteed medicine/blood claims
public exposure of patient location
public exposure of patient search history
unreviewed high-risk emergency capability changes
unverified blood availability source shown as trusted
```

---

# 35. Testing Requirements

Required tests:

1. Facility can be created.
2. Facility can be verified.
3. Suspended facility does not appear as active.
4. Search by facility type works.
5. Search near location works.
6. Search by medicine works.
7. Stale medicine stock shows stale warning.
8. Search by blood group works.
9. Stale blood availability shows warning.
10. Search by lab test works.
11. Emergency mode prioritizes emergency-capable facilities.
12. Insurance filter returns accepted facilities.
13. Patient location is not stored without permission.
14. Facility claim requires document review.
15. User report creates moderation case.
16. Partner update is audited.
17. High-risk field update requires review.
18. Public health analytics are aggregate only.
19. Map provider failure returns safe error.
20. French labels render.
21. Reservation expires correctly.
22. Unverified listing shows warning or is hidden by policy.
23. Duplicate detection job can flag duplicates.
24. Freshness job marks stock stale.
25. Call/directions clicks are tracked without exposing sensitive patient data.

---

# 36. Acceptance Criteria

This module is complete when:

1. Verified healthcare facility map exists.
2. Facility directory supports hospitals, clinics, pharmacies, labs, imaging centers, blood banks, emergency services, and specialist centers.
3. Listings have verification statuses.
4. Listings have freshness statuses.
5. Facility profiles include services, hours, contacts, location, verification, and last updated time.
6. Patients can search by location.
7. Patients can search by service.
8. Patients can search by medicine.
9. Patients can search by lab test.
10. Patients can search by blood group.
11. Patients can filter by insurance.
12. Emergency mode exists.
13. Pharmacy stock availability is freshness-aware.
14. Blood availability is freshness-aware.
15. Lab test availability is freshness-aware.
16. Facility claiming workflow exists.
17. User report/correction workflow exists.
18. Partner dashboard exists.
19. Admin map governance dashboard exists.
20. Public health aggregate analytics exist.
21. Safety disclaimers exist.
22. Location privacy rules exist.
23. Map provider abstraction exists.
24. PostGIS/geospatial search is supported or planned.
25. Data update audits exist.
26. English/French labels exist.
27. Tests cover search, freshness, verification, privacy, reports, and integrations.
28. The map does not guarantee treatment, stock, blood, or immediate service.
29. The module integrates with Partner Governance, Pharmacy Stock, Blood Availability, Lab Tests, Insurance, Notifications, and Public Health Reporting.
30. All high-risk data changes are audited and reviewable.

---

# 37. First Developer Task

Use this task for Jules, Codex, or another coding agent:

```text
Read docs/PROJECT_KNOWLEDGE.md, docs/PRD.md, docs/UIUX_PRODUCT_INTERFACE_PRD.md, docs/product/COLOR_SYSTEM.md, docs/product/ICON_SYSTEM.md, docs/governance/OPESCARE_DATA_GOVERNANCE_PRIVACY_CONSENT.md, docs/partners/OPESCARE_PARTNER_CONTRIBUTION_GOVERNANCE.md, docs/communications/OPESCARE_COMMUNICATION_ALERTS_TASKS_MESSAGING_SYSTEM.md, docs/documents/OPESCARE_VERIFIABLE_DOCUMENT_TEMPLATES_V2.md, and docs/care-map/OPESCARE_VERIFIED_CARE_ACCESS_MAP.md.

We are building OpesCare from scratch.
Do not use OpesHIS OS.
Do not copy OpesHIS OS map, facility directory, database, UI, or listing assumptions.

Task: Create the OpesCare Verified Care Access Map foundation.

Scope:
1. Create module placeholder: app/Modules/CareMap.
2. Create docs/care-map folder if missing.
3. Add model placeholders:
   - CareFacility
   - CareFacilityService
   - CareFacilityHour
   - CareFacilityInsurance
   - PharmacyStockAvailability
   - LabTestAvailability
   - BloodAvailability
   - FacilityClaim
   - FacilityReport
   - FacilityUpdateAudit
   - SavedFacility
   - MedicineReservationRequest

4. Add service placeholders:
   - CareMapSearchService
   - FacilityVerificationService
   - FacilityClaimService
   - FacilityReportService
   - FacilityFreshnessService
   - PharmacyStockSearchService
   - BloodAvailabilitySearchService
   - LabTestSearchService
   - InsuranceNetworkSearchService
   - MapProviderService
   - GeocodingService

5. Add route placeholders for:
   - facility search
   - nearby search
   - medicine search
   - lab test search
   - blood search
   - emergency search
   - insurance network search
   - facility details
   - save facility
   - report wrong information
   - partner facility management
   - admin verification/moderation

6. Add map provider abstraction. Do not hard-code one provider.

7. Add location privacy rules:
   - request permission before using exact location
   - do not store exact patient location without consent

8. Add freshness logic placeholders:
   - pharmacy stock freshness
   - blood availability freshness
   - lab test freshness
   - facility profile freshness

9. Add safety disclaimers:
   - medicine availability may change
   - blood availability may change
   - emergency care availability may change
   - insurance coverage must be confirmed

10. Add admin dashboard placeholders:
   - pending listings
   - stale listings
   - facility claims
   - reports/corrections
   - duplicate listings
   - coverage analytics

11. Add tests proving:
   - verified facility appears in search
   - suspended facility does not appear as active
   - medicine search returns freshness warning
   - blood search returns freshness warning
   - emergency search prioritizes emergency-capable facilities
   - facility claim requires review
   - user report creates moderation case
   - location is not stored without permission
   - high-risk field update requires review
   - French labels render

12. Do not implement full pharmacy/lab/blood modules in this task.
13. Do not expose patient data in placeholder responses.
14. Open a PR with summary, files created, screenshots/map UI placeholders, tests, risks, and next recommended tasks.
```

---

# 38. Final Rule

The Verified Care Access Map must help patients find care without creating false certainty.

The correct model is:

```text
verified listings
freshness-aware availability
clear safety disclaimers
privacy-safe location use
partner-governed updates
audited changes
care access navigation
not guaranteed treatment
not guaranteed stock
not guaranteed blood
```

If a facility, service, medicine, blood unit, or insurance acceptance cannot be verified or is stale, the platform must say so clearly.
