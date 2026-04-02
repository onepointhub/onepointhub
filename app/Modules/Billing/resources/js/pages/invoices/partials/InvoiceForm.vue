<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { index, store, update } from '@/routes/billing/invoices'
import LineItemRow from './LineItemRow.vue'

interface LineItem {
  description: string
  quantity: number
  unit_price: number
  tax_rate: number
  time_entry_ids: number[] | null
}

interface InvoiceProp {
  id?: number
  client_id?: number
  project_id?: number | null
  issue_date?: string
  due_date?: string
  currency?: string
  tax_rate?: string
  discount_amount?: string
  notes?: string | null
  terms?: string | null
  items?: LineItem[]
}

const props = defineProps<{
  invoice?: InvoiceProp
  clients: { id: number, name: string }[]
  projects: { id: number, name: string, client_id: number | null }[]
  nextNumber?: string
  defaultCurrency?: string
  billableExpenses?: { id: number, description: string, amount: string, expense_date: string }[]
}>()

const form = useForm({
  client_id: props.invoice?.client_id ?? null,
  project_id: props.invoice?.project_id ?? null,
  issue_date: props.invoice?.issue_date ?? new Date().toISOString().slice(0, 10),
  due_date: props.invoice?.due_date ?? '',
  currency: props.invoice?.currency ?? props.defaultCurrency ?? 'USD',
  tax_rate: Number.parseFloat(props.invoice?.tax_rate ?? '0'),
  discount_amount: Number.parseFloat(props.invoice?.discount_amount ?? '0'),
  notes: props.invoice?.notes ?? '',
  terms: props.invoice?.terms ?? '',
  items: (props.invoice?.items ?? []).map(i => ({
    description: i.description,
    quantity: Number.parseFloat(i.quantity as unknown as string),
    unit_price: Number.parseFloat(i.unit_price as unknown as string),
    tax_rate: Number.parseFloat(i.tax_rate as unknown as string),
    time_entry_ids: i.time_entry_ids,
  })) as LineItem[],
})

const subtotal = computed(() =>
  form.items.reduce((sum, i) => sum + i.quantity * i.unit_price, 0),
)
const taxAmount = computed(() => subtotal.value * (form.tax_rate / 100))
const total = computed(() => Math.max(0, subtotal.value + taxAmount.value - form.discount_amount))

function addItem() {
  form.items.push({ description: '', quantity: 1, unit_price: 0, tax_rate: form.tax_rate, time_entry_ids: null })
}

function removeItem(index: number) {
  form.items.splice(index, 1)
}

function updateItem(index: number, field: keyof LineItem, value: string | number | null) {
  ;(form.items[index] as unknown as Record<string, unknown>)[field] = value
}

function importExpense(expense: { id: number, description: string, amount: string }) {
  form.items.push({
    description: expense.description,
    quantity: 1,
    unit_price: Number.parseFloat(expense.amount),
    tax_rate: 0,
    time_entry_ids: null,
  })
}

function submit() {
  if (props.invoice?.id) {
    form.put(update(props.invoice.id).url)
  }
  else {
    form.post(store().url)
  }
}
</script>

