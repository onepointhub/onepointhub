<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import { index, show } from '@/routes/projects'
import { destroy } from '@/routes/projects/time'

interface TimeEntry {
  id: number
  description: string | null
  started_at: string
  ended_at: string | null
  duration_minutes: number | null
  billable: boolean
  invoiced_at: string | null
  task: { id: number, title: string } | null
  user: { id: number, name: string, avatar: string | null }
}

interface Props {
  project: { id: number, name: string }
  entries: TimeEntry[]
  totals: { total_minutes: number, billable_minutes: number, non_billable_minutes: number }
  canEdit: boolean
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Projects', href: index() },
  { title: props.project.name, href: show({ project: props.project.id }) },
  { title: 'Time Log', href: '#' },
]

function formatMinutes(minutes: number | null): string {
  if (!minutes) {
    return '—'
  }
  const h = Math.floor(minutes / 60)
  const m = minutes % 60
  return h > 0 ? `${h}h ${m}m` : `${m}m`
}

function deleteEntry(entry: TimeEntry) {
  router.delete(destroy({ project: props.project.id, entry: entry.id }))
}
</script>

<template>
  <Head :title="`Time Log — ${project.name}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-4">
      <!-- Totals -->
      <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg border p-4">
          <p class="text-xs text-muted-foreground uppercase tracking-wide">
            Total
          </p>
          <p class="text-2xl font-semibold mt-1">
            {{ formatMinutes(totals.total_minutes) }}
          </p>
        </div>
        <div class="rounded-lg border p-4">
          <p class="text-xs text-muted-foreground uppercase tracking-wide">
            Billable
          </p>
          <p class="text-2xl font-semibold mt-1">
            {{ formatMinutes(totals.billable_minutes) }}
          </p>
        </div>
        <div class="rounded-lg border p-4">
          <p class="text-xs text-muted-foreground uppercase tracking-wide">
            Non-billable
          </p>
          <p class="text-2xl font-semibold mt-1">
            {{ formatMinutes(totals.non_billable_minutes) }}
          </p>
        </div>
      </div>

      <!-- Entries table -->
      <div v-if="entries.length === 0" class="py-16 text-center text-sm text-muted-foreground">
        No time entries yet.
      </div>

      <div v-else class="rounded-lg border">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b text-left text-muted-foreground">
              <th class="p-3 font-medium">
                Description
              </th>
              <th class="p-3 font-medium">
                Person
              </th>
              <th class="p-3 font-medium">
                Date
              </th>
              <th class="p-3 font-medium">
                Duration
              </th>
              <th class="p-3 font-medium">
                Billable
              </th>
              <th class="p-3 font-medium" />
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="entry in entries"
              :key="entry.id"
              class="border-b last:border-0 hover:bg-muted/50"
            >
              <td class="p-3">
                <span class="font-medium">{{ entry.description ?? '—' }}</span>
                <p v-if="entry.task" class="text-xs text-muted-foreground mt-0.5">
                  {{ entry.task.title }}
                </p>
              </td>
              <td class="p-3 text-muted-foreground">
                {{ entry.user.name }}
              </td>
              <td class="p-3 text-muted-foreground">
                {{ entry.started_at.slice(0, 10) }}
              </td>
              <td class="p-3">
                {{ formatMinutes(entry.duration_minutes) }}
              </td>
              <td class="p-3">
                <Badge :variant="entry.billable ? 'default' : 'secondary'" class="text-xs">
                  {{ entry.billable ? 'Billable' : 'Non-billable' }}
                </Badge>
              </td>
              <td class="p-3">
                <Button
                  v-if="canEdit && !entry.invoiced_at"
                  size="sm"
                  variant="ghost"
                  class="text-destructive text-xs"
                  @click="deleteEntry(entry)"
                >
                  Delete
                </Button>
                <Badge v-if="entry.invoiced_at" variant="outline" class="text-xs">
                  Invoiced
                </Badge>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
