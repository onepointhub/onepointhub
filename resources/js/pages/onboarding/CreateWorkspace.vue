<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3'
import OnboardingController from '@/actions/App/Http/Controllers/OnboardingController'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Spinner } from '@/components/ui/spinner'
import AuthLayout from '@/layouts/AuthLayout.vue'
</script>

<template>
  <AuthLayout
    title="Create your workspace"
    description="Step 1 of 3 - Give your workspace a name to get started"
  >
    <Head title="Create Workspace" />

    <Form
      v-slot="{ errors, processing }"
      v-bind="OnboardingController.storeWorkspace.form()"
      class="flex flex-col gap-6"
    >
      <div class="grid gap-2">
        <Label for="name">Workspace name</Label>
        <Input
          id="name"
          name="name"
          type="text"
          required
          autofocus
          :tabindex="1"
          autocomplete="organization"
          placeholder="Acme Corp"
        />
        <InputError :message="errors.name" />
      </div>

      <Button type="submit" class="w-full" tabindex="2" :disabled="processing">
        <Spinner v-if="processing" />
        Continue
      </Button>
    </Form>
  </AuthLayout>
</template>
