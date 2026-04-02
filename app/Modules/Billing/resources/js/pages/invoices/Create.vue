<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import InvoiceForm from './partials/InvoiceForm.vue'

defineProps<{
  clients: { id: number, name: string }[]
  projects: { id: number, name: string, client_id: number | null }[]
  nextNumber: string
  defaultCurrency: string
}>()
</script>

<template>
  <AppLayout>
    <Head title="New Invoice" />
    <div class="mx-auto max-w-4xl px-4 py-8">
      <h1 class="mb-6 text-2xl font-bold">
        New Invoice
      </h1>
      <InvoiceForm
        :clients="clients"
        :projects="projects"
        :next-number="nextNumber"
        :default-currency="defaultCurrency"
      />
    </div>
  </AppLayout>
</template>
