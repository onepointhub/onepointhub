<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'

interface UserOption {
  id: number
  name: string
  avatar: string | null
}

interface ClientOption {
  id: number
  name: string
}

interface MemberEntry {
  user_id: number
  role: string
  hourly_rate?: string
}

interface FormData {
  name: string
  description: string
  client_id: string | null
  status: string
  type: string
  budget?: string
  budget_type: string | null
  colour?: string
  starts_at?: string
  ends_at?: string
  members: MemberEntry[]
}

interface Props {
  form: InertiaForm<FormData>
  clients: ClientOption[]
  users: UserOption[]
  submitLabel?: string
}

const props = withDefaults(defineProps<Props>(), { submitLabel: 'Save' })

function addMember() {
  props.form.members.push({ user_id: 0, role: 'member', hourly_rate: '' })
}

function removeMember(index: number) {
  props.form.members.splice(index, 1)
}
</script>

<template>
  <form class="flex flex-col gap-6" @submit.prevent>
    <!-- Name -->
    <div class="flex flex-col gap-1.5">
      <Label for="project-name">Name *</Label>
      <Input id="project-name" v-model="form.name" />
      <p v-if="form.errors.name" class="text-destructive text-sm">
        {{ form.errors.name }}
      </p>
    </div>

    <!-- Description -->
    <div class="flex flex-col gap-1.5">
      <Label for="project-description">Description</Label>
      <Textarea id="project-description" v-model="form.description" rows="3" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
      <!-- Status -->
      <div class="flex flex-col gap-1.5">
        <Label>Status</Label>
        <Select v-model="form.status">
          <SelectTrigger>
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
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

      <!-- Type -->
      <div class="flex flex-col gap-1.5">
        <Label>Type</Label>
        <Select v-model="form.type">
          <SelectTrigger>
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="fixed">
              Fixed
            </SelectItem>
            <SelectItem value="hourly">
              Hourly
            </SelectItem>
            <SelectItem value="retainer">
              Retainer
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Client -->
      <div class="flex flex-col gap-1.5">
        <Label>Client</Label>
        <Select :model-value="form.client_id ?? ''" @update:model-value="form.client_id = ($event as string) || null">
          <SelectTrigger>
            <SelectValue placeholder="No client" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">
              No client
            </SelectItem>
            <SelectItem v-for="c in clients" :key="c.id" :value="String(c.id)">
              {{ c.name }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Colour -->
      <div class="flex flex-col gap-1.5">
        <Label for="project-colour">Colour</Label>
        <div class="flex gap-2 items-center">
          <input id="project-colour" v-model="form.colour" type="color" class="h-9 w-12 rounded border p-0.5 cursor-pointer">
          <Input v-model="form.colour" placeholder="#3b82f6" class="font-mono" />
        </div>
      </div>

      <!-- Budget -->
      <div class="flex flex-col gap-1.5">
        <Label for="project-budget">Budget</Label>
        <Input id="project-budget" v-model="form.budget" type="number" min="0" step="0.01" />
        <p v-if="form.errors.budget" class="text-destructive text-sm">
          {{ form.errors.budget }}
        </p>
      </div>

      <!-- Budget Type -->
      <div class="flex flex-col gap-1.5">
        <Label>Budget Type</Label>
        <Select :model-value="form.budget_type ?? ''" @update:model-value="form.budget_type = ($event as string) || null">
          <SelectTrigger>
            <SelectValue placeholder="Select type" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="money">
              Money
            </SelectItem>
            <SelectItem value="hours">
              Hours
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Start Date -->
      <div class="flex flex-col gap-1.5">
        <Label for="project-starts-at">Start Date</Label>
        <Input id="project-starts-at" v-model="form.starts_at" type="date" />
      </div>

      <!-- End Date -->
      <div class="flex flex-col gap-1.5">
        <Label for="project-ends-at">End Date</Label>
        <Input id="project-ends-at" v-model="form.ends_at" type="date" />
        <p v-if="form.errors.ends_at" class="text-destructive text-sm">
          {{ form.errors.ends_at }}
        </p>
      </div>
    </div>

    <!-- Team Members -->
    <div class="flex flex-col gap-3">
      <div class="flex items-center justify-between">
        <Label>Team Members</Label>
        <Button type="button" variant="outline" size="sm" @click="addMember">
          Add Member
        </Button>
      </div>
      <div v-for="(member, index) in form.members" :key="index" class="flex gap-2 items-center">
        <Select v-model="member.user_id" class="flex-1">
          <SelectTrigger>
            <SelectValue placeholder="Select user" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem
              v-for="u in users"
              :key="u.id"
              :value="u.id"
            >
              {{ u.name }}
            </SelectItem>
          </SelectContent>
        </Select>
        <Select v-model="member.role" class="w-28">
          <SelectTrigger>
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="lead">
              Lead
            </SelectItem>
            <SelectItem value="member">
              Member
            </SelectItem>
          </SelectContent>
        </Select>
        <Input v-model="member.hourly_rate" type="number" placeholder="Rate" class="w-24" />
        <Button type="button" variant="ghost" size="sm" class="text-destructive" @click="removeMember(index)">
          Remove
        </Button>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-2">
      <slot name="cancel" />
      <Button type="submit" :disabled="form.processing">
        {{ submitLabel }}
      </Button>
    </div>
  </form>
</template>
