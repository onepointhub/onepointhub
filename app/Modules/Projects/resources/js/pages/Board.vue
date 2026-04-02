<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import Sortable from 'sortablejs'
import { onMounted, onUnmounted, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { index, show } from '@/routes/projects'
import { move } from '@/routes/projects/tasks'
import KanbanCard from '../components/KanbanCard.vue'

interface Label { id: number, name: string, colour: string }

interface TaskCard {
  id: number
  title: string
  priority: string
  position: number
  due_at: string | null
  sub_tasks_count: number
  assignee: { id: number, name: string, avatar: string | null } | null
  labels: Label[]
}

interface Props {
  project: { id: number, name: string }
  columns: Record<string, TaskCard[]>
  canEdit: boolean
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Projects', href: index() },
  { title: props.project.name, href: show({ project: props.project.id }) },
  { title: 'Board', href: '#' },
]

const STATUSES = ['backlog', 'todo', 'in_progress', 'in_review', 'done']
const STATUS_LABELS: Record<string, string> = {
  backlog: 'Backlog',
  todo: 'To Do',
  in_progress: 'In Progress',
  in_review: 'In Review',
  done: 'Done',
}

const sortableInstances: Sortable[] = []
const columnRefs = ref<Record<string, HTMLElement | null>>({})

onMounted(() => {
  if (!props.canEdit) {
    return
  }

  STATUSES.forEach((status) => {
    const el = columnRefs.value[status]
    if (!el) {
      return
    }

    const instance = Sortable.create(el, {
      group: 'tasks',
      animation: 150,
      ghostClass: 'opacity-30',
      onEnd(evt) {
        const taskId = Number(evt.item.dataset.taskId)
        const newStatus = evt.to.dataset.status as string
        const newPosition = evt.newIndex ?? 0

        axios.patch(move({ project: props.project.id, task: taskId }).url, {
          status: newStatus,
          position: newPosition,
        })
      },
    })

    sortableInstances.push(instance)
  })
})

onUnmounted(() => {
  sortableInstances.forEach(s => s.destroy())
})
</script>

<template>
  <Head :title="`Board — ${project.name}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex gap-4 overflow-x-auto p-4 min-h-[calc(100vh-8rem)]">
      <div
        v-for="status in STATUSES"
        :key="status"
        class="flex flex-col gap-2 min-w-65 max-w-70"
      >
        <!-- Column header -->
        <div class="flex items-center justify-between px-1">
          <h3 class="text-sm font-medium">
            {{ STATUS_LABELS[status] }}
          </h3>
          <span class="text-xs text-muted-foreground">{{ columns[status]?.length ?? 0 }}</span>
        </div>

        <!-- Cards container -->
        <div
          :ref="el => columnRefs[status] = el as HTMLElement"
          :data-status="status"
          class="flex flex-col gap-2 rounded-lg bg-muted/40 p-2 min-h-16"
        >
          <div
            v-for="task in columns[status]"
            :key="task.id"
            :data-task-id="task.id"
          >
            <KanbanCard :task="task" @click="() => {}" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
