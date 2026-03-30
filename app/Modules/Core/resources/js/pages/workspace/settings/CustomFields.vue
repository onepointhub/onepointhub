<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import AppLayout from '@/layouts/AppLayout.vue'
import { destroy, store } from '@/routes/workspace/custom-fields'

interface Field {
  id: number
  label: string
  type: string
  options: string[] | null
}

interface Props {
  fields: Field[]
  types: string[]
}

defineProps<Props>()

const form = useForm({
  label: '',
  type: 'text',
  options: [] as string[],
})

const newOption = ref('')

function addOption() {
  if (newOption.value.trim()) {
    form.options.push(newOption.value.trim())
    newOption.value = ''
  }
}

function removeOption(i: number) {
  form.options.splice(i, 1)
}

function submit() {
  form.post(store().url)
}

function deleteField(field: Field) {
  router.delete(destroy({ customField: field.id }))
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Workspace Settings', href: '#' },
  { title: 'Custom Fields', href: '#' },
]
</script>

<template>
  <Head title="Custom Fields" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-2xl flex flex-col gap-6 p-4">
      <!-- Existing fields -->
      <Card>
        <CardHeader>
          <CardTitle>Client Custom Fields</CardTitle>
        </CardHeader>
        <CardContent>
          <div
            v-if="fields.length === 0"
            class="py-8 text-center text-sm text-muted-foreground"
          >
            No custom fields defined yet.
          </div>
          <ul v-else class="divide-y">
            <li
              v-for="field in fields"
              :key="field.id"
              class="flex items-center justify-between py-3"
            >
              <div>
                <span class="font-medium">{{ field.label }}</span>
                <Badge variant="outline" class="ml-2 capitalize">
                  {{ field.type }}
                </Badge>
                <span v-if="field.options" class="ml-2 text-xs text-muted-foreground">
                  {{ field.options.join(', ') }}
                </span>
              </div>
              <Button size="sm" variant="ghost" class="text-destructive" @click="deleteField(field)">
                Delete
              </Button>
            </li>
          </ul>
        </CardContent>
      </Card>

      <!-- Add new field -->
      <Card>
        <CardHeader>
          <CardTitle>Add Field</CardTitle>
        </CardHeader>
        <CardContent>
          <form class="flex flex-col gap-4" @submit.prevent="submit">
            <div class="flex flex-col gap-1.5">
              <Label for="label">Label</Label>
              <Input id="label" v-model="form.label" placeholder="e.g. Industry" />
              <p v-if="form.errors.label" class="text-destructive text-sm">
                {{ form.errors.label }}
              </p>
            </div>
            <div class="flex flex-col gap-1.5">
              <Label for="type">Type</Label>
              <Select v-model="form.type">
                <SelectTrigger id="type">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="t in types" :key="t" :value="t" class="capitalize">
                    {{ t }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div v-if="form.type === 'select'" class="flex flex-col gap-1.5">
              <Label>Options</Label>
              <div class="flex gap-2">
                <Input v-model="newOption" placeholder="Option label" @keydown.enter.prevent="addOption" />
                <Button type="button" variant="outline" @click="addOption">
                  Add
                </Button>
              </div>
              <div class="flex flex-wrap gap-2 mt-1">
                <Badge
                  v-for="(opt, i) in form.options"
                  :key="i"
                  variant="secondary"
                  class="cursor-pointer"
                  @click="removeOption(i)"
                >
                  {{ opt }} ×
                </Badge>
              </div>
            </div>
            <div class="flex justify-end">
              <Button type="submit" :disabled="form.processing">
                Add Field
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
