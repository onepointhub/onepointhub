<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { getInitials } from '@/composables/useInitials'
import { timeAgo } from '@/composables/useTimeAgo'
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard } from '@/routes'

interface RecentMember {
  id: number
  name: string
  avatar: string | null
  role: string
}

interface ActivityEntry {
  id: number
  event: string
  subject_type: string
  actor: { name: string, avatar: string | null } | null
  created_at: string
}

interface ActiveProject {
  id: number
  name: string
  colour: string | null
  status: string
  client_name: string | null
}

interface TaskItem {
  id: number
  title: string
  priority: string
  due_at: string | null
  project_name: string | null
}

interface OverdueTask extends TaskItem {
  days_overdue: number
}

interface TimeEntryItem {
  id: number
  duration_minutes: number
  project_name: string | null
  user: { name: string, avatar: string | null } | null
  created_at: string
}

interface Props {
  memberCount: number
  pendingInvitationsCount: number
  recentMembers: RecentMember[]
  recentActivity: ActivityEntry[]
  activeProjectsCount: number
  openTasksCount: number
  clientsCount: number
  hoursThisWeek: number
  activeProjects: ActiveProject[]
  openTasks: TaskItem[]
  overdueTasks: OverdueTask[]
  recentTimeEntries: TimeEntryItem[]
}

defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard(),
  },
]

function eventVariant(event: string): 'default' | 'secondary' | 'destructive' {
  if (event === 'created') {
    return 'default'
  }
  if (event === 'deleted') {
    return 'destructive'
  }
  return 'secondary'
}

function priorityVariant(priority: string): 'default' | 'secondary' | 'destructive' {
  if (priority === 'urgent' || priority === 'high') {
    return 'destructive'
  }
  if (priority === 'medium') {
    return 'default'
  }
  return 'secondary'
}

