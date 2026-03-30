<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3'
import { Checkbox } from '@/components/ui/checkbox'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

interface CustomField {
  id: number
  label: string
  type: string
  options: string[] | null
}

interface Props {
  fields?: CustomField[]
  form: InertiaForm<any>
}

defineProps<Props>()
</script>

<template>
  <div v-if="fields">
    <div v-if="fields?.length > 0" class="flex flex-col gap-4">
      <h3 class="text-sm font-medium text-muted-foreground">
        Custom Fields
      </h3>

      <div
        v-for="field in fields"
        :key="field.id"
        class="flex flex-col gap-1.5"
      >
        <Label :for="`cf-${field.id}`">{{ field.label }}</Label>

        <Input
          v-if="field.type === 'text' || field.type === 'number'"
          :id="`cf-${field.id}`"
          v-model="form.custom_fields[field.id]"
          :type="field.type"
        />

        <Input
          v-else-if="field.type === 'date'"
          :id="`cf-${field.id}`"
          v-model="form.custom_fields[field.id]"
          type="date"
        />

        <Select
          v-else-if="field.type === 'select'"
          v-model="form.custom_fields[field.id]"
        >
          <SelectTrigger :id="`cf-${field.id}`">
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="opt in field.options" :key="opt" :value="opt">
              {{ opt }}
            </SelectItem>
          </SelectContent>
        </Select>

        <Checkbox
          v-else-if="field.type === 'checkbox'"
          :id="`cf-${field.id}`"
          v-model:checked="form.custom_fields[field.id]"
        />
      </div>
    </div>
  </div>
</template>
