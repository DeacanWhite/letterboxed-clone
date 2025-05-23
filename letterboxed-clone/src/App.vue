<template>
  <div id="app">
    <Navigation />
    <router-view></router-view>
  </div>
</template>

<script>

import Navigation from './components/Navigation.vue';
export default {
  name: 'App',
  components: {
    Navigation
  },
};
</script>

<style>
/* Optional: Basic styling for navigation */
nav {
  padding: 30px;
}
nav a {
  font-weight: bold;
  color: #2c3e50;
}
nav a.router-link-exact-active {
  color: #42b983;
}
</style>