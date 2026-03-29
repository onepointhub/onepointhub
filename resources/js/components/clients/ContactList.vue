<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { getInitials } from '@/composables/useInitials'
import { destroy, store, update } from '@/routes/clients/contacts'

interface Contact {
  id: number
  name: string
  email: string | null
  phone: string | null
  role: string | null
  is_primary: boolean
}

interface Props {
  clientId: number
  contacts: Contact[]
  canEdit: boolean
}

const props = defineProps<Props>()

const showAddDialog = ref(false)

const addForm = useForm({
  name: '',
  email: '',
  phone: '',
  role: '',
  is_primary: false,
})

function addContact() {
  addForm.post(store({ client: props.clientId }).url, {
    onSuccess: () => {
      showAddDialog.value = false
      addForm.reset()
    },
  })
}

function setPrimary(contact: Contact) {
  router.patch(update({ client: props.clientId, contact: contact.id }), {
    name: contact.name,
    is_primary: true,
  })
}

function deleteContact(contact: Contact) {
  router.delete(destroy({ client: props.clientId, contact: contact.id }))
}
</script>

<template>
  <div class="flex flex-col gap-4">
    <div class="flex items-center justify-between">
      <h3 class="font-medium">
        Contacts
      </h3>
      <Dialog v-if="canEdit" v-model:open="showAddDialog">
        <DialogTrigger as-child>
          <Button size="sm" variant="outline">
            Add Contact
          </Button>
        </DialogTrigger>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Add Contact</DialogTitle>
          </DialogHeader>
          <form class="flex flex-col gap-4" @submit.prevent="addContact">
            <div class="flex flex-col gap-1.5">
              <Label for="contact-name">Name *</Label>
              <Input id="contact-name" v-model="addForm.name" />
              <p v-if="addForm.errors.name" class="text-destructive text-sm">
                {{ addForm.errors.name }}
              </p>
            </div>
            <div class="flex flex-col gap-1.5">
              <Label for="contact-email">Email</Label>
              <Input id="contact-email" v-model="addForm.email" type="email" />
            </div>
            <div class="flex flex-col gap-1.5">
              <Label for="contact-phone">Phone</Label>
              <Input id="contact-phone" v-model="addForm.phone" type="tel" />
            </div>
            <div class="flex flex-col gap-1.5">
              <Label for="contact-role">Role / Title</Label>
              <Input id="contact-role" v-model="addForm.role" placeholder="CEO" />
            </div>
            <div class="flex justify-end gap-2">
              <Button type="button" variant="outline" @click="showAddDialog = false">
                Cancel
              </Button>
              <Button type="submit" :disabled="addForm.processing">
                Add
              </Button>
            </div>
          </form>
        </DialogContent>
      </Dialog>
    </div>

    <div
      v-if="contacts.length === 0"
      class="rounded-lg border border-dashed py-8 text-center text-sm text-muted-foreground"
    >
      No contacts yet.
    </div>

    <ul v-else class="divide-y rounded-lg border">
      <li
        v-for="contact in contacts"
        :key="contact.id"
        class="flex items-center justify-between gap-4 p-4"
      >
        <div class="flex items-center gap-3">
          <Avatar class="h-9 w-9">
            <AvatarFallback>{{ getInitials(contact.name) }}</AvatarFallback>
          </Avatar>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-medium">{{ contact.name }}</span>
              <Badge v-if="contact.is_primary" variant="secondary" class="text-xs">
                Primary
              </Badge>
            </div>
            <div class="text-sm text-muted-foreground">
              <a v-if="contact.email" :href="`mailto:${contact.email}`" class="hover:underline">
                {{ contact.email }}
              </a>
              <span v-if="contact.phone" class="ml-2">{{ contact.phone }}</span>
            </div>
            <p v-if="contact.role" class="text-xs text-muted-foreground">
              {{ contact.role }}
            </p>
          </div>
        </div>

        <div v-if="canEdit" class="flex shrink-0 gap-2">
          <Button
            v-if="!contact.is_primary"
            size="sm"
            variant="ghost"
            @click="setPrimary(contact)"
          >
            Set primary
          </Button>
          <Button size="sm" variant="ghost" class="text-destructive" @click="deleteContact(contact)">
            Remove
          </Button>
        </div>
      </li>
    </ul>
  </div>
</template>
