<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { getInitials } from '@/composables/useInitials'
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

interface Props {
  memberCount: number
  pendingInvitationsCount: number
  recentMembers: RecentMember[]
  recentActivity: ActivityEntry[]
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

function timeAgo(isoString: string): string {
  const diff = Math.floor((Date.now() - new Date(isoString).getTime()) / 1000)
  if (diff < 60) {
    return `${diff}s ago`
  }
  if (diff < 3600) {
    return `${Math.floor(diff / 60)}m ago`
  }
  if (diff < 86400) {
    return `${Math.floor(diff / 3600)}h ago`
  }
  return new Date(isoString).toLocaleDateString()
}
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-4">
      <!-- Stat cards -->
      <div class="grid gap-4 md:grid-cols-2">
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
      </div>

      <!-- Members + Activity -->
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
                <div class="flex items-center gap-2 min-w-0">
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
    </div>
  </AppLayout>
</template>
