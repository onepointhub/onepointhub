<script setup lang="ts">
import { computed } from 'vue'
import { SidebarInset } from '@/components/ui/sidebar'

interface Props {
  class?: string
}

const props = defineProps<Props>()
const className = computed(() => props.class)
</script>

<template>
  <SidebarInset :class="className">
    <slot />
  </SidebarInset>
</template>
