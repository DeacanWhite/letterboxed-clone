<template>
  <div class="tmdb-cards">
    <h2 class="my-3">Recommended Movies</h2>
    <div class="row">
      <div
        v-for="movie in movies"
        :key="movie.id"
        class="col-md-4 mb-4"
      >
        <div class="card">
          <img
            :src="'https://image.tmdb.org/t/p/w500' + movie.poster_path"
            class="card-img-top"
            :alt="movie.title"
          />
          <div class="card-body">
            <h5 class="card-title">{{ movie.title }}</h5>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "TMDBCards",
  data() {
    return {
      movies: [], // Array to store movie data
    };
  },
  methods: {
    async fetchMovies() {
      const apiKey = "15d2ea6d0dc1d476efbca3eba2b9bbfb";
      const url = `https://api.themoviedb.org/3/movie/popular?api_key=${apiKey}&language=en-US&page=1`;

      try {
        const response = await fetch(url);
        const data = await response.json();
        this.movies = data.results.slice(0, 3); // Get the first 3 movies
      } catch (error) {
        console.error("Error fetching movies:", error);
      }
    },
  },
  mounted() {
    this.fetchMovies(); // Fetch movies when the component is mounted
  },
};
</script>

<style scoped>
.tmdb-cards .card {
  border: none;
}
.tmdb-cards .card-img-top {
  object-fit: cover;
}
</style>