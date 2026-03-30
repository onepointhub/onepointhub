<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3'
import OnboardingController from '@/actions/App/Modules/Core/Http/Controllers/OnboardingController'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Spinner } from '@/components/ui/spinner'
import AuthLayout from '@/layouts/AuthLayout.vue'

const currencies = [
  { code: 'USD', name: 'US Dollar' },
  { code: 'EUR', name: 'Euro' },
  { code: 'GBP', name: 'British Pound' },
  { code: 'AUD', name: 'Australian Dollar' },
  { code: 'CAD', name: 'Canadian Dollar' },
  { code: 'JPY', name: 'Japanese Yen' },
  { code: 'CHF', name: 'Swiss Franc' },
  { code: 'NZD', name: 'New Zealand Dollar' },
  { code: 'SGD', name: 'Singapore Dollar' },
  { code: 'HKD', name: 'Hong Kong Dollar' },
  { code: 'SEK', name: 'Swedish Krona' },
  { code: 'NOK', name: 'Norwegian Krone' },
  { code: 'DKK', name: 'Danish Krone' },
  { code: 'MXN', name: 'Mexican Peso' },
  { code: 'BRL', name: 'Brazilian Real' },
  { code: 'INR', name: 'Indian Rupee' },
  { code: 'ZAR', name: 'South African Rand' },
]
</script>

<template>
  <AuthLayout
    title="Choose your currency"
    description="Step 3 of 3 — Set the primary currency for billing and invoices"
  >
    <Head title="Choose Currency" />

    <Form
      v-slot="{ errors, processing }"
      v-bind="OnboardingController.storeCurrency.form()"
      class="flex flex-col gap-6"
    >
      <div class="grid gap-2">
        <Label for="currency">Currency</Label>
        <Select id="currency" name="currency" default-value="USD" required>
          <SelectTrigger class="w-full" tabindex="1">
            <SelectValue placeholder="Select a currency" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem
              v-for="item in currencies"
              :key="item.code"
              :value="item.code"
            >
              {{ item.code }} — {{ item.name }}
            </SelectItem>
          </SelectContent>
        </Select>
        <InputError :message="errors.currency" />
      </div>

      <Button type="submit" class="w-full" tabindex="2" :disabled="processing">
        <Spinner v-if="processing" />
        Finish setup
      </Button>
    </Form>
  </AuthLayout>
</template>
