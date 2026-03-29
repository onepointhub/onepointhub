<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import ModeToggle from '@/components/ModeToggle.vue'
import NotificationBell from '@/components/NotificationBell.vue'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { SidebarTrigger } from '@/components/ui/sidebar'
import UserMenuContent from '@/components/UserMenuContent.vue'
import { getInitials } from '@/composables/useInitials'

withDefaults(
  defineProps<{
    breadcrumbs?: BreadcrumbItem[]
  }>(),
  {
    breadcrumbs: () => [],
  },
)

const page = usePage()
const auth = computed(() => page.props.auth)
</script>

<template>
  <header
    class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
  >
    <div class="flex w-full items-center justify-between">
      <div class="flex items-center gap-2">
        <SidebarTrigger class="-ml-1" />
        <template v-if="breadcrumbs && breadcrumbs.length > 0">
          <Breadcrumbs :breadcrumbs="breadcrumbs" />
        </template>
      </div>
      <div class="flex items-center gap-1">
        <NotificationBell />

        <ModeToggle />

        <DropdownMenu>
          <DropdownMenuTrigger :as-child="true">
            <Button
              variant="ghost"
              size="icon"
              class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
            >
              <Avatar
                class="size-8 overflow-hidden rounded-full"
              >
                <AvatarImage
                  :src="auth.user.avatar || ''"
                  :alt="auth.user.name"
                />
                <AvatarFallback
                  class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                >
                  {{ getInitials(auth.user?.name) }}
                </AvatarFallback>
              </Avatar>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-56">
            <UserMenuContent :user="auth.user" />
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>
  </header>
</template>
