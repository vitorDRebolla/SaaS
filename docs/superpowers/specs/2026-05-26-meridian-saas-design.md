# Meridian — Team Project Intelligence Platform: Design Spec

**Date:** 2026-05-26  
**Status:** Approved

---

## 1. Product Concept

**Name:** Meridian  
**Tagline:** The project operating system for teams that ship.

Meridian is a modern, opinionated project operating system for software teams (3–100 people). It combines structured issue tracking (à la Linear) with team analytics intelligence and a trigger-based automation engine. The UI targets engineers who care about keyboard shortcuts, density, and zero friction.

**Core value props:**
- Inline team analytics (velocity, throughput, workload heatmaps) without context switching
- Automation engine: trigger-based rules that eliminate manual triage
- Full polymorphic audit trail on every entity mutation
- API-first: personal tokens + HMAC-signed outbound webhooks

**SaaS pillars:**
- Multi-tenant workspaces (teams)
- Role-based access control (owner / admin / member / viewer)
- Subscription-ready billing architecture (Stripe-ready, scaffolded but not wired)
- Real-time collaborative updates via WebSockets
- Dark/light theme, keyboard-first UX, fully responsive

---

## 2. Domain Model

### Core Entities

| Model | Key Fields | Notes |
|---|---|---|
| `User` | id, name, email, password, avatar_url, timezone, preferences (jsonb), email_verified_at | Global, belongs to many teams |
| `Team` | id, name, slug (unique), avatar_url, settings (jsonb), plan, subscription_status, trial_ends_at | Root tenant |
| `TeamMember` | id, team_id, user_id, role, joined_at | Roles: owner/admin/member/viewer |
| `TeamInvitation` | id, team_id, email, role, token (unique), expires_at, accepted_at | 7-day expiry |
| `Project` | id, team_id, name, description, identifier (e.g. PROJ), color, settings (jsonb), status, archived_at | `identifier` used for issue slugs |
| `IssueStatus` | id, project_id, name, color, type (backlog/started/completed/cancelled), position | Ordered per project |
| `IssueLabel` | id, project_id, name, color | Per project |
| `Issue` | id, project_id, team_id, creator_id, assignee_id, status_id, title, description (text), priority (none/low/medium/high/urgent), sequence_number (int, auto per project), position (float), due_date, started_at, completed_at, archived_at | `team_id` denormalized for query performance |
| `issue_label` | issue_id, label_id | Pivot |
| `Comment` | id, issue_id, user_id, content (text), edited_at | Markdown supported |
| `Attachment` | id, attachable_type, attachable_id, user_id, name, disk_path, size_bytes, mime_type | Polymorphic |
| `TimeEntry` | id, issue_id, user_id, started_at, stopped_at, duration_seconds, description | |
| `ActivityLog` | id, loggable_type, loggable_id, causer_id, team_id, event, old_values (jsonb), new_values (jsonb), created_at | Written by observers |
| `Notification` | Laravel default | database + broadcast channels |
| `AutomationRule` | id, project_id, name, trigger_type, trigger_config (jsonb), active | |
| `AutomationAction` | id, rule_id, action_type, action_config (jsonb), position | Ordered actions per rule |
| `Webhook` | id, team_id, url, subscribed_events (jsonb), secret, active, last_response_code, last_called_at | HMAC-SHA256 signed |
| `FeatureFlag` | id, name, description, globally_enabled, rollout_percentage, allowed_team_ids (jsonb) | Admin-controlled |

### Relationship Summary
```
Team
  ├── TeamMember (users)
  ├── TeamInvitation
  ├── Project
  │   ├── IssueStatus
  │   ├── IssueLabel
  │   ├── Issue
  │   │   ├── Comment
  │   │   ├── Attachment (polymorphic)
  │   │   ├── TimeEntry
  │   │   └── IssueLabel (pivot)
  │   └── AutomationRule → AutomationAction
  ├── Webhook
  └── ActivityLog (polymorphic on all above)
```

---

## 3. Backend Architecture

