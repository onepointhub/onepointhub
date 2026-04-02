<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'
import { Head, Link } from '@inertiajs/vue3'
import axios from 'axios'
import { computed, ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import AppLayout from '@/layouts/AppLayout.vue'
import { index } from '@/routes/clients'
import { execute, upload } from '@/routes/clients/import'

interface Props {
  clientFields: string[]
}

const props = defineProps<Props>()

type Step = 'upload' | 'mapping' | 'preview' | 'done'

const step = ref<Step>('upload')
const uploading = ref(false)
const importing = ref(false)
const error = ref('')

const csvPath = ref('')
const csvHeaders = ref<string[]>([])
const csvPreview = ref<Record<string, string>[]>([])
const mapping = ref<Record<string, string>>({})
const duplicateHandling = ref('skip')

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Clients', href: index() },
  { title: 'Import', href: '#' },
]

const fieldOptions = computed(() => [
  { value: '', label: '— Skip column —' },
  ...props.clientFields.map(f => ({ value: f, label: f.replace('_', ' ') })),
])

async function uploadFile(event: Event) {
  const input = event.target as HTMLInputElement
  if (!input.files?.[0]) {
    return
  }

  uploading.value = true
  error.value = ''

  const form = new FormData()
  form.append('file', input.files[0])
  form.append('_token', (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content ?? '')

  try {
    const res = await axios.post(upload().url, form)
    csvPath.value = res.data.path
    csvHeaders.value = res.data.headers
    csvPreview.value = res.data.preview

    // Initialize mapping: auto-match if header === field name
    mapping.value = {}
    csvHeaders.value.forEach((h) => {
      mapping.value[h] = props.clientFields.includes(h) ? h : ''
    })

    step.value = 'mapping'
  }
  catch {
    error.value = 'Upload failed. Please check the file and try again.'
  }
  finally {
    uploading.value = false
  }
}

async function runImport() {
  importing.value = true
  error.value = ''

  try {
    await axios.post(execute().url, {
      path: csvPath.value,
      mapping: mapping.value,
      duplicate_handling: duplicateHandling.value,
    })
    step.value = 'done'
  }
  catch {
    error.value = 'Import failed. Please try again.'
  }
  finally {
    importing.value = false
  }
}
</script>

<template>
  <Head title="Import Clients" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-3xl flex flex-col gap-6 p-4">
      <!-- Upload -->
      <Card v-if="step === 'upload'">
        <CardHeader>
          <CardTitle>Import Clients from CSV</CardTitle>
        </CardHeader>
        <CardContent class="flex flex-col gap-4">
          <p class="text-sm text-muted-foreground">
            Upload a CSV file. The first row must contain column headers.
          </p>
          <div class="flex flex-col gap-1.5">
            <Label for="csv-file">CSV File</Label>
            <Input id="csv-file" type="file" accept=".csv,text/csv" :disabled="uploading" @change="uploadFile" />
          </div>
          <p v-if="error" class="text-destructive text-sm">
            {{ error }}
          </p>
        </CardContent>
      </Card>

      <!-- Column mapping -->
      <Card v-else-if="step === 'mapping'">
        <CardHeader>
          <CardTitle>Map Columns</CardTitle>
        </CardHeader>
        <CardContent class="flex flex-col gap-4">
          <p class="text-sm text-muted-foreground">
            Match each CSV column to a client field. Columns set to "Skip" will be ignored.
          </p>

          <div
            v-for="header in csvHeaders"
            :key="header"
            class="flex items-center gap-4"
          >
            <span class="w-40 shrink-0 font-mono text-sm">{{ header }}</span>
            <Select v-model="mapping[header]">
              <SelectTrigger class="w-48">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="opt in fieldOptions" :key="opt.value" :value="opt.value" class="capitalize">
                  {{ opt.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="flex flex-col gap-1.5">
            <Label>Duplicate handling (matched by name)</Label>
            <Select v-model="duplicateHandling">
              <SelectTrigger class="w-48">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="skip">
                  Skip duplicate
                </SelectItem>
                <SelectItem value="update">
                  Update existing
                </SelectItem>
                <SelectItem value="create">
                  Always create new
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="border-t pt-4">
            <h3 class="mb-3 text-sm font-medium">
              Preview (first 5 rows)
            </h3>
            <div class="overflow-x-auto rounded border">
              <table class="min-w-full text-sm">
                <thead class="bg-muted text-xs uppercase">
                  <tr>
                    <th v-for="h in csvHeaders" :key="h" class="px-3 py-2 text-left">
                      {{ h }}
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y">
                  <tr v-for="(row, i) in csvPreview" :key="i">
                    <td v-for="h in csvHeaders" :key="h" class="px-3 py-2">
                      {{ row[h] }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <p v-if="error" class="text-destructive text-sm">
            {{ error }}
          </p>

          <div class="flex justify-end gap-3">
            <Button variant="outline" @click="step = 'upload'">
              Back
            </Button>
            <Button :disabled="importing" @click="runImport">
              {{ importing ? 'Queuing…' : 'Start Import' }}
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Done -->
      <Card v-else-if="step === 'done'">
        <CardContent class="flex flex-col items-center gap-4 py-16">
          <p class="text-lg font-medium">
            Import queued!
          </p>
          <p class="text-sm text-muted-foreground">
            You will receive a notification when the import is complete.
          </p>
          <Link :href="index()">
            <Button>Back to Clients</Button>
          </Link>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
