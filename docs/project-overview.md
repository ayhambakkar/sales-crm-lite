# Sales CRM Lite — Project Overview

## Vision

Sales CRM Lite is a lightweight, single-tenant Customer Relationship Management system designed for small sales teams. It provides a clean, focused workspace to manage leads, convert them into customers, and track follow-up activities — without the bloat of enterprise CRM solutions.

## Goals

- Demonstrate professional full-stack development skills in a GitHub portfolio context
- Build a maintainable, real-world-grade application following MVC principles
- Deliver a system that a small sales team could realistically adopt

## Key Design Decisions

| Decision | Choice | Rationale |
|---|---|---|
| Tenancy | Single-tenant | Simpler architecture; realistic for one sales team |
| UI Language | English | Broader audience on GitHub; professional standard |
| User Roles | Admin + Sales Rep | Sufficient separation of concerns without over-engineering |
| Frontend | Tailwind CSS + Vanilla JS | No framework overhead; shows raw CSS/JS competence |
| Backend | PHP 8 (MVC, no framework) | Demonstrates core PHP; common in SME environments |
| Database | MySQL / MariaDB | Industry-standard relational DB for CRM data |

## System Scope

The application covers the following modules:

| Module | Description |
|---|---|
| Authentication | Secure login/logout, session management, role-based access |
| Dashboard | KPI overview, recent activity, upcoming follow-ups |
| Leads | Pipeline of prospects, status tracking, assignment |
| Customers | Converted leads; full contact and account management |
| Follow-Ups | Scheduled tasks tied to leads or customers |
| Activities | Audit log of interactions (calls, emails, meetings) |
| Reporting | Sales performance metrics and exportable reports |

**MVP Scope (Phase 1):** Authentication, Dashboard, Leads, Customers, Follow-Ups.  
**Phase 2:** Activities, Reporting.

## Architecture Overview

```
sales-crm-lite/
├── public/             # Document root (index.php, assets)
├── src/
│   ├── Controllers/    # Request handling, one per module
│   ├── Models/         # Data access layer (PDO)
│   ├── Views/          # PHP templates
│   └── Core/           # Router, Request, Session, Auth helpers
├── config/             # DB config, constants
├── database/           # SQL schema + seed files
├── docs/               # Project documentation
└── tests/              # Unit / integration tests
```

### Request Lifecycle

```
Browser → public/index.php → Router → Controller → Model → View → Response
```

All routes pass through a single front controller. Auth middleware protects every route except `/login`.

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.2+ |
| Database | MySQL 8 / MariaDB 10.6+ |
| CSS | Tailwind CSS 3 (CDN or CLI build) |
| JavaScript | Vanilla JS (ES2020+) |
| Version Control | Git + GitHub |
| Local Dev | Apache/Nginx + PHP built-in server |

## Non-Goals

- No REST API or mobile app in this version
- No email sending or calendar sync
- No multi-company / SaaS features
- No automated test pipeline (CI/CD is a stretch goal)
