<script setup lang="ts">
import type { NavItem } from '@/types'
import { Link } from '@inertiajs/vue3'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { useCurrentUrl } from '@/composables/useCurrentUrl'
import { toUrl } from '@/lib/utils'
import { index as membersIndex } from '@/routes/workspace/members'

const sidebarNavItems: NavItem[] = [
  {
    title: 'Members',
    href: membersIndex(),
  },
]

const { isCurrentOrParentUrl } = useCurrentUrl()
</script>

<template>
  <div class="px-4 py-6">
    <Heading
      title="Workspace Settings"
      description="Manage workspace members and configuration"
    />

    <div class="flex flex-col lg:flex-row lg:space-x-12">
      <aside class="w-full max-w-xl lg:w-48">
        <nav class="flex flex-col space-y-1 space-x-0" aria-label="Workspace Settings">
          <Button
            v-for="item in sidebarNavItems"
            :key="toUrl(item.href)"
            variant="ghost"
            class="w-full justify-start"
            :class="[{ 'bg-muted': isCurrentOrParentUrl(item.href) }]"
            as-child
          >
            <Link :href="item.href">
              <component
                :is="item.icon"
                class="h-4 w-4"
              />
              {{ item.title }}
            </Link>
          </Button>
        </nav>
      </aside>

      <Separator class="my-6 lg:hidden" />

      <div class="flex-1 md:max-w-2xl">
        <section class="max-w-xl space-y-12">
          <slot />
        </section>
      </div>
    </div>
  </div>
</template>
