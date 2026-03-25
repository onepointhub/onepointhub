<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Form, Head, router } from '@inertiajs/vue3'
import InvitationController from '@/actions/App/Http/Controllers/WorkspaceSettings/InvitationController'
import MemberController from '@/actions/App/Http/Controllers/WorkspaceSettings/MemberController'
import Heading from '@/components/Heading.vue'
import InputError from '@/components/InputError.vue'
import { Avatar } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Spinner } from '@/components/ui/spinner'
import { useInitials } from '@/composables/useInitials'
import AppLayout from '@/layouts/AppLayout.vue'
import WorkspaceSettingsLayout from '@/layouts/workspace/settings/Layout.vue'
import { index } from '@/routes/workspace/members'

interface Member {
  id: number
  name: string
  email: string
  avatar: string | null
  role: string
}

interface Props {
  members: Member[]
  roles: string[]
  canManageMembers: boolean
}

defineProps<Props>()

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Members',
    href: index(),
  },
]

const { getInitials } = useInitials()

function removeMember(memberId: number) {
  router.delete(MemberController.destroy({ user: memberId }).url, {
    preserveScroll: true,
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Workspace Members" />

    <WorkspaceSettingsLayout>
      <div class="flex flex-col space-y-8">
        <!-- Invite form (owners/admins only) -->
        <div v-if="canManageMembers">
          <Heading
            variant="small"
            title="Invite a member"
            description="Send a 48-hour invitation link to add someone to this workspace"
          />

          <Form
            v-slot="{ errors, processing }"
            v-bind="InvitationController.store.form()"
            class="mt-4 flex flex-col gap-4"
          >
            <div class="grid gap-2">
              <Label for="email">Email address</Label>
              <Input
                id="email"
                name="email"
                type="email"
                required
                placeholder="colleague@example.com"
                autocomplete="off"
              />
              <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
              <Label for="role">Role</Label>
              <Select
                name="role"
                default-value="member"
              >
                <SelectTrigger>
                  <SelectValue placeholder="Select role" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="role in roles.filter((r) => r !== 'owner')"
                    :key="role"
                    :value="role"
                  >
                    {{ role.charAt(0).toUpperCase() + role.slice(1) }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <InputError :message="errors.role" />
            </div>

            <div>
              <Button
                type="submit"
                :disabled="processing"
              >
                <Spinner v-if="processing" />
                Send invitation
              </Button>
            </div>
          </Form>
        </div>

        <!-- Members list -->
        <div>
          <Heading
            variant="small"
            title="Members"
            description="Everyone with access to this workspace"
          />

          <div class="mt-4 divide-y divide-border rounded-lg border">
            <div
              v-for="member in members"
              :key="member.id"
              class="flex items-center justify-between p-4"
            >
              <div class="flex items-center gap-3">
                <Avatar class="h-9 w-9">
                  <AvatarImage
                    :src="member.avatar || ''"
                    :alt="member.name"
                  />
                  <AvatarFallback>
                    {{ getInitials(member.name) }}
                  </AvatarFallback>
                </Avatar>
                <div>
                  <p class="text-sm font-medium">
                    {{ member.name }}
                  </p>
                  <p class="text-xs text-muted-foreground">
                    {{ member.email }}
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-2">
                <span class="text-xs capitalize text-muted-foreground">
                  {{ member.role }}
                </span>
                <Button
                  v-if="canManageMembers"
                  variant="ghost"
                  size="sm"
                  @click="removeMember(member.id)"
                >
                  Remove
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </WorkspaceSettingsLayout>
  </AppLayout>
</template>
