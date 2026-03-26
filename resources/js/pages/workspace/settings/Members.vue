<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Form, Head, router, usePage } from '@inertiajs/vue3'
import InvitationController from '@/actions/App/Http/Controllers/WorkspaceSettings/InvitationController'
import MemberController from '@/actions/App/Http/Controllers/WorkspaceSettings/MemberController'
import Heading from '@/components/Heading.vue'
import InputError from '@/components/InputError.vue'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Spinner } from '@/components/ui/spinner'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
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
const authUserId = usePage().props.auth.user.id

function updateRole(memberId: number, role: string) {
  router.patch(MemberController.update({ user: memberId }).url, { role }, {
    preserveScroll: true,
  })
}

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

          <Table class="mt-4">
            <TableHeader>
              <TableRow>
                <TableHead>Member</TableHead>
                <TableHead>Role</TableHead>
                <TableHead
                  v-if="canManageMembers"
                  class="w-0"
                />
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="member in members"
                :key="member.id"
              >
                <TableCell>
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
                </TableCell>
                <TableCell>
                  <Select
                    v-if="canManageMembers"
                    :model-value="member.role"
                    :disabled="member.id === authUserId"
                    @update:model-value="(role) => updateRole(member.id, role as string)"
                  >
                    <SelectTrigger class="w-32">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem
                        v-for="role in roles"
                        :key="role"
                        :value="role"
                      >
                        {{ role.charAt(0).toUpperCase() + role.slice(1) }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  <Badge
                    v-else
                    variant="secondary"
                    class="capitalize"
                  >
                    {{ member.role }}
                  </Badge>
                </TableCell>
                <TableCell v-if="canManageMembers">
                  <Button
                    variant="ghost"
                    size="sm"
                    @click="removeMember(member.id)"
                  >
                    Remove
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>
    </WorkspaceSettingsLayout>
  </AppLayout>
</template>
