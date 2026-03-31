<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { index, show, update } from '@/routes/projects'
import ProjectForm from '../components/ProjectForm.vue'

interface UserOption { id: number, name: string, avatar: string | null }
interface ClientOption { id: number, name: string }

interface ProjectData {
  id: number
  name: string
  description: string | null
  client_id: string | null
  status: string
  type: string
  budget?: string
  budget_type: string | null
  colour?: string
  starts_at?: string
  ends_at?: string
  members: { user_id: number, role: string, hourly_rate?: string }[]
}

const props = defineProps<{ project: ProjectData, clients: ClientOption[], users: UserOption[] }>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Projects', href: index() },
  { title: props.project.name, href: show({ project: props.project.id }) },
  { title: 'Edit', href: '#' },
]

const form = useForm({
  name: props.project.name,
  description: props.project.description ?? '',
  client_id: props.project.client_id,
  status: props.project.status,
  type: props.project.type,
  budget: props.project.budget,
  budget_type: props.project.budget_type,
  colour: props.project.colour,
  starts_at: props.project.starts_at,
  ends_at: props.project.ends_at,
  members: props.project.members,
})

function submit() {
  form.patch(update({ project: props.project.id }).url)
}
</script>

<template>
  <Head :title="`Edit: ${project.name}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-2xl p-4">
      <h1 class="text-xl font-semibold mb-6">
        Edit Project
      </h1>
      <ProjectForm
        :form="form"
        :clients="clients"
        :users="users"
        submit-label="Save Changes"
        @submit="submit"
      />
    </div>
  </AppLayout>
</template>
