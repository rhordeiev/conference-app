import './bootstrap';
import {createApp} from 'vue'
import App from './App.vue'
import router from './router'
import vuetify from './plugins/vuetify'
import store from './store/store'
import VueGoogleMaps from '@fawmi/vue-google-maps'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const app = createApp(App)

app.use(router)
app.use(vuetify)
app.use(store)
app.use(VueGoogleMaps, {
    load: {
        key: 'AIzaSyADZFKnOO3OO4-DoaG_PP9AJyoe0Ld0rjQ',
    },
})
app.component('QuillEditor', QuillEditor)

app.mount('#app')