### Stack
- **PHP 8.3** / **Laravel 11**
- **PostgreSQL 16** — primary database; JSONB for flexible config columns
- **Redis** — cache (analytics snapshots, feature flags), queues, rate limiting
- **Laravel Sanctum** — SPA cookie auth + bearer tokens for API clients
- **Laravel Reverb** — self-hosted WebSocket server (no external Pusher dependency)
- **Laravel Horizon** — queue visibility, monitoring, retry management
- **Pest** — test framework (feature + unit)

### Directory Structure
```
app/
  Actions/
    Auth/          CreateUser, IssuePasswordReset
    Teams/         CreateTeam, InviteMember, UpdateMemberRole, RemoveMember
    Projects/      CreateProject, ArchiveProject
    Issues/        CreateIssue, UpdateIssue, BulkUpdateIssues, DeleteIssue
    Comments/      CreateComment, UpdateComment
    Automations/   EvaluateRules
    Webhooks/      DispatchWebhook
  Events/
    Issues/        IssueCreated, IssueUpdated, IssueDeleted
    Comments/      CommentCreated, CommentUpdated
    Members/       MemberJoined, MemberRemoved
  Http/
    Controllers/Api/V1/
      AuthController
      TeamController
      TeamMemberController
      TeamInvitationController
      TeamAnalyticsController
      TeamWebhookController
      ProjectController
      ProjectStatusController
      ProjectLabelController
      ProjectAnalyticsController
      IssueController
      IssueCommentController
      IssueAttachmentController
      IssueTimeEntryController
      NotificationController
      AutomationController
      FeatureFlagController (admin only)
      SearchController
    Middleware/
      EnsureTeamMember     — resolves :team slug, gates by membership
      EnsureProjectAccess  — project belongs to team
      ThrottleApiRequests  — tiered rate limiting
    Requests/              — strict FormRequest per action, no validation in controllers
    Resources/             — JsonResource per model; never expose bare Eloquent
  Jobs/
    ProcessWebhookDelivery
    EvaluateAutomationRules
    SendTeamInvitationEmail
    GenerateTeamAnalyticsSnapshot
    CleanupExpiredInvitations
  Models/                  — strict types, no guarded wildcard, explicit casts
  Notifications/
    IssueAssigned
    IssueMentioned
    TeamInvitationReceived
    CommentAdded
  Observers/               — ActivityLog written here on all mutations
  Policies/
    TeamPolicy, ProjectPolicy, IssuePolicy, CommentPolicy, WebhookPolicy
  Services/
    AnalyticsService       — velocity, throughput, workload calculations
    FileUploadService      — S3-compatible (local disk in dev via MinIO)
    FeatureFlagService     — resolve flags for a team/user
    BroadcastService       — typed event→channel mapping
routes/
  api.php                  — all /api/v1/* routes; no web routes (SPA handles routing)
```

