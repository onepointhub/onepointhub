<script setup lang="ts">
import type { AppNotification } from '@/types/global'
import { router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import NotificationController from '@/actions/App/Modules/Core/Http/Controllers/NotificationController'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { timeAgo } from '@/composables/useTimeAgo'

const page = usePage()
const unreadCount = computed(() => page.props.notifications.unread_count)
const recent = computed(() => page.props.notifications.recent)

function markRead(notification: AppNotification) {
  router.patch(NotificationController.markRead({ id: notification.id }).url, {}, {
    preserveScroll: true,
  })
}

function markAllRead() {
  router.post(NotificationController.markAllRead().url, {}, {
    preserveScroll: true,
  })
}
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button
        variant="ghost"
        size="icon"
        class="relative"
        aria-label="Notifications"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="1.5"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0
              01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"
          />
        </svg>
        <span
          v-if="unreadCount > 0"
          class="absolute -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-destructive text-[10px]
            font-bold text-white"
        >
          {{ unreadCount > 9 ? '9+' : unreadCount }}
        </span>
      </Button>
    </DropdownMenuTrigger>

    <DropdownMenuContent
      align="end"
      class="w-80"
    >
      <DropdownMenuLabel class="flex items-center justify-between">
        <span>Notifications</span>
        <button
          v-if="unreadCount > 0"
          class="text-xs font-normal text-muted-foreground hover:text-foreground"
          @click="markAllRead"
        >
          Mark all read
        </button>
      </DropdownMenuLabel>

      <DropdownMenuSeparator />

      <template v-if="recent.length > 0">
        <DropdownMenuItem
          v-for="notification in recent"
          :key="notification.id"
          class="flex flex-col items-start gap-1 py-3"
          :class="{ 'opacity-60': notification.read_at !== null }"
          @click="notification.read_at === null && markRead(notification)"
        >
          <span class="text-sm leading-snug">{{ notification.message }}</span>
          <span class="text-xs text-muted-foreground"> {{ timeAgo(notification.created_at) }}</span>
        </DropdownMenuItem>
      </template>

      <div
        v-else
        class="py-6 text-center text-sm text-muted-foreground"
      >
        No notifications
      </div>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
