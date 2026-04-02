import type { DefineComponent } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import createServer from '@inertiajs/vue3/server'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createSSRApp, h } from 'vue'
import { renderToString } from 'vue/server-renderer'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createServer(
  page =>
    createInertiaApp({
      page,
      render: renderToString,
      title: title => (title ? `${title} - ${appName}` : appName),
      resolve: (name) => {
        const pages = import.meta.glob<DefineComponent>([
          './pages/**/*.vue',
          '../../app/Modules/*/resources/js/pages/**/*.vue',
        ])

        const parts = name.split('/')
        if (parts.length > 1) {
          const modulePage = `../../app/Modules/${parts[0]}/resources/js/pages/${parts.slice(1).join('/')}.vue`
          if (pages[modulePage]) {
            return pages[modulePage]()
          }
        }

        return resolvePageComponent(`./pages/${name}.vue`, pages)
      },
      setup: ({ App, props, plugin }) =>
        createSSRApp({ render: () => h(App, props) }).use(plugin),
    }),
  { cluster: true },
)
