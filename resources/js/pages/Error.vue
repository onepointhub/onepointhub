<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { home } from '@/routes'

const props = defineProps<{ status: number }>()

const title = computed(() => {
  const titles: Record<number, string> = {
    401: 'Unauthorised',
    403: 'Forbidden',
    404: 'Page Not Found',
    419: 'Session Expired',
    429: 'Too Many Requests',
    500: 'Server Error',
    503: 'Service Unavailable',
  }
  return titles[props.status] ?? 'Error'
})

const description = computed(() => {
  const descriptions: Record<number, string> = {
    401: 'You are not authenticated. Please log in to continue.',
    403: 'You do not have permission to access this page.',
    404: 'The page you are looking for could not be found.',
    419: 'Your session has expired. Please refresh the page.',
    429: 'You have made too many requests. Please wait and try again.',
    500: 'Something went wrong on our end. Please try again later.',
    503: 'The service is temporarily unavailable. Please try again shortly.',
  }
  return descriptions[props.status] ?? 'An unexpected error occurred.'
})
</script>

<template>
  <Head :title="`${status} — ${title}`" />

  <div class="flex min-h-screen flex-col items-center justify-center gap-6 p-8 text-center">
    <p class="text-8xl font-bold text-muted-foreground/30">
      {{ status }}
    </p>
    <div class="space-y-2">
      <h1 class="text-2xl font-semibold tracking-tight">
        {{ title }}
      </h1>
      <p class="max-w-sm text-sm text-muted-foreground">
        {{ description }}
      </p>
    </div>
    <Link
      :href="home()"
      class="text-sm text-primary underline-offset-4 hover:underline"
    >
      Go back home
    </Link>
  </div>
</template>
