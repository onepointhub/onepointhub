import type { DefineComponent } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'
import '../css/app.css'

const appName = import.meta.env.VITE_APP_NAME || 'OnePointHub'

createInertiaApp({
  title: title => (title ? `${title} - ${appName}` : appName),
  resolve: (name) => {
    const appPages = import.meta.glob<DefineComponent>('./pages/**/*.vue')
    const modulePages = import.meta.glob<DefineComponent>('../../app/Modules/**/resources/js/pages/**/*.vue')

    const parts = name.split('::')
    const modulePage = `../../app/Modules/${parts[0]}/resources/js/pages/${parts.slice(1).join('/')}.vue`

    if (modulePages[modulePage]) {
      return modulePages[modulePage]()
    }

    return resolvePageComponent(`./pages/${name}.vue`, appPages)
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
  progress: {
    color: '#4B5563',
  },
})
