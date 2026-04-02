<script setup lang="ts">
import type { WorkspaceItem } from '@/types/global'
import { Link, router, usePage } from '@inertiajs/vue3'
import { ChevronsUpDown, Plus } from 'lucide-vue-next'
import { computed } from 'vue'
import WorkspaceSwitchController from '@/actions/App/Modules/Core/Http/Controllers/WorkspaceSwitchController'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  useSidebar,
} from '@/components/ui/sidebar'
import { getInitials } from '@/composables/useInitials'
import { create as createWorkspace } from '@/routes/onboarding/workspace'

const page = usePage()
const currentWorkspace = computed(() => page.props.workspace as WorkspaceItem | null)
const workspaces = computed(() => page.props.workspaces as WorkspaceItem[])
const { isMobile } = useSidebar()

function switchWorkspace(workspaceId: number): void {
  if (workspaceId === currentWorkspace.value?.id) {
    return
  }

  router.post(WorkspaceSwitchController().url, { workspace_id: workspaceId })
}
</script>

<template>
  <SidebarMenu>
    <SidebarMenuItem>
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <SidebarMenuButton
            size="lg"
            class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
          >
            <div
              class="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary
                text-sidebar-primary-foreground text-sm font-semibold"
            >
              {{ currentWorkspace ? getInitials(currentWorkspace.name) : '?' }}
            </div>
            <div class="grid flex-1 text-left text-sm leading-tight">
              <span class="truncate font-semibold">
                {{ currentWorkspace?.name ?? 'Select workspace' }}
              </span>
            </div>
            <ChevronsUpDown class="ml-auto size-4" />
          </SidebarMenuButton>
        </DropdownMenuTrigger>

        <DropdownMenuContent
          class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
          :side="isMobile ? 'bottom' : 'right'"
          align="start"
          :side-offset="4"
        >
          <DropdownMenuItem
            v-for="workspace in workspaces"
            :key="workspace.id"
            class="cursor-pointer"
            :class="{ 'font-medium': workspace.id === currentWorkspace?.id }"
            @click="switchWorkspace(workspace.id)"
          >
            <div
              class="mr-2 flex size-6 items-center justify-center rounded-sm bg-sidebar-primary
                text-sidebar-primary-foreground text-xs font-bold"
            >
              {{ getInitials(workspace.name) }}
            </div>
            {{ workspace.name }}
          </DropdownMenuItem>

          <DropdownMenuSeparator />

          <DropdownMenuItem as-child>
            <Link :href="createWorkspace()">
              <Plus class="mr-2 size-4" />
              Create workspace
            </Link>
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </SidebarMenuItem>
  </SidebarMenu>
</template>
