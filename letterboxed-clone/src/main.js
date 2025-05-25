import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './style.css'

import './assets/bootstrap/css/bootstrap.min.css';
import './assets/bootstrap/js/bootstrap.bundle.min.js';
import { lazyLoad } from './directives/lazyload.js'

const app = createApp(App)

// register a global directive for lazy loading images
app.directive('lazy-load', lazyLoad)

app.use(router) 

app.mount('#app')