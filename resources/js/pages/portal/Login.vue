<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import PortalLayout from '@/layouts/PortalLayout.vue'

defineProps<{ workspaceSlug: string, clientSlug: string }>()
</script>

<template>
  <PortalLayout title="Portal Access">
    <div class="mx-auto max-w-sm">
      <Card>
        <CardHeader>
          <CardTitle>Portal Access</CardTitle>
        </CardHeader>
        <CardContent>
          <p class="text-sm text-muted-foreground">
            Your access link has expired or is invalid. Please contact your account manager to receive a new portal access link.
          </p>
        </CardContent>
      </Card>
    </div>
  </PortalLayout>
</template>
