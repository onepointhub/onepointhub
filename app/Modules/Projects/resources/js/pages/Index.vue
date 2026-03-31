<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import AppLayout from '@/layouts/AppLayout.vue'
import { index } from '@/routes/projects'

interface Member {
  id: number
  name: string
  avatar: string | null
}

interface ProjectItem {
  id: number
  name: string
  status: string
  type: string
  colour: string | null
  ends_at: string | null
  client: { id: number, name: string } | null
  tasks_count: number
  completed_tasks_count: number
  members: Member[]
}

interface Filters {
  search?: string
  status?: string
  client_id?: string
  sort?: string
  direction?: string
}

interface Props {
  projects: {
    data: ProjectItem[]
    links: { url: string | null, label: string, active: boolean }[]
    meta: { total: number }
  }
  filters: Filters
  canCreate: boolean
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Projects', href: index() }]

const view = ref<'grid' | 'table'>('grid')
const search = ref(props.filters.search ?? '')

function statusVariant(status: string): 'default' | 'secondary' | 'outline' | 'destructive' {
  const map: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
    active: 'default',
    on_hold: 'secondary',
    completed: 'outline',
    archived: 'secondary',
  }
  return map[status] ?? 'outline'
}

function progress(item: ProjectItem): number {
  if (item.tasks_count === 0) {
    return 0
  }
  return Math.round((item.completed_tasks_count / item.tasks_count) * 100)
}

function applyFilters(extra: Record<string, string | undefined> = {}) {
  router.get(index(), {
    search: search.value || undefined,
    status: props.filters.status,
    ...extra,
  }, { preserveState: true, replace: true })
}
</script>

<template>
  <Head title="Projects" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-4">
      <!-- Toolbar -->
      <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-2 flex-wrap">
          <Input
            v-model="search"
            placeholder="Search projects…"
            class="w-56"
            @keyup.enter="applyFilters()"
          />
          <Select
            :model-value="filters.status ?? ''"
            @update:model-value="applyFilters({ status: ($event as string) || undefined })"
          >
            <SelectTrigger class="w-36">
              <SelectValue placeholder="All statuses" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">
                All statuses
              </SelectItem>
              <SelectItem value="active">
                Active
              </SelectItem>
              <SelectItem value="on_hold">
                On Hold
              </SelectItem>
              <SelectItem value="completed">
                Completed
              </SelectItem>
              <SelectItem value="archived">
                Archived
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="flex items-center gap-2">
          <Button variant="outline" size="sm" @click="view = view === 'grid' ? 'table' : 'grid'">
            {{ view === 'grid' ? 'Table view' : 'Card view' }}
          </Button>
          <Link v-if="canCreate" href="#create()#">
            <Button size="sm">
              New Project
            </Button>
          </Link>
        </div>
      </div>

      <!-- Empty state -->
      <div
        v-if="projects.data.length === 0"
        class="rounded-lg border border-dashed py-24 text-center text-sm text-muted-foreground"
      >
        <p class="text-base font-medium">
          No projects yet.
        </p>
        <p class="mt-1">
          Create your first project to get started.
        </p>
        <Link v-if="canCreate" href="#create()#" class="mt-4 inline-block">
          <Button size="sm">
            New Project
          </Button>
        </Link>
      </div>

      <!-- Card grid -->
      <div v-else-if="view === 'grid'" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <Link
          v-for="project in projects.data"
          :key="project.id"
          href="#show({ project: project.id })#"
        >
          <Card class="hover:shadow-md transition-shadow cursor-pointer h-full">
            <CardHeader class="pb-2">
              <div class="flex items-start justify-between gap-2">
                <div class="flex items-center gap-2">
                  <span
                    v-if="project.colour"
                    class="inline-block h-3 w-3 rounded-full shrink-0"
                    :style="{ backgroundColor: project.colour }"
                  />
                  <CardTitle class="text-sm font-medium leading-snug">
                    {{ project.name }}
                  </CardTitle>
                </div>
                <Badge :variant="statusVariant(project.status)" class="capitalize shrink-0 text-xs">
                  {{ project.status.replace('_', ' ') }}
                </Badge>
              </div>
              <p v-if="project.client" class="text-xs text-muted-foreground mt-1">
                {{ project.client.name }}
              </p>
            </CardHeader>
            <CardContent class="flex flex-col gap-3">
              <!-- Progress bar -->
              <div v-if="project.tasks_count > 0">
                <div class="flex justify-between text-xs text-muted-foreground mb-1">
                  <span>Progress</span>
                  <span>{{ progress(project) }}%</span>
                </div>
                <div class="h-1.5 rounded-full bg-muted overflow-hidden">
                  <div
                    class="h-full bg-primary rounded-full transition-all"
                    :style="{ width: `${progress(project)}%` }"
                  />
                </div>
              </div>
              <p v-if="project.ends_at" class="text-xs text-muted-foreground">
                Due {{ project.ends_at }}
              </p>
            </CardContent>
          </Card>
        </Link>
      </div>

      <!-- Table view -->
      <div v-else class="rounded-lg border">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b text-left text-muted-foreground">
              <th class="p-3 font-medium">
                Name
              </th>
              <th class="p-3 font-medium">
                Status
              </th>
              <th class="p-3 font-medium">
                Client
              </th>
              <th class="p-3 font-medium">
                Progress
              </th>
              <th class="p-3 font-medium">
                Due
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="project in projects.data"
              :key="project.id"
              class="border-b last:border-0 hover:bg-muted/50"
            >
              <td class="p-3">
                <Link href="#show({ project: project.id })#" class="font-medium hover:underline">
                  {{ project.name }}
                </Link>
              </td>
              <td class="p-3">
                <Badge :variant="statusVariant(project.status)" class="capitalize text-xs">
                  {{ project.status.replace('_', ' ') }}
                </Badge>
              </td>
              <td class="p-3 text-muted-foreground">
                {{ project.client?.name ?? '—' }}
              </td>
              <td class="p-3">
                <span v-if="project.tasks_count > 0">{{ progress(project) }}%</span>
                <span v-else class="text-muted-foreground">—</span>
              </td>
              <td class="p-3 text-muted-foreground">
                {{ project.ends_at ?? '—' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
