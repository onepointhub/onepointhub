<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import AppLayout from '@/layouts/AppLayout.vue'
import {
  bulkArchive as clientsBulkArchive,
  create as clientsCreate,
  exportMethod as clientsExport,
  index as clientsIndex,
  show as clientsShow,
} from '@/routes/clients'

interface Client {
  id: number
  name: string
  slug: string
  type: string
  status: string
  currency: string | null
  created_at: string
}

interface PaginatedClients {
  data: Client[]
  total: number
  current_page: number
  last_page: number
  next_page_url: string | null
  prev_page_url: string | null
}

interface Filters {
  search?: string
  status?: string
  type?: string
}

interface Props {
  clients: PaginatedClients
  filters: Filters
  statuses: string[]
  types: string[]
  canCreate: boolean
  canDelete: boolean
}

const props = defineProps<Props>()

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')
const type = ref(props.filters.type ?? '')

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Clients', href: clientsIndex() },
]

function applyFilters() {
  router.get(
    clientsIndex(),
    { search: search.value || undefined, status: status.value || undefined, type: type.value || undefined },
    { preserveState: true, replace: true },
  )
}

watch([status, type], applyFilters)

let searchTimeout: ReturnType<typeof setTimeout>
watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(applyFilters, 300)
})

function statusVariant(s: string): 'default' | 'secondary' | 'destructive' {
  return s === 'active' ? 'default' : 'secondary'
}

function exportUrl(): string {
  const params = new URLSearchParams()
  if (search.value) {
    params.set('search', search.value)
  }
  if (status.value) {
    params.set('status', status.value)
  }
  if (type.value) {
    params.set('type', type.value)
  }
  return `${clientsExport()}?${params.toString()}`
}

const selectedIds = ref<number[]>([])

const allSelected = computed(() => props.clients.data.length > 0 && selectedIds.value.length === props.clients.data.length)

function toggleAll() {
  selectedIds.value = allSelected.value ? [] : props.clients.data.map(c => c.id)
}

function toggleClient(id: number) {
  const idx = selectedIds.value.indexOf(id)
  if (idx >= 0) {
    selectedIds.value.splice(idx, 1)
  }
  else {
    selectedIds.value.push(id)
  }
}

function bulkArchive() {
  router.post(clientsBulkArchive(), { ids: selectedIds.value }, {
    onSuccess: () => { selectedIds.value = [] },
  })
}
</script>

<template>
  <Head title="Clients" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-4">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold">
          Clients
        </h1>
        <div class="flex gap-2">
          <a :href="exportUrl()" class="inline-flex">
            <Button variant="outline" size="sm">
              Export CSV
            </Button>
          </a>
          <Link v-if="canCreate" :href="clientsCreate()">
            <Button size="sm">
              Add Client
            </Button>
          </Link>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap gap-3">
        <Input
          v-model="search"
          placeholder="Search by name or email…"
          class="w-64"
        />

        <Select v-model="status">
          <SelectTrigger class="w-36">
            <SelectValue placeholder="All statuses" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">
              All statuses
            </SelectItem>
            <SelectItem v-for="s in statuses" :key="s" :value="s" class="capitalize">
              {{ s }}
            </SelectItem>
          </SelectContent>
        </Select>

        <Select v-model="type">
          <SelectTrigger class="w-36">
            <SelectValue placeholder="All types" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">
              All types
            </SelectItem>
            <SelectItem v-for="t in types" :key="t" :value="t" class="capitalize">
              {{ t }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Empty state -->
      <div
        v-if="clients.data.length === 0"
        class="rounded-lg border border-dashed py-16 text-center"
      >
        <p class="text-muted-foreground text-sm">
          No clients found.
        </p>
        <Link v-if="canCreate && !filters.search && !filters.status && !filters.type" :href="clientsCreate()" class="mt-3 inline-block">
          <Button size="sm">
            Add your first client
          </Button>
        </Link>
      </div>

      <!-- Table -->
      <div v-else class="rounded-lg border">
        <div v-if="selectedIds.length > 0" class="flex items-center gap-3 rounded-lg bg-muted px-4 py-2">
          <span class="text-sm text-muted-foreground">{{ selectedIds.length }} selected</span>
          <Button size="sm" variant="outline" @click="bulkArchive">
            Archive selected
          </Button>
          <Button size="sm" variant="ghost" @click="selectedIds = []">
            Clear
          </Button>
        </div>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead class="w-10">
                <Checkbox :checked="allSelected" @update:checked="toggleAll" />
              </TableHead>
              <TableHead>Name</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Currency</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Added</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="client in clients.data"
              :key="client.id"
              class="cursor-pointer"
              @click="router.visit(clientsShow({ client: client.id }))"
            >
              <TableCell @click.stop>
                <Checkbox
                  :checked="selectedIds.includes(client.id)"
                  @update:checked="toggleClient(client.id)"
                />
              </TableCell>
              <TableCell class="font-medium">
                {{ client.name }}
              </TableCell>
              <TableCell class="capitalize text-muted-foreground">
                {{ client.type }}
              </TableCell>
              <TableCell class="text-muted-foreground">
                {{ client.currency ?? '—' }}
              </TableCell>
              <TableCell>
                <Badge :variant="statusVariant(client.status)" class="capitalize">
                  {{ client.status }}
                </Badge>
              </TableCell>
              <TableCell class="text-muted-foreground">
                {{ client.created_at }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Pagination -->
      <div v-if="clients.last_page > 1" class="flex justify-end gap-2">
        <Link v-if="clients.prev_page_url" :href="clients.prev_page_url">
          <Button variant="outline" size="sm">
            Previous
          </Button>
        </Link>
        <span class="flex items-center text-sm text-muted-foreground">
          Page {{ clients.current_page }} of {{ clients.last_page }}
        </span>
        <Link v-if="clients.next_page_url" :href="clients.next_page_url">
          <Button variant="outline" size="sm">
            Next
          </Button>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
