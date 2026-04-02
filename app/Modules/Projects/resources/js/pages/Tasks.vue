<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import AppLayout from '@/layouts/AppLayout.vue'
import { index, show } from '@/routes/projects'
import { bulk, update } from '@/routes/projects/tasks'

interface Label { id: number, name: string, colour: string }
interface TaskItem {
  id: number
  title: string
  status: string
  priority: string
  due_at: string | null
  completed_at: string | null
  sub_tasks_count: number
  milestone: { id: number, name: string } | null
  assignee: { id: number, name: string, avatar: string | null } | null
  labels: Label[]
}

interface Props {
  project: { id: number, name: string }
  tasks: TaskItem[]
  filters: Record<string, string>
  canEdit: boolean
  canDelete: boolean
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Projects', href: index() },
  { title: props.project.name, href: show({ project: props.project.id }) },
  { title: 'Tasks', href: '#' },
]

const groupByMilestone = ref(false)
const selectedIds = ref<number[]>([])

const groupedTasks = computed(() => {
  if (!groupByMilestone.value) {
    return null
  }

  const groups: Record<string, { name: string, tasks: TaskItem[] }> = {
    none: { name: 'No Milestone', tasks: [] },
  }

  props.tasks.forEach((task) => {
    if (task.milestone) {
      const key = String(task.milestone.id)
      if (!groups[key]) {
        groups[key] = { name: task.milestone.name, tasks: [] }
      }
      groups[key].tasks.push(task)
    }
    else {
      groups.none.tasks.push(task)
    }
  })

  return groups
})

function toggleSelect(id: number) {
  const idx = selectedIds.value.indexOf(id)
  if (idx >= 0) {
    selectedIds.value.splice(idx, 1)
  }
  else {
    selectedIds.value.push(id)
  }
}

function selectAll() {
  selectedIds.value = props.tasks.map(t => t.id)
}

function clearSelection() {
  selectedIds.value = []
}

function bulkAction(action: string, value?: string) {
  router.post(bulk({ project: props.project.id }), {
    task_ids: selectedIds.value,
    action,
    value,
  }, { onSuccess: () => clearSelection() })
}

function inlineStatus(task: TaskItem, status: string) {
  router.patch(
    update({ project: props.project.id, task: task.id }),
    { title: task.title, status },
    { preserveState: true },
  )
}

function priorityVariant(p: string): 'default' | 'secondary' | 'destructive' | 'outline' {
  const map: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    urgent: 'destructive',
    high: 'default',
    medium: 'secondary',
    low: 'outline',
  }
  return map[p] ?? 'outline'
}
</script>

<template>
  <Head :title="`Tasks — ${project.name}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4">
      <!-- Toolbar -->
      <div class="flex items-center justify-between gap-2 flex-wrap">
        <div class="flex items-center gap-2">
          <Button variant="outline" size="sm" @click="groupByMilestone = !groupByMilestone">
            {{ groupByMilestone ? 'Flat view' : 'Group by milestone' }}
          </Button>
        </div>

        <!-- Bulk actions -->
        <div v-if="selectedIds.length > 0" class="flex items-center gap-2">
          <span class="text-sm text-muted-foreground">{{ selectedIds.length }} selected</span>
          <Button size="sm" variant="outline" @click="bulkAction('status', 'done')">
            Mark Done
          </Button>
          <Button v-if="canDelete" size="sm" variant="outline" class="text-destructive" @click="bulkAction('delete')">
            Delete
          </Button>
          <Button size="sm" variant="ghost" @click="clearSelection">
            Clear
          </Button>
        </div>

        <div v-else class="flex items-center gap-2">
          <Button size="sm" variant="ghost" @click="selectAll">
            Select all
          </Button>
        </div>
      </div>

      <!-- Flat list -->
      <template v-if="!groupByMilestone">
        <div v-if="tasks.length === 0" class="py-16 text-center text-sm text-muted-foreground">
          No tasks yet.
        </div>
        <div v-else class="rounded-lg border divide-y">
          <div
            v-for="task in tasks"
            :key="task.id"
            class="flex items-center gap-3 p-3 hover:bg-muted/50"
          >
            <Checkbox
              :checked="selectedIds.includes(task.id)"
              @update:checked="toggleSelect(task.id)"
            />
            <span class="flex-1 text-sm" :class="{ 'line-through text-muted-foreground': task.completed_at }">
              {{ task.title }}
            </span>
            <Badge :variant="priorityVariant(task.priority)" class="text-xs capitalize hidden sm:inline-flex">
              {{ task.priority }}
            </Badge>
            <Select
              v-if="canEdit"
              :model-value="task.status"
              @update:model-value="inlineStatus(task, ($event as string))"
            >
              <SelectTrigger class="h-7 w-32 text-xs">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="backlog">
                  Backlog
                </SelectItem>
                <SelectItem value="todo">
                  To Do
                </SelectItem>
                <SelectItem value="in_progress">
                  In Progress
                </SelectItem>
                <SelectItem value="in_review">
                  In Review
                </SelectItem>
                <SelectItem value="done">
                  Done
                </SelectItem>
              </SelectContent>
            </Select>
            <span v-if="task.due_at" class="text-xs text-muted-foreground hidden md:inline">{{ task.due_at }}</span>
          </div>
        </div>
      </template>

      <!-- Grouped by milestone -->
      <template v-else>
        <div
          v-for="(group, key) in groupedTasks"
          :key="key"
          class="flex flex-col gap-2"
        >
          <h4 class="text-sm font-medium text-muted-foreground">
            {{ group.name }}
          </h4>
          <div class="rounded-lg border divide-y">
            <div
              v-for="task in group.tasks"
              :key="task.id"
              class="flex items-center gap-3 p-3 hover:bg-muted/50"
            >
              <Checkbox
                :checked="selectedIds.includes(task.id)"
                @update:checked="toggleSelect(task.id)"
              />
              <span class="flex-1 text-sm" :class="{ 'line-through text-muted-foreground': task.completed_at }">
                {{ task.title }}
              </span>
              <Badge :variant="priorityVariant(task.priority)" class="text-xs capitalize hidden sm:inline-flex">
                {{ task.priority }}
              </Badge>
              <Select
                v-if="canEdit"
                :model-value="task.status"
                @update:model-value="inlineStatus(task, ($event as string))"
              >
                <SelectTrigger class="h-7 w-32 text-xs">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="backlog">
                    Backlog
                  </SelectItem>
                  <SelectItem value="todo">
                    To Do
                  </SelectItem>
                  <SelectItem value="in_progress">
                    In Progress
                  </SelectItem>
                  <SelectItem value="in_review">
                    In Review
                  </SelectItem>
                  <SelectItem value="done">
                    Done
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>
      </template>
    </div>
  </AppLayout>
</template>
