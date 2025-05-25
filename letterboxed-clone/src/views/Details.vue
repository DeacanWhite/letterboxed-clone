<template>
    <div class="details-view">
        <div v-if="loading" class="loading-container">
            <span class="spinner-border" role="status"></span>
            <p>Loading details...</p>
        </div>

        <div v-else-if="error" class="alert alert-danger">
            {{ error }}
        </div>

        <div v-else-if="item" class="details-container">
            <!-- Hero Section -->
            <div class="hero-section">
                <div class="backdrop" v-if="item.backdrop_path" :style="{ backgroundImage: `url(https://image.tmdb.org/t/p/original${item.backdrop_path})` }">
                    <div class="backdrop-overlay"></div>
                </div>
                
                <div class="hero-content">
                    <div class="poster-section">
                        <img 
                            v-if="item.poster_path" 
                            :src="`https://image.tmdb.org/t/p/w500${item.poster_path}`" 
                            :alt="item.title || item.name"
                            class="poster-image"
                        />
                        <div v-else class="poster-placeholder">
                            <i class="fas fa-film fa-4x"></i>
                            <p>No Poster Available</p>
                        </div>
                    </div>

                    <div class="info-section">
                        <div class="title-section">
                            <h1 class="title">{{ item.title || item.name }}</h1>
                            <div class="meta-info">
                                <span class="badge" :class="mediaType === 'movie' ? 'bg-primary' : 'bg-success'">
                                    {{ mediaType === 'movie' ? 'Movie' : 'TV Show' }}
                                </span>
                                <span class="release-date">{{ formatDate(item.release_date || item.first_air_date) }}</span>
                                <span class="rating">
                                    <i class="fas fa-star text-warning"></i>
                                    {{ item.vote_average ? item.vote_average.toFixed(1) : 'N/A' }}/10
                                </span>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button 
                                v-if="isAuthenticated"
                                @click="toggleWatchlist"
                                class="btn"
                                :class="isInWatchlist ? 'btn-danger' : 'btn-outline-light'"
                            >
                                <i :class="isInWatchlist ? 'fas fa-heart' : 'far fa-heart'"></i>
                                {{ isInWatchlist ? 'Remove from Watchlist' : 'Add to Watchlist' }}
                            </button>
                            <button v-else @click="showLoginPrompt" class="btn btn-outline-light">
                                <i class="far fa-heart"></i>
                                Add to Watchlist
                            </button>
                        </div>

                        <div class="overview-section" v-if="item.overview">
                            <h3>Overview</h3>
                            <p class="overview">{{ item.overview }}</p>
                        </div>

                        <!-- Additional Info -->
                        <div class="additional-info">
                            <div v-if="item.genres && item.genres.length" class="info-item">
                                <strong>Genres:</strong>
                                <span class="genres">
                                    <span v-for="(genre, index) in item.genres" :key="genre.id" class="genre-tag">
                                        {{ genre.name }}<span v-if="index < item.genres.length - 1">, </span>
                                    </span>
                                </span>
                            </div>
                            <div v-if="item.runtime" class="info-item">
                                <strong>Runtime:</strong> {{ formatRuntime(item.runtime) }}
                            </div>
                            <div v-if="item.number_of_seasons" class="info-item">
                                <strong>Seasons:</strong> {{ item.number_of_seasons }}
                            </div>
                            <div v-if="item.number_of_episodes" class="info-item">
                                <strong>Episodes:</strong> {{ item.number_of_episodes }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trailer Section -->
            <div class="content-section">
                <div class="section-card">
                    <h2>Trailer</h2>
                    <div class="trailer-container">
                        <CustomVideoPlayer :video-src="getTrailerUrl()" />
                    </div>
                </div>
            </div>

            <!-- Reviews Section -->
            <div class="content-section">
                <div class="section-card">
                    <MovieReviews 
                        :movie-id="itemId" 
                        :movie-type="mediaType"
                    />
                </div>
            </div>
        </div>

        <!-- Login Modal -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h4>Login Required</h4>
                    <button @click="closeModal" class="btn-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Please log in to add movies to your watchlist.</p>
                </div>
                <div class="modal-footer">
                    <button @click="closeModal" class="btn btn-secondary">Cancel</button>
                    <router-link to="/login" class="btn btn-primary" @click="closeModal">
                        Go to Login
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import MovieReviews from '../components/MovieReviews.vue';
import CustomVideoPlayer from '../components/CustomVideoPlayer.vue';
export default {
    name: 'DetailsView',
    components: {
        MovieReviews,
        CustomVideoPlayer
    },
    data() {
        return {
            item: null,
            loading: true,
            error: null,
            apiKey: '15d2ea6d0dc1d476efbca3eba2b9bbfb',
            watchlist: [],
            showModal: false,
            // Use hardcoded trailer video for demonstration (mercury hates me)
            trailerVideos: {
                default: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4'
            }
        };
    },
    computed: {
        isAuthenticated() {
            return localStorage.getItem('isAuthenticated') === 'true';
        },
        mediaType() {
            return this.$route.params.type;
        },
        itemId() {
            return this.$route.params.id;
        },
        isInWatchlist() {
            return this.watchlist.some(watchItem => watchItem.id == this.itemId);
        }
    },
    methods: {
        async fetchDetails() {
            this.loading = true;
            this.error = null;

            try {
                const endpoint = this.mediaType === 'movie' ? 'movie' : 'tv';
                const url = `https://api.themoviedb.org/3/${endpoint}/${this.itemId}?api_key=${this.apiKey}&language=en-US`;
                
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                
                const data = await response.json();
                this.item = data;
            } catch (error) {
                this.error = `Error fetching details: ${error.message}`;
                console.error('Error fetching item details:', error);
            } finally {
                this.loading = false;
            }
        },

        formatDate(dateString) {
            if (!dateString) return 'Unknown';
            
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
            } catch (e) {
                return dateString;
            }
        },

        formatRuntime(minutes) {
            if (!minutes) return 'Unknown';
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return hours > 0 ? `${hours}h ${mins}m` : `${mins}m`;
        },

        loadWatchlist() {
            if (this.isAuthenticated) {
                const user = JSON.parse(localStorage.getItem('user') || '{}');
                const watchlistKey = `watchlist_${user.id}`;
                this.watchlist = JSON.parse(localStorage.getItem(watchlistKey) || '[]');
            }
        },

        saveWatchlist() {
            if (this.isAuthenticated) {
                const user = JSON.parse(localStorage.getItem('user') || '{}');
                const watchlistKey = `watchlist_${user.id}`;
                localStorage.setItem(watchlistKey, JSON.stringify(this.watchlist));
            }
        },

        toggleWatchlist() {
            if (!this.isAuthenticated) {
                this.showLoginPrompt();
                return;
            }

            const index = this.watchlist.findIndex(watchItem => watchItem.id == this.itemId);
            
            if (index > -1) {
                // Remove from watchlist
                this.watchlist.splice(index, 1);
            } else {
                // Add to watchlist
                const watchlistItem = {
                    id: this.item.id,
                    title: this.item.title || this.item.name,
                    poster_path: this.item.poster_path,
                    media_type: this.mediaType,
                    vote_average: this.item.vote_average,
                    release_date: this.item.release_date || this.item.first_air_date,
                    added_date: new Date().toISOString()
                };
                this.watchlist.push(watchlistItem);
            }
            
            this.saveWatchlist();
        },

        showLoginPrompt() {
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
        },

        getTrailerUrl() {
            return this.trailerVideos.default;
        }
    },
    mounted() {
        this.fetchDetails();
        this.loadWatchlist();
    },
    watch: {
        '$route'() {
            // Refetch data when route changes
            this.fetchDetails();
        },
        isAuthenticated() {
            this.loadWatchlist();
        }
    }
};
</script>

