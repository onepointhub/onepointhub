<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import { index, store } from '@/routes/projects'
import ProjectForm from '../components/ProjectForm.vue'

interface UserOption { id: number, name: string, avatar: string | null }
interface ClientOption { id: number, name: string }

defineProps<{ clients: ClientOption[], users: UserOption[] }>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Projects', href: index() },
  { title: 'New Project', href: '#' },
]

const form = useForm({
  name: '',
  description: '',
  client_id: '',
  status: 'active',
  type: 'fixed',
  budget: '',
  budget_type: '',
  colour: '',
  starts_at: '',
  ends_at: '',
  members: [] as { user_id: number, role: string, hourly_rate: '' }[],
})

function submit() {
  form.post(store().url)
}
</script>

<template>
  <Head title="New Project" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-2xl p-4">
      <h1 class="text-xl font-semibold mb-6">
        New Project
      </h1>
      <ProjectForm
        :form="form"
        :clients="clients"
        :users="users"
        submit-label="Create Project"
        @submit.prevent="submit"
      >
        <template #cancel>
          <Link :href="index()">
            <Button type="button" variant="outline">
              Cancel
            </Button>
          </Link>
        </template>
      </ProjectForm>
    </div>
  </AppLayout>
</template>
