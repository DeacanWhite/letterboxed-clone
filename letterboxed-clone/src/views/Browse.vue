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
                <div class="card">
                    <img 
                        v-if="item.poster_path" 
                        :src="`https://image.tmdb.org/t/p/w500${item.poster_path}`" 
                        class="card-img" 
                        :alt="item.title || item.name" 
                    />
                    <div v-else class="card-img no-poster d-flex align-items-center justify-content-center">
                        <span>No poster available</span>
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
            timeWindow: 'week'
        };
    },
    computed: {
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
    },
    mounted() {
        // Fetch trending data when component is mounted
        this.fetchTrending();
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

/* Pagination styles - similar to News component */
.pagination-controls {
    margin-top: 2rem;
    display: flex;
    justify-content: center;
    align-items: center;
    list-style-type: none;
    padding: 0;
}

:deep(.pagination-controls .page-item) {
    margin: 0 0.25rem;
}

:deep(.pagination-controls .page-link) {
    padding: 0.5rem 0.75rem;
    border: 1px solid #ddd;
    cursor: pointer;
    color: #007bff;
    text-decoration: none;
}

:deep(.pagination-controls .active .page-link) {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

:deep(.pagination-controls .disabled .page-link) {
    color: #6c757d;
    pointer-events: none;
    cursor: auto;
    background-color: #fff;
    border-color: #dee2e6;
}
</style>