import type { Auth } from '@/types/auth'

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

declare module '@inertiajs/core' {
  export interface InertiaConfig {
    sharedPageProps: {
      name: string
      auth: Auth
      sidebarOpen: boolean
      notifications: {
        unread_count: number
        recent: AppNotification[]
      }
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
