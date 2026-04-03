<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import { create, index, show } from '@/routes/billing/invoices'
import { markPaid, voidMethod } from '@/routes/billing/invoices/bulk'

interface Invoice {
  id: number
  number: string
  status: string
  issue_date: string
  due_date: string
  total: string
  currency: string
  client: { id: number, name: string }
}

const props = defineProps<{
  invoices: {
    data: Invoice[]
    links: { url: string | null, label: string, active: boolean }[]
    meta: { current_page: number, last_page: number, total: number }
  }
  summary: { outstanding: number, overdue: number, paid: number }
  clients: { id: number, name: string }[]
  filters: { status?: string, client_id?: number, date_from?: string, date_to?: string }
}>()

const statusColors: Record<string, string> = {
  draft: 'secondary',
  sent: 'default',
  viewed: 'default',
  partial: 'outline',
  paid: 'success',
  overdue: 'destructive',
  void: 'secondary',
}

const selected = ref<number[]>([])
const allSelected = computed(() => selected.value.length === props.invoices.data.length && props.invoices.data.length > 0)

function toggleAll() {
  selected.value = allSelected.value ? [] : props.invoices.data.map(i => i.id)
}

function toggleOne(id: number) {
  const idx = selected.value.indexOf(id)
  if (idx === -1) {
    selected.value.push(id)
  }
  else {
    selected.value.splice(idx, 1)
  }
}

function isOverdue(invoice: Invoice): boolean {
  return invoice.status === 'overdue' || (
    ['sent', 'viewed', 'partial'].includes(invoice.status)
    && new Date(invoice.due_date) < new Date()
  )
}

function applyFilters(patch: Record<string, string | undefined>) {
  router.get(index().url, { ...props.filters, ...patch }, { preserveState: true, replace: true })
}

function bulkVoid() {
  if (!selected.value.length) {
    return
  }
  router.post(voidMethod(), { ids: selected.value }, {
    onSuccess: () => {
      selected.value = []
    },
  })
}

function bulkMarkPaid() {
  if (!selected.value.length) {
    return
  }
  router.post(markPaid(), { ids: selected.value }, {
    onSuccess: () => {
      selected.value = []
    },
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Invoices" />
    <div class="mx-auto max-w-7xl px-4 py-8">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">
          Invoices
        </h1>
        <Button :href="create().url">
          New Invoice
        </Button>
      </div>

      <!-- Summary Row -->
      <div class="mb-6 grid grid-cols-3 gap-4">
        <div class="rounded-lg border bg-card p-4">
          <p class="text-sm text-muted-foreground">
            Outstanding
          </p>
          <p class="text-2xl font-bold">
            ${{ summary.outstanding.toFixed(2) }}
          </p>
        </div>
        <div class="rounded-lg border bg-destructive/10 p-4">
          <p class="text-sm text-muted-foreground">
            Overdue
          </p>
          <p class="text-2xl font-bold text-destructive">
            ${{ summary.overdue.toFixed(2) }}
          </p>
        </div>
        <div class="rounded-lg border bg-card p-4">
          <p class="text-sm text-muted-foreground">
            Paid
          </p>
          <p class="text-2xl font-bold">
            ${{ summary.paid.toFixed(2) }}
          </p>
        </div>
      </div>

      <!-- Filters -->
      <div class="mb-4 flex flex-wrap gap-2">
        <select
          :value="filters.status"
          class="rounded-md border px-3 py-1.5 text-sm"
          @change="applyFilters({ status: ($event.target as HTMLSelectElement).value || undefined })"
        >
          <option value="">
            All Statuses
          </option>
          <option value="draft">
            Draft
          </option>
          <option value="sent">
            Sent
          </option>
          <option value="viewed">
            Viewed
          </option>
          <option value="partial">
            Partial
          </option>
          <option value="paid">
            Paid
          </option>
          <option value="overdue">
            Overdue
          </option>
          <option value="void">
            Void
          </option>
        </select>
        <select
          :value="filters.client_id"
          class="rounded-md border px-3 py-1.5 text-sm"
          @change="applyFilters({ client_id: ($event.target as HTMLSelectElement).value || undefined })"
        >
          <option value="">
            All Clients
          </option>
          <option v-for="c in clients" :key="c.id" :value="c.id">
            {{ c.name }}
          </option>
        </select>
        <input
          type="date"
          :value="filters.date_from"
          class="rounded-md border px-3 py-1.5 text-sm"
          placeholder="From"
          @change="applyFilters({ date_from: ($event.target as HTMLInputElement).value || undefined })"
        >
        <input
          type="date"
          :value="filters.date_to"
          class="rounded-md border px-3 py-1.5 text-sm"
          placeholder="To"
          @change="applyFilters({ date_to: ($event.target as HTMLInputElement).value || undefined })"
        >
      </div>

      <!-- Bulk Actions -->
      <div v-if="selected.length > 0" class="mb-3 flex items-center gap-2 rounded-lg bg-muted px-4 py-2 text-sm">
        <span>{{ selected.length }} selected</span>
        <Button variant="outline" size="sm" @click="bulkMarkPaid">
          Mark Paid
        </Button>
        <Button variant="outline" size="sm" @click="bulkVoid">
          Void
        </Button>
      </div>

      <!-- Table -->
      <div class="overflow-hidden rounded-lg border">
        <table class="w-full text-sm">
          <thead class="bg-muted/50">
            <tr>
              <th class="w-10 px-4 py-3">
                <input type="checkbox" :checked="allSelected" @change="toggleAll">
              </th>
              <th class="px-4 py-3 text-left">
                Number
              </th>
              <th class="px-4 py-3 text-left">
                Client
              </th>
              <th class="px-4 py-3 text-left">
                Issue Date
              </th>
              <th class="px-4 py-3 text-left">
                Due Date
              </th>
              <th class="px-4 py-3 text-left">
                Status
              </th>
              <th class="px-4 py-3 text-right">
                Total
              </th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr
              v-for="invoice in invoices.data"
              :key="invoice.id"
              :class="{ 'bg-red-50 dark:bg-red-950/20': isOverdue(invoice) }"
              class="hover:bg-muted/30 transition-colors"
            >
              <td class="px-4 py-3">
                <input type="checkbox" :checked="selected.includes(invoice.id)" @change="toggleOne(invoice.id)">
              </td>
              <td class="px-4 py-3 font-medium">
                <a :href="show(invoice.id).url" class="hover:underline">{{ invoice.number }}</a>
              </td>
              <td class="px-4 py-3">
                {{ invoice.client.name }}
              </td>
              <td class="px-4 py-3">
                {{ invoice.issue_date }}
              </td>
              <td class="px-4 py-3" :class="{ 'font-medium text-destructive': isOverdue(invoice) }">
                {{ invoice.due_date }}
              </td>
              <td class="px-4 py-3">
                <Badge :variant="statusColors[invoice.status] as any">
                  {{ invoice.status }}
                </Badge>
              </td>
              <td class="px-4 py-3 text-right font-medium">
                {{ invoice.currency }} {{ invoice.total }}
              </td>
            </tr>
            <tr v-if="invoices.data.length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                No invoices found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="invoices.meta.last_page > 1" class="mt-4 flex justify-center gap-1">
        <template v-for="link in invoices.links" :key="link.label">
          <Button
            v-if="link.url"
            :variant="link.active ? 'default' : 'outline'"
            size="sm"
            @click="router.get(link.url)"
          >
            {{ link.label }}
          </Button>
        </template>
      </div>
    </div>
  </AppLayout>
</template>
