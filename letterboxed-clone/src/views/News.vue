<template>
    <div class="news-view">
        <h1>News</h1>

        <!-- Filter inputs -->
        <div class="filters">
            <div class="filter-group">
                <label for="filterDate">Date:</label>
                <input type="text" id="filterDate" v-model="fNews.date" placeholder="Filter by date..." />
            </div>
            
            <div class="filter-group">
                <label for="filterTitle">Title:</label>
                <input type="text" id="filterTitle" v-model="fNews.title" placeholder="Filter by title..." />
            </div>
            
            <div class="filter-group">
                <label for="filterContent">Content:</label>
                <input type="text" id="filterContent" v-model="fNews.content" placeholder="Filter by content..." />
            </div>
            
            <div class="filter-group">
                <label for="filterCategory">Category:</label>
                <select id="filterCategory" v-model="fNews.category">
                    <option value="">All Categories</option>
                    <option v-for="category in uniqueCategories" :key="category" :value="category">
                        {{ category }}
                    </option>
                </select>
            </div>
        </div>

        <p v-if="loading">Loading news...</p>
        <p v-if="error">{{ error }}</p>
        
        <ul v-if="!loading && !error && getItems.length">
            <li v-for="item in getItems" :key="item.id"> 
                <div class="news-item">
                    <img v-if="item.imageUrl" :src="item.imageUrl" :alt="item.title" class="news-image" />
                    <div class="news-content">
                        <small class="news-date">{{ item.date }}</small>
                        <h2>{{ item.title }}</h2>
                        <p class="news-category"><strong>Category:</strong> {{ item.category }}</p>
                        <p v-if="item.content">{{ item.content }}</p>
                    </div>
                </div>
            </li>
        </ul>
        
        <p v-if="!loading && !error && !newsItems.length">No news items to display.</p>
        <p v-if="!loading && !error && newsItems.length && !getItems.length">No news items match your filter.</p>

        <paginate
            v-if="!loading && !error && getPageCount > 1" 
            :page-count="getPageCount"
            :click-handler="clickCallback"
            :prev-text="'Previous'"
            :next-text="'Next'"
            :container-class="'pagination-controls'"
            ref="paginate" 
            />
    </div>
</template>

<script>
export default {
    name: 'NewsView',
    components: {
        paginate: VuejsPaginateNext,
    },
    data() {
        return {
            perPage: 3,
            currentPage: 1,
            fNews: { 
                date: '',
                title: '',
                content: '',
                category: ''
            },
            newsItems: [],
            loading: true,
            error: null
        };
    },
    computed: {
        // Extract categories for the dropdown menu
        uniqueCategories() {
            // Get all categories, filter out empty ones, and remove duplicates
            const categories = this.newsItems
                .map(item => item.category)
                .filter(category => category); // Filter out null/undefined/empty values
                
            // Use Set to remove duplicates and convert back to array
            return [...new Set(categories)].sort();
        },
        
        filteredNews() {     
            // Filter out  items with missing fields
            const validItems = this.newsItems.filter(item => 
                item && item.date && item.title && item.content && item.category
            );
            
            return validItems.filter(item => {
                const dateMatch = item.date.toLowerCase().includes(this.fNews.date.toLowerCase());
                const titleMatch = item.title.toLowerCase().includes(this.fNews.title.toLowerCase());
                const contentMatch = item.content.toLowerCase().includes(this.fNews.content.toLowerCase());
                const categoryMatch = !this.fNews.category || item.category === this.fNews.category;
                
                return dateMatch && titleMatch && contentMatch && categoryMatch;
            });
        },
        
        // Return the items according to the page number
        getItems() {
            if (!this.filteredNews.length) return [];
            let current = this.currentPage * this.perPage;
            let start = current - this.perPage;
            return this.filteredNews.slice(start, current);
        },
        
        // Return the total number of pages required
        getPageCount() {
            if (!this.filteredNews.length) return 0;
            return Math.ceil(this.filteredNews.length / this.perPage);
        }
    },
    methods: {
        //set the clicked page
        clickCallback(pageNum) {
            this.currentPage = Number(pageNum);
        }
    },
    watch: {
        // Reset to page 1 when filters change
        fNews: {
            handler() {
                this.currentPage = 1;
            },
            deep: true
        }
    },
    mounted() {
        this.loading = true;
        this.error = null;
        var self = this;

        $.getJSON('./assets/news.json', function(data) {
            self.newsItems = data;
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            self.error = 'Failed to load news: ' + textStatus + ', ' + errorThrown;
            console.error('Error fetching news with jQuery:', textStatus, errorThrown, jqXHR);
            self.newsItems = [];
        })
        .always(function() {
            self.loading = false;
        });
    },
};
</script>

<style scoped>
.news-view {
    padding: 2rem;
    margin-top: 6rem;
}

.filters {
    margin-bottom: 2rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    padding: 1rem;
    border-radius: 8px;
    border-color: #667eea;
    margin-top: 3rem;
    margin-bottom: 5rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-weight: bold;
    margin-bottom: 0.25rem;
    color: #495057;
}

.filter-group input[type="text"],
.filter-group select {
    padding: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.9rem;
    background-color: white;
}

.filter-group input[type="text"]:focus,
.filter-group select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

ul {
    list-style-type: none;
    padding: 0;
}

li {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #eee;
}

li:last-child {
    border-bottom: none;
}

.news-item {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.news-image {
    width: 150px;
    object-fit: cover;
    border-radius: 4px;
    flex-shrink: 0;
}

.news-content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.news-date {
    color: #6c757d;
    font-size: 0.85rem;
}

.news-category {
    color: #495057;
    font-size: 0.9rem;
    margin: 0.5rem 0;
}

h2 {
    margin: 0.5rem 0;
    color: #212529;
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