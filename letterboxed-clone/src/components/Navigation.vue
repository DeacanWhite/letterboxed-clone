<template>
  <nav v-if="!hideNavigation" class="navbar navbar-expand-xl fixed-top">
    <div class="container">
      <router-link class="navbar-brand" to="/">
        <span class="letterboxed-brand">Letterboxed</span>
      </router-link>

      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <router-link class="nav-link" to="/" active-class="active"
              >Home</router-link
            >
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/browse" active-class="active">Browse</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/news" active-class="active"
              >News</router-link
            >
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/about" active-class="active"
              >About</router-link
            >
          </li>
          <!-- Show Dashboard link only when authenticated -->
          <li v-if="isAuthenticated" class="nav-item">
            <router-link class="nav-link" to="/dashboard" active-class="active"
              >Dashboard</router-link
            >
          </li>
          <!-- Show Watchlist link only when authenticated -->
          <li v-if="isAuthenticated" class="nav-item">
            <router-link class="nav-link" to="/watchlist" active-class="active"
              >Watchlist</router-link
            >
          </li>
        </ul>

        <!-- Search form (show only when authenticated) -->
        <form v-if="isAuthenticated" class="d-flex me-3">
          <input
            class="form-control me-2"
            type="search"
            placeholder="Search movies..."
            aria-label="Search"
          />
          <button type="button" class="btn btn-dark">Search</button>
        </form>

        <!-- Log in and Sign up buttons -->
        <div class="auth-section">
          <!-- Show when not authenticated -->
          <div v-if="!isAuthenticated" class="auth-buttons">
            <router-link to="/login" class="btn btn-outline-primary me-2">
              Login
            </router-link>
            <router-link to="/register" class="btn btn-primary">
              Sign Up
            </router-link>
          </div>
          
          <!-- Show when authenticated -->
          <div v-else class="user-menu">
            <div class="user-info">
              <span class="user-greeting">Hi, {{ username }}!</span>
              <button @click="logout" class="btn btn-outline-danger btn-sm">
                Logout
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  name: "Navigation",
  data() {
    return {
      isAuthenticated: false,
      user: null
    }
  },
  computed: {
    hideNavigation() {
      // Hide navigation on login and register pages
      return this.$route.meta.hideNavigation;
    },
    username() {
      return this.user?.username || 'User';
    }
  },
  methods: {
    checkAuthStatus() {
      this.isAuthenticated = localStorage.getItem('isAuthenticated') === 'true';
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
    },
    logout() {
      // Clear authentication data
      localStorage.removeItem('user');
      localStorage.removeItem('isAuthenticated');
      
      // Update component state
      this.isAuthenticated = false;
      this.user = null;
      
      // Redirect to home page
      this.$router.push({ name: 'Home' });
    }
  },
  mounted() {
    this.checkAuthStatus();
    
    // Check for storage changes, in case user logs in/out in another tab
    window.addEventListener('storage', this.checkAuthStatus);
  },
  unmounted() {
    // Clean up event listener when component is unmounted
    window.removeEventListener('storage', this.checkAuthStatus);
  },
  watch: {
    // Watch for route changes to update auth status
    '$route'() {
      this.checkAuthStatus();
    }
  }
};
</script>

<style scoped>
.navbar {
  margin-bottom: 10px;
}

.container {
    background-color: #ffffff;
    border-radius: 1rem;
    padding: 1rem;
}

.letterboxed-brand {
  font-weight: 700;
  letter-spacing: 0.5px;
}

.nav-link {
  font-weight: 500;
  transition: color 0.2s;
}

.auth-section {
  display: flex;
  align-items: center;
}

.auth-buttons {
  display: flex;
  align-items: center;
}

.auth-buttons .btn {
  padding: 0.375rem 1rem;
  font-size: 0.9rem;
  font-weight: 500;
  border-radius: 6px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-outline-primary {
  border-color: #667eea;
  color: #667eea;
}

.btn-outline-primary:hover {
  background-color: #667eea;
  border-color: #667eea;
  color: white;
}

.btn-primary {
  background-color: #667eea;
  border-color: #667eea;
  color: white;
}

.btn-primary:hover {
  background-color: #5a6fd8;
  border-color: #5a6fd8;
}

.user-menu {
  display: flex;
  align-items: center;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-greeting {
  font-weight: 500;
  color: #333;
}

.btn-outline-danger {
  border-color: #dc3545;
  color: #dc3545;
  padding: 0.25rem 0.75rem;
  font-size: 0.875rem;
}

.btn-outline-danger:hover {
  background-color: #dc3545;
  border-color: #dc3545;
  color: white;
}

/* Responsive adjustments */
@media (max-width: 991px) {
  .auth-section {
    margin-top: 1rem;
    width: 100%;
  }
  
  .auth-buttons {
    width: 100%;
    justify-content: center;
  }
  
  .user-info {
    width: 100%;
    justify-content: space-between;
  }
}

@media (max-width: 576px) {
  .auth-buttons {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .auth-buttons .btn {
    width: 100%;
  }
}
</style>