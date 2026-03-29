<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Deferred, Head, Link } from '@inertiajs/vue3'
import ContactList from '@/components/clients/ContactList.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import AppLayout from '@/layouts/AppLayout.vue'
import { archive, edit, index } from '@/routes/clients'

interface Contact {
  id: number
  name: string
  email: string | null
  phone: string | null
  role: string | null
  is_primary: boolean
}

interface ActivityEntry {
  id: number
  event: string
  actor: { name: string, avatar: string | null } | null
  created_at: string
}

interface ClientData {
  id: number
  name: string
  slug: string
  type: string
  status: string
  currency: string | null
  website: string | null
  vat_number: string | null
  notes: string | null
  created_at: string
}

interface Props {
  client: ClientData
  contacts: Contact[]
  activity: ActivityEntry[]
  canEdit: boolean
  canDelete: boolean
  canManagePortal: boolean
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Clients', href: index() },
  { title: props.client.name, href: '#' },
]

function statusVariant(s: string): 'default' | 'secondary' {
  return s === 'active' ? 'default' : 'secondary'
}
</script>

<template>
  <Head :title="client.name" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-4">
      <!-- Header -->
      <div class="flex items-start justify-between gap-4">
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-xl font-semibold">
              {{ client.name }}
            </h1>
            <Badge :variant="statusVariant(client.status)" class="capitalize">
              {{ client.status }}
            </Badge>
            <Badge variant="outline" class="capitalize">
              {{ client.type }}
            </Badge>
          </div>
          <p v-if="client.website" class="mt-1 text-sm text-muted-foreground">
            <a :href="client.website" target="_blank" rel="noopener" class="hover:underline">
              {{ client.website }}
            </a>
          </p>
        </div>

        <div class="flex shrink-0 gap-2">
          <Link v-if="canEdit" :href="edit({ client: client.id })">
            <Button variant="outline" size="sm">
              Edit
            </Button>
          </Link>
          <Link v-if="canDelete" :href="archive({ client: client.id })" method="patch" as="button">
            <Button variant="outline" size="sm">
              Archive
            </Button>
          </Link>
        </div>
      </div>

      <!-- Tabs -->
      <Tabs default-value="overview">
        <TabsList>
          <TabsTrigger value="overview">
            Overview
          </TabsTrigger>
          <TabsTrigger value="contacts">
            Contacts ({{ contacts.length }})
          </TabsTrigger>
          <TabsTrigger value="projects">
            Projects
          </TabsTrigger>
          <TabsTrigger value="invoices">
            Invoices
          </TabsTrigger>
          <TabsTrigger value="activity">
            Activity
          </TabsTrigger>
        </TabsList>

        <!-- Overview -->
        <TabsContent value="overview">
          <div class="grid gap-4 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle class="text-sm font-medium text-muted-foreground">
                  Details
                </CardTitle>
              </CardHeader>
              <CardContent class="flex flex-col gap-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-muted-foreground">Currency</span>
                  <span>{{ client.currency ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-muted-foreground">VAT / Tax No.</span>
                  <span>{{ client.vat_number ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-muted-foreground">Client since</span>
                  <span>{{ client.created_at }}</span>
                </div>
              </CardContent>
            </Card>

            <Card v-if="client.notes">
              <CardHeader>
                <CardTitle class="text-sm font-medium text-muted-foreground">
                  Notes
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p class="whitespace-pre-wrap text-sm">
                  {{ client.notes }}
                </p>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <!-- Contacts -->
        <TabsContent value="contacts">
          <Card>
            <CardContent class="pt-6">
              <ContactList
                :client-id="client.id"
                :contacts="contacts"
                :can-edit="canEdit"
              />
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Projects stub -->
        <TabsContent value="projects">
          <Card>
            <CardContent class="py-16 text-center">
              <p class="text-muted-foreground text-sm">
                Projects will be available in v0.4.
              </p>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Invoices stub -->
        <TabsContent value="invoices">
          <Card>
            <CardContent class="py-16 text-center">
              <p class="text-muted-foreground text-sm">
                Invoices will be available in v0.5.
              </p>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Activity — deferred -->
        <TabsContent value="activity">
          <Card>
            <CardContent class="pt-6">
              <Deferred data="activity">
                <template #fallback>
                  <div class="space-y-3">
                    <div v-for="n in 5" :key="n" class="h-10 animate-pulse rounded bg-muted" />
                  </div>
                </template>

                <div
                  v-if="activity.length === 0"
                  class="py-8 text-center text-sm text-muted-foreground"
                >
                  No activity recorded for this client yet.
                </div>

                <ul v-else class="divide-y">
                  <li
                    v-for="entry in activity"
                    :key="entry.id"
                    class="flex items-center justify-between py-3 text-sm"
                  >
                    <div class="flex items-center gap-2">
                      <span class="text-muted-foreground">{{ entry.actor?.name ?? 'System' }}</span>
                      <Badge variant="outline" class="capitalize">
                        {{ entry.event }}
                      </Badge>
                    </div>
                    <span class="text-xs text-muted-foreground">{{ entry.created_at }}</span>
                  </li>
                </ul>
              </Deferred>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </AppLayout>
</template>
