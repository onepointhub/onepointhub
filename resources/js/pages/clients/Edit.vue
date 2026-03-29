<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Head, Link, useForm } from '@inertiajs/vue3'
import ClientForm from '@/components/clients/ClientForm.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import AppLayout from '@/layouts/AppLayout.vue'
import { index as clientsIndex, show as clientsShow, update as clientsUpdate } from '@/routes/clients'

// import { clients as clientsRoute } from '@/routes'

interface ClientData {
  id: number
  name: string
  type: string
  status: string
  currency: string | null
  website: string | null
  vat_number: string | null
  notes: string | null
}

interface Props {
  client: ClientData
  statuses: string[]
  types: string[]
}

const props = defineProps<Props>()

const form = useForm({
  name: props.client.name,
  type: props.client.type,
  status: props.client.status,
  currency: props.client.currency ?? '',
  website: props.client.website ?? '',
  vat_number: props.client.vat_number ?? '',
  notes: props.client.notes ?? '',
})

function submit() {
  form.patch(clientsUpdate({ client: props.client.id }).url)
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Clients', href: clientsIndex() },
  { title: props.client.name, href: clientsShow({ client: props.client.id }) },
  { title: 'Edit', href: '#' },
]
</script>

<template>
  <Head :title="`Edit ${client.name}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-2xl p-4">
      <Card>
        <CardHeader>
          <CardTitle>Edit {{ client.name }}</CardTitle>
        </CardHeader>
        <CardContent>
          <ClientForm
            :form="form"
            :statuses="statuses"
            :types="types"
            submit-label="Save Changes"
            @submit.prevent="submit"
          >
            <template #cancel>
              <Link :href="clientsShow({ client: client.id })">
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
