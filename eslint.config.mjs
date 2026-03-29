import antfu from '@antfu/eslint-config'

export default antfu({
  ignores: [
    '**/storage/**',
    '**/js/wayfinder/**',
    '**/js/routes/**',
    '**/js/actions/**',
    '**/js/components/ui/**',
    '**/docker-compose.yml',
  ],
  vue: {
    overrides: {
      'vue/no-template-shadow': ['error', { allow: ['errors'] }],
      'e18e/prefer-static-regex': 'off',
      'vue/no-mutating-props': 'off',
    },
  },
})
