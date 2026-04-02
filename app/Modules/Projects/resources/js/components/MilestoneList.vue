<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { complete, destroy, store } from '@/routes/projects/milestones'

interface MilestoneItem {
  id: number
  name: string
  due_at: string | null
  completed_at: string | null
  tasks_count: number
  completed_tasks_count: number
  is_overdue: boolean
}

interface Props {
  projectId: number
  milestones: MilestoneItem[]
  canEdit: boolean
}

const props = defineProps<Props>()

const showAddDialog = ref(false)

const addForm = useForm({
  name: '',
  due_at: '',
})

function addMilestone() {
  addForm.post(store({ project: props.projectId }).url, {
    onSuccess: () => {
      showAddDialog.value = false
      addForm.reset()
    },
  })
}

function completeMilestone(milestone: MilestoneItem) {
  router.patch(complete({ project: props.projectId, milestone: milestone.id }))
}

function deleteMilestone(milestone: MilestoneItem) {
  router.delete(destroy({ project: props.projectId, milestone: milestone.id }))
}

function progress(milestone: MilestoneItem): number {
  if (milestone.tasks_count === 0) {
    return 0
  }
  return Math.round((milestone.completed_tasks_count / milestone.tasks_count) * 100)
}
</script>

<template>
  <div class="flex flex-col gap-4">
    <div class="flex items-center justify-between">
      <h3 class="font-medium">
        Milestones
      </h3>
      <Dialog v-if="canEdit" v-model:open="showAddDialog">
        <DialogTrigger as-child>
          <Button size="sm" variant="outline">
            Add Milestone
          </Button>
        </DialogTrigger>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Add Milestone</DialogTitle>
          </DialogHeader>
          <form class="flex flex-col gap-4" @submit.prevent="addMilestone">
            <div class="flex flex-col gap-1.5">
              <Label for="milestone-name">Name *</Label>
              <Input id="milestone-name" v-model="addForm.name" />
              <p v-if="addForm.errors.name" class="text-destructive text-sm">
                {{ addForm.errors.name }}
              </p>
            </div>
            <div class="flex flex-col gap-1.5">
              <Label for="milestone-due">Due Date</Label>
              <Input id="milestone-due" v-model="addForm.due_at" type="date" />
            </div>
            <div class="flex justify-end gap-2">
              <Button type="button" variant="outline" @click="showAddDialog = false">
                Cancel
              </Button>
              <Button type="submit" :disabled="addForm.processing">
                Add
              </Button>
            </div>
          </form>
        </DialogContent>
      </Dialog>
    </div>

    <div
      v-if="milestones.length === 0"
      class="rounded-lg border border-dashed py-8 text-center text-sm text-muted-foreground"
    >
      No milestones yet.
    </div>

    <ul v-else class="flex flex-col gap-3">
      <li
        v-for="milestone in milestones"
        :key="milestone.id"
        class="rounded-lg border p-4"
        :class="{ 'opacity-60': milestone.completed_at }"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="flex-1">
            <div class="flex items-center gap-2">
              <span class="font-medium text-sm">{{ milestone.name }}</span>
              <Badge v-if="milestone.completed_at" variant="outline" class="text-xs">
                Done
              </Badge>
              <Badge v-else-if="milestone.is_overdue" variant="destructive" class="text-xs">
                Overdue
              </Badge>
            </div>
            <p v-if="milestone.due_at" class="text-xs text-muted-foreground mt-0.5">
              Due {{ milestone.due_at }}
            </p>
            <!-- Progress -->
            <div v-if="milestone.tasks_count > 0" class="mt-2">
              <div class="flex justify-between text-xs text-muted-foreground mb-1">
                <span>{{ milestone.completed_tasks_count }}/{{ milestone.tasks_count }} tasks</span>
                <span>{{ progress(milestone) }}%</span>
              </div>
              <div class="h-1.5 rounded-full bg-muted overflow-hidden">
                <div
                  class="h-full bg-primary rounded-full transition-all"
                  :style="{ width: `${progress(milestone)}%` }"
                />
              </div>
            </div>
          </div>

          <div v-if="canEdit" class="flex shrink-0 gap-1">
            <Button
              v-if="!milestone.completed_at"
              size="sm"
              variant="ghost"
              class="text-xs"
              @click="completeMilestone(milestone)"
            >
              Complete
            </Button>
            <Button
              size="sm"
              variant="ghost"
              class="text-xs text-destructive"
              @click="deleteMilestone(milestone)"
            >
              Delete
            </Button>
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>
