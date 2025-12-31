<template>
  <div class="auth-container">
    <div class="card">
      <h2>Login</h2>
      
      <div v-if="errorMessage" class="alert error">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label>Email</label>
          <input 
            v-model="form.email" 
            type="email" 
            required 
            placeholder="Enter your email" 
          />
        </div>

        <div class="form-group">
          <label>Password</label>
          <input 
            v-model="form.password" 
            type="password" 
            required 
            placeholder="Enter your password" 
          />
        </div>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
      </form>
      <div style="margin-top: 10px; text-align: right;">
  <router-link to="/forgot-password" style="font-size: 0.9rem; color: #666;">
    Forgot Password?
  </router-link>
</div>

      <p class="switch-auth">
        Don't have an account? 
        <router-link to="/register">Register here</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from '../axios'; // Import our configured axios instance

const router = useRouter();
const form = ref({ email: '', password: '' });
const errorMessage = ref('');
const loading = ref(false);

const handleLogin = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    // 1. Call the API
    const response = await axios.post('/login', form.value);
    
    // 2. Save Token & User Info
    localStorage.setItem('token', response.data.token);
    localStorage.setItem('user', JSON.stringify(response.data.user));

    // 3. Redirect to Home
    router.push('/');
    
  } catch (error) {
    // Handle specific error message from Laravel or generic error
    errorMessage.value = error.response?.data?.message || 'Login failed. Please check your connection.';
  } finally {
    loading.value = false;
  }
};
</script>