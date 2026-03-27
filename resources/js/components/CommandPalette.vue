<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { ref, watch } from 'vue'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import {
  CommandDialog,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from '@/components/ui/command'
import { getInitials } from '@/composables/useInitials'
import { search } from '@/routes'

interface SearchResult {
  type: string
  label: string
  sublabel: string
  avatar: string | null
  href: string | null
}

const open = ref(false)
const query = ref('')
const results = ref<SearchResult[]>([])
const loading = ref(false)

defineExpose({ open })

const fetchResults = useDebounceFn(async (q: string) => {
  if (q.length < 2) {
    results.value = []
    return
  }
  loading.value = true
  try {
    const res = await fetch(`${search.url({ query: { q } })}`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    const data = await res.json()
    results.value = data.results ?? []
  }
  finally {
    loading.value = false
  }
}, 300)

watch(query, fetchResults)

function onSelect(result: SearchResult) {
  open.value = false
  if (result.href) {
    router.visit(result.href)
  }
}
</script>

<template>
  <CommandDialog v-model:open="open">
    <CommandInput
      v-model="query"
      placeholder="Search members, projects, clients…"
    />
    <CommandList>
      <CommandEmpty>
        {{ query.length < 2 ? 'Type at least 2 characters to search.' : 'No results found.' }}
      </CommandEmpty>

      <CommandGroup
        v-if="results.length > 0"
        heading="Results"
      >
        <CommandItem
          v-for="result in results"
          :key="`${result.type}-${result.label}`"
          :value="result.label"
          @select="onSelect(result)"
        >
          <Avatar class="mr-2 h-6 w-6">
            <AvatarImage :src="result.avatar || ''" :alt="result.label" />
            <AvatarFallback class="text-[10px]">
              {{ getInitials(result.label) }}
            </AvatarFallback>
          </Avatar>
          <div class="flex flex-col">
            <span class="text-sm">{{ result.label }}</span>
            <span class="text-xs text-muted-foreground">{{ result.sublabel }}</span>
          </div>
        </CommandItem>
      </CommandGroup>
    </CommandList>
  </CommandDialog>
</template>
