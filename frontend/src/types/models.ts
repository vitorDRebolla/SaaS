export type Role = 'owner' | 'admin' | 'member' | 'viewer'
export type Priority = 'none' | 'low' | 'medium' | 'high' | 'urgent'
export type StatusType = 'backlog' | 'started' | 'completed' | 'cancelled'
export type Plan = 'free' | 'pro' | 'enterprise'

export interface User {
  id: number
  name: string
  email: string
  avatar_url: string | null
  timezone: string
  preferences: Record<string, unknown>
  is_admin: boolean
  email_verified_at: string | null
  created_at: string
}

export interface Team {
  id: number
  name: string
  slug: string
  avatar_url: string | null
  settings: Record<string, unknown>
  plan: Plan
  subscription_status: string
  trial_ends_at: string | null
  member_role?: Role
  members_count?: number
  projects_count?: number
  created_at: string
}

export interface TeamMember {
  id: number
  team_id: number
  user_id: number
  role: Role
  joined_at: string | null
  user: User
}

export interface TeamInvitation {
  id: number
  team_id: number
  email: string
  role: Role
  expires_at: string
  accepted_at: string | null
  is_pending: boolean
  created_at: string
}

export interface Project {
  id: number
  team_id: number
  name: string
  description: string | null
  identifier: string
  color: string
  settings: Record<string, unknown>
  status: string
  archived_at: string | null
  statuses?: IssueStatus[]
  labels?: IssueLabel[]
  issues_count?: number
  created_at: string
  updated_at: string
}

export interface IssueStatus {
  id: number
  project_id: number
  name: string
  color: string
  type: StatusType
  position: number
}

export interface IssueLabel {
  id: number
  project_id: number
  name: string
  color: string
}

export interface Issue {
  id: number
  project_id: number
  team_id: number
  identifier: string
  title: string
  description: string | null
  priority: Priority
  sequence_number: number
  position: number
  due_date: string | null
  started_at: string | null
  completed_at: string | null
  archived_at: string | null
  status: IssueStatus
  status_id: number
  assignee: User | null
  assignee_id: number | null
  creator: User
  creator_id: number
  labels: IssueLabel[]
  comments_count?: number
  attachments_count?: number
  created_at: string
  updated_at: string
}

export interface Comment {
  id: number
  issue_id: number
  content: string
  edited_at: string | null
  user: User
  user_id: number
  created_at: string
  updated_at: string
}

export interface Attachment {
  id: number
  name: string
  size_bytes: number
  mime_type: string
  url: string
  user: User
  created_at: string
}

export interface TimeEntry {
  id: number
  issue_id: number
  user_id: number
  started_at: string
  stopped_at: string | null
  duration_seconds: number
  description: string | null
  user: User
  created_at: string
}

export interface ActivityLog {
  id: number
  loggable_type: string
  loggable_id: number
  event: string
  old_values: Record<string, unknown>
  new_values: Record<string, unknown>
  causer: User | null
  created_at: string
}

export interface AutomationRule {
  id: number
  project_id: number
  name: string
  trigger_type: string
  trigger_config: Record<string, unknown>
  active: boolean
  actions: AutomationAction[]
  created_at: string
}

export interface AutomationAction {
  id: number
  action_type: string
  action_config: Record<string, unknown>
  position: number
}

export interface Webhook {
  id: number
  team_id: number
  name: string
  url: string
  subscribed_events: string[]
  active: boolean
  last_response_code: number | null
  last_called_at: string | null
  created_at: string
}

export interface FeatureFlag {
  id: number
  name: string
  description: string | null
  globally_enabled: boolean
  rollout_percentage: number
  allowed_team_ids: number[]
  created_at: string
  updated_at: string
}

export interface Notification {
  id: string
  type: string
  data: Record<string, unknown>
  read_at: string | null
  created_at: string
}

export interface AnalyticsSnapshot {
  total_issues: number
  completed_issues: number
  in_progress: number
  overdue: number
  velocity: Array<{ week: string; count: number }>
  throughput: Array<{ month: string; count: number }>
  generated_at: string
}

export interface ProjectAnalytics {
  status_breakdown: Array<{ name: string; type: StatusType; color: string; count: number }>
  priority_breakdown: Array<{ priority: Priority; count: number }>
  velocity_by_week: Array<{ week: string; count: number }>
  member_workload: Array<{ assignee_id: number; count: number; assignee: User }>
  total_issues: number
  completed_issues: number
  open_issues: number
}