<template>
  <form class="space-y-6" @submit.prevent="submit">
    <!-- Client & Project -->
    <div class="grid grid-cols-2 gap-4">
      <div>
        <Label for="client_id">Client <span class="text-red-500">*</span></Label>
        <select id="client_id" v-model="form.client_id" class="mt-1 w-full rounded-md border px-3 py-2 text-sm">
          <option :value="null">
            Select client
          </option>
          <option v-for="c in clients" :key="c.id" :value="c.id">
            {{ c.name }}
          </option>
        </select>
        <p v-if="form.errors.client_id" class="mt-1 text-xs text-red-500">
          {{ form.errors.client_id }}
        </p>
      </div>
      <div>
        <Label for="project_id">Project (optional)</Label>
        <select id="project_id" v-model="form.project_id" class="mt-1 w-full rounded-md border px-3 py-2 text-sm">
          <option :value="null">
            None
          </option>
          <option v-for="p in projects" :key="p.id" :value="p.id">
            {{ p.name }}
          </option>
        </select>
      </div>
    </div>

    <!-- Dates & Currency -->
    <div class="grid grid-cols-3 gap-4">
      <div>
        <Label for="issue_date">Issue Date <span class="text-red-500">*</span></Label>
        <Input id="issue_date" v-model="form.issue_date" type="date" class="mt-1" />
        <p v-if="form.errors.issue_date" class="mt-1 text-xs text-red-500">
          {{ form.errors.issue_date }}
        </p>
      </div>
      <div>
        <Label for="due_date">Due Date <span class="text-red-500">*</span></Label>
        <Input id="due_date" v-model="form.due_date" type="date" class="mt-1" />
        <p v-if="form.errors.due_date" class="mt-1 text-xs text-red-500">
          {{ form.errors.due_date }}
        </p>
      </div>
      <div>
        <Label for="currency">Currency</Label>
        <Input id="currency" v-model="form.currency" maxlength="3" class="mt-1 uppercase" />
      </div>
    </div>

    <!-- Line Items -->
    <div>
      <div class="mb-2 flex items-center justify-between">
        <h3 class="font-semibold">
          Line Items
        </h3>
        <Button type="button" variant="outline" size="sm" @click="addItem">
          + Add Item
        </Button>
      </div>
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b text-left text-xs text-muted-foreground">
            <th class="px-2 pb-2">
              Description
            </th>
            <th class="w-24 px-2 pb-2">
              Qty
            </th>
            <th class="w-32 px-2 pb-2">
              Unit Price
            </th>
            <th class="w-24 px-2 pb-2">
              Tax %
            </th>
            <th class="w-28 px-2 pb-2 text-right">
              Amount
            </th>
            <th class="w-10" />
          </tr>
        </thead>
        <tbody>
          <LineItemRow
            v-for="(item, i) in form.items"
            :key="i"
            :item="item"
            :index="i"
            @update="updateItem"
            @remove="removeItem"
          />
        </tbody>
      </table>
      <p v-if="form.errors.items" class="mt-1 text-xs text-red-500">
        {{ form.errors.items }}
      </p>
    </div>

    <!-- Import Billable Expenses -->
    <div v-if="billableExpenses && billableExpenses.length > 0">
      <h3 class="mb-2 font-semibold text-sm">
        Import Billable Expenses
      </h3>
      <div class="space-y-1">
        <div
          v-for="expense in billableExpenses"
          :key="expense.id"
          class="flex items-center justify-between rounded border px-3 py-2 text-sm"
        >
          <span>{{ expense.description }} — {{ expense.amount }}</span>
          <Button type="button" variant="ghost" size="sm" @click="importExpense(expense)">
            Import
          </Button>
        </div>
      </div>
    </div>

    <!-- Totals -->
    <div class="ml-auto w-64 space-y-1 text-sm">
      <div class="flex justify-between">
        <span class="text-muted-foreground">Subtotal</span>
        <span>{{ subtotal.toFixed(2) }}</span>
      </div>
      <div class="flex items-center justify-between gap-2">
        <span class="text-muted-foreground">Tax (%)</span>
        <Input v-model.number="form.tax_rate" type="number" min="0" max="100" step="0.01" class="w-20 text-right" />
      </div>
      <div class="flex items-center justify-between gap-2">
        <span class="text-muted-foreground">Discount</span>
        <Input v-model.number="form.discount_amount" type="number" min="0" step="0.01" class="w-20 text-right" />
      </div>
      <div class="flex justify-between border-t pt-1 font-semibold">
        <span>Total</span>
        <span>{{ form.currency }} {{ total.toFixed(2) }}</span>
      </div>
    </div>

    <!-- Notes & Terms -->
    <div class="grid grid-cols-2 gap-4">
      <div>
        <Label for="notes">Notes</Label>
        <textarea id="notes" v-model="form.notes" rows="3" class="mt-1 w-full rounded-md border px-3 py-2 text-sm" />
      </div>
      <div>
        <Label for="terms">Payment Terms</Label>
        <textarea id="terms" v-model="form.terms" rows="3" class="mt-1 w-full rounded-md border px-3 py-2 text-sm" />
      </div>
    </div>

    <div class="flex justify-end gap-3">
      <Button type="button" variant="outline" @click="$inertia.visit(index().url)">
        Cancel
      </Button>
      <Button type="submit" :disabled="form.processing">
        {{ invoice?.id ? 'Update Invoice' : 'Create Invoice' }}
      </Button>
    </div>
  </form>
</template>
