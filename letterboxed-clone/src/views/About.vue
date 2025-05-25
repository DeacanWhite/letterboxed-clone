<template>
    <div class="about">
        <h1>About Letterboxed Clone</h1>
        <p>
            This project is a clone of the popular movie discover app Letterboxed.
            It is built with Vue.js and aims to provide a easy discovery experience for movie enthusiasts.
            Users can browse movies, read reviews, and manage their watchlist.
            The app features a personalized experience where users can enter their names and select their preferred view.
            The app also includes a simple authentication system to manage user sessions.
        </p>

        <div class="user-input-section">
            <h2>Personalize Your Experience</h2>
            <div class="input-group">
                <div class="form-field">
                    <label for="firstName">First Name:</label>
                    <input type="text" id="firstName" v-model="firstName" placeholder="Enter first name" />
                </div>
                
                <div class="form-field">
                    <label for="lastName">Last Name:</label>
                    <input type="text" id="lastName" v-model="lastName" placeholder="Enter last name" />
                </div>
            </div>
            
            <div class="welcome-message" v-if="welcomeMessage">
                <p>{{ welcomeMessage }}</p>
            </div>
        </div>

        <div class="image-selection">
            <h2>Select Your Preferred View</h2>
            <div class="radio-group">
                <div class="radio-option">
                    <input type="radio" id="mountain" value="mountain" v-model="selectedImage" />
                    <label for="mountain">Mountain</label>
                </div>
                
                <div class="radio-option">
                    <input type="radio" id="ocean" value="ocean" v-model="selectedImage" />
                    <label for="ocean">Ocean</label>
                </div>
            </div>
            
            <div class="image-display" v-if="selectedImage">
                <img :src="imageSource" :alt="selectedImage" />
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            firstName: '',
            lastName: '',
            selectedImage: ''
        };
    },
    computed: {
        welcomeMessage() {
            if (this.firstName || this.lastName) {
                let name = [this.firstName, this.lastName].filter(Boolean).join(' ');
                return `Welcome, ${name}! Enjoy exploring my Letterboxed Clone.`;
            }
            return '';
        },
        imageSource() {
            if (this.selectedImage === 'mountain') {
                return 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80';
            } else if (this.selectedImage === 'ocean') {
                return 'https://images.unsplash.com/photo-1505118380757-91f5f5632de0?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80';
            }
            return '';
        }
    }
};
</script>

<style scoped>
.about {
    margin: 2rem auto;
    margin-top: 6rem;
    padding: 2rem;
    text-align: center;
}

h1 {
    margin-bottom: 3rem;
    color: #2c3e50;
}

h2 {
    margin: 1.5rem 0 1rem;
    color: #2c3e50;
}

p {
    margin-bottom: 0.5rem;
    color: #444;
    line-height: 1.5;
}

.user-input-section,
.image-selection {
    margin: 2rem 0;
    padding: 1.5rem;
    background: #ffffff;
}

.input-group {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1rem;
    margin: 1rem 0;
}

.form-field {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    min-width: 250px;
}

label {
    margin-bottom: 0.5rem;
    font-weight: bold;
    color: #555;
    text-align: left;
}

input[type="text"] {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1rem;
}

.welcome-message {
    margin-top: 1rem;
    padding: 1rem;
    background: #e8f4ff;
    border-radius: 4px;
    font-weight: bold;
}

.radio-group {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin: 1rem 0;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.image-display {
    margin-top: 1.5rem;
}

img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
}

.creator {
    margin-top: 2rem;
    font-style: italic;
}
</style>