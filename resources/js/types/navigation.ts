import type { InertiaLinkProps } from '@inertiajs/vue3'
import type { LucideIcon } from 'lucide-vue-next'

export interface BreadcrumbItem {
  title: string
  href: NonNullable<InertiaLinkProps['href']>
}

export interface NavItem {
  title: string
  href: NonNullable<InertiaLinkProps['href']>
  icon?: LucideIcon
  isActive?: boolean
}