function formatDuration(minutes: number): string {
  const h = Math.floor(minutes / 60)
  const m = minutes % 60
  if (h === 0) {
    return `${m}m`
  }
  if (m === 0) {
    return `${h}h`
  }
  return `${h}h ${m}m`
}
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-4">
      <!-- Stat cards -->
      <div class="grid gap-4 md:grid-cols-3">
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              Members
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-3xl font-bold">
              {{ memberCount }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              Pending Invitations
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-3xl font-bold">
              {{ pendingInvitationsCount }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              Active Projects
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-3xl font-bold">
              {{ activeProjectsCount }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              Open Tasks
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-3xl font-bold">
              {{ openTasksCount }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              Clients
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-3xl font-bold">
              {{ clientsCount }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              Hours This Week
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-3xl font-bold">
              {{ hoursThisWeek }}h
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Row 1: Members + Activity -->
      <div class="grid gap-4 md:grid-cols-2">
        <!-- Recent members -->
        <Card>
          <CardHeader>
            <CardTitle class="text-base">
              Recent Members
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div
              v-if="recentMembers.length === 0"
              class="py-4 text-center text-sm text-muted-foreground"
            >
              No members yet.
            </div>
            <ul v-else class="space-y-3">
              <li
                v-for="member in recentMembers"
                :key="member.id"
                class="flex items-center justify-between"
              >
                <div class="flex items-center gap-3">
                  <Avatar class="h-8 w-8">
                    <AvatarImage
                      :src="member.avatar || ''"
                      :alt="member.name"
                    />
                    <AvatarFallback class="text-xs">
                      {{ getInitials(member.name) }}
                    </AvatarFallback>
                  </Avatar>
                  <span class="text-sm font-medium">{{ member.name }}</span>
                </div>
                <Badge variant="secondary" class="capitalize">
                  {{ member.role }}
                </Badge>
              </li>
            </ul>
          </CardContent>
        </Card>

        <!-- Recent activity -->
        <Card>
          <CardHeader>
            <CardTitle class="text-base">
              Recent Activity
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div
              v-if="recentActivity.length === 0"
              class="py-4 text-center text-sm text-muted-foreground"
            >
              No activity recorded yet.
            </div>
            <ul v-else class="space-y-3">
              <li
                v-for="entry in recentActivity"
                :key="entry.id"
                class="flex items-center justify-between gap-2"
              >
                <div class="flex min-w-0 items-center gap-2">
                  <Avatar class="h-6 w-6 shrink-0">
                    <AvatarImage
                      v-if="entry.actor"
                      :src="entry.actor.avatar || ''"
                      :alt="entry.actor.name"
                    />
                    <AvatarFallback class="text-[10px]">
                      {{ entry.actor ? getInitials(entry.actor.name) : 'SYS' }}
                    </AvatarFallback>
                  </Avatar>
                  <span class="truncate text-sm text-muted-foreground">
                    {{ entry.subject_type }}
                  </span>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                  <Badge :variant="eventVariant(entry.event)" class="capitalize">
                    {{ entry.event }}
                  </Badge>
                  <span class="text-xs text-muted-foreground">
                    {{ timeAgo(entry.created_at) }}
                  </span>
                </div>
              </li>
            </ul>
          </CardContent>
        </Card>
      </div>

      <!-- Row 2: Active Projects + Open Tasks -->
      <div class="grid gap-4 md:grid-cols-2">
        <!-- Active projects -->
        <Card>
          <CardHeader>
            <CardTitle class="text-base">
              Active Projects
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div
              v-if="activeProjects.length === 0"
              class="py-4 text-center text-sm text-muted-foreground"
            >
              No active projects.
            </div>
            <ul v-else class="space-y-3">
              <li
                v-for="project in activeProjects"
                :key="project.id"
                class="flex items-center justify-between gap-2"
              >
                <div class="flex min-w-0 items-center gap-2">
                  <span
                    class="h-2.5 w-2.5 shrink-0 rounded-full"
                    :style="{ backgroundColor: project.colour ?? '#94a3b8' }"
                  />
                  <span class="truncate text-sm font-medium">{{ project.name }}</span>
                </div>
                <span
                  v-if="project.client_name"
                  class="shrink-0 text-xs text-muted-foreground"
                >
                  {{ project.client_name }}
                </span>
              </li>
            </ul>
          </CardContent>
        </Card>

        <!-- Open tasks -->
        <Card>
          <CardHeader>
            <CardTitle class="text-base">
              Open Tasks
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div
              v-if="openTasks.length === 0"
              class="py-4 text-center text-sm text-muted-foreground"
            >
              No open tasks.
            </div>
            <ul v-else class="space-y-3">
              <li
                v-for="task in openTasks"
                :key="task.id"
                class="flex items-center justify-between gap-2"
              >
                <div class="min-w-0">
                  <p class="truncate text-sm font-medium">
                    {{ task.title }}
                  </p>
                  <p
                    v-if="task.project_name"
                    class="truncate text-xs text-muted-foreground"
                  >
                    {{ task.project_name }}
                  </p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                  <Badge :variant="priorityVariant(task.priority)" class="capitalize">
                    {{ task.priority }}
                  </Badge>
                  <span
                    v-if="task.due_at"
                    class="text-xs text-muted-foreground"
                  >
                    {{ task.due_at }}
                  </span>
                </div>
              </li>
            </ul>
          </CardContent>
        </Card>
      </div>

      <!-- Row 3: Overdue Tasks + Recent Time Entries -->
      <div class="grid gap-4 md:grid-cols-2">
        <!-- Overdue tasks -->
        <Card>
          <CardHeader>
            <CardTitle class="text-base">
              Overdue Tasks
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div
              v-if="overdueTasks.length === 0"
              class="py-4 text-center text-sm text-muted-foreground"
            >
              No overdue tasks.
            </div>
            <ul v-else class="space-y-3">
              <li
                v-for="task in overdueTasks"
                :key="task.id"
                class="flex items-center justify-between gap-2"
              >
                <div class="min-w-0">
                  <p class="truncate text-sm font-medium">
                    {{ task.title }}
                  </p>
                  <p
                    v-if="task.project_name"
                    class="truncate text-xs text-muted-foreground"
                  >
                    {{ task.project_name }}
                  </p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                  <Badge :variant="priorityVariant(task.priority)" class="capitalize">
                    {{ task.priority }}
                  </Badge>
                  <span class="text-xs text-destructive">
                    {{ task.days_overdue }}d overdue
                  </span>
                </div>
              </li>
            </ul>
          </CardContent>
        </Card>

        <!-- Recent time entries -->
        <Card>
          <CardHeader>
            <CardTitle class="text-base">
              Recent Time Entries
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div
              v-if="recentTimeEntries.length === 0"
              class="py-4 text-center text-sm text-muted-foreground"
            >
              No time entries yet.
            </div>
            <ul v-else class="space-y-3">
              <li
                v-for="entry in recentTimeEntries"
                :key="entry.id"
                class="flex items-center justify-between gap-2"
              >
                <div class="flex min-w-0 items-center gap-2">
                  <Avatar class="h-6 w-6 shrink-0">
                    <AvatarImage
                      v-if="entry.user"
                      :src="entry.user.avatar || ''"
                      :alt="entry.user.name"
                    />
                    <AvatarFallback class="text-[10px]">
                      {{ entry.user ? getInitials(entry.user.name) : '?' }}
                    </AvatarFallback>
                  </Avatar>
                  <div class="min-w-0">
                    <p
                      v-if="entry.project_name"
                      class="truncate text-sm font-medium"
                    >
                      {{ entry.project_name }}
                    </p>
                    <p
                      v-if="entry.user"
                      class="truncate text-xs text-muted-foreground"
                    >
                      {{ entry.user.name }}
                    </p>
                  </div>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                  <span class="text-sm font-medium">
                    {{ formatDuration(entry.duration_minutes) }}
                  </span>
                  <span class="text-xs text-muted-foreground">
                    {{ timeAgo(entry.created_at) }}
                  </span>
                </div>
              </li>
            </ul>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
