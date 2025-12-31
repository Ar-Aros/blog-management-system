<template>
  <div>
    <nav class="navbar">
      <div class="nav-content">
        <h1 class="brand-logo">MyBlog</h1>
        
        <div v-if="user" class="nav-actions">
          <router-link to="/bookmarks" class="nav-link">
             🔖 Saved
          </router-link>

          <div class="user-chip">
            <span class="user-name">{{ user.name }}</span>
            <img 
              :src="user.profile_picture 
                    ? `http://localhost:8000/storage/${user.profile_picture}` 
                    : 'https://cdn-icons-png.flaticon.com/512/149/149071.png'" 
              class="nav-avatar" 
            />
          </div>

          <button @click="logout" class="logout-icon-btn" title="Logout">
            ⏻
          </button>
        </div>

        <div v-else class="nav-actions">
          <router-link to="/login" class="nav-btn-ghost">Login</router-link>
          <router-link to="/register" class="nav-btn-primary">Get Started</router-link>
        </div>
      </div>
    </nav>

    <div class="container">
      
      <div v-if="user" class="create-card">
        <div class="card-header">
          <div class="avatar-placeholder">
            {{ user.name.charAt(0).toUpperCase() }}
          </div>
          <h2>{{ isEditing ? 'Edit Your Post' : 'Create New Post' }}</h2>
        </div>
        
        <div class="form-body">
          <div class="input-group">
            <input 
              v-model="form.title" 
              placeholder="Give your post a catchy title..." 
              class="modern-input title-input" 
            />
          </div>

          <div class="input-group">
            <textarea 
              v-model="form.content" 
              placeholder="What's on your mind today?" 
              class="modern-input content-input"
            ></textarea>
          </div>
          
          <div class="options-row">
            <div class="select-wrapper">
              <select v-model="form.visibility" class="modern-select">
                <option value="public">🌍 Public</option>
                <option value="private">🔒 Private</option>
              </select>
            </div>
            
            <input 
              v-model="form.tags" 
              placeholder="#tags (e.g. tech, life)" 
              class="modern-input tags-input" 
            />
          </div>

          <div class="action-footer">
            <label class="file-upload-btn">
              <input type="file" @change="handleFile" class="hidden-file-input" />
              <span>📷 {{ form.image ? 'Image Selected' : 'Add Image' }}</span>
            </label>
            
            <div class="button-group">
              <button v-if="isEditing" @click="cancelEdit" class="cancel-btn">
                Cancel
              </button>
              <button @click="createPost" :disabled="loading" class="publish-btn">
                {{ isEditing ? 'Update Post' : (loading ? 'Posting...' : '🚀 Publish') }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="search-bar">
        <input v-model="searchQuery" placeholder="Search posts..." />
      </div>

      <div v-if="posts.length">
        <PostItem 
  v-for="post in filteredPosts" 
  :key="post.id" 
  :post="post" 
  @delete="removePost"
  @edit="startEdit"  
/>
      </div>
      <div v-else class="empty-state">No posts found. Start writing!</div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from '../axios';
import PostItem from '../components/PostItem.vue';
import { useRouter } from 'vue-router';

const router = useRouter();

// --- STATE ---
const posts = ref([]);
const loading = ref(false);
const searchQuery = ref('');
// Safe user parsing
const user = JSON.parse(localStorage.getItem('user')) || null;

// Form State
const isEditing = ref(false);
const editingId = ref(null);
const form = ref({
  title: '',
  content: '',
  visibility: 'public',
  tags: '',
  image: null
});

// --- API ACTIONS ---

// 1. Fetch Posts
const fetchPosts = async () => {
  try {
    const response = await axios.get('/posts');
    posts.value = response.data;
  } catch (error) {
    console.error("Error fetching posts", error);
  }
};

// 2. Handle File Selection
const handleFile = (event) => {
  form.value.image = event.target.files[0];
};

// 3. Create or Update Post
const createPost = async () => {
  if(!form.value.title || !form.value.content) return alert('Title and Content required');
  
  loading.value = true;
  
  const formData = new FormData();
  formData.append('title', form.value.title);
  formData.append('content', form.value.content);
  formData.append('visibility', form.value.visibility);
  formData.append('tags', form.value.tags); 
  
  if (form.value.image) {
    formData.append('image', form.value.image);
  }

  try {
    if (isEditing.value) {
        // --- UPDATE MODE ---
        formData.append('_method', 'PUT'); // Required for Laravel file uploads on PUT
        const res = await axios.post(`/posts/${editingId.value}`, formData, {
             headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        // Update local list instantly
        const index = posts.value.findIndex(p => p.id === editingId.value);
        if(index !== -1) posts.value[index] = res.data;
        
        cancelEdit();
    } else {
        // --- CREATE MODE ---
        await axios.post('/posts', formData, {
             headers: { 'Content-Type': 'multipart/form-data' }
        });
        await fetchPosts(); 
        cancelEdit(); // Resets form
    }
  } catch (e) {
    console.error(e);
    alert('Failed to save post.');
  } finally {
    loading.value = false;
  }
};

// 4. Delete Post
const removePost = (id) => {
  posts.value = posts.value.filter(p => p.id !== id);
};

// 5. Start Editing (Populate Form)
const startEdit = (post) => {
    isEditing.value = true;
    editingId.value = post.id;
    
    // Fill the form
    form.value = {
        title: post.title,
        content: post.content,
        visibility: post.visibility,
        // Convert tags array [{name:'tech'}] -> string "tech"
        tags: post.tags ? post.tags.map(t => t.name).join(', ') : '', 
        image: null 
    };
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// 6. Cancel Editing
const cancelEdit = () => {
    isEditing.value = false;
    editingId.value = null;
    form.value = { title: '', content: '', visibility: 'public', tags: '', image: null };
};

// 7. Logout
const logout = async () => {
  try {
    await axios.post('/logout');
  } catch (e) {
    console.error(e);
  } finally {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    router.push('/login');
  }
};

// --- COMPUTED ---
const filteredPosts = computed(() => {
  if (!searchQuery.value) return posts.value;
  return posts.value.filter(post => 
    post.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    post.content.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

// Load posts on startup
onMounted(fetchPosts);
</script>


<style scoped>
/* Main Container */
.container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

/* --- NEW CREATE CARD STYLES --- */
.create-card {
  background: white;
  border-radius: 16px;
  padding: 25px;
  margin-bottom: 30px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  border: 1px solid #f0f0f0;
  transition: transform 0.2s;
}

.card-header {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 20px;
  border-bottom: 1px solid #f7f7f7;
  padding-bottom: 15px;
}

.avatar-placeholder {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #42b883, #35495e);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.2rem;
}

.create-card h2 {
  font-size: 1.2rem;
  color: #333;
  margin: 0;
  font-weight: 600;
}

/* Inputs */
.modern-input {
  width: 100%;
  background: #f9f9f9;
  border: 1px solid #eee;
  border-radius: 10px;
  padding: 15px;
  font-size: 1rem;
  transition: all 0.3s ease;
  box-sizing: border-box;
  margin-bottom: 15px;
}

.modern-input:focus {
  background: white;
  border-color: #42b883;
  outline: none;
  box-shadow: 0 0 0 4px rgba(66, 184, 131, 0.1);
}

.title-input {
  font-weight: bold;
  font-size: 1.1rem;
}

.content-input {
  min-height: 120px;
  resize: vertical;
}

/* Options Row */
.options-row {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}

.select-wrapper {
  position: relative;
  min-width: 120px;
}

.modern-select {
  width: 100%;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #eee;
  background: white;
  font-size: 0.95rem;
  cursor: pointer;
}

.tags-input {
  margin-bottom: 0; /* Override default */
}

/* Footer Actions */
.action-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 10px;
}

/* Custom File Upload Button */
.hidden-file-input {
  display: none;
}

.file-upload-btn {
  display: inline-flex;
  align-items: center;
  cursor: pointer;
  color: #666;
  font-size: 0.95rem;
  padding: 8px 12px;
  border-radius: 8px;
  background: #f5f5f5;
  transition: background 0.2s;
}

.file-upload-btn:hover {
  background: #e0e0e0;
  color: #333;
}

/* Buttons */
.button-group {
  display: flex;
  gap: 10px;
}

.publish-btn {
  background: #42b883;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 30px;
  font-weight: bold;
  font-size: 1rem;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(66, 184, 131, 0.3);
  transition: transform 0.2s, box-shadow 0.2s;
}

.publish-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(66, 184, 131, 0.4);
}

.publish-btn:disabled {
  background: #a8d5c2;
  cursor: not-allowed;
  transform: none;
}

.cancel-btn {
  background: transparent;
  color: #888;
  border: none;
  padding: 10px 20px;
  cursor: pointer;
  font-size: 0.95rem;
}

.cancel-btn:hover {
  color: #555;
  text-decoration: underline;
}

/* Search Bar (Keep consistent) */
.search-bar { margin-bottom: 20px; }
.search-bar input {
  width: 100%;
  padding: 15px;
  border-radius: 30px;
  border: 1px solid #ddd;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

/* --- MODERN NAVBAR STYLES --- */
.navbar {
  background: white;
  box-shadow: 0 2px 15px rgba(0,0,0,0.05);
  position: sticky;
  top: 0;
  z-index: 100;
  padding: 0 20px;
}

.nav-content {
  max-width: 800px; /* Matches your container width */
  margin: 0 auto;
  height: 70px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.brand-logo {
  font-size: 1.5rem;
  font-weight: 800;
  color: #333; /* Or your brand color */
  margin: 0;
  letter-spacing: -1px;
}

.nav-actions {
  display: flex;
  align-items: center;
  gap: 20px;
}

/* Links */
.nav-link {
  color: #666;
  text-decoration: none;
  font-weight: 500;
  font-size: 0.95rem;
  transition: color 0.2s;
  display: flex;
  align-items: center;
  gap: 5px;
}

.nav-link:hover {
  color: #42b883;
}

/* User Chip (Name + Avatar) */
.user-chip {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 5px 10px;
  background: #f5f7fa;
  border-radius: 30px;
}

.user-name {
  font-weight: 600;
  color: #333;
  font-size: 0.95rem;
}

.nav-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid white;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Logout Button */
.logout-icon-btn {
  background: none;
  border: none;
  font-size: 1.2rem;
  color: #e74c3c;
  cursor: pointer;
  padding: 5px;
  border-radius: 50%;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logout-icon-btn:hover {
  background: #fde8e7;
}

/* Guest Buttons */
.nav-btn-ghost {
  color: #333;
  text-decoration: none;
  font-weight: 600;
  margin-right: 10px;
}

.nav-btn-primary {
  background: #42b883;
  color: white;
  text-decoration: none;
  padding: 10px 20px;
  border-radius: 20px;
  font-weight: 600;
  transition: background 0.2s;
}

.nav-btn-primary:hover {
  background: #3aa876;
}

</style>