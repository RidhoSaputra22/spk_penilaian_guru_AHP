---
applyTo: '**'
---
Admin / Operator
User & Access
• Create / activate / deactivate accounts (Admin, Assessor, Teacher)
• Reset passwords
Master Data
• Manage teacher data (identity, subject, status)
• Manage assessor/team data
• Manage assessment periods (semester/year, open/close)
• Manage criteria & sub-criteria (kompetensi + indikator)
• Manage scoring scale (range + descriptions)
KPI Form Builder (Create the form assessors will use)
• Create KPI form template (per period or reusable)
• Add sections (by criteria/sub-criteria)
• Add indicators/items per section
• Set field type per indicator (numeric score, dropdown, yes/no, notes)
• Set rules (required, min/max score, default scale)
• Assign form to period + teacher groups + assessors
• Publish/lock form (no edits after scoring starts)
• Clone/copy form from previous period
AHP Weighting (Handled by Admin)
• Set AHP hierarchy (Goal → Criteria → Sub-criteria)
• Input pairwise comparison for criteria (and sub-criteria if used)
• Auto-calculate weights + show Consistency Ratio (CR)
• Revise until CR acceptable
• Lock/finalize weights for the period (so assessors only score)
Monitoring & Control
• Open/close scoring window per period
• Track scoring progress (who is not yet assessed)
• Optionally re-open a finalized teacher assessment (with reason/log)
Results & Reporting
• Generate final results (AHP weights × KPI scores)• View ranking list + filters (period, subject, status)
• View teacher detail breakdown (weights + indicator scores)
• Export/print reports:
• PDF ranking
• PDF teacher detail
• PDF AHP weights + CR
• Excel recap
Audit & History
• Store results per period (history/trend)
• Change log for key actions (form publish, weight lock, assessment finalization)
Assessor / Tim Penilai
Account
• Login/logout
• Update own profile/password
Teacher KPI Scoring (Only input KPI scores)
• Select period → select teacher → system loads the published KPI form
• Input scores per indicator (based on form field type)
• Add notes/comments per indicator/section (optional)
• Save draft
• Submit/finalize assessment for that teacher (locked)
View Results (Optional access)
• View results for teachers they assessed (detail + notes)
• No access to change weights or form structure
Teacher (Guru)
Account
• Login/logout
• Update own profile/password
Evidence & Status (Optional but scalable)
• Upload evidence per indicator (document/photo/link) if required by form• See assessment status (not started / draft / finalized)
Results (Restricted)
• View only their own KPI results:
• final score
• breakdown per criteria/sub-criteria/indicator (as allowed)
• assessor notes (if allowed)
• Download personal result report (optional)

## Concept of the App (SPK Penilaian Kinerja Guru + AHP)

This web app helps the madrasah do **teacher performance evaluation** in a structured way and produce a **fair final score + ranking** each assessment period (e.g., per semester). The app uses **AHP** to decide how important each criterion is, then combines that with the assessor’s KPI scores.

### How it works (simple flow)

1. **Admin sets up the assessment**

* Admin creates the assessment period (e.g., “Semester Ganjil 2025/2026”).
* Admin defines the **criteria & indicators** (example: Pedagogik, Profesional, Sosial, Kepribadian + sub-indicators).
* Admin builds the **KPI input form** that the assessor will fill (so the scoring is consistent for all teachers).

2. **Admin sets the weighting using AHP**

* Admin compares criteria using **pairwise comparison** (which one is more important, and how much).
* The system calculates **weights** and checks **Consistency Ratio (CR)** to ensure the comparisons are logical.
* Admin locks the weights so they won’t change during scoring.

3. **Assessor scores teachers using the form**

* Assessor selects a teacher and fills in KPI scores based on the published form (per indicator).
* Assessor can add notes/comments.
* Assessor submits/finalizes the assessment.

4. **System calculates results automatically**

* The final score is computed as:
  **Final Score = (KPI Scores) × (AHP Weights)**
* The system shows:

  * teacher final score
  * breakdown per criterion/indicator
  * ranking list for that period

5. **Teacher can see their own result**

* Teacher logs in and can only see **their own** KPI results (and optionally download a report).
* Teachers don’t see other teachers’ scores.

### Why this is useful

* **Consistent scoring**: everyone uses the same form and indicators.
* **Fair weighting**: AHP makes the importance of criteria explicit and measurable.
* **Transparent results**: breakdown shows why a teacher gets a certain score.
* **Efficient reporting**: ranking + PDF/Excel exports are generated automatically.

