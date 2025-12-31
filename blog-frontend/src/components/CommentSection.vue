<template>
  <div class="comments-section">
    <h4>Comments</h4>
    
    <div v-for="comment in comments" :key="comment.id" class="comment">
      <div class="comment-header">
        <strong>{{ comment.user.name }}</strong>
        <span class="date">{{ formatDate(comment.created_at) }}</span>
        <button v-if="isOwner(comment)" @click="deleteComment(comment.id)" class="delete-btn">x</button>
      </div>
      <p>{{ comment.content }}</p>

      <div v-if="comment.replies && comment.replies.length > 0" class="replies">
         <div v-for="reply in comment.replies" :key="reply.id" class="comment reply">
            <strong>{{ reply.user.name }}</strong>: {{ reply.content }}
         </div>
      </div>

      <button 
        v-if="currentUser && activeReplyId !== comment.id" 
        @click="activeReplyId = comment.id" 
        class="text-btn">
        Reply
      </button>
      
      <div v-if="activeReplyId === comment.id" class="reply-form">
        <input v-model="replyContent" placeholder="Write a reply..." />
        <button @click="submitReply(comment.id)">Send</button>
        <button @click="activeReplyId = null" class="cancel-btn">Cancel</button>
      </div>
    </div>

    <div v-if="currentUser" class="add-comment">
      <input v-model="newComment" placeholder="Write a comment..." @keyup.enter="submitComment" />
    </div>
    
    <div v-else style="margin-top: 10px; color: #777; font-style: italic;">
      <router-link to="/login">Login</router-link> to post a comment.
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from '../axios';

const props = defineProps(['post']);
const comments = ref(props.post.comments || []);
const newComment = ref('');
const replyContent = ref('');
const activeReplyId = ref(null);

// Safe check for user (returns null if guest)
const currentUser = JSON.parse(localStorage.getItem('user')) || null;

const formatDate = (date) => new Date(date).toLocaleDateString();
// Update isOwner to check if currentUser exists first
const isOwner = (comment) => currentUser && comment.user_id === currentUser.id;

// ... keep your submitComment, submitReply, and deleteComment functions exactly as they were ...
const submitComment = async () => {
  if (!newComment.value) return;
  try {
    const res = await axios.post(`/posts/${props.post.id}/comments`, { content: newComment.value });
    comments.value.push(res.data);
    newComment.value = '';
  } catch (e) {
    alert('Failed to post comment');
  }
};

const submitReply = async (parentId) => {
  if (!replyContent.value) return;
  try {
    const res = await axios.post(`/posts/${props.post.id}/comments`, { 
      content: replyContent.value,
      parent_id: parentId
    });
    const parent = comments.value.find(c => c.id === parentId);
    if(parent) {
      if(!parent.replies) parent.replies = [];
      parent.replies.push(res.data);
    }
    activeReplyId.value = null;
    replyContent.value = '';
  } catch (e) {
    alert('Failed to reply');
  }
};

const deleteComment = async (id) => {
  if(!confirm('Delete comment?')) return;
  await axios.delete(`/comments/${id}`);
  comments.value = comments.value.filter(c => c.id !== id);
};
</script>

<style scoped>
/* Keep your existing styles */
.comments-section { margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px; }
.comment { padding: 8px 0; border-bottom: 1px solid #f9f9f9; }
.replies { margin-left: 20px; border-left: 2px solid #ddd; padding-left: 10px; }
.text-btn { background: none; color: #42b883; padding: 0; font-size: 0.8rem; cursor: pointer; }
.delete-btn { background: none; color: red; border: none; float: right; cursor: pointer; }
.date { font-size: 0.7rem; color: #888; margin-left: 10px; }
.add-comment { margin-top: 15px; }
input { padding: 5px; width: 100%; box-sizing: border-box; }
</style>