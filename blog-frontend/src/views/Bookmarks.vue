<template>
  <div class="container">
    <h2>My Saved Posts</h2>
    
    <div v-if="loading" class="loading">Loading...</div>
    
    <div v-else-if="posts.length === 0" class="empty">
      <p>You haven't saved any posts yet.</p>
      <router-link to="/">Go to Feed</router-link>
    </div>
    
    <div v-else class="feed">
      <PostItem 
        v-for="post in posts" 
        :key="post.id" 
        :post="post" 
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../axios';
import PostItem from '../components/PostItem.vue';

const posts = ref([]);
const loading = ref(true);

onMounted(async () => {
  try {
    const res = await axios.get('/my-bookmarks');
    posts.value = res.data;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.container { max-width: 800px; margin: 0 auto; padding: 20px; }
.loading, .empty { text-align: center; margin-top: 50px; color: #666; }
</style>