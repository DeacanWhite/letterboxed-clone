<template>
  <div id="app">
    <Navigation ref="navigation" />
    <router-view @authenticated="handleAuthentication" @logout="handleLogout"></router-view>
  </div>
</template>

<script>
import Navigation from './components/Navigation.vue';

export default {
  name: 'App',
  components: {
    Navigation
  },
  mounted() {
    // Check authentication status on app load
    this.checkInitialAuthStatus();
  },
  methods: {
    checkInitialAuthStatus() {
      const isAuthenticated = localStorage.getItem('isAuthenticated') === 'true';
      const currentRoute = this.$route.name;
      
      // If user is authenticated and on login/register page, redirect to dashboard
      if (isAuthenticated && (currentRoute === 'Login' || currentRoute === 'Register')) {
        this.$router.push({ name: 'Dashboard' });
      }
      // If user is not authenticated and trying to access protected route, redirect to login
      else if (!isAuthenticated && this.$route.meta.requiresAuth) {
        this.$router.push({ name: 'Login' });
      }
    },
    handleAuthentication(status) {
      if (status && this.$refs.navigation) {
        this.$refs.navigation.checkAuthStatus();
      }
    },
    handleLogout() {
      if (this.$refs.navigation) {
        this.$refs.navigation.checkAuthStatus();
      }
    }
  }
};
</script>

<style>
/* Global styles for authentication */
body {
  margin: 0;
  font-family: system-ui, Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Ensure proper spacing for fixed navigation */
#app {
  min-height: 100vh;
}

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

/* Form validation styles */
.is-invalid {
  border-color: #dc3545 !important;
}

.invalid-feedback {
  display: block;
  width: 100%;
  margin-top: 0.25rem;
  font-size: 0.875em;
  color: #dc3545;
}

/* Button loading states */
.spinner-border {
  display: inline-block;
  width: 2rem;
  height: 2rem;
  vertical-align: text-bottom;
  border: 0.25em solid currentColor;
  border-right-color: transparent;
  border-radius: 50%;
  animation: spinner-border .75s linear infinite;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
  border-width: 0.2em;
}

@keyframes spinner-border {
  to { transform: rotate(360deg); }
}

/* Alert styles */
.alert {
  position: relative;
  padding: 0.75rem 1.25rem;
  margin-bottom: 1rem;
  border: 1px solid transparent;
  border-radius: 0.25rem;
}

.alert-danger {
  color: #721c24;
  background-color: #f8d7da;
  border-color: #f5c6cb;
}

.alert-success {
  color: #155724;
  background-color: #d4edda;
  border-color: #c3e6cb;
}

/* Responsive utilities */
.d-flex {
  display: flex !important;
}

.justify-content-center {
  justify-content: center !important;
}

.align-items-center {
  align-items: center !important;
}

.me-2 {
  margin-right: 0.5rem !important;
}

.me-3 {
  margin-right: 1rem !important;
}

.mt-3 {
  margin-top: 1rem !important;
}

/* Grid system for stats */
.row {
  display: flex;
  flex-wrap: wrap;
  margin-right: -15px;
  margin-left: -15px;
}

.col-md-4 {
  position: relative;
  width: 100%;
  padding-right: 15px;
  padding-left: 15px;
  flex: 0 0 33.333333%;
  max-width: 33.333333%;
}

@media (max-width: 768px) {
  .col-md-4 {
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 1rem;
  }
}
</style>