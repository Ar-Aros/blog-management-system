<template>
  <div class="auth-container">
    <div class="auth-card">
      <h2>Reset Password</h2>
      
      <form @submit.prevent="handleReset">
        <div class="form-group">
          <label>New Password</label>
          <input v-model="password" type="password" required />
        </div>

        <div class="form-group">
          <label>Confirm Password</label>
          <input v-model="confirmPassword" type="password" required />
        </div>

        <button type="submit" class="auth-btn">Update Password</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const password = ref('');
const confirmPassword = ref('');

const handleReset = () => {
  if (password.value !== confirmPassword.value) {
    alert("Passwords do not match!");
    return;
  }
  
  // Mock success
  alert("Password successfully updated! Please login.");
  router.push('/login');
};
</script>

<style scoped>
/* Same styles as above */
.auth-container { display: flex; justify-content: center; align-items: center; height: 80vh; }
.auth-card { width: 100%; max-width: 400px; padding: 2rem; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
.auth-btn { width: 100%; padding: 10px; background-color: #42b883; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
</style>