<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { useInitials } from '@/composables/useInitials'
import { timeAgo } from '@/composables/useTimeAgo'
import AppLayout from '@/layouts/AppLayout.vue'
import WorkspaceSettingsLayout from '@/layouts/workspace/settings/Layout.vue'
import { index as activityLogIndex } from '@/routes/workspace/activity-log'
import { index as membersIndex } from '@/routes/workspace/members'

interface Actor {
  id: number
  name: string
  avatar: string | null
}

interface ActivityLogEntry {
  id: number
  event: string
  subject_type: string
  subject_id: string
  actor: Actor | null
  created_at: string
}

defineProps<{ logs: ActivityLogEntry[] }>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Workspace Settings',
    href: membersIndex(),
  },
  {
    title: 'Activity Log',
    href: activityLogIndex(),
  },
]

const { getInitials } = useInitials()

function eventVariant(event: string): 'default' | 'secondary' | 'destructive' {
  if (event === 'created') {
    return 'default'
  }
  if (event === 'deleted') {
    return 'destructive'
  }

  return 'secondary'
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Activity Log" />

    <WorkspaceSettingsLayout>
      <div class="space-y-6">
        <div>
          <h3 class="text-lg font-medium">
            Activity Log
          </h3>
          <p class="text-sm text-muted-foreground">
            A record of actions taken within this workspace. Showing the last 50 events.
          </p>
        </div>

        <div v-if="logs.length === 0" class="rounded-lg border border-dashed p-8 text-center">
          <p class="text-sm text-muted-foreground">
            No activity recorded yet.
          </p>
        </div>

        <Table v-else>
          <TableHeader>
            <TableRow>
              <TableHead>Actor</TableHead>
              <TableHead>Event</TableHead>
              <TableHead>Resource</TableHead>
              <TableHead class="text-right">
                When
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="log in logs"
              :key="log.id"
            >
              <TableCell>
                <div class="flex items-center gap-2">
                  <Avatar class="h-7 w-7">
                    <AvatarImage
                      v-if="log.actor"
                      :src="log.actor.avatar || ''"
                      :alt="log.actor.name"
                    />
                    <AvatarFallback class="text-xs">
                      {{ log.actor ? getInitials(log.actor.name) : 'SYS' }}
                    </AvatarFallback>
                  </Avatar>
                  <span class="text-sm">
                    {{ log.actor?.name ?? 'System' }}
                  </span>
                </div>
              </TableCell>
              <TableCell>
                <Badge :variant="eventVariant(log.event)" class="capitalize">
                  {{ log.event }}
                </Badge>
              </TableCell>
              <TableCell class="text-sm text-muted-foreground">
                {{ log.subject_type }} #{{ log.subject_id }}
              </TableCell>
              <TableCell class="text-right text-sm text-muted-foreground">
                {{ timeAgo(log.created_at) }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </WorkspaceSettingsLayout>
  </AppLayout>
</template>
