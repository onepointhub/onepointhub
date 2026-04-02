<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'
import { toPng } from 'html-to-image'
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { useGantt } from '@/composables/useGantt'
import AppLayout from '@/layouts/AppLayout.vue'
import { index, show } from '@/routes/projects'

interface MilestoneItem {
  id: number
  name: string
  due_at: string
  completed_at: string | null
  is_overdue: boolean
}

interface TaskItem {
  id: number
  title: string
  status: string
  priority: string
  starts_at: string
  due_at: string
  completed_at: string | null
  milestone_id: number | null
}

interface Props {
  project: { id: number, name: string, starts_at: string | null, ends_at: string | null }
  milestones: MilestoneItem[]
  tasks: TaskItem[]
  canEdit: boolean
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Projects', href: index() },
  { title: props.project.name, href: show({ project: props.project.id }) },
  { title: 'Gantt', href: '#' },
]

const gantt = useGantt(props.project.starts_at, props.project.ends_at)
const chartRef = ref<HTMLElement | null>(null)

const ROW_HEIGHT = 36
const LABEL_WIDTH = 200
const HEADER_HEIGHT = 32

function taskBarColour(task: TaskItem): string {
  if (task.completed_at) {
    return '#10b981'
  }
  const map: Record<string, string> = {
    urgent: '#ef4444',
    high: '#f97316',
    medium: '#6366f1',
    low: '#94a3b8',
  }
  return map[task.priority] ?? '#6366f1'
}

async function exportPng() {
  if (!chartRef.value) {
    return
  }
  const dataUrl = await toPng(chartRef.value, { backgroundColor: '#ffffff' })
  const a = document.createElement('a')
  a.href = dataUrl
  a.download = `${props.project.name}-gantt.png`
  a.click()
}

const allRows = [
  ...props.milestones.map(m => ({ type: 'milestone' as const, item: m })),
  ...props.tasks.map(t => ({ type: 'task' as const, item: t })),
]

const chartHeight = HEADER_HEIGHT + allRows.length * ROW_HEIGHT + ROW_HEIGHT
</script>

<template>
  <Head :title="`Gantt — ${project.name}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4">
      <!-- Toolbar -->
      <div class="flex items-center justify-between gap-2">
        <Select v-model="gantt.zoom.value">
          <SelectTrigger class="w-32">
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="week">
              Week
            </SelectItem>
            <SelectItem value="month">
              Month
            </SelectItem>
            <SelectItem value="quarter">
              Quarter
            </SelectItem>
          </SelectContent>
        </Select>

        <Button variant="outline" size="sm" @click="exportPng">
          Export PNG
        </Button>
      </div>

      <!-- Chart -->
      <div ref="chartRef" class="overflow-x-auto rounded-lg border bg-white">
        <div class="flex" :style="{ minWidth: `${LABEL_WIDTH + gantt.totalWidth.value}px` }">
          <!-- Row labels -->
          <div class="shrink-0 border-r" :style="{ width: `${LABEL_WIDTH}px` }">
            <!-- Header spacer -->
            <div class="border-b bg-muted" :style="{ height: `${HEADER_HEIGHT}px` }" />
            <!-- Row labels -->
            <div
              v-for="(row) in allRows"
              :key="`label-${row.type}-${row.item.id}`"
              class="flex items-center border-b px-3 text-xs truncate"
              :style="{ height: `${ROW_HEIGHT}px` }"
            >
              <span
                v-if="row.type === 'milestone'"
                class="font-medium text-muted-foreground"
              >
                📍 {{ row.item.name }}
              </span>
              <span v-else>{{ (row.item as TaskItem).title }}</span>
            </div>
          </div>

          <!-- Timeline area -->
          <div class="relative flex-1 overflow-hidden">
            <!-- SVG timeline -->
            <svg
              :width="gantt.totalWidth.value"
              :height="chartHeight"
              class="block"
            >
              <!-- Header columns -->
              <g>
                <rect
                  v-for="col in gantt.headerColumns.value"
                  :key="col.label"
                  :x="col.x"
                  y="0"
                  :width="col.width"
                  :height="HEADER_HEIGHT"
                  fill="#f8fafc"
                  stroke="#e2e8f0"
                />
                <text
                  v-for="col in gantt.headerColumns.value"
                  :key="`t-${col.label}`"
                  :x="col.x + col.width / 2"
                  :y="HEADER_HEIGHT / 2 + 5"
                  text-anchor="middle"
                  class="text-xs"
                  font-size="11"
                  fill="#64748b"
                >
                  {{ col.label }}
                </text>
              </g>

              <!-- Row backgrounds -->
              <rect
                v-for="(row, i) in allRows"
                :key="`bg-${i}`"
                :x="0"
                :y="HEADER_HEIGHT + i * ROW_HEIGHT"
                :width="gantt.totalWidth.value"
                :height="ROW_HEIGHT"
                :fill="i % 2 === 0 ? '#ffffff' : '#f8fafc'"
                stroke="#e2e8f0"
                stroke-width="0"
              />
              <line
                v-for="(row, i) in allRows"
                :key="`line-${i}`"
                :x1="0"
                :y1="HEADER_HEIGHT + (i + 1) * ROW_HEIGHT"
                :x2="gantt.totalWidth.value"
                :y2="HEADER_HEIGHT + (i + 1) * ROW_HEIGHT"
                stroke="#e2e8f0"
                stroke-width="1"
              />

              <!-- Milestone diamonds -->
              <template v-for="(row, i) in allRows" :key="`bar-${row.type}-${row.item.id}`">
                <template v-if="row.type === 'milestone'">
                  <polygon
                    :points="`
                      ${gantt.dateToX((row.item as MilestoneItem).due_at)},${HEADER_HEIGHT + i * ROW_HEIGHT + ROW_HEIGHT / 2 - 8}
                      ${gantt.dateToX((row.item as MilestoneItem).due_at) + 8},${HEADER_HEIGHT + i * ROW_HEIGHT + ROW_HEIGHT / 2}
                      ${gantt.dateToX((row.item as MilestoneItem).due_at)},${HEADER_HEIGHT + i * ROW_HEIGHT + ROW_HEIGHT / 2 + 8}
                      ${gantt.dateToX((row.item as MilestoneItem).due_at) - 8},${HEADER_HEIGHT + i * ROW_HEIGHT + ROW_HEIGHT / 2}
                    `"
                    :fill="(row.item as MilestoneItem).is_overdue ? '#ef4444' : '#6366f1'"
                  />
                </template>

                <!-- Task bars -->
                <template v-else>
                  <rect
                    :x="gantt.dateToX((row.item as TaskItem).starts_at)"
                    :y="HEADER_HEIGHT + i * ROW_HEIGHT + 8"
                    :width="gantt.durationToWidth((row.item as TaskItem).starts_at, (row.item as TaskItem).due_at)"
                    :height="ROW_HEIGHT - 16"
                    :fill="taskBarColour(row.item as TaskItem)"
                    rx="3"
                    class="cursor-pointer"
                  />
                </template>
              </template>
            </svg>
          </div>
        </div>
      </div>

      <p v-if="allRows.length === 0" class="text-center text-sm text-muted-foreground py-12">
        No tasks or milestones with dates. Add due dates to tasks and milestones to see them here.
      </p>
    </div>
  </AppLayout>
</template>
