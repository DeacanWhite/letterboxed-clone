<template>
    <div class="login-view">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h2>Welcome Back</h2>
                    <p>Sign in to your Letterboxed account</p>
                </div>

                <form @submit.prevent="login" class="login-form" ref="loginForm">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            v-model="input.username" 
                            :class="{'is-invalid': usernameError}"
                            placeholder="Enter your username"
                            required 
                        />
                        <div v-if="usernameError" class="invalid-feedback">{{ usernameError }}</div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            v-model="input.password" 
                            :class="{'is-invalid': passwordError}"
                            placeholder="Enter your password"
                            required 
                        />
                        <div v-if="passwordError" class="invalid-feedback">{{ passwordError }}</div>
                    </div>

                    <button type="submit" class="btn btn-primary" :disabled="loading">
                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                        {{ loading ? 'Signing in...' : 'Sign In' }}
                    </button>

                    <div v-if="errorMessage" class="alert alert-danger mt-3">
                        {{ errorMessage }}
                    </div>
                </form>

                <div class="login-footer">
                    <p>Don't have an account? 
                        <router-link to="/register" class="register-link">Sign up here</router-link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'LoginView',
    data() {
        return {
            input: {
                username: "",
                password: ""
            },
            loading: false,
            errorMessage: '',
            usernameError: '',
            passwordError: ''
        }
    },
    methods: {
        validateForm() {
            this.usernameError = '';
            this.passwordError = '';
            let isValid = true;

            if (!this.input.username) {
                this.usernameError = 'Username is required';
                isValid = false;
            } else if (this.input.username.length > 50) {
                this.usernameError = 'Username must be less than 50 characters';
                isValid = false;
            }

            if (!this.input.password) {
                this.passwordError = 'Password is required';
                isValid = false;
            } else if (this.input.password.length < 6) {
                this.passwordError = 'Password must be at least 6 characters';
                isValid = false;
            }

            return isValid;
        },
        
        async login() {
            if (!this.validateForm()) {
                return;
            }

            this.loading = true;
            this.errorMessage = '';

            try {
                const requestOptions = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        username: this.input.username,
                        password: this.input.password 
                    })
                };

                // You'll need to update this URL to match your API endpoint
                const response = await fetch("resources/api_user.php/", requestOptions);
                const data = await response.json();
                
                if (data == null) {
                    this.errorMessage = "Invalid username or password. Please try again.";
                } else {
                    // Store user data in localStorage or Vuex store
                    localStorage.setItem('user', JSON.stringify(data));
                    localStorage.setItem('isAuthenticated', 'true');
                    
                    // Emit authentication event to parent
                    this.$emit("authenticated", true);
                    
                    // Redirect to dashboard
                    this.$router.push({ name: "Dashboard" });
                }
            } catch (error) {
                this.errorMessage = "Connection error. Please try again later.";
                console.error('Login error:', error);
            } finally {
                this.loading = false;
            }
        },

        // Mock login function to simulate API call
        /*
        async login() {
            if (!this.validateForm()) {
                return;
            }

            this.loading = true;
            this.errorMessage = '';

            try {
                // Mock API call
                const mockApiCall = async (requestOptions) => {

                    await new Promise(resolve => setTimeout(resolve, 800));
                    
                    // Mock user database
                    const validUsers = [
                        { id: 1, username: 'admin', password: '*YqL7axSYV289cNr', email: 'admin@example.com' },
                        { id: 2, username: 'user', password: 'password', email: 'user@example.com' },
                        { id: 3, username: 'demo', password: 'demo123', email: 'demo@example.com' }
                    ];
                    
                    const requestData = JSON.parse(requestOptions.body);
                    const user = validUsers.find(u => 
                        u.username === requestData.username && 
                        u.password === requestData.password
                    );
                    
                    // Return null if invalid credentials
                    if (!user) {
                        return null;
                    }
                    
                    // Return user data
                    return {
                        id: user.id,
                        username: user.username,
                        email: user.email
                    };
                };

                const requestOptions = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        username: this.input.username,
                        password: this.input.password 
                    })
                };

                const data = await mockApiCall(requestOptions);
                
                if (data == null) {
                    this.errorMessage = "Invalid username or password. Please try again.";
                } else {
                    // Store user data in localStorage or Vuex store
                    localStorage.setItem('user', JSON.stringify(data));
                    localStorage.setItem('isAuthenticated', 'true');
                    
                    // Emit authentication event to parent
                    this.$emit("authenticated", true);
                    
                    // Redirect to dashboard
                    this.$router.push({ name: "Dashboard" });
                }
            } catch (error) {
                this.errorMessage = "Connection error. Please try again later.";
                console.error('Login error:', error);
            } finally {
                this.loading = false;
            }
        },
        */

        reset() {
            this.input.username = '';
            this.input.password = '';
            this.errorMessage = '';
            this.usernameError = '';
            this.passwordError = '';
        }
    }

}
</script>

<style scoped>
.login-view {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.login-container {
    width: 100%;
    max-width: 400px;
}

.login-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    padding: 2rem;
}

.login-header {
    text-align: center;
    margin-bottom: 2rem;
}

.login-header h2 {
    color: #333;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.login-header p {
    color: #666;
    margin: 0;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #333;
}

.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.btn {
    width: 100%;
    padding: 0.75rem;
    font-size: 1rem;
    font-weight: 500;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.alert {
    padding: 0.75rem;
    border-radius: 6px;
    margin: 0;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.login-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e1e5e9;
}

.login-footer p {
    margin: 0;
    color: #666;
}

.register-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.register-link:hover {
    text-decoration: underline;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>