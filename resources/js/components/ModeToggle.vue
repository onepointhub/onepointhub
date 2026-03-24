<script setup lang="ts">
import { useColorMode } from '@vueuse/core'
import { MoonIcon, SunIcon } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

const mode = useColorMode({
  disableTransition: false,
})
</script>

<template>
  <Button variant="outline" @click="mode = mode === 'light' ? 'dark' : 'light'">
    <SunIcon class="h-[1.2rem] w-[1.2rem] hidden dark:block" />
    <MoonIcon class="h-[1.2rem] w-[1.2rem] dark:hidden" />
    <span class="sr-only">Toggle theme</span>
  </Button>
</template>
