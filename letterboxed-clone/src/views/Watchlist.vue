<template>
    <div class="watchlist-view">
        <h1>My Watchlist</h1>
        
        <div v-if="!isAuthenticated" class="alert alert-warning text-center">
            <h4>Please log in to view your watchlist</h4>
            <router-link to="/login" class="btn btn-primary">Go to Login</router-link>
        </div>

        <div v-else-if="loading" class="text-center">
            <span class="spinner-border" role="status"></span>
            Loading your watchlist...
        </div>

        <div v-else-if="watchlist.length === 0" class="empty-watchlist text-center">
            <i class="far fa-heart fa-4x mb-3 text-muted"></i>
            <h3>Your watchlist is empty</h3>
            <p class="text-muted">Start adding movies and TV shows to keep track of what you want to watch!</p>
            <router-link to="/browse" class="btn btn-primary">Browse Movies</router-link>
        </div>

        <div v-else class="row">
            <div v-for="item in watchlist" :key="item.id" class="col-md-6 col-lg-4 col-xl-3 mb-4">
                <div class="card">
                    <div class="card-image-container">
                        <img 
                            v-if="item.poster_path" 
                            :src="`https://image.tmdb.org/t/p/w500${item.poster_path}`" 
                            class="card-img" 
                            :alt="item.title" 
                        />
                        <div v-else class="card-img no-poster d-flex align-items-center justify-content-center">
                            <span>No poster available</span>
                        </div>
                        
                        <!-- Remove from watchlist button -->
                        <div class="watchlist-btn-container">
                            <button 
                                @click="removeFromWatchlist(item.id)"
                                class="btn watchlist-btn btn-danger"
                                title="Remove from Watchlist"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <span class="badge" :class="item.media_type === 'movie' ? 'bg-primary' : 'bg-success'">
                            {{ item.media_type === 'movie' ? 'Movie' : 'TV Show' }}
                        </span>
                        <h5 class="card-title mt-2">{{ item.title }}</h5>
                        <p class="card-text release-date">
                            <small class="text-muted">
                                {{ formatDate(item.release_date) }}
                            </small>
                        </p>
                        <div class="rating">
                            <span class="star">★</span> 
                            {{ item.vote_average ? item.vote_average.toFixed(1) : 'N/A' }}
                        </div>
                        <p class="added-date">
                            <small class="text-muted">
                                Added: {{ formatDate(item.added_date) }}
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'WatchlistView',
    data() {
        return {
            watchlist: [],
            loading: true
        };
    },
    computed: {
        isAuthenticated() {
            return localStorage.getItem('isAuthenticated') === 'true';
        }
    },
    methods: {
        loadWatchlist() {
            this.loading = true;
            
            if (this.isAuthenticated) {
                const user = JSON.parse(localStorage.getItem('user') || '{}');
                const watchlistKey = `watchlist_${user.id}`;
                this.watchlist = JSON.parse(localStorage.getItem(watchlistKey) || '[]');
            }
            
            this.loading = false;
        },

        saveWatchlist() {
            if (this.isAuthenticated) {
                const user = JSON.parse(localStorage.getItem('user') || '{}');
                const watchlistKey = `watchlist_${user.id}`;
                localStorage.setItem(watchlistKey, JSON.stringify(this.watchlist));
            }
        },

        removeFromWatchlist(movieId) {
            const index = this.watchlist.findIndex(item => item.id === movieId);
            if (index > -1) {
                this.watchlist.splice(index, 1);
                this.saveWatchlist();
            }
        },

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
        }
    },
    mounted() {
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
.watchlist-view {
    margin-top: 6rem;
    padding: 1rem;
}

.watchlist-view h1 {
    text-align: center;
    margin-bottom: 3rem;
}

.empty-watchlist {
    padding: 3rem 1rem;
}

.card {
    border: none;
    border-radius: 1rem;
    overflow: hidden;
    position: relative;
    min-width: 250px;
    padding: 0;
}

.card-image-container {
    position: relative;
}

.card-body {
    text-align: left;
}

.card-img {
    height: 300px;
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

.added-date {
    margin-bottom: 0;
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
    transition: all 0.3s ease;
}

.watchlist-btn:hover {
    transform: scale(1.1);
}
</style>