<template>
  <div class="auth-container">
    <div class="auth-card">
      <h2>Forgot Password</h2>
      <p class="subtitle">Enter your email to receive a reset link.</p>
      
      <form @submit.prevent="handleForgot">
        <div class="form-group">
          <label>Email Address</label>
          <input v-model="email" type="email" placeholder="user@example.com" required />
        </div>

        <button type="submit" class="auth-btn">Send Reset Link</button>
        
        <div class="links">
          <router-link to="/login">Back to Login</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const email = ref('');

const handleForgot = () => {
  // Mock success behavior
  alert(`If an account exists for ${email.value}, we have sent a reset link!`);
  // Optional: Redirect to a "Reset" page directly for demo purposes
  // router.push('/reset-password-demo'); 
};
</script>

<style scoped>
/* Reusing simple styles */
.auth-container { display: flex; justify-content: center; align-items: center; height: 80vh; }
.auth-card { width: 100%; max-width: 400px; padding: 2rem; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
.auth-btn { width: 100%; padding: 10px; background-color: #42b883; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
.auth-btn:hover { background-color: #3aa876; }
.subtitle { color: #666; margin-bottom: 20px; font-size: 0.9rem; }
.links { margin-top: 15px; text-align: center; font-size: 0.9rem; }
a { color: #42b883; text-decoration: none; }
</style>