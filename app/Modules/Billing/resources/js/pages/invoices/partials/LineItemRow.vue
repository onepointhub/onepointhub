<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

interface LineItem {
  description: string
  quantity: number
  unit_price: number
  tax_rate: number
  time_entry_ids: number[] | null
}

const props = defineProps<{
  item: LineItem
  index: number
}>()

const emit = defineEmits<{
  update: [index: number, field: keyof LineItem, value: string | number | null]
  remove: [index: number]
}>()

const lineTotal = computed(() => {
  return (props.item.quantity * props.item.unit_price).toFixed(2)
})
</script>

<template>
  <tr>
    <td class="px-2 py-1">
      <Input
        :value="item.description"
        placeholder="Description"
        @input="emit('update', index, 'description', ($event.target as HTMLInputElement).value)"
      />
    </td>
    <td class="w-24 px-2 py-1">
      <Input
        type="number"
        :value="item.quantity"
        min="0.01"
        step="0.01"
        @input="emit('update', index, 'quantity', parseFloat(($event.target as HTMLInputElement).value) || 0)"
      />
    </td>
    <td class="w-32 px-2 py-1">
      <Input
        type="number"
        :value="item.unit_price"
        min="0"
        step="0.01"
        @input="emit('update', index, 'unit_price', parseFloat(($event.target as HTMLInputElement).value) || 0)"
      />
    </td>
    <td class="w-24 px-2 py-1">
      <Input
        type="number"
        :value="item.tax_rate"
        min="0"
        max="100"
        step="0.01"
        @input="emit('update', index, 'tax_rate', parseFloat(($event.target as HTMLInputElement).value) || 0)"
      />
    </td>
    <td class="w-28 px-2 py-1 text-right text-sm font-medium">
      {{ lineTotal }}
    </td>
    <td class="w-10 px-2 py-1 text-center">
      <Button variant="ghost" size="sm" @click="emit('remove', index)">
        ×
      </Button>
    </td>
  </tr>
</template>
