import type { Auth } from '@/types/auth'
import type { NavItem } from '@/types/navigation'

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
  interface ImportMetaEnv {
    readonly VITE_APP_NAME: string
    [key: string]: string | boolean | undefined
  }

  interface ImportMeta {
    readonly env: ImportMetaEnv
    readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>
  }
}

export interface AppNotification {
  id: string
  type: string | null
  message: string
  read_at: string | null
  created_at: string
}

export interface WorkspaceItem {
  id: number
  name: string
  slug: string
}

declare module '@inertiajs/core' {
  export interface InertiaConfig {
    sharedPageProps: {
      name: string
      navigation: NavItem[]
      auth: Auth
      sidebarOpen: boolean
      notifications: {
        unread_count: number
        recent: AppNotification[]
      }
      workspace: WorkspaceItem | null
      workspaces: WorkspaceItem[]
      [key: string]: unknown
    }
  }
}

declare module 'vue' {
  interface ComponentCustomProperties {
    $inertia: typeof Router
    $page: Page
    $headManager: ReturnType<typeof createHeadManager>
  }
}
