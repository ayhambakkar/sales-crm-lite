# Sales CRM Lite — User Stories

## Personas

**Admin** — Manages the system, users, and has visibility into all data across the team.  
**Sales Rep** — Works their own pipeline: creates leads, updates statuses, logs follow-ups.

---

## Epic 1: Authentication

### US-001 — Login
> As a **user**, I want to log in with my email and password, so that I can access the CRM securely.

**Acceptance Criteria:**
- Given valid credentials → redirect to Dashboard
- Given invalid credentials → show error message, do not reveal which field is wrong
- Given an inactive account → show "Account is disabled" message
- Form fields are validated client-side (non-empty, valid email format)

### US-002 — Logout
> As a **user**, I want to log out, so that my session is closed when I leave the computer.

**Acceptance Criteria:**
- Clicking "Logout" destroys the server-side session
- User is redirected to the login page
- Back button after logout does not restore the session

### US-003 — Session timeout
> As a **user**, I want my session to expire after 2 hours of inactivity, so that the CRM is not left open unattended.

**Acceptance Criteria:**
- After 2 hours without activity, next request redirects to login with a "Session expired" message

---

## Epic 2: User Management

### US-010 — View user list
> As an **Admin**, I want to see a list of all users, so that I can manage team accounts.

**Acceptance Criteria:**
- List shows: name, email, role, status (active/inactive), created date
- Admin cannot access this page as a Sales Rep

### US-011 — Create user
> As an **Admin**, I want to create a new user account, so that new team members can access the CRM.

**Acceptance Criteria:**
- Form fields: first name, last name, email, role, password (auto-generated or manual)
- Duplicate email is rejected with a clear error
- New user is set to "active" by default

### US-012 — Edit user
> As an **Admin**, I want to edit a user's name, email, and role, so that I can keep accounts up to date.

**Acceptance Criteria:**
- Changes are saved immediately
- Email uniqueness is re-validated on save

### US-013 — Deactivate user
> As an **Admin**, I want to deactivate a user, so that former employees can no longer log in without deleting their data.

**Acceptance Criteria:**
- Deactivated user cannot log in
- Deactivated user's leads and customers remain in the system
- Admin cannot deactivate themselves

### US-014 — Reset password
> As an **Admin**, I want to reset a user's password, so that I can help a locked-out team member.

**Acceptance Criteria:**
- Admin sets a new password directly (no email flow in v1)
- Password is hashed before storage

---

## Epic 3: Dashboard

### US-020 — Overview KPIs
> As a **Sales Rep**, I want to see my key numbers on the dashboard, so that I know my pipeline status at a glance.

**Acceptance Criteria:**
- Shows: my open leads count, my customer count, my pending follow-ups count
- Numbers are clickable and navigate to the corresponding filtered list

### US-021 — Admin overview
> As an **Admin**, I want to see team-wide KPIs on the dashboard, so that I can monitor overall performance.

