<template>
  <div class="register-container">
    <div class="register-card">
      <div class="card-header">
        <h2>Create Account</h2>
        <p>Join our community to start blogging</p>
      </div>

      <form @submit.prevent="handleRegister">
        
        <div class="form-group">
          <label>Full Name</label>
          <input v-model="form.name" type="text" placeholder="e.g. John Doe" required class="input-field" />
        </div>

        <div class="form-group">
          <label>Username</label>
          <input v-model="form.username" type="text" placeholder="e.g. johnd" required class="input-field" />
        </div>

        <div class="form-group">
          <label>Email Address</label>
          <input v-model="form.email" type="email" placeholder="john@example.com" required class="input-field" />
        </div>

        <div class="form-row">
          <div class="form-group half">
            <label>Password</label>
            <input v-model="form.password" type="password" placeholder="******" required class="input-field" />
          </div>
          <div class="form-group half">
            <label>Confirm</label>
            <input v-model="form.password_confirmation" type="password" placeholder="******" required class="input-field" />
          </div>
        </div>

        <div class="form-group file-group">
          <label>Profile Picture (Optional)</label>
          <input type="file" @change="handleFile" accept="image/*" class="file-input" />
          <small>Max size: 2MB</small>
        </div>

        <div v-if="errorMessage" class="error-msg">
          {{ errorMessage }}
        </div>

        <button type="submit" :disabled="loading" class="submit-btn">
          {{ loading ? 'Creating Account...' : 'Sign Up' }}
        </button>

        <p class="login-link">
          Already have an account? <router-link to="/login">Login here</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from '../axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const file = ref(null);
const form = ref({
  name: '',
  username: '',
  email: '',
  password: '',
  password_confirmation: ''
});
const loading = ref(false);
const errorMessage = ref('');

const handleFile = (event) => {
  file.value = event.target.files[0];
};

const handleRegister = async () => {
  loading.value = true;
  errorMessage.value = '';

  const formData = new FormData();
  formData.append('name', form.value.name);
  formData.append('username', form.value.username);
  formData.append('email', form.value.email);
  formData.append('password', form.value.password);
  formData.append('password_confirmation', form.value.password_confirmation);
  
  if (file.value) {
    formData.append('image', file.value);
  }

  try {
    const response = await axios.post('/register', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    localStorage.setItem('token', response.data.token);
    localStorage.setItem('user', JSON.stringify(response.data.user));
    router.push('/');
  } catch (error) {
    console.error(error);
    // Display specific validation error or general message
    if(error.response && error.response.data.errors) {
       const firstKey = Object.keys(error.response.data.errors)[0];
       errorMessage.value = error.response.data.errors[firstKey][0];
    } else {
       errorMessage.value = error.response?.data?.message || 'Registration failed.';
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Page Background */
.register-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 20px;
}

/* Card Style */
.register-card {
  background: white;
  width: 100%;
  max-width: 450px;
  padding: 2.5rem;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.card-header {
  text-align: center;
  margin-bottom: 2rem;
}

.card-header h2 {
  margin: 0;
  color: #333;
  font-size: 1.8rem;
}

.card-header p {
  margin-top: 5px;
  color: #666;
}

/* Form Styles */
.form-group {
  margin-bottom: 1.2rem;
}

.form-row {
  display: flex;
  gap: 15px;
}

.form-group.half {
  flex: 1;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  color: #444;
  font-weight: 500;
  font-size: 0.9rem;
}

.input-field {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s;
  box-sizing: border-box; /* Ensures padding doesn't break layout */
}

.input-field:focus {
  outline: none;
  border-color: #42b883;
  box-shadow: 0 0 0 3px rgba(66, 184, 131, 0.1);
}

/* File Input */
.file-group {
  background: #f9f9f9;
  padding: 10px;
  border-radius: 8px;
  border: 1px dashed #ccc;
}
.file-input {
  width: 100%;
  font-size: 0.9rem;
}
small {
  display: block;
  margin-top: 5px;
  color: #888;
  font-size: 0.8rem;
}

/* Button */
.submit-btn {
  width: 100%;
  padding: 14px;
  background-color: #42b883;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1.1rem;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.3s;
  margin-top: 10px;
}

.submit-btn:hover:not(:disabled) {
  background-color: #3aa876;
}

.submit-btn:disabled {
  background-color: #a8d5c2;
  cursor: not-allowed;
}

/* Error Message */
.error-msg {
  color: #e74c3c;
  background: #fde8e7;
  padding: 10px;
  border-radius: 6px;
  margin-bottom: 15px;
  text-align: center;
  font-size: 0.9rem;
}

/* Link */
.login-link {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.9rem;
  color: #666;
}

.login-link a {
  color: #42b883;
  text-decoration: none;
  font-weight: bold;
}
</style>