<style scoped>
.details-view {
    min-height: 100vh;
}

.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 70vh;
    gap: 1rem;
}

.details-container {
    margin-top: 5rem;
}

/* Hero Section */
.hero-section {
    position: relative;
    min-height: 70vh;
    display: flex;
    align-items: center;
    margin-top: 6rem;
    justify-content: flex-start;
}

.backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-size: cover;
    background-position: center;
    border-radius: 2rem;
}

.backdrop-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        to right,
        rgba(0, 0, 0, 0.9) 0%,
        rgba(0, 0, 0, 0.7) 50%,
        rgba(0, 0, 0, 0.9) 100%
    );
    border-radius: 2rem;
}

.hero-content {
    position: relative;
    z-index: 2;
    display: flex;
    gap: 2rem;
    padding: 2rem;
    width: 90vw;
    text-align: left;
}

.poster-section {
    flex-shrink: 0;
}

.poster-image {
    width: 300px;
    height: 450px;
    object-fit: cover;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.poster-placeholder {
    width: 300px;
    height: 450px;
    background-color: #f0f0f0;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #999;
}

.info-section {
    flex: 1;
    color: white;
}

.title {
    font-size: 3rem;
    font-weight: bold;
    margin-bottom: 1rem;
    line-height: 1.2;
}

.meta-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.release-date, .rating {
    font-size: 1.1rem;
}

.action-buttons {
    margin-bottom: 2rem;
}

.action-buttons .btn {
    margin-right: 1rem;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
}

.overview-section h3 {
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.overview {
    font-size: 1.1rem;
    line-height: 1.6;
    margin-bottom: 2rem;
}

.additional-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.info-item {
    background: rgba(255, 255, 255, 0.1);
    padding: 1rem;
    border-radius: 8px;
    backdrop-filter: blur(10px);
}

.genre-tag {
    font-weight: normal;
}

/* Content Sections */
.content-section {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.section-card {
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.section-card h2 {
    margin-bottom: 2rem;
    color: #333;
}

/* Modal styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 0.5rem;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-header h4 {
    margin: 0;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6c757d;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

/* Add trailer container styling */
.trailer-container {
    margin-top: 1rem;
    width: 100%;
}

/* Ensure video player fits well in the section */
.trailer-container .video-player {
    width: 100%;
    max-width: 100%;
    margin: 0;
}

/* Responsive adjustments for trailer */
@media (max-width: 768px) {
    .trailer-container {
        margin-top: 0.5rem;
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-content {
        flex-direction: column;
        text-align: center;
        padding: 1rem;
    }

    .poster-image,
    .poster-placeholder {
        width: 250px;
        height: 375px;
        margin: 0 auto;
    }

    .title {
        font-size: 2rem;
    }

    .meta-info {
        justify-content: center;
    }

    .additional-info {
        grid-template-columns: 1fr;
    }

    .content-section {
        padding: 1rem;
    }

    .section-card {
        padding: 1.5rem;
    }
}
</style>