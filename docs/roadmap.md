# Sales CRM Lite — Roadmap

## Overview

The project is split into two phases. Phase 1 delivers a fully functional MVP. Phase 2 adds depth for portfolio showcase value.

```
Phase 1 (MVP)          Phase 2 (Depth)
──────────────         ────────────────
Auth & Roles           Activities log
Dashboard              Reporting & charts
Leads (full CRUD)      CSV export
Customers (full CRUD)  UI polish & dark mode
Follow-Ups             README + live demo
```

---

## Phase 1 — MVP

**Goal:** A working, deployable CRM that demonstrates core full-stack skills.

### Milestone 1.1 — Project Foundation
- [ ] Set up directory structure (MVC layout)
- [ ] Configure autoloading (PSR-4 via Composer or manual)
- [ ] Write `.env` config loader (DB credentials, app settings)
- [ ] Build front controller (`public/index.php`) and Router
- [ ] Create base Controller and View renderer
- [ ] Set up database schema (`database/schema.sql`)
- [ ] Create seed data (`database/seed.sql`) for local testing
- [ ] Configure Tailwind CSS (CDN or CLI build)
- [ ] Build shared layout template (header, nav, footer)

**Deliverable:** A request hits the router and renders a basic HTML page.

---

### Milestone 1.2 — Authentication
- [ ] `users` table with roles, password hash, active flag
- [ ] Login form (email + password)
- [ ] Session-based authentication
- [ ] Auth middleware (redirect if not logged in)
- [ ] Role-based access control helper
- [ ] Logout endpoint
- [ ] Session expiry (2h inactivity)

**Deliverable:** Login/logout works; protected routes redirect to login.

---

### Milestone 1.3 — User Management (Admin)
- [ ] `Admin > Users` list page
- [ ] Create user form
- [ ] Edit user form
- [ ] Deactivate / delete user action
- [ ] Password reset by Admin
- [ ] CSRF protection on all forms

**Deliverable:** Admin can fully manage team accounts.

---

### Milestone 1.4 — Leads Module
- [ ] `leads` table schema
- [ ] Lead list page (with search + status filter)
- [ ] Create lead form
- [ ] Lead detail / view page
- [ ] Edit lead form
- [ ] Status update (inline or detail page)
- [ ] Delete lead (with confirmation)
- [ ] Role-scoped visibility (Sales Rep = own leads only)
- [ ] "Convert to Customer" flow

**Deliverable:** Full leads pipeline management for both roles.

---

### Milestone 1.5 — Customers Module
- [ ] `customers` table schema
- [ ] Customer list page (with search)
- [ ] Create customer form
- [ ] Customer detail page (with linked follow-ups)
- [ ] Edit customer form
- [ ] Delete customer (with confirmation)
- [ ] Role-scoped visibility

**Deliverable:** Full customer account management.

---

### Milestone 1.6 — Follow-Ups Module
- [ ] `follow_ups` table schema
- [ ] Follow-up list page (with filter by status + date)
- [ ] Create follow-up (from list or from Lead/Customer detail)
- [ ] Edit follow-up form
- [ ] Mark as Done / Cancelled actions
- [ ] Overdue highlighting
- [ ] Delete follow-up

**Deliverable:** Task management tied to leads and customers.

---

### Milestone 1.7 — Dashboard
- [ ] KPI cards (leads count, customers count, pending follow-ups)
- [ ] "Due today / overdue" follow-ups widget
- [ ] Recent leads widget
- [ ] Role-aware data scoping

**Deliverable:** Meaningful landing page after login.

---

### Milestone 1.8 — MVP Hardening
- [ ] Input validation on all forms (server-side)
- [ ] XSS protection (htmlspecialchars on all output)
- [ ] CSRF tokens on all state-changing forms
- [ ] SQL injection prevention audit (all queries use prepared statements)
- [ ] 404 and error page handling
- [ ] Mobile responsiveness check (Tailwind breakpoints)
- [ ] Cross-browser smoke test (Chrome, Firefox, Safari)

**Deliverable:** Production-grade security and UX baseline.

---

## Phase 2 — Depth & Polish

**Goal:** Demonstrate senior-level thinking: audit trails, analytics, and portfolio presentation.

### Milestone 2.1 — Activities Log
- [ ] `activities` table schema
- [ ] Auto-log on Lead status change
- [ ] Auto-log on Customer creation (from Lead)
- [ ] Auto-log on Follow-Up marked Done
- [ ] Manual activity creation (note, call, email)
- [ ] Activities displayed on Lead and Customer detail pages

**Deliverable:** Immutable audit trail of all CRM interactions.

---

### Milestone 2.2 — Reporting (Admin only)
- [ ] Leads by status — bar chart
- [ ] Conversion rate (Won / Total) — stat card
- [ ] Follow-up completion rate per user — table
- [ ] Date range filter for all reports
- [ ] Chart rendered with Chart.js or native Canvas API

**Deliverable:** Meaningful analytics for portfolio differentiation.

---

### Milestone 2.3 — UI Polish
- [ ] Consistent empty states (no data illustrations or copy)
- [ ] Pagination on all list pages (> 20 records)
- [ ] Flash messages for all create/edit/delete actions
- [ ] Breadcrumb navigation
- [ ] Favicon and page titles per route
- [ ] (Optional) Dark mode toggle

---

### Milestone 2.4 — Portfolio Presentation
- [ ] Write `README.md` with screenshots, setup instructions, feature list
- [ ] Add `database/schema.sql` and `database/seed.sql` to repo
- [ ] Deploy to a free hosting tier (e.g. Railway, Render, or shared hosting)
- [ ] Add live demo link to GitHub repo description
- [ ] Record a short Loom walkthrough (optional)

---

## Effort Estimates

| Milestone | Estimated Days |
|---|---|
| 1.1 Foundation | 1–2 days |
| 1.2 Auth | 1 day |
| 1.3 User Management | 1 day |
| 1.4 Leads | 2–3 days |
| 1.5 Customers | 1–2 days |
| 1.6 Follow-Ups | 1–2 days |
| 1.7 Dashboard | 1 day |
| 1.8 Hardening | 1 day |
| **Phase 1 Total** | **~10 days** |
| 2.1 Activities | 1–2 days |
| 2.2 Reporting | 2 days |
| 2.3 UI Polish | 1–2 days |
| 2.4 Portfolio | 1 day |
| **Phase 2 Total** | **~7 days** |

> Estimates assume focused, part-time work (3–5 hours/day). Adjust to your actual schedule.

---

## Branching Strategy

```
main          ← stable, always deployable
└── dev       ← integration branch
    ├── feature/auth
    ├── feature/leads
    ├── feature/customers
    └── feature/follow-ups
```

- Feature branches merge into `dev` via pull requests
- `dev` merges into `main` at the end of each milestone
- Commit style: [Conventional Commits](https://www.conventionalcommits.org/)
  - `feat:`, `fix:`, `refactor:`, `docs:`, `style:`, `chore:`

---

## Definition of Done

A milestone is complete when:
1. All checklist items are implemented
2. No known bugs in the covered functionality
3. Code passes a self-review against PSR-12 and security checklist
4. Changes are committed with descriptive messages on a feature branch
5. Feature branch is merged into `dev`
