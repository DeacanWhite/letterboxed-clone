<template>
  <div class="movie-reviews">
    <h3>Reviews</h3>
    
    <!-- Add Review Form for authenticated users -->
    <div v-if="isAuthenticated" class="add-review-section mb-4">
      <h5>Write a Review</h5>
      <form @submit.prevent="submitReview" class="review-form">
        <div class="rating-input mb-3">
          <label class="form-label">Rating:</label>
          <div class="star-rating">
            <span 
              v-for="star in 5" 
              :key="star"
              @click="setRating(star)"
              @mouseover="hoverRating = star"
              @mouseleave="hoverRating = 0"
              class="star"
              :class="{ 
                'filled': star <= (hoverRating || newReview.rating),
                'hoverable': true 
              }"
            >
              ★
            </span>
          </div>
          <small class="text-muted">{{ newReview.rating }}/5 stars</small>
        </div>
        
        <div class="mb-3">
          <label for="reviewText" class="form-label">Your Review:</label>
          <textarea 
            id="reviewText"
            v-model="newReview.review_text" 
            class="form-control" 
            rows="4" 
            placeholder="Share your thoughts about this movie..."
            required
            maxlength="1000"
          ></textarea>
          <small class="text-muted">{{ newReview.review_text.length }}/1000 characters</small>
        </div>
        
        <button 
          type="submit" 
          class="btn btn-primary"
          :disabled="submitting || !newReview.rating"
        >
          <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
          {{ submitting ? 'Submitting...' : 'Submit Review' }}
        </button>
      </form>
      
      <div v-if="submitMessage" class="alert mt-3" :class="submitSuccess ? 'alert-success' : 'alert-danger'">
        {{ submitMessage }}
      </div>
    </div>

    <!-- Login prompt for non-authenticated users -->
    <div v-else class="alert alert-info">
      <router-link to="/login">Log in</router-link> to write a review.
    </div>

    <!-- Reviews List -->
    <div class="reviews-list">
      <div v-if="loading" class="text-center">
        <div class="spinner-border"></div>
        <p>Loading reviews...</p>
      </div>
      
      <div v-else-if="reviews.length === 0" class="no-reviews text-center text-muted">
        <p>No reviews yet. Be the first to review this movie!</p>
      </div>
      
      <div v-else>
        <div v-for="review in reviews" :key="review.id" class="review-item">
          <div class="review-header">
            <div class="reviewer-info">
              <strong>{{ review.username }}</strong>
              <div class="review-rating">
                <span v-for="star in 5" :key="star" class="star" :class="{ 'filled': star <= review.rating }">
                  ★
                </span>
                <span class="rating-text">({{ review.rating }}/5)</span>
              </div>
            </div>
            <small class="review-date text-muted">
              {{ formatDate(review.created_at) }}
            </small>
          </div>
          <div class="review-content">
            <p>{{ review.review_text }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MovieReviews',
  props: {
    movieId: {
      type: [String, Number],
      required: true
    },
    movieType: {
      type: String,
      default: 'movie'
    }
  },
  data() {
    return {
      reviews: [],
      loading: true,
      newReview: {
        rating: 0,
        review_text: ''
      },
      hoverRating: 0,
      submitting: false,
      submitMessage: '',
      submitSuccess: false,
      API_BASE: window.location.origin + '/cos30043/s103994779/A4/resources'
    };
  },
  computed: {
    isAuthenticated() {
      return localStorage.getItem('isAuthenticated') === 'true';
    }
  },
  methods: {
    async loadReviews() {
      this.loading = true;
      try {
        const response = await fetch(`${this.API_BASE}/api_reviews.php?movie_id=${this.movieId}&movie_type=${this.movieType}`);
        const data = await response.json();
        
        if (data.success) {
          this.reviews = data.reviews;
        } else {
          console.error('Error loading reviews:', data.message);
        }
      } catch (error) {
        console.error('Error loading reviews:', error);
      } finally {
        this.loading = false;
      }
    },

    setRating(rating) {
      this.newReview.rating = rating;
    },

    async submitReview() {
      if (!this.newReview.rating || !this.newReview.review_text.trim()) {
        this.submitMessage = 'Please provide both a rating and review text.';
        this.submitSuccess = false;
        return;
      }

      this.submitting = true;
      this.submitMessage = '';

      try {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        
        const reviewData = {
          movie_id: this.movieId,
          movie_type: this.movieType,
          user_id: user.id,
          username: user.username,
          rating: this.newReview.rating,
          review_text: this.newReview.review_text.trim()
        };

        const response = await fetch(`${this.API_BASE}/api_reviews.php`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(reviewData)
        });

        const result = await response.json();

        if (result.success) {
          this.submitMessage = 'Review submitted successfully!';
          this.submitSuccess = true;
          
          // Reset form
          this.newReview = {
            rating: 0,
            review_text: ''
          };
          
          // Reload reviews
          await this.loadReviews();
        } else {
          this.submitMessage = result.message || 'Failed to submit review.';
          this.submitSuccess = false;
        }
      } catch (error) {
        this.submitMessage = 'Network error. Please try again.';
        this.submitSuccess = false;
        console.error('Error submitting review:', error);
      } finally {
        this.submitting = false;
        
        // Clear message after 5 seconds
        setTimeout(() => {
          this.submitMessage = '';
        }, 5000);
      }
    },

    formatDate(dateString) {
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
      } catch (e) {
        return dateString;
      }
    }
  },
  mounted() {
    this.loadReviews();
  },
  watch: {
    movieId() {
      this.loadReviews();
    }
  }
};
</script>

<style scoped>
.movie-reviews {
  margin-top: 2rem;
}

.add-review-section {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 0.5rem;
  margin-bottom: 2rem;
}

.star-rating {
  display: flex;
  gap: 0.25rem;
  margin: 0.5rem 0;
}

.star {
  font-size: 1.5rem;
  color: #ddd;
  transition: color 0.2s;
}

.star.filled {
  color: #ffc107;
}

.star.hoverable {
  cursor: pointer;
}

.star.hoverable:hover {
  color: #ffc107;
}

.review-form textarea {
  resize: vertical;
}

.reviews-list {
  margin-top: 1rem;
}

.review-item {
  border: 1px solid #dee2e6;
  border-radius: 0.5rem;
  padding: 1rem;
  margin-bottom: 1rem;
  background: white;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

.reviewer-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.review-rating {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.25rem;
}

.review-rating .star {
  font-size: 1rem;
}

.rating-text {
  font-size: 0.875rem;
  color: #6c757d;
}

.review-date {
  font-size: 0.875rem;
}

.review-content {
  margin-top: 0.5rem;
  display: flex;
  align-items: flex-start;
}

.review-content p {
  margin: 0;
  line-height: 1.6;
}

.no-reviews {
  padding: 2rem;
  background: #f8f9fa;
  border-radius: 0.5rem;
}
</style>