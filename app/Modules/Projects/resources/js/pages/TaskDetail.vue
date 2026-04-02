<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Deferred, Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Checkbox } from '@/components/ui/checkbox'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import AppLayout from '@/layouts/AppLayout.vue'
import { index, show, tasks } from '@/routes/projects'
import { update } from '@/routes/projects/tasks'
// import { projects as projectsRoute } from '@/routes'

interface SubTask {
  id: number
  title: string
  status: string
  completed_at: string | null
  assignee: { id: number, name: string } | null
}

interface ActivityEntry {
  id: number
  event: string
  actor: { name: string } | null
  created_at: string
}

interface Comment {
  id: number
  body: string
  user: { id: number, name: string, avatar: string | null }
  created_at: string
}

interface TaskData {
  id: number
  title: string
  description: string | null
  status: string
  priority: string
  due_at: string | null
  completed_at: string | null
  estimated_hours: number | null
  milestone: { id: number, name: string } | null
  assignee: { id: number, name: string, avatar: string | null } | null
  labels: { id: number, name: string, colour: string }[]
  created_by: { name: string } | null
  created_at: string
}

interface Props {
  project: { id: number, name: string }
  task: TaskData
  subTasks: SubTask[]
  comments: Comment[]
  activity: ActivityEntry[]
  canEdit: boolean
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Projects', href: index() },
  { title: props.project.name, href: show({ project: props.project.id }) },
  { title: 'Tasks', href: tasks({ project: props.project.id }) },
  { title: props.task.title, href: '#' },
]

const editingTitle = ref(false)
const titleValue = ref(props.task.title)

function saveTitle() {
  editingTitle.value = false
  if (titleValue.value !== props.task.title) {
    router.patch(
      update({ project: props.project.id, task: props.task.id }),
      { title: titleValue.value, status: props.task.status },
    )
  }
}

function toggleSubTask(subTask: SubTask) {
  const newStatus = subTask.completed_at ? 'todo' : 'done'
  router.patch(
    update({ project: props.project.id, task: subTask.id }),
    { title: subTask.title, status: newStatus },
    { preserveState: true },
  )
}

function updateStatus(status: any) {
  router.patch(
    update({ project: props.project.id, task: props.task.id }),
    { title: props.task.title, status },
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
  <Head :title="task.title" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-3xl p-4 flex flex-col gap-6">
      <!-- Title -->
      <div>
        <div v-if="!editingTitle" class="flex items-start gap-2">
          <h1 class="text-xl font-semibold flex-1 cursor-pointer hover:underline" @click="editingTitle = true">
            {{ task.title }}
          </h1>
        </div>
        <div v-else class="flex items-start gap-2">
          <input
            v-model="titleValue"
            class="flex-1 rounded border px-2 py-1 text-xl font-semibold focus:outline-none"
            @blur="saveTitle"
            @keyup.enter="saveTitle"
            @keyup.escape="editingTitle = false"
          >
        </div>
      </div>

      <!-- Meta row -->
      <div class="flex flex-wrap gap-3 items-center">
        <Select v-if="canEdit" :model-value="task.status" @update:model-value="updateStatus">
          <SelectTrigger class="w-36 h-8">
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
        <Badge :variant="priorityVariant(task.priority)" class="capitalize">
          {{ task.priority }}
        </Badge>
        <span v-if="task.milestone" class="text-sm text-muted-foreground">📍 {{ task.milestone.name }}</span>
        <span v-if="task.due_at" class="text-sm text-muted-foreground">📅 {{ task.due_at }}</span>
        <span v-if="task.assignee" class="text-sm text-muted-foreground">👤 {{ task.assignee.name }}</span>
      </div>

      <!-- Labels -->
      <div v-if="task.labels.length" class="flex flex-wrap gap-1">
        <span
          v-for="label in task.labels"
          :key="label.id"
          class="inline-block rounded-full px-2 py-0.5 text-xs text-white"
          :style="{ backgroundColor: label.colour }"
        >
          {{ label.name }}
        </span>
      </div>

      <!-- Description -->
      <div>
        <h2 class="text-sm font-medium mb-2">
          Description
        </h2>
        <p v-if="task.description" class="whitespace-pre-wrap text-sm text-muted-foreground">
          {{ task.description }}
        </p>
        <p v-else class="text-sm text-muted-foreground italic">
          No description.
        </p>
      </div>

      <!-- Sub-tasks -->
      <div v-if="subTasks.length > 0">
        <h2 class="text-sm font-medium mb-2">
          Sub-tasks ({{ subTasks.filter(s => s.completed_at).length }}/{{ subTasks.length }})
        </h2>
        <ul class="flex flex-col gap-1">
          <li
            v-for="sub in subTasks"
            :key="sub.id"
            class="flex items-center gap-2 text-sm"
          >
            <Checkbox
              :checked="!!sub.completed_at"
              :disabled="!canEdit"
              @update:checked="toggleSubTask(sub)"
            />
            <span :class="{ 'line-through text-muted-foreground': sub.completed_at }">{{ sub.title }}</span>
          </li>
        </ul>
      </div>

      <!-- Comments — deferred -->
      <div>
        <h2 class="text-sm font-medium mb-3">
          Comments
        </h2>
        <Deferred data="comments">
          <template #fallback>
            <div class="space-y-2">
              <div v-for="n in 3" :key="n" class="h-12 animate-pulse rounded bg-muted" />
            </div>
          </template>
          <div v-if="comments.length === 0" class="text-sm text-muted-foreground italic">
            No comments yet.
          </div>
          <ul v-else class="flex flex-col gap-3">
            <li v-for="comment in comments" :key="comment.id" class="flex gap-3 text-sm">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <span class="font-medium">{{ comment.user.name }}</span>
                  <span class="text-xs text-muted-foreground">{{ comment.created_at }}</span>
                </div>
                <p class="whitespace-pre-wrap text-muted-foreground">
                  {{ comment.body }}
                </p>
              </div>
            </li>
          </ul>
        </Deferred>
      </div>

      <!-- Activity — deferred -->
      <div>
        <h2 class="text-sm font-medium mb-3">
          Activity
        </h2>
        <Deferred data="activity">
          <template #fallback>
            <div class="space-y-2">
              <div v-for="n in 3" :key="n" class="h-8 animate-pulse rounded bg-muted" />
            </div>
          </template>
          <div v-if="activity.length === 0" class="text-sm text-muted-foreground italic">
            No activity yet.
          </div>
          <ul v-else class="divide-y">
            <li
              v-for="entry in activity"
              :key="entry.id"
              class="flex items-center justify-between py-2 text-sm"
            >
              <div class="flex items-center gap-2">
                <span class="text-muted-foreground">{{ entry.actor?.name ?? 'System' }}</span>
                <Badge variant="outline" class="capitalize text-xs">
                  {{ entry.event }}
                </Badge>
              </div>
              <span class="text-xs text-muted-foreground">{{ entry.created_at }}</span>
            </li>
          </ul>
        </Deferred>
      </div>
    </div>
  </AppLayout>
</template>
