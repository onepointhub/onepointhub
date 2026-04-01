<script setup lang="ts">
import type { NavItem } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { LayoutGrid, LucideSettings2 } from 'lucide-vue-next'
import NavMain from '@/components/NavMain.vue'
import {
  Sidebar,
  SidebarContent,
  SidebarHeader,
} from '@/components/ui/sidebar'
import WorkspaceSwitcher from '@/components/WorkspaceSwitcher.vue'
import { dashboard } from '@/routes'
import { index } from '@/routes/workspace/members'

const page = usePage()

const mainNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: dashboard(),
    icon: LayoutGrid,
  },
  ...(page.props.navigation || []),
  {
    title: 'Settings',
    href: index(),
    icon: LucideSettings2,
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
