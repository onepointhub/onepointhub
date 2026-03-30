<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import PortalLayout from '@/layouts/PortalLayout.vue'
import { logout } from '@/routes/portal/auth'

interface Props {
  client: { name: string, slug: string }
  contact: { name: string, email: string | null }
  workspaceSlug: string
  clientSlug: string
}

defineProps<Props>()
</script>

<template>
  <PortalLayout :title="`${client.name} Portal`" :client-name="client.name">
    <div class="flex flex-col gap-6">
      <div>
        <h1 class="text-xl font-semibold">
          Welcome, {{ contact.name }}
        </h1>
        <p class="mt-1 text-sm text-muted-foreground">
          You are viewing the portal for {{ client.name }}.
        </p>
      </div>

      <div class="grid gap-4 md:grid-cols-3">
        <Card>
          <CardHeader>
            <CardTitle class="text-sm">
              Projects
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-sm text-muted-foreground">
              Coming in v0.4
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle class="text-sm">
              Invoices
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-sm text-muted-foreground">
              Coming in v0.5
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle class="text-sm">
              Files
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-sm text-muted-foreground">
              Coming in v0.7
            </p>
          </CardContent>
        </Card>
      </div>

      <div class="flex justify-end">
        <Link
          :href="logout({ workspace_slug: workspaceSlug, client_slug: clientSlug })"
          method="post"
          as="button"
        >
          <Button variant="ghost" size="sm">
            Sign out
          </Button>
        </Link>
      </div>
    </div>
  </PortalLayout>
</template>
