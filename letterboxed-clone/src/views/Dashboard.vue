<template>
    <div class="dashboard-view">
        <div class="dashboard-container">
            <!-- Welcome Header -->
            <div class="welcome-section">
                <div class="welcome-content">
                    <h1 class="welcome-title">Welcome back, {{ username }}!</h1>
                </div>
                <div class="user-avatar">
                    <div class="avatar-circle">
                        {{ userInitials }}
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="actions-section">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <router-link to="/browse" class="action-btn">
                        <div class="action-icon">🔍</div>
                        <span>Browse Movies</span>
                    </router-link>
                    <router-link to="/news" class="action-btn">
                        <div class="action-icon">📰</div>
                        <span>Latest News</span>
                    </router-link>
                    <router-link to="/watchlist" class="action-btn">
                        <div class="action-icon">📰</div>
                        <span>View Watchlist</span>
                    </router-link>
                    <button @click="logout" class="action-btn logout-btn">
                        <div class="action-icon">🚪</div>
                        <span>Logout</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'DashboardView',
    data() {
        return {
            user: null,
        }
    },
    computed: {
        username() {
            return this.user?.username || 'User';
        },
        userInitials() {
            if (this.user?.username) {
                return this.user.username.charAt(0).toUpperCase();
            }
            return 'U';
        }
    },
    methods: {
        loadUserData() {
            // Load user data from localStorage
            const userData = localStorage.getItem('user');
            if (userData) {
                this.user = JSON.parse(userData);
            }
        },
        logout() {
            // Clear user data
            localStorage.removeItem('user');
            localStorage.removeItem('isAuthenticated');
            
            // Emit logout event to parent
            this.$emit('logout');
            
            // Redirect to login
            this.$router.push({ name: 'Login' });
        },
    },
    mounted() {
        this.loadUserData();
        
        // Check if user is authenticated
        const isAuthenticated = localStorage.getItem('isAuthenticated');
        if (!isAuthenticated || !this.user) {
            this.$router.push({ name: 'Login' });
        }
    }
}
</script>

<style scoped>
.dashboard-view {
    margin-top: 6rem;
    padding: 2rem 1rem;
    min-height: calc(100vh - 6rem);
}

.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
}

.welcome-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
}

.welcome-content h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2.5rem;
    font-weight: 600;
}

.welcome-content p {
    margin: 0;
    font-size: 1.2rem;
    opacity: 0.9;
}

.user-avatar {
    flex-shrink: 0;
}

.avatar-circle {
    width: 80px;
    height: 80px;
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: bold;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.stats-section {
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    height: 100%;
    transition: transform 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-icon {
    font-size: 2.5rem;
    flex-shrink: 0;
}

.stat-content h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    color: #666;
    font-weight: 500;
}

.stat-number {
    margin: 0;
    font-size: 2rem;
    font-weight: bold;
    color: #333;
}

.actions-section {
    margin-bottom: 2rem;
}

.actions-section h2 {
    margin-bottom: 1rem;
    color: #333;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem;
    background: white;
    border: none;
    border-radius: 12px;
    text-decoration: none;
    color: #333;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
    min-width: 120px;
    cursor: pointer;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    color: #333;
    text-decoration: none;
}

.logout-btn:hover {
    background-color: #dc3545;
    color: white;
}

.action-icon {
    font-size: 2rem;
}

.action-btn span {
    font-weight: 500;
}

.activity-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.activity-section h2 {
    margin-bottom: 1.5rem;
    color: #333;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 8px;
}

.activity-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.activity-content p {
    margin: 0 0 0.25rem 0;
    color: #333;
}

.activity-content small {
    color: #666;
}

@media (max-width: 768px) {
    .welcome-section {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    .welcome-content h1 {
        font-size: 2rem;
    }

    .action-buttons {
        justify-content: center;
    }
}
</style>