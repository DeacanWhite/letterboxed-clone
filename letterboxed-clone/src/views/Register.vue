<template>
    <div class="register-view">
        <div class="register-container">
            <div class="register-card">
                <div class="register-header">
                    <h2>Join Letterboxed</h2>
                    <p>Create your account to start discovering movies</p>
                </div>

                <form @submit.prevent="register" class="register-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            v-model="input.username" 
                            :class="{'is-invalid': usernameError}"
                            placeholder="Choose a username"
                            required 
                        />
                        <div v-if="usernameError" class="invalid-feedback">{{ usernameError }}</div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            v-model="input.email" 
                            :class="{'is-invalid': emailError}"
                            placeholder="Enter your email"
                            required 
                        />
                        <div v-if="emailError" class="invalid-feedback">{{ emailError }}</div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            v-model="input.password" 
                            :class="{'is-invalid': passwordError}"
                            placeholder="Create a password"
                            required 
                        />
                        <div v-if="passwordError" class="invalid-feedback">{{ passwordError }}</div>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input 
                            type="password" 
                            id="confirmPassword" 
                            v-model="input.confirmPassword" 
                            :class="{'is-invalid': confirmPasswordError}"
                            placeholder="Confirm your password"
                            required 
                        />
                        <div v-if="confirmPasswordError" class="invalid-feedback">{{ confirmPasswordError }}</div>
                    </div>

                    <div class="form-group form-check">
                        <input 
                            type="checkbox" 
                            id="terms" 
                            v-model="input.acceptTerms" 
                            :class="{'is-invalid': termsError}"
                            required 
                        />
                        <label for="terms" class="form-check-label">
                            I agree to the <a href="#" class="terms-link">Terms of Service</a> and <a href="#" class="terms-link">Privacy Policy</a>
                        </label>
                        <div v-if="termsError" class="invalid-feedback">{{ termsError }}</div>
                    </div>

                    <button type="submit" class="btn btn-primary" :disabled="loading">
                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                        {{ loading ? 'Creating Account...' : 'Create Account' }}
                    </button>

                    <div v-if="errorMessage" class="alert alert-danger mt-3">
                        {{ errorMessage }}
                    </div>

                    <div v-if="successMessage" class="alert alert-success mt-3">
                        {{ successMessage }}
                    </div>
                </form>

                <div class="register-footer">
                    <p>Already have an account? 
                        <router-link to="/login" class="login-link">Sign in here</router-link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'RegisterView',
    data() {
        return {
            input: {
                username: "",
                email: "",
                password: "",
                confirmPassword: "",
                acceptTerms: false
            },
            loading: false,
            errorMessage: '',
            successMessage: '',
            usernameError: '',
            emailError: '',
            passwordError: '',
            confirmPasswordError: '',
            termsError: ''
        }
    },
    methods: {
        validateForm() {
            // Reset all errors
            this.usernameError = '';
            this.emailError = '';
            this.passwordError = '';
            this.confirmPasswordError = '';
            this.termsError = '';
            let isValid = true;

            // Username validation
            if (!this.input.username) {
                this.usernameError = 'Username is required';
                isValid = false;
            } else if (this.input.username.length < 3) {
                this.usernameError = 'Username must be at least 3 characters';
                isValid = false;
            } else if (this.input.username.length > 50) {
                this.usernameError = 'Username must be less than 50 characters';
                isValid = false;
            } else if (!/^[a-zA-Z0-9_]+$/.test(this.input.username)) {
                this.usernameError = 'Username can only contain letters, numbers, and underscores';
                isValid = false;
            }

            // Email validation
            if (!this.input.email) {
                this.emailError = 'Email is required';
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.input.email)) {
                this.emailError = 'Please enter a valid email address';
                isValid = false;
            }

            // Password validation
            if (!this.input.password) {
                this.passwordError = 'Password is required';
                isValid = false;
            } else if (this.input.password.length < 8) {
                this.passwordError = 'Password must be at least 8 characters';
                isValid = false;
            } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(this.input.password)) {
                this.passwordError = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
                isValid = false;
            }

            // Confirm password validation
            if (!this.input.confirmPassword) {
                this.confirmPasswordError = 'Please confirm your password';
                isValid = false;
            } else if (this.input.password !== this.input.confirmPassword) {
                this.confirmPasswordError = 'Passwords do not match';
                isValid = false;
            }

            // Terms validation
            if (!this.input.acceptTerms) {
                this.termsError = 'You must accept the terms and conditions';
                isValid = false;
            }

            return isValid;
        },

        async register() {
            if (!this.validateForm()) {
                return;
            }

            this.loading = true;
            this.errorMessage = '';
            this.successMessage = '';

            try {
                const requestOptions = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        username: this.input.username,
                        email: this.input.email,
                        password: this.input.password 
                    })
                };

                // register api endpoint
                const response = await fetch("/cos30043/s103994779/A4/resources/api_register.php", requestOptions);
                const data = await response.json();
                
                if (data.success) {
                    this.successMessage = "Account created successfully! You can now log in.";

                    // redirect to login after a short delay
                    setTimeout(() => {
                        this.$router.push({ name: "Login" });
                    }, 2000);
                } else {
                    this.errorMessage = data.message || "Registration failed. Please try again.";
                }
            } catch (error) {
                this.errorMessage = "Connection error. Please try again later.";
                console.error('Registration error:', error);
            } finally {
                this.loading = false;
            }
        },

        reset() {
            this.input = {
                username: "",
                email: "",
                password: "",
                confirmPassword: "",
                acceptTerms: false
            };
            this.errorMessage = '';
            this.successMessage = '';
            this.usernameError = '';
            this.emailError = '';
            this.passwordError = '';
            this.confirmPasswordError = '';
            this.termsError = '';
        }
    }
}
</script>

<style scoped>
.register-view {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.register-container {
    width: 100%;
    max-width: 450px;
}

.register-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    padding: 2rem;
}

.register-header {
    text-align: center;
    margin-bottom: 2rem;
}

.register-header h2 {
    color: #333;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.register-header p {
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

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"] {
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

.form-check {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.form-check input[type="checkbox"] {
    margin-top: 0.25rem;
    flex-shrink: 0;
}

.form-check-label {
    font-size: 0.9rem;
    color: #666;
    line-height: 1.4;
}

.terms-link {
    color: #667eea;
    text-decoration: none;
}

.terms-link:hover {
    text-decoration: underline;
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

.alert-success {
    background-color: #d1edff;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.register-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e1e5e9;
}

.register-footer p {
    margin: 0;
    color: #666;
}

.login-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.login-link:hover {
    text-decoration: underline;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>