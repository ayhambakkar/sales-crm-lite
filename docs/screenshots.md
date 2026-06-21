# Screenshot Checklist

Use this checklist when preparing Sales CRM Lite for GitHub, portfolio pages, recruiting conversations, or client demos.

Recommended image location:

```text
docs/assets/screenshots/
```

Recommended dimensions:

- Desktop screenshots: 1440px wide or larger.
- Detail screenshots: crop only when it improves readability.
- Use production-like demo data, not empty screens unless the empty state is the subject.
- Avoid showing real secrets, production data, database passwords, session cookies, or private URLs.

## Required Portfolio Screenshots

| Screenshot | Suggested path | What to show |
| --- | --- | --- |
| Login | `docs/assets/screenshots/login.png` | Centered CRM login card and branding |
| Dashboard | `docs/assets/screenshots/dashboard.png` | KPI cards, recent records, upcoming follow-ups |
| Leads listing | `docs/assets/screenshots/leads-index.png` | Search, filters, sort controls, pagination, status/priority badges |
| Lead detail | `docs/assets/screenshots/lead-detail.png` | Lead fields, activity/follow-up context, conversion action or converted state |
| Customers listing | `docs/assets/screenshots/customers-index.png` | Customer filters, status badges, assigned user context |
| Customer detail | `docs/assets/screenshots/customer-detail.png` | Customer profile and related follow-ups/activity |
| Follow-ups | `docs/assets/screenshots/follow-ups-index.png` | Open, overdue, done, and cancelled tasks |
| Activity log | `docs/assets/screenshots/activity-log.png` | Audit trail filters and recent entries |
| Reports | `docs/assets/screenshots/reports.png` | KPI/report summary and admin performance table |
| CSV export buttons | `docs/assets/screenshots/csv-exports.png` | Export buttons on a list/report page |

## Optional Screenshots

- User management screen for admin-only access.
- Sales rep view showing scoped records.
- Mobile or narrow viewport navigation.
- Production `/health` response for deployment documentation.

## Capture Notes

1. Seed demo data locally with `database/seeders/002_seed_demo_crm_data.sql`.
2. Build CSS with `npm run build:css`.
3. Run the local server with `php -S localhost:8000 -t public`.
4. Log in as both admin and sales rep to capture role differences.
5. Keep filenames lowercase and hyphenated.
6. Update the README screenshot table if any filenames change.
