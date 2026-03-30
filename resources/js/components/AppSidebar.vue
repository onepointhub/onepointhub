<script setup lang="ts">
import type { NavItem } from '@/types'
import { LayoutGrid } from 'lucide-vue-next'
import NavMain from '@/components/NavMain.vue'
import {
  Sidebar,
  SidebarContent,
  SidebarHeader,
} from '@/components/ui/sidebar'
import WorkspaceSwitcher from '@/components/WorkspaceSwitcher.vue'
import { dashboard } from '@/routes'

const mainNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: dashboard(),
    icon: LayoutGrid,
  },
]
</script>

<template>
  <Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
      <WorkspaceSwitcher />
    </SidebarHeader>

    <SidebarContent>
      <NavMain :items="mainNavItems" />
    </SidebarContent>
  </Sidebar>
  <slot />
</template>
