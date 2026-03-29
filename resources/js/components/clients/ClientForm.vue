<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'

interface ClientFormData {
  name: string
  type: string
  status: string
  currency: string
  website: string
  vat_number: string
  notes: string
}

interface Props {
  form: InertiaForm<ClientFormData>
  statuses: string[]
  types: string[]
  submitLabel?: string
}

const props = withDefaults(defineProps<Props>(), {
  submitLabel: 'Save',
})
</script>

<template>
  <form class="flex flex-col gap-6" @submit.prevent>
    <!-- Name -->
    <div class="flex flex-col gap-1.5">
      <Label for="name">Name <span class="text-destructive">*</span></Label>
      <Input id="name" v-model="form.name" placeholder="Acme Corp" />
      <p v-if="form.errors.name" class="text-destructive text-sm">
        {{ form.errors.name }}
      </p>
    </div>

    <!-- Type + Status -->
    <div class="grid gap-4 sm:grid-cols-2">
      <div class="flex flex-col gap-1.5">
        <Label for="type">Type <span class="text-destructive">*</span></Label>
        <Select v-model="form.type">
          <SelectTrigger id="type">
            <SelectValue placeholder="Select type" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="t in types" :key="t" :value="t" class="capitalize">
              {{ t }}
            </SelectItem>
          </SelectContent>
        </Select>
        <p v-if="form.errors.type" class="text-destructive text-sm">
          {{ form.errors.type }}
        </p>
      </div>

      <div class="flex flex-col gap-1.5">
        <Label for="status">Status <span class="text-destructive">*</span></Label>
        <Select v-model="form.status">
          <SelectTrigger id="status">
            <SelectValue placeholder="Select status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="s in statuses" :key="s" :value="s" class="capitalize">
              {{ s }}
            </SelectItem>
          </SelectContent>
        </Select>
        <p v-if="form.errors.status" class="text-destructive text-sm">
          {{ form.errors.status }}
        </p>
      </div>
    </div>

    <!-- Currency + Website -->
    <div class="grid gap-4 sm:grid-cols-2">
      <div class="flex flex-col gap-1.5">
        <Label for="currency">Currency</Label>
        <Input id="currency" v-model="form.currency" placeholder="USD" maxlength="3" />
        <p v-if="form.errors.currency" class="text-destructive text-sm">
          {{ form.errors.currency }}
        </p>
      </div>

      <div class="flex flex-col gap-1.5">
        <Label for="website">Website</Label>
        <Input id="website" v-model="form.website" type="url" placeholder="https://example.com" />
        <p v-if="form.errors.website" class="text-destructive text-sm">
          {{ form.errors.website }}
        </p>
      </div>
    </div>

    <!-- VAT Number -->
    <div class="flex flex-col gap-1.5">
      <Label for="vat_number">VAT / Tax Number</Label>
      <Input id="vat_number" v-model="form.vat_number" placeholder="GB123456789" />
      <p v-if="form.errors.vat_number" class="text-destructive text-sm">
        {{ form.errors.vat_number }}
      </p>
    </div>

    <!-- Notes -->
    <div class="flex flex-col gap-1.5">
      <Label for="notes">Notes</Label>
      <Textarea id="notes" v-model="form.notes" rows="4" placeholder="Any relevant notes…" />
      <p v-if="form.errors.notes" class="text-destructive text-sm">
        {{ form.errors.notes }}
      </p>
    </div>

    <div class="flex justify-end gap-3">
      <slot name="cancel" />
      <Button type="submit" :disabled="form.processing">
        {{ props.submitLabel }}
      </Button>
    </div>
  </form>
</template>
