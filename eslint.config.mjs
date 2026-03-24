import antfu from '@antfu/eslint-config'

export default antfu({
  ignores: [
    '**/storage/**',
    '**/js/wayfinder/**',
    '**/js/routes/**',
    '**/js/actions/**',
    '**/js/components/ui/**',
  ],
  vue: {
    overrides: {
      'vue/no-template-shadow': ['error', { allow: ['errors'] }],
    },
  },
})
