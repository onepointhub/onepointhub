<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import InvoiceForm from './partials/InvoiceForm.vue'

defineProps<{
  invoice: {
    id: number
    number: string
    client_id: number
    project_id: number | null
    issue_date: string
    due_date: string
    currency: string
    tax_rate: string
    discount_amount: string
    notes: string | null
    terms: string | null
    items: {
      description: string
      quantity: number
      unit_price: number
      tax_rate: number
      time_entry_ids: number[] | null
    }[]
  }
  clients: { id: number, name: string }[]
  projects: { id: number, name: string, client_id: number | null }[]
  billableExpenses: { id: number, description: string, amount: string, expense_date: string }[]
}>()
</script>

<template>
  <AppLayout>
    <Head :title="`Edit ${invoice.number}`" />
    <div class="mx-auto max-w-4xl px-4 py-8">
      <h1 class="mb-6 text-2xl font-bold">
        Edit {{ invoice.number }}
      </h1>
      <InvoiceForm
        :invoice="invoice"
        :clients="clients"
        :projects="projects"
        :billable-expenses="billableExpenses"
      />
    </div>
  </AppLayout>
</template>
