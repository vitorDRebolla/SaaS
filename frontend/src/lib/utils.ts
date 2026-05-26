import type { Priority, StatusType } from '@/types/models'

export function cn(...classes: (string | boolean | undefined | null)[]): string {
  return classes.filter(Boolean).join(' ')
}

export function formatDate(date: string | null, options?: Intl.DateTimeFormatOptions): string {
  if (!date) return ''
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    ...options,
  }).format(new Date(date))
}

export function formatRelativeTime(date: string): string {
  const d = new Date(date)
  const diff = Date.now() - d.getTime()
  const seconds = Math.floor(diff / 1000)
  if (seconds < 60) return 'just now'
  const minutes = Math.floor(seconds / 60)
  if (minutes < 60) return `${minutes}m ago`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours}h ago`
  const days = Math.floor(hours / 24)
  if (days < 7) return `${days}d ago`
  return formatDate(date)
}

export function formatDuration(seconds: number): string {
  const h = Math.floor(seconds / 3600)
  const m = Math.floor((seconds % 3600) / 60)
  if (h > 0) return `${h}h ${m}m`
  return `${m}m`
}

export function formatBytes(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1048576) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / 1048576).toFixed(1)} MB`
}

export const PRIORITY_CONFIG: Record<Priority, { label: string; color: string; icon: string }> = {
  none:   { label: 'No priority', color: 'text-zinc-400', icon: '○' },
  low:    { label: 'Low',         color: 'text-blue-400',  icon: '▽' },
  medium: { label: 'Medium',      color: 'text-amber-400', icon: '▷' },
  high:   { label: 'High',        color: 'text-orange-500',icon: '▲' },
  urgent: { label: 'Urgent',      color: 'text-red-500',   icon: '⚑' },
}

export const STATUS_TYPE_COLORS: Record<StatusType, string> = {
  backlog:   'text-zinc-400',
  started:   'text-amber-400',
  completed: 'text-emerald-500',
  cancelled: 'text-red-400',
}

export function generateInitials(name: string): string {
  return name
    .split(' ')
    .map((n) => n[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()
}

export function generateAvatarColor(id: number): string {
  const colors = [
    'bg-violet-500', 'bg-indigo-500', 'bg-blue-500', 'bg-cyan-500',
    'bg-teal-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500',
  ]
  return colors[id % colors.length] ?? 'bg-indigo-500'
}
