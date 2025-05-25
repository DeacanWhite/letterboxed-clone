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
        
        <div class="form-buttons">
          <button 
            type="submit" 
            class="btn btn-primary"
            :disabled="submitting || !newReview.rating"
          >
            <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
            {{ submitting ? (editingReview ? 'Updating...' : 'Submitting...') : (editingReview ? 'Update Review' : 'Submit Review') }}
          </button>
          
          <button 
            v-if="editingReview"
            type="button" 
            class="btn btn-secondary ms-2"
            @click="cancelEdit"
          >
            Cancel
          </button>
        </div>
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
            
            <div class="review-meta">
              <small class="review-date text-muted">
                {{ formatDate(review.created_at) }}
              </small>
              
              <!-- Edit and Delete buttons for user's own reviews -->
              <div v-if="canModifyReview(review)" class="action-buttons mt-1">
                <button 
                  @click="startEdit(review)"
                  class="btn btn-sm btn-outline-primary me-1"
                  title="Edit Review"
                  :disabled="editingReview && editingReview.id === review.id"
                >
                  <i class="fas fa-edit"></i>
                </button>
                <button 
                  @click="deleteReview(review.id)"
                  class="btn btn-sm btn-outline-danger"
                  title="Delete Review"
                  :disabled="deleting[review.id]"
                >
                  <span v-if="deleting[review.id]" class="spinner-border spinner-border-sm"></span>
                  <i v-else class="fas fa-trash"></i>
                </button>
              </div>
            </div>
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
      editingReview: null,
      hoverRating: 0,
      submitting: false,
      submitMessage: '',
      submitSuccess: false,
      deleting: {},
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

    canModifyReview(review) {
      if (!this.isAuthenticated) return false;
      const user = JSON.parse(localStorage.getItem('user') || '{}');
      return review.user_id == user.id;
    },

    startEdit(review) {
      this.editingReview = review;
      this.newReview = {
        rating: review.rating,
        review_text: review.review_text
      };
      document.querySelector('.add-review-section').scrollIntoView({ behavior: 'smooth' });
    },

    cancelEdit() {
      this.editingReview = null;
      this.newReview = {
        rating: 0,
        review_text: ''
      };
      this.submitMessage = '';
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
        
        if (this.editingReview) {
          // Update existing review
          await this.updateReview(user);
        } else {
          // Create new review
          await this.createReview(user);
        }
      } catch (error) {
        this.submitMessage = 'Network error. Please try again.';
        this.submitSuccess = false;
        console.error('Error submitting review:', error);
      } finally {
        this.submitting = false;
        
        setTimeout(() => {
          this.submitMessage = '';
        }, 5000);
      }
    },

    async createReview(user) {
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
    },

    async updateReview(user) {
      const reviewData = {
        review_id: this.editingReview.id,
        user_id: user.id,
        rating: this.newReview.rating,
        review_text: this.newReview.review_text.trim()
      };

      const response = await fetch(`${this.API_BASE}/api_reviews.php`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(reviewData)
      });

      const result = await response.json();

      if (result.success) {
        this.submitMessage = 'Review updated successfully!';
        this.submitSuccess = true;
        
        // Reset form and editing state
        this.cancelEdit();
        
        // Reload reviews
        await this.loadReviews();
      } else {
        this.submitMessage = result.message || 'Failed to update review.';
        this.submitSuccess = false;
      }
    },

    async deleteReview(reviewId) {
      if (!confirm('Are you sure you want to delete this review?')) {
        return;
      }

      const user = JSON.parse(localStorage.getItem('user') || '{}');
      this.deleting = { ...this.deleting, [reviewId]: true };

      try {
        const response = await fetch(`${this.API_BASE}/api_reviews.php?review_id=${reviewId}&user_id=${user.id}`, {
          method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
          // Remove the review from the local array
          this.reviews = this.reviews.filter(review => review.id != reviewId);
          
          // If edit is in progress, cancel the edit
          if (this.editingReview && this.editingReview.id == reviewId) {
            this.cancelEdit();
          }
          
          // Show success message
          this.submitMessage = 'Review deleted successfully!';
          this.submitSuccess = true;
          
          // Clear message
          setTimeout(() => {
            this.submitMessage = '';
          }, 3000);
        } else {
          alert(result.message || 'Failed to delete review');
        }
      } catch (error) {
        console.error('Error deleting review:', error);
        alert('Network error. Please try again.');
      } finally {
        this.deleting = { ...this.deleting, [reviewId]: false };
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
      this.cancelEdit();
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

.review-actions {
  display: flex;
  align-items: center;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.reviewer-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.review-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  text-align: right;
}

.review-date {
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
}

.action-buttons {
  display: flex;
  gap: 0.25rem;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
}

.form-buttons {
  display: flex;
  align-items: center;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .review-header {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .review-meta {
    align-items: flex-start;
    text-align: left;
    width: 100%;
  }
  
  .action-buttons {
    justify-content: flex-start;
  }
}
</style>