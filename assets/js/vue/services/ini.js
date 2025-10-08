import { createApp } from 'vue'
import { plugin as VueTippy } from 'vue-tippy'
import 'tippy.js/dist/tippy.css'
import ToastPlugin from 'vue-toast-notification';

export function iniVueOnId(selectorId, component, store, props = null) {
    if(document.getElementById(selectorId)) {
        const app = createApp(component, {
            props: props
        });
        if(store != null) {
          app.use(store);
        }
        app.use(ToastPlugin);
        app.use(
          VueTippy,
          // optional
          {
              directive: 'tippy', // => v-tippy
              component: 'tippy', // => <tippy/>
              componentSingleton: 'tippy-singleton', // => <tippy-singleton/>,
              defaultProps: {
                  placement: 'auto-end',
                  allowHTML: true,
              }, // => Global default options * see all props
          }
        );
        app.mount('#'+selectorId);
    }
}