<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3'
import OnboardingController from '@/actions/App/Http/Controllers/OnboardingController'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'
import AuthLayout from '@/layouts/AuthLayout.vue'
</script>

<template>
  <AuthLayout
    title="Invite your team"
    description="Step 2 of 3 — Invite colleagues now or skip and do it later"
  >
    <Head title="Invite Team" />

    <Form
      v-slot="{ errors, processing }"
      v-bind="OnboardingController.storeInvite.form()"
      class="flex flex-col gap-6"
    >
      <div class="grid gap-2">
        <Label for="emails">Email addresses</Label>
        <Input
          id="emails"
          name="emails"
          type="email"
          :tabindex="1"
          autocomplete="email"
          placeholder="colleague@example.com"
        />
        <InputError :message="errors.emails" />
      </div>

      <div class="flex flex-col gap-2">
        <Button type="submit" class="w-full" tabindex="2" :disabled="processing">
          <Spinner v-if="processing" />
          Send invite &amp; continue
        </Button>

        <Button
          type="submit"
          variant="ghost"
          class="w-full"
          tabindex="3"
          name="skip"
          value="1"
        >
          Skip for now
        </Button>
      </div>
    </Form>
  </AuthLayout>
</template>
