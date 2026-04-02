<script setup lang="ts">
import type { NavItem } from '@/types'
import { Link } from '@inertiajs/vue3'
import * as LucideIcons from 'lucide-vue-next'
import {
  SidebarGroup,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar'
import { useCurrentUrl } from '@/composables/useCurrentUrl'

defineProps<{
  items: NavItem[]
}>()

const { isCurrentUrl } = useCurrentUrl()

function getIcon(icon: any) {
  if (typeof icon === 'string') {
    // Capitalize first letter and handle lucide icon naming convention if necessary
    // Lucide icons in the package are usually PascalCase, e.g., 'users' -> 'Users'
    const iconName = icon.charAt(0).toUpperCase() + icon.slice(1)
    return (LucideIcons as any)[iconName] || (LucideIcons as any)[`${iconName}Icon`] || icon
  }
  return icon
}
</script>

<template>
  <SidebarGroup class="px-2 py-0">
    <SidebarMenu>
      <SidebarMenuItem v-for="item in items" :key="item.title">
        <SidebarMenuButton
          as-child
          :is-active="isCurrentUrl(item.href)"
          :tooltip="item.title"
        >
          <Link :href="item.href">
            <component :is="getIcon(item.icon)" v-if="item.icon" />
            <span>{{ item.title }}</span>
          </Link>
        </SidebarMenuButton>
      </SidebarMenuItem>
    </SidebarMenu>
  </SidebarGroup>
</template>
