<template>
    <div class="browse-view">
        <h1>Browse Trending</h1>
        
        <div class="time-window-selector mb-4">
            <div class="btn-group">
                <button 
                    class="btn" 
                    :class="timeWindow === 'day' ? 'btn-primary' : 'btn-outline-primary'" 
                    @click="setTimeWindow('day')">
                    Trending Today
                </button>
                <button 
                    class="btn" 
                    :class="timeWindow === 'week' ? 'btn-primary' : 'btn-outline-primary'" 
                    @click="setTimeWindow('week')">
                    Trending This Week
                </button>
            </div>
        </div>

        <p v-if="loading" class="text-center">
            <span class="spinner-border" role="status"></span>
            Loading trending content...
        </p>
        
        <p v-if="error" class="alert alert-danger">{{ error }}</p>
        
        <div v-if="!loading && !error && currentItems.length" class="row">
            <div v-for="item in currentItems" :key="item.id" class="col-md-6 col-lg-4 col-xl-3 mb-4">
                <div class="card movie-card" @click="goToDetails(item)">
                    <div class="card-image-container">
                        <img 
                            v-if="item.poster_path" 
                            v-lazy-load="`https://image.tmdb.org/t/p/w500${item.poster_path}`"
                            class="card-img" 
                            :alt="item.title || item.name" 
                        />
                        <div v-else class="card-img no-poster d-flex align-items-center justify-content-center">
                            <span>No poster available</span>
                        </div>
                        
                        <!-- Watchlist button -->
                        <div class="watchlist-btn-container">
                            <button 
                                v-if="isAuthenticated"
                                @click.stop="toggleWatchlist(item)"
                                class="btn watchlist-btn"
                                :class="isInWatchlist(item.id) ? 'btn-danger' : 'btn-outline-light'"
                                :title="isInWatchlist(item.id) ? 'Remove from Watchlist' : 'Add to Watchlist'"
                            >
                                <i :class="isInWatchlist(item.id) ? 'fas fa-heart' : 'far fa-heart'"></i>
                            </button>
                            <button 
                                v-else
                                @click="showLoginPrompt"
                                class="btn watchlist-btn btn-outline-light"
                                title="Login to add to watchlist"
                            >
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <span class="badge" :class="item.media_type === 'movie' ? 'bg-primary' : 'bg-success'">
                            {{ item.media_type === 'movie' ? 'Movie' : 'TV Show' }}
                        </span>
                        <h5 class="card-title mt-2">{{ item.title || item.name }}</h5>
                        <p class="card-text release-date">
                            <small class="text-muted">
                                {{ formatDate(item.release_date || item.first_air_date) }}
                            </small>
                        </p>
                        <div class="rating">
                            <span class="star">★</span> 
                            {{ item.vote_average ? item.vote_average.toFixed(1) : 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <p v-if="!loading && !error && !movies.length" class="text-center">
            No trending content found.
        </p>
        
        <div v-if="!loading && !error && pageCount > 1" class="pagination-container mt-4">
            <paginate
                :page-count="pageCount"
                :click-handler="handlePageChange"
                :prev-text="'Previous'"
                :next-text="'Next'"
                :container-class="'pagination-controls'"
                :page-class="'page-item'"
                :page-link-class="'page-link'"
                :prev-class="'page-item'"
                :prev-link-class="'page-link'"
                :next-class="'page-item'"
                :next-link-class="'page-link'"
                :active-class="'active'"
            />
        </div>

        <!-- Login prompt modal -->
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
export default {
    name: 'BrowseView',
    components: {
        paginate: VuejsPaginateNext,
    },
    data() {
        return {
            apiKey: '15d2ea6d0dc1d476efbca3eba2b9bbfb',
            movies: [],
            loading: true,
            error: null,
            currentPage: 1,
            itemsPerPage: 10,
            timeWindow: 'week',
            watchlist: [],
            showModal: false
        };
    },
    computed: {
        isAuthenticated() {
            return localStorage.getItem('isAuthenticated') === 'true';
        },
        // Get the movies/TV shows for current page
        currentItems() {
            const startIndex = (this.currentPage - 1) * this.itemsPerPage;
            const endIndex = startIndex + this.itemsPerPage;
            return this.movies.slice(startIndex, endIndex);
        },
        // Calculate total pages
        pageCount() {
            return Math.ceil(this.movies.length / this.itemsPerPage);
        }
    },
    methods: {
        // Fetch trending movies/TV shows
        async fetchTrending() {
            this.loading = true;
            this.error = null;
            
            try {
                const url = `https://api.themoviedb.org/3/trending/all/${this.timeWindow}?api_key=${this.apiKey}&language=en-US`;
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                
                const data = await response.json();
                this.movies = data.results;
                this.currentPage = 1;
            } catch (error) {
                this.error = `Error fetching trending content: ${error.message}`;
                console.error('Error fetching trending content:', error);
                this.movies = [];
            } finally {
                this.loading = false;
            }
        },
        
        // Handle page change
        handlePageChange(pageNumber) {
            this.currentPage = pageNumber;
        },
        
        // Change time window and fetch new data
        setTimeWindow(window) {
            if (this.timeWindow !== window) {
                this.timeWindow = window;
                this.fetchTrending();
            }
        },

        // Navigate to details page
        goToDetails(item) {
            // Determine if it's a movie or TV show
            const type = item.media_type === 'movie' ? 'movie' : 'tv';
            this.$router.push({
                name: 'Details',
                params: {
                    type: type,
                    id: item.id
                }
            });
        },
        
        // Format date to a more readable format
        formatDate(dateString) {
            if (!dateString) return 'Unknown';
            
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
            } catch (e) {
                return dateString;
            }
        },

        // Watchlist methods
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

        isInWatchlist(movieId) {
            return this.watchlist.some(item => item.id === movieId);
        },

        toggleWatchlist(movie) {
            if (!this.isAuthenticated) {
                this.showLoginPrompt();
                return;
            }

            const index = this.watchlist.findIndex(item => item.id === movie.id);
            
            if (index > -1) {
                // Remove from watchlist
                this.watchlist.splice(index, 1);
            } else {
                // Add to watchlist
                const watchlistItem = {
                    id: movie.id,
                    title: movie.title || movie.name,
                    poster_path: movie.poster_path,
                    media_type: movie.media_type,
                    vote_average: movie.vote_average,
                    release_date: movie.release_date || movie.first_air_date,
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
        }
    },
    mounted() {
        // Fetch trending data when component is mounted
        this.fetchTrending();
        this.loadWatchlist();
    },
    watch: {
        isAuthenticated() {
            this.loadWatchlist();
        }
    }
};
</script>

<style scoped>
.browse-view {
    margin-top: 6rem;
    padding: 1rem;
}

.browse-view h1 {
    text-align: center;
    margin-bottom: 3rem;
}

.time-window-selector {
    display: flex;
    justify-content: center;
}

/* Update the button styles */
.time-window-selector .btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    margin: 0 0.25rem;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
}

.time-window-selector .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.time-window-selector .btn.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.5);
    transform: translateY(-2px);
}

