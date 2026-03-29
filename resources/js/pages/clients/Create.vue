<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { onMounted, watch } from 'vue'
import ClientForm from '@/components/clients/ClientForm.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import AppLayout from '@/layouts/AppLayout.vue'
import { create as clientsCreate, index as clientsIndex, store as clientsStore } from '@/routes/clients'

interface Props {
  statuses: string[]
  types: string[]
}

const props = defineProps<Props>()

const DRAFT_KEY = 'client-create-draft'

const form = useForm({
  name: '',
  type: props.types[0] ?? 'company',
  status: props.statuses[0] ?? 'active',
  currency: '',
  website: '',
  vat_number: '',
  notes: '',
})

onMounted(() => {
  const draft = localStorage.getItem(DRAFT_KEY)
  if (draft) {
    Object.assign(form, JSON.parse(draft))
  }
})

watch(
  () => ({ ...form.data() }),
  (data) => { localStorage.setItem(DRAFT_KEY, JSON.stringify(data)) },
  { deep: true },
)

function submit() {
  form.post(clientsStore().url, {
    onSuccess: () => { localStorage.removeItem(DRAFT_KEY) },
  })
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Clients', href: clientsIndex() },
  { title: 'New Client', href: clientsCreate() },
]
</script>

<template>
  <Head title="New Client" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-2xl p-4">
      <Card>
        <CardHeader>
          <CardTitle>New Client</CardTitle>
        </CardHeader>
        <CardContent>
          <ClientForm
            :form="form"
            :statuses="statuses"
            :types="types"
            submit-label="Create Client"
            @submit.prevent="submit"
          >
            <template #cancel>
              <Link :href="clientsIndex()">
                <Button type="button" variant="outline">
                  Cancel
                </Button>
              </Link>
            </template>
          </ClientForm>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
