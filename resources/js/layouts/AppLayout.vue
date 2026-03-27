<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { onMounted, onUnmounted, ref } from 'vue'
import AppContent from '@/components/AppContent.vue'
import AppShell from '@/components/AppShell.vue'
import AppSidebar from '@/components/AppSidebar.vue'
import AppSidebarHeader from '@/components/AppSidebarHeader.vue'
import CommandPalette from '@/components/CommandPalette.vue'

interface Props {
  breadcrumbs?: BreadcrumbItem[]
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
})

const commandPalette = ref<InstanceType<typeof CommandPalette> | null>(null)

function handleKeydown(event: KeyboardEvent) {
  if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
    event.preventDefault()
    if (commandPalette.value) {
      commandPalette.value.open = !commandPalette.value.open
    }
  }
}

onMounted(() => window.addEventListener('keydown', handleKeydown))
onUnmounted(() => window.removeEventListener('keydown', handleKeydown))
</script>

<template>
  <AppShell>
    <AppSidebar />
    <AppContent class="overflow-x-hidden">
      <AppSidebarHeader :breadcrumbs="breadcrumbs" />
      <slot />
    </AppContent>
  </AppShell>
  <CommandPalette ref="commandPalette" />
</template>
