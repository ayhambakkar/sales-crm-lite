# Sales CRM Lite — Requirements

## 1. Functional Requirements

### 1.1 Authentication & Session Management

| ID | Requirement |
|---|---|
| AUTH-01 | Users must log in with email and password |
| AUTH-02 | Passwords must be hashed using bcrypt (PHP `password_hash`) |
| AUTH-03 | Sessions must expire after 2 hours of inactivity |
| AUTH-04 | All routes except `/login` must redirect unauthenticated users |
| AUTH-05 | Users can log out and their session is destroyed immediately |
| AUTH-06 | Admin can create, edit, deactivate, and delete user accounts |
| AUTH-07 | Deactivated users cannot log in |

### 1.2 User Management (Admin only)

| ID | Requirement |
|---|---|
| USR-01 | Admin can view a list of all users |
| USR-02 | Admin can create a new user (name, email, password, role) |
| USR-03 | Admin can edit a user's name, email, and role |
| USR-04 | Admin can reset a user's password |
| USR-05 | Admin can deactivate or delete a user |
| USR-06 | System must prevent deletion of the last remaining Admin |

### 1.3 Dashboard

| ID | Requirement |
|---|---|
| DASH-01 | Dashboard displays total count of Leads, Customers, and open Follow-Ups |
| DASH-02 | Dashboard shows the 5 most recent Follow-Ups due today or overdue |
| DASH-03 | Dashboard shows the 5 most recently added Leads |
| DASH-04 | Sales Reps see their own data only; Admins see all data |
| DASH-05 | Dashboard is the default page after login |

### 1.4 Leads

| ID | Requirement |
|---|---|
| LEAD-01 | A Lead has: first name, last name, email, phone, company, status, source, assigned user, notes, created date |
| LEAD-02 | Lead statuses: New, Contacted, Qualified, Proposal Sent, Won, Lost |
| LEAD-03 | Users can create, view, edit, and delete leads |
| LEAD-04 | Sales Reps can only see and manage leads assigned to them |
| LEAD-05 | Admins can see and manage all leads |
| LEAD-06 | Admin can assign a lead to any Sales Rep |
| LEAD-07 | A Lead marked as "Won" can be converted to a Customer |
| LEAD-08 | Leads list supports filtering by status and search by name/company |
| LEAD-09 | Leads list is sortable by date created and company name |
| LEAD-10 | Lead sources: Website, Referral, Cold Call, LinkedIn, Email Campaign, Other |

### 1.5 Customers

| ID | Requirement |
|---|---|
| CUST-01 | A Customer has: first name, last name, email, phone, company, industry, address, assigned user, notes, created date |
| CUST-02 | Customers can be created manually or converted from a Won Lead |
| CUST-03 | When converted from a Lead, core fields are pre-populated |
| CUST-04 | Users can create, view, edit, and delete customers |
| CUST-05 | Sales Reps can only see and manage customers assigned to them |
| CUST-06 | Admins can see and manage all customers |
| CUST-07 | Customer detail page shows all associated Follow-Ups and Activities |
| CUST-08 | Customers list supports search by name, company, and email |

### 1.6 Follow-Ups

| ID | Requirement |
|---|---|
| FU-01 | A Follow-Up has: title, due date, type, status, linked entity (Lead or Customer), assigned user, notes |
| FU-02 | Follow-Up types: Call, Email, Meeting, Demo, Other |
| FU-03 | Follow-Up statuses: Pending, Done, Cancelled |
| FU-04 | Users can create Follow-Ups linked to a Lead or Customer |
| FU-05 | Users can mark a Follow-Up as Done or Cancelled |
| FU-06 | Overdue Follow-Ups (past due date, still Pending) are visually highlighted |
| FU-07 | Sales Reps see only their own Follow-Ups; Admins see all |
| FU-08 | Follow-Ups list can be filtered by status and date range |

### 1.7 Activities (Phase 2)

| ID | Requirement |
|---|---|
| ACT-01 | An Activity is auto-logged when: a Lead status changes, a Customer is created, a Follow-Up is marked Done |
| ACT-02 | Activities can also be logged manually with type and description |
| ACT-03 | Activity types: Status Change, Call, Email, Meeting, Note, Conversion |
| ACT-04 | Activities are read-only once created (immutable audit log) |
| ACT-05 | Activities are visible on the Lead/Customer detail page |

### 1.8 Reporting (Phase 2)

| ID | Requirement |
|---|---|
| REP-01 | Admin can view a leads-by-status summary (counts per status) |
| REP-02 | Admin can view conversion rate: Won / Total Leads |
| REP-03 | Admin can view follow-up completion rate per user |
| REP-04 | Reports can be filtered by date range |
| REP-05 | Reports page includes a visual bar chart (rendered with JS canvas or SVG) |

---

## 2. Non-Functional Requirements

| ID | Requirement |
|---|---|
| NFR-01 | Application must run on PHP 8.2+ with no framework dependencies |
| NFR-02 | All database queries must use PDO with prepared statements (no raw string interpolation) |
| NFR-03 | UI must be responsive (mobile-friendly) using Tailwind CSS |
| NFR-04 | Page load time under normal conditions must be under 1 second (local) |
| NFR-05 | HTML output must be valid and pass W3C validation |
| NFR-06 | All user-facing strings must be escaped (htmlspecialchars) to prevent XSS |
| NFR-07 | CSRF tokens must protect all state-changing forms (POST/PUT/DELETE) |
| NFR-08 | Code must follow PSR-12 coding standard |
| NFR-09 | Git history must be clean with descriptive commit messages (conventional commits) |
| NFR-10 | Sensitive configuration (DB credentials) must be in `.env` files excluded from Git |

---

## 3. Out of Scope (v1)

- Email notifications or SMTP integration
- Calendar sync (Google Calendar, Outlook)
- File / document attachments
- REST API or mobile client
- Multi-company (multi-tenant) support
- Two-factor authentication
- Automated CI/CD pipeline
- Data export (CSV/PDF) — stretch goal for Phase 2
