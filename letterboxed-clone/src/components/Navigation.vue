<template>
  <nav class="navbar navbar-expand-xl">
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
            <router-link class="nav-link" to="/news" active-class="active"
              >News</router-link
            >
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/about" active-class="active"
              >About</router-link
            >
          </li>
        </ul>

        <form class="d-flex">
          <input
            class="form-control me-2"
            type="search"
            placeholder="Search movies..."
            aria-label="Search"
          />
          <button type="button" class="btn btn-dark">Search</button>
        </form>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  name: "Navigation",
};
</script>

<style scoped>
.navbar {
  margin-top: 20px;
  margin-bottom: 10px;
}

.letterboxed-brand {
  font-weight: 700;
  letter-spacing: 0.5px;
}

.nav-link {
  font-weight: 500;
  transition: color 0.2s;
}
</style>
