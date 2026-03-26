<script setup lang="ts">
import type { NavItem } from '@/types'
import { LayoutGrid } from 'lucide-vue-next'
import ModeToggle from '@/components/ModeToggle.vue'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
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

    <ModeToggle />

    <SidebarFooter>
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>
