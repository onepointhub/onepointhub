<script setup lang="ts">
import { Head } from '@inertiajs/vue3'

interface Props {
  title?: string
  clientName?: string
}

withDefaults(defineProps<Props>(), {
  title: 'Portal',
  clientName: '',
})
</script>

<template>
  <Head :title="title" />

  <div class="min-h-screen bg-background">
    <!-- Portal header -->
    <header class="border-b bg-card px-6 py-4">
      <div class="flex items-center justify-between">
        <span class="font-semibold">{{ clientName }}</span>
        <span class="text-sm text-muted-foreground">Client Portal</span>
      </div>
    </header>

    <!-- Content -->
    <main class="mx-auto max-w-4xl p-6">
      <slot />
    </main>
  </div>
</template>
