<template>
  <div class="post-card">
    <div class="post-header">
      <div class="user-details">
        <h3>{{ post.title }}</h3>
        <small>By {{ post.user.name }} • {{ new Date(post.created_at).toLocaleDateString() }}</small>
      </div>
      
      <div v-if="isOwner" class="owner-actions">
        <button @click="$emit('edit', post)" class="icon-btn edit-btn" title="Edit">
          ✎
        </button>
        <button @click="deletePost" class="icon-btn delete-btn" title="Delete">
          🗑️
        </button>
      </div>
    </div>

    <span class="badge" :class="post.visibility">{{ post.visibility }}</span>

    <p class="content">{{ post.content }}</p>
    
    <img v-if="post.image" :src="`http://localhost:8000/storage/${post.image}`" class="post-img" />

    <div class="tags" v-if="post.tags && post.tags.length">
      <span v-for="tag in post.tags" :key="tag.id" class="tag">#{{ tag.name }}</span>
    </div>

    <div class="actions">
      <template v-if="currentUser">
          <button @click="toggleLike" :class="{ liked: isLiked }">
            {{ isLiked ? '♥ Liked' : '♡ Like' }} ({{ likesCount }})
          </button>

          <button @click="toggleBookmark" :class="{ bookmarked: isBookmarked }">
            {{ isBookmarked ? '★ Saved' : '☆ Save' }}
          </button>
      </template>

      <span v-else style="color: #777; font-size: 0.9rem;">
        ♥ {{ likesCount }} Likes
      </span>

      <button @click="showComments = !showComments">
        💬 Comments ({{ post.comments ? post.comments.length : 0 }})
      </button>
    </div>

    <CommentSection v-if="showComments" :post="post" />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from '../axios';
import CommentSection from './CommentSection.vue';

const props = defineProps(['post']);
// Define Emits so we can tell the Parent (Home.vue) to remove the post from the list
const emit = defineEmits(['delete']); 

const currentUser = JSON.parse(localStorage.getItem('user')) || null;

// Check if current user owns this post
const isOwner = computed(() => currentUser && currentUser.id === props.post.user_id);

// States
const isLiked = ref(currentUser ? props.post.likes.some(l => l.user_id === currentUser.id) : false);
const likesCount = ref(props.post.likes_count); // Fix: use snake_case from API
const isBookmarked = ref(props.post.is_bookmarked); // The API already handles false for guests
const showComments = ref(false);

// ... keep toggleLike function ...

const toggleBookmark = async () => {
  isBookmarked.value = !isBookmarked.value; // Optimistic update
  try {
    await axios.post(`/posts/${props.post.id}/bookmark`);
  } catch (e) {
    isBookmarked.value = !isBookmarked.value; // Revert on error
  }
};

const deletePost = async () => {
  if(!confirm("Are you sure you want to delete this post?")) return;
  
  try {
    await axios.delete(`/posts/${props.post.id}`);
    // Tell parent component to remove this post from the list
    emit('delete', props.post.id); 
  } catch (e) {
    alert("Failed to delete post");
  }
};
</script>

<style scoped>
.post-card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.post-header { display: flex; justify-content: space-between; align-items: center; }
.badge { padding: 2px 8px; border-radius: 10px; font-size: 0.8rem; text-transform: uppercase; }
.badge.public { background: #e3f2fd; color: #1565c0; }
.badge.private { background: #ffebee; color: #c62828; }
.post-img { max-width: 100%; border-radius: 8px; margin-top: 10px; }
.actions { margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px; display: flex; gap: 10px; }
.actions button { background: none; color: #555; border: 1px solid #ddd; padding: 5px 10px; }
.actions button.liked { color: #e91e63; border-color: #e91e63; }
.tag { color: #42b883; margin-right: 5px; font-weight: bold; }
.owner-actions {
  display: flex;
  gap: 10px;
}
.icon-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  padding: 5px;
  border-radius: 50%;
  transition: background 0.2s;
}
.edit-btn { color: #f39c12; }
.edit-btn:hover { background: #fef5e7; }
.delete-btn { color: #e74c3c; }
.delete-btn:hover { background: #fde8e7; }
</style>