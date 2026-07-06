# Barangay Santa Ines Health Monitoring System

Capstone project (BSIT, Group 5): web-based community health monitoring and decision support system with purok-level heatmap visualization, for Barangay Santa Ines, Plaridel, Bulacan.

## Stack
- Backend: PHP 7.4+, MySQL, PDO for all database access (never raw string-concatenated queries)
- Frontend: HTML5, CSS3, Bootstrap 5, JavaScript, jQuery
- Mapping: Leaflet.js — choropleth via GeoJSON purok polygons (NOT leaflet.heat point-density; residents only have a `purok` category field, no coordinates)
- Charts: Chart.js
- Tables: DataTables.js
- PDF reports: mPDF
- Local server: XAMPP

## Finalized user roles — do not add an LGU/barangay-office login
- Administrator
- Barangay Health Worker (BHW)
- Health Center Staff
- Residents: view-only public announcement page, no login required

Rationale: fieldwork only covered health center staff, not the barangay office. The LGU receives generated reports; it does not use the system directly.

## Core modules
1. Login / Authentication
2. Smart Dashboard (threshold-based LGU alerts + seasonal disease prediction)
3. Resident Profiling and Management (+ QR code lookup)
4. Maternal Health Monitoring
5. Infant Monitoring (0–12 months)
6. Vaccination Records Management
7. Disease and Illness Case Recording
8. Heatmap Visualization by Purok (choropleth, green/yellow/red by threshold)
9. Reports Generation (mPDF)
10. Public Announcement Management (+ auto-drafted advisory from threshold alerts)

## Development cycle plan
- Cycle 1: Login/Auth, Dashboard, Resident Profiling, QR lookup, audit logging (RA 10173 compliance)
- Cycle 2: Maternal Health Monitoring, Infant Monitoring, Vaccination Records
- Cycle 3: Disease Recording, Heatmap (choropleth + GeoJSON), purok ranking panel, date-range filter, Reports, auto-drafted advisory
- Testing: functional, usability, performance, UAT, ISO/IEC 25010:2023 + TAM
- Deployment: training for BHWs and administrator

## Conventions
- All database access through PDO prepared statements
- Every write to residents, maternal, infant, vaccination, or disease tables must call a `log_action()` helper that writes to the `audit_logs` table
- File structure: one folder per module under `/modules/{module_name}/`
- Keep this file updated if module scope changes — do not silently drift from what's documented in Chapter 3