### API Design (v1)
```
Auth
  POST   /api/v1/auth/register
  POST   /api/v1/auth/login
  POST   /api/v1/auth/logout
  POST   /api/v1/auth/forgot-password
  POST   /api/v1/auth/reset-password
  GET    /api/v1/auth/me
  PATCH  /api/v1/auth/me
  GET    /api/v1/auth/teams          — teams for current user

Teams (slug-routed)
  GET    /api/v1/teams
  POST   /api/v1/teams
  GET    /api/v1/teams/{team}
  PATCH  /api/v1/teams/{team}
  DELETE /api/v1/teams/{team}
  GET    /api/v1/teams/{team}/members
  POST   /api/v1/teams/{team}/invitations
  DELETE /api/v1/teams/{team}/invitations/{invitation}
  PATCH  /api/v1/teams/{team}/members/{member}
  DELETE /api/v1/teams/{team}/members/{member}
  GET    /api/v1/teams/{team}/analytics
  GET    /api/v1/teams/{team}/activity
  GET    /api/v1/teams/{team}/notifications
  POST   /api/v1/teams/{team}/notifications/read-all
  GET    /api/v1/teams/{team}/webhooks
  POST   /api/v1/teams/{team}/webhooks
  PATCH  /api/v1/teams/{team}/webhooks/{webhook}
  DELETE /api/v1/teams/{team}/webhooks/{webhook}
  POST   /api/v1/teams/{team}/webhooks/{webhook}/test
  GET    /api/v1/teams/{team}/api-tokens
  POST   /api/v1/teams/{team}/api-tokens
  DELETE /api/v1/teams/{team}/api-tokens/{token}

Projects
  GET    /api/v1/teams/{team}/projects
  POST   /api/v1/teams/{team}/projects
  GET    /api/v1/teams/{team}/projects/{project}
  PATCH  /api/v1/teams/{team}/projects/{project}
  DELETE /api/v1/teams/{team}/projects/{project}
  GET    /api/v1/teams/{team}/projects/{project}/analytics
  GET/POST/PATCH/DELETE  …/statuses/{status}
  GET/POST/PATCH/DELETE  …/labels/{label}
  GET/POST/PATCH/DELETE  …/automations/{rule}

Issues
  GET    /api/v1/projects/{project}/issues       — filterable + searchable
  POST   /api/v1/projects/{project}/issues
  GET    /api/v1/projects/{project}/issues/{issue}
  PATCH  /api/v1/projects/{project}/issues/{issue}
  DELETE /api/v1/projects/{project}/issues/{issue}
  POST   /api/v1/projects/{project}/issues/bulk  — bulk status/assignee change
  GET/POST/PATCH/DELETE  …/comments
  GET/POST/DELETE        …/attachments
  GET/POST/PATCH/DELETE  …/time-entries

Search
  GET    /api/v1/search?q=…&team={team}&type=issue|project|comment

Admin
  GET/POST/PATCH/DELETE  /api/v1/admin/feature-flags/{flag}
  GET                    /api/v1/admin/teams
  GET                    /api/v1/admin/metrics
```

### Key Architecture Decisions

**Actions over Services:** Each use-case is a single `__invoke`-able class. No fat controllers, no fat services. Actions are independently testable and composable.

**Observer-driven audit trail:** `ActivityLog` writes happen in Observers — no scattered `ActivityLog::create()` calls in business logic. Observers fire on `created`, `updated`, `deleted`.

**JSONB for flexibility:** `settings`, `preferences`, `trigger_config`, `action_config`, `properties` are JSONB. Avoids schema migrations for configuration iteration.

**Denormalized `team_id` on Issue:** Allows efficient team-scoped queries without always joining through Project.

**Rate limiting tiers (Redis):**
- Unauthenticated: 60 req/min
- Authenticated: 300 req/min
- Webhook delivery: 30 req/min per endpoint

---

## 4. Frontend Architecture

### Stack
- **Vue 3** (Composition API exclusively, no Options API)
- **TypeScript** (strict mode; no `any` except third-party boundaries)
- **Vite 5** — dev server + build
- **Pinia** — state management
- **Vue Router 4** — with typed route params
- **VueUse** — composable utilities (useLocalStorage, useIntersectionObserver, useDark, onKeyStroke)
- **TailwindCSS v4** — utility-first; CSS variable tokens for theming
- **ApexCharts + vue3-apexcharts** — analytics charts
- **@vueuse/motion** — animation (enter/exit transitions)
- **Headless composables** — custom accessible Dialog, Dropdown, Popover (no heavyweight UI lib dependency)