.time-window-selector .btn.btn-outline-primary {
    background: rgba(102, 126, 234, 0.1);
    border: 2px solid #667eea;
    color: #667eea;
}

.time-window-selector .btn.btn-outline-primary:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

.card{
    border: none;
    border-radius: 1rem;
    overflow: hidden;
    position: relative;
    min-width: 250px;
    padding: 0;
}

.card-body {
    text-align: left;
}

.card-img {
    height: 100%;
    object-fit: cover;
}

.no-poster {
    height: 300px;
    background-color: #f0f0f0;
    color: #999;
    text-align: center;
}

.star {
    color: #FFD700;
    margin-right: 5px;
}

/* Movie card hover effects */
.movie-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.movie-card:hover {
    transform: translateY(-5px);
}
/* Watchlist button styles */
.watchlist-btn-container {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
}

.watchlist-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.7);
    border: 2px solid rgba(255, 255, 255, 0.8);
    color: white;
    transition: all 0.3s ease;
}

.watchlist-btn:hover {
    background: rgba(0, 0, 0, 0.9);
    border-color: white;
    transform: scale(1.1);
}

.watchlist-btn.btn-danger {
    background: rgba(220, 53, 69, 0.9);
    border-color: #dc3545;
}

.watchlist-btn.btn-danger:hover {
    background: #dc3545;
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

/* Pagination styles - similar to News component */
.pagination-controls {
    margin-top: 2rem;
    display: flex;
    justify-content: center;
    align-items: center;
    list-style-type: none;
    padding: 0;
    gap: 0.5rem;
}

:deep(.pagination-controls .page-item) {
    margin: 0;
}

:deep(.pagination-controls .page-link) {
    padding: 0.75rem 1rem;
    border: 2px solid #667eea;
    cursor: pointer;
    color: #667eea;
    text-decoration: none;
    background: rgba(102, 126, 234, 0.1);
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(102, 126, 234, 0.2);
}

:deep(.pagination-controls .page-item:first-child .page-link) {
    border-radius: 0.5rem 0.5rem 0.5rem 0.5rem;
}

:deep(.pagination-controls .page-item:last-child .page-link) {
    border-radius: 0.5rem 0.5rem 0.5rem 0.5rem;
}

:deep(.pagination-controls .page-link:hover) {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
}

:deep(.pagination-controls .active .page-link) {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.5);
    transform: translateY(-2px);
}

:deep(.pagination-controls .disabled .page-link) {
    color: #ccc;
    pointer-events: none;
    cursor: not-allowed;
    background-color: #f8f9fa;
    border-color: #dee2e6;
    box-shadow: none;
    transform: none;
}

:deep(.pagination-controls .disabled .page-link:hover) {
    background-color: #f8f9fa;
    color: #ccc;
    transform: none;
    box-shadow: none;
}
</style>