**Acceptance Criteria:**
- Shows totals across all users (not just the admin's own records)

### US-022 — Today's follow-ups widget
> As a **user**, I want to see my follow-ups due today and overdue on the dashboard, so that I don't miss any tasks.

**Acceptance Criteria:**
- Shows up to 5 items, sorted by due date ascending
- Overdue items are visually distinct (e.g. red text)
- Each item links to the related Lead or Customer

### US-023 — Recent leads widget
> As a **user**, I want to see the 5 most recently added leads, so that I can quickly pick up where I left off.

**Acceptance Criteria:**
- Shows lead name, company, status, and assigned user
- Admin sees all recent leads; Sales Rep sees only their own

---

## Epic 4: Leads

### US-030 — View lead list
> As a **user**, I want to see a list of my leads, so that I can manage my pipeline.

**Acceptance Criteria:**
- Default sort: newest first
- Columns: name, company, status, source, assigned to, created date
- Sales Rep sees only their leads; Admin sees all leads

### US-031 — Filter and search leads
> As a **user**, I want to filter leads by status and search by name or company, so that I can find specific leads quickly.

**Acceptance Criteria:**
- Status filter is a dropdown (All, New, Contacted, Qualified, Proposal Sent, Won, Lost)
- Search is case-insensitive and matches partial strings
- Filters can be combined

### US-032 — Create lead
> As a **user**, I want to create a new lead, so that I can track a new prospect.

**Acceptance Criteria:**
- Required fields: first name, last name, email or phone (at least one)
- Optional fields: company, source, notes
- Default status: New
- Default assigned user: current user (Admin can override)

### US-033 — Edit lead
> As a **user**, I want to edit a lead's details, so that I can keep information current.

**Acceptance Criteria:**
- Sales Rep can only edit their own leads
- All fields are editable
- Status change is logged as an Activity (Phase 2)

### US-034 — Update lead status
> As a **Sales Rep**, I want to quickly update a lead's status from the list, so that I can keep my pipeline accurate without opening the detail page.

**Acceptance Criteria:**
- Status can be changed via an inline dropdown on the list or a prominent button on the detail page
- Changing to "Won" shows a prompt to convert to Customer

### US-035 — Convert lead to customer
> As a **user**, I want to convert a Won lead to a customer, so that I can move them into the customer management workflow.

**Acceptance Criteria:**
- A "Convert to Customer" button appears when lead status is "Won"
- Pre-fills customer form with lead's data
- Original lead record is retained (linked to the customer)
- Confirmation step before conversion

### US-036 — Delete lead
> As a **user**, I want to delete a lead, so that I can remove test or duplicate entries.

**Acceptance Criteria:**
- Sales Rep can only delete their own leads
- Deletion requires a confirmation dialog
- Associated Follow-Ups are also deleted (cascade)

---

## Epic 5: Customers

### US-040 — View customer list
> As a **user**, I want to see a list of my customers, so that I can manage active accounts.

**Acceptance Criteria:**
- Columns: name, company, email, phone, assigned to, created date
- Sales Rep sees only their customers; Admin sees all

### US-041 — Search customers
> As a **user**, I want to search customers by name, company, or email, so that I can find a specific account quickly.

**Acceptance Criteria:**
- Search is case-insensitive, partial match
- Results update on form submission (not live-search in v1)

### US-042 — Create customer
> As a **user**, I want to manually create a customer, so that I can add existing clients who weren't tracked as leads.

**Acceptance Criteria:**
- Required fields: first name, last name, email or phone
- Optional fields: company, industry, address, notes

### US-043 — View customer detail
> As a **user**, I want to see a customer's full profile, so that I can review all information and history in one place.

**Acceptance Criteria:**
- Shows all fields, linked Follow-Ups (open and closed), and Activity log (Phase 2)

### US-044 — Edit customer
> As a **user**, I want to edit customer information, so that I can keep records accurate.

**Acceptance Criteria:**
- Sales Rep can only edit their own customers

### US-045 — Delete customer
> As a **user**, I want to delete a customer, so that I can remove outdated or duplicate records.

**Acceptance Criteria:**
- Deletion requires confirmation
- Associated Follow-Ups are also deleted (cascade)

---

## Epic 6: Follow-Ups

### US-050 — View follow-up list
> As a **user**, I want to see all my follow-ups, so that I can plan my workday.

**Acceptance Criteria:**
- Default sort: due date ascending
- Columns: title, type, linked entity, due date, status
- Overdue items are visually highlighted

### US-051 — Filter follow-ups
> As a **user**, I want to filter follow-ups by status and date range, so that I can focus on what's relevant.

**Acceptance Criteria:**
- Status filter: All, Pending, Done, Cancelled
- Date range: from / to date pickers

### US-052 — Create follow-up
> As a **user**, I want to create a follow-up task linked to a lead or customer, so that I remember to take action at the right time.

**Acceptance Criteria:**
- Required fields: title, due date, type, linked entity
- Default status: Pending
- Can be created from the Follow-Up list page or from a Lead/Customer detail page

### US-053 — Mark follow-up as done
> As a **user**, I want to mark a follow-up as Done, so that I can track completion without deleting it.

**Acceptance Criteria:**
- One-click "Mark Done" action from the list or detail page
- Status changes to "Done", done date is recorded

### US-054 — Edit follow-up
> As a **user**, I want to edit a follow-up's details, so that I can reschedule or update the task.

**Acceptance Criteria:**
- Cannot edit a follow-up that is already Done or Cancelled

### US-055 — Delete follow-up
> As a **user**, I want to delete a follow-up, so that I can remove irrelevant tasks.

**Acceptance Criteria:**
- Deletion requires confirmation

---

## Epic 7: Reporting (Phase 2)

### US-060 — Leads by status report
> As an **Admin**, I want to see a breakdown of leads by status, so that I can understand where the pipeline is concentrated.

**Acceptance Criteria:**
- Bar chart showing count per status
- Filterable by date range and assigned user

### US-061 — Conversion rate report
> As an **Admin**, I want to see the lead-to-customer conversion rate, so that I can assess team effectiveness.

**Acceptance Criteria:**
- Shows: total leads, won leads, conversion % over a selected date range

### US-062 — Follow-up completion rate
> As an **Admin**, I want to see follow-up completion rates per user, so that I can identify who is staying on top of their tasks.

**Acceptance Criteria:**
- Table: user name, total follow-ups, done, pending, overdue, completion %