### Directory Structure
```
src/
  assets/
    fonts/        Inter, Geist Mono
    icons/        Custom SVG icon set (Lucide-based)
  components/
    ui/           Design system primitives (no business logic)
      Button.vue, Input.vue, Select.vue, Textarea.vue
      Modal.vue, Drawer.vue, Popover.vue, Dropdown.vue
      Badge.vue, Avatar.vue, AvatarGroup.vue
      Table.vue, DataTable.vue
      Tabs.vue, Card.vue, Separator.vue
      Toast.vue, ToastProvider.vue
      Skeleton.vue, Spinner.vue
      Kbd.vue, Tooltip.vue
      EmptyState.vue, ErrorState.vue
    features/
      issues/     IssueCard.vue, IssueRow.vue, IssueDetail.vue, IssueKanban.vue, IssueFilters.vue
      projects/   ProjectCard.vue, ProjectList.vue
      analytics/  VelocityChart.vue, ThroughputChart.vue, WorkloadHeatmap.vue, BurnDownChart.vue
      members/    MemberRow.vue, MemberInviteForm.vue
      activity/   ActivityFeed.vue, ActivityItem.vue
      notifications/ NotificationPanel.vue, NotificationItem.vue
      automations/ RuleBuilder.vue, TriggerPicker.vue, ActionPicker.vue
    layouts/
      AppLayout.vue      Sidebar + top nav + notification bell
      AuthLayout.vue     Centered card
      OnboardingLayout.vue  Step wizard shell
  composables/
    useApi.ts          Typed fetch wrapper with error handling
    useTeam.ts         Current team resolution + membership check
    useProject.ts      Current project + statuses/labels
    useIssues.ts       Issue CRUD + optimistic updates
    useNotifications.ts  Real-time notification feed
    useSearch.ts       Debounced search with history
    useTheme.ts        Dark/light toggle + persistence
    useKeyboard.ts     Global keyboard shortcut registry
    useEcho.ts         Laravel Echo channel lifecycle
    usePagination.ts   Cursor-based pagination helper
    useFileUpload.ts   Drag-drop + progress tracking
  lib/
    api.ts             Base fetch client; attaches CSRF + auth headers
    echo.ts            Laravel Echo initialization with Reverb
    date.ts            Date formatting utilities
    cn.ts              Tailwind class merging (clsx + tailwind-merge)
  pages/
    Landing.vue
    auth/  Login.vue, Register.vue, ForgotPassword.vue, ResetPassword.vue
    onboarding/  Step1Team.vue, Step2Members.vue, Step3Project.vue
    dashboard/   Dashboard.vue
    projects/    ProjectList.vue, ProjectBoard.vue, ProjectSettings.vue
    issues/      IssueDetail.vue
    analytics/   Analytics.vue
    notifications/ Notifications.vue
    settings/    Profile.vue, Security.vue, ApiTokens.vue, Members.vue, Webhooks.vue, Billing.vue
    admin/       FeatureFlags.vue, SystemMetrics.vue
  router/
    index.ts           Route definitions
    guards.ts          requiresAuth, requiresTeam, requiresRole, requiresAdmin
  stores/
    auth.ts            User, login/logout/register actions
    team.ts            Current team, members, invitations
    projects.ts        Project list + current project
    issues.ts          Issue list per project, filters, board state
    notifications.ts   Unread count + notification list
    ui.ts              Theme, sidebar collapsed, active modals
  types/
    api.ts             API response envelope types
    models.ts          Strict interfaces mirroring all domain models
    enums.ts           Priority, Role, IssueStatusType, AutomationTrigger …
```

### Design System

**Color tokens (CSS variables, both themes):**
```
--color-bg-base          Page background
--color-bg-surface       Card/panel surface
--color-bg-elevated      Dropdown/modal surface
--color-border           Default border
--color-border-strong    Focused/active border
--color-text-primary     Main text
--color-text-secondary   Muted labels
--color-text-placeholder Form placeholders
--color-accent           Brand (indigo-500 by default, team-configurable)
--color-accent-hover
--color-danger           Red
--color-warning          Amber
--color-success          Emerald
```

**Typography:**
- Body: Inter, 14px base, 1.5 line-height
- Code/identifiers: Geist Mono (issue IDs like `PROJ-42`)
- Scale: 12 / 13 / 14 / 16 / 20 / 24 / 32px

**Spacing:** 4px grid (p-1 = 4px, p-2 = 8px … consistent with Tailwind defaults)

**Animation:** 150ms ease-out for micro-interactions; 200ms for panel slides; no gratuitous motion.

---

## 5. Real-time & Async

