<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Head, router, usePage } from '@inertiajs/vue3'
import NotificationPreferenceController from '@/actions/App/Http/Controllers/Settings/NotificationPreferenceController'
import Heading from '@/components/Heading.vue'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import { Separator } from '@/components/ui/separator'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'

interface Props {
  types: string[]
  preferences: Record<string, { email_enabled: boolean }>
}

const props = defineProps<Props>()

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Notifications',
    href: usePage().url,
  },
]

function isEmailEnabled(type: string): boolean {
  return props.preferences[type]?.email_enabled ?? true
}

function toggle(type: string, enabled: boolean) {
  router.patch(
    NotificationPreferenceController.update({ type }).url,
    { email_enabled: enabled },
    { preserveScroll: true },
  )
}

function label(type: string): string {
  return type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Notification Preferences" />

    <SettingsLayout>
      <div class="space-y-6">
        <Heading
          title="Notifications"
          description="Choose which email notifications you want to receive."
        />

        <Separator />

        <div class="space-y-4">
          <p class="text-sm font-medium">
            Email notifications
          </p>

          <div
            v-for="type in types"
            :key="type"
            class="flex items-center gap-3"
          >
            <Checkbox
              :id="type"
              :checked="isEmailEnabled(type)"
              @update:checked="(val: boolean) => toggle(type, val as boolean)"
            />
            <Label
              :for="type"
              class="cursor-pointer font-normal"
            >
              {{ label(type) }}
            </Label>
          </div>
        </div>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>
