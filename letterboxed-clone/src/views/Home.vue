
<template>
    <div class="home">
        <div class="hero-section">
            <div class="hero-content">
                <h2 class="hero-title">Letterboxed Clone</h2>
                <p>By Deacan White</p>
                <div class="hero-buttons">
                    <router-link to="/news">
                        <button>Recent News</button>
                    </router-link>
                    <router-link to="/about">
                        <button>Learn More</button>
                    </router-link>
                </div>
            </div>
        </div>
        <div class="home-content">
            <h1>Welcome to Letterboxed Clone</h1>
            <p>
                This is a simple clone of Letterboxed. Browse, discover, and share your favorite movies! Dive into a curated collection of films, keep track of what you've watched, and connect with a community of fellow movie enthusiasts. Whether you're searching for hidden gems or revisiting classics, Letterboxed Clone makes it easy to explore, review, and discuss cinema from around the world.
            </p>
            <TMDBCards />
        </div>
    </div>
</template>

<script>
import TMDBCards from '../components/FeaturedCard.vue';

export default {
    name: 'HomeView',
    components: {
        TMDBCards
    }
}
</script>

<style scoped>
.home {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.hero-section {
    background: url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=3870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center;
    background-size: cover;
    width: 90vw;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 2rem;
    margin-top: 6rem;
    margin-bottom: 1rem;
}

.hero-title {
    font-size: 4.5rem;
    margin-bottom: 1rem;
    color: #fff !important;
}

.hero-content p {
    font-size: 1.5rem;
    color: #ffffff;
    margin-bottom: 4.5rem;
}

button {
    padding: 12px 32px;
    font-size: 1rem;
    background: #d5d5d5;
    color: #242424;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin: 8px;
}

button:hover {
    background: #444;
}
</style>
