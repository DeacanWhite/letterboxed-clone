import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './style.css'

import './assets/bootstrap/css/bootstrap.min.css';
import './assets/bootstrap/js/bootstrap.bundle.min.js';

const app = createApp(App)

app.use(router) 

app.mount('#app')