### Broadcasting (Laravel Reverb + Echo)

Private channels:
- `teams.{teamId}` — team-level events: MemberJoined, MemberRemoved, ProjectCreated
- `projects.{projectId}` — IssueCreated, IssueUpdated, IssueDeleted
- `issues.{issueId}` — CommentCreated, CommentUpdated
- `users.{userId}` — NotificationCreated (private to recipient)

### Queue Jobs

| Job | Trigger | Queue |
|---|---|---|
| `ProcessWebhookDelivery` | Domain events | `webhooks` (dedicated) |
| `EvaluateAutomationRules` | Issue mutations | `default` |
| `SendTeamInvitationEmail` | Invitation created | `emails` |
| `GenerateTeamAnalyticsSnapshot` | Scheduled daily 02:00 UTC | `analytics` |
| `CleanupExpiredInvitations` | Scheduled hourly | `default` |

### Caching (Redis)

| Key Pattern | TTL | Content |
|---|---|---|
| `team:{id}:analytics:snapshot` | 24h | Pre-computed velocity/throughput |
| `feature-flags` | 5min | Resolved flag map |
| `user:{id}:teams` | 1min | Team list for auth |
| `project:{id}:statuses` | 10min | Status list (rarely changes) |

---

## 6. DevOps

### docker-compose services

| Service | Image | Purpose |
|---|---|---|
| `app` | custom php-fpm 8.3 | Laravel API |
| `nginx` | nginx:alpine | Reverse proxy; serves SPA static files in prod |
| `db` | postgres:16-alpine | Primary database |
| `redis` | redis:7-alpine | Cache + queues + broadcasting state |
| `horizon` | same as app | Queue worker via `artisan horizon` |
| `reverb` | same as app | WebSocket server via `artisan reverb:start` |
| `minio` | minio/minio | S3-compatible local file storage |
| `mailpit` | axllent/mailpit | Local email capture |
| `vite` | node:20-alpine | Frontend dev server (dev profile only) |

### CI Structure (GitHub Actions)

```
.github/workflows/
  ci.yml           PHP lint (Pint) + Larastan + Pest
  frontend-ci.yml  ESLint + TypeScript check + Vitest
```

---

## 7. Testing Strategy

**Backend (Pest):**
- Feature tests hit the actual database (SQLite in-memory for speed, Postgres-compatible queries)
- Each Action class has a unit test
- Each API endpoint has a feature test: happy path + auth guards + validation errors
- Factories for all models; seeders for realistic demo data
- No mocked database; real query assertions

**Frontend (Vitest):**
- Unit tests for all composables
- Component tests for UI primitives (Button, Input, Modal)
- Type checking via `tsc --noEmit` in CI

**E2E (Playwright — scaffolded, not wired):**
- `e2e/` directory with example spec
- `playwright.config.ts` pointing at local dev URL

---

## 8. Code Quality

| Tool | Scope |
|---|---|
| PHP CS Fixer / Pint | Laravel coding style |
| Larastan (PHPStan level 8) | Static analysis |
| ESLint + vue/recommended | Frontend linting |
| Prettier | Frontend formatting |
| Conventional Commits | Git commit messages |
| lint-staged + Husky | Pre-commit hooks |

---

## 9. Seeded Demo Environment

`artisan db:seed` will create:
- 3 demo teams: "Acme Engineering", "Design Co", "Startup Labs"
- 5 users per team (cross-membership to demonstrate multi-team)
- 2–3 projects per team with full issue status boards
- 50–100 issues per project with realistic priorities, assignments, due dates
- 200+ comments and activity log entries
- Sample automation rules and webhooks
- Admin user: `admin@meridian.test` / `password`
- Demo user: `demo@meridian.test` / `password`

---

## 10. Out of Scope (explicitly deferred)

- Stripe payment processing (billing architecture scaffolded, not wired)
- AI/LLM integration (architecture ready for future addition)
- Mobile native apps
- SAML/SSO
- Self-hosted deployment scripts (Docker handles dev; production deployment guide noted in README)
