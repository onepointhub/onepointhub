<script setup lang="ts">
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { getInitials } from '@/composables/useInitials'

interface Label {
  id: number
  name: string
  colour: string
}

interface TaskCard {
  id: number
  title: string
  priority: string
  due_at: string | null
  sub_tasks_count: number
  assignee: { id: number, name: string, avatar: string | null } | null
  labels: Label[]
}

defineProps<{ task: TaskCard }>()

const emit = defineEmits<{ click: [id: number] }>()

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
  <div
    class="rounded-lg border bg-card p-3 shadow-sm cursor-grab active:cursor-grabbing hover:shadow-md transition-shadow"
    @click="emit('click', task.id)"
  >
    <p class="text-sm font-medium leading-snug mb-2">
      {{ task.title }}
    </p>

    <!-- Labels -->
    <div v-if="task.labels.length" class="flex flex-wrap gap-1 mb-2">
      <span
        v-for="label in task.labels"
        :key="label.id"
        class="inline-block rounded-full px-2 py-0.5 text-xs text-white"
        :style="{ backgroundColor: label.colour }"
      >
        {{ label.name }}
      </span>
    </div>

    <div class="flex items-center justify-between gap-2">
      <Badge :variant="priorityVariant(task.priority)" class="capitalize text-xs">
        {{ task.priority }}
      </Badge>

      <div class="flex items-center gap-2 text-xs text-muted-foreground">
        <span v-if="task.sub_tasks_count > 0">☑ {{ task.sub_tasks_count }}</span>
        <span v-if="task.due_at">📅 {{ task.due_at }}</span>
        <Avatar v-if="task.assignee" class="h-5 w-5">
          <AvatarFallback class="text-[9px]">
            {{ getInitials(task.assignee.name) }}
          </AvatarFallback>
        </Avatar>
      </div>
    </div>
  </div>
</template>
