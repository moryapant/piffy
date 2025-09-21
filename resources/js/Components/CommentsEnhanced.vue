<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from "vue";
import { useForm, Link, router } from "@inertiajs/vue3";
import UserAvatar from "@/Components/UserAvatar.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue";
import { timeAgo } from "@/utils/dateUtils";
import axios from "axios";

const props = defineProps({
  postId: {
    type: Number,
    required: true,
  },
  comments: {
    type: Array,
    default: () => [],
  },
  commentsCount: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(['comments-updated']);

// Reactive comments data for real-time updates
const commentsData = ref([...props.comments]);
const replyingTo = ref(null);
const editingComment = ref(null);
const collapsedComments = ref(new Set());
const showDeleteConfirm = ref(false);
const commentToDelete = ref(null);
const sortOrder = ref('newest'); // newest, oldest, top

// Sort comments based on selected order
const sortedComments = computed(() => {
  const topLevelComments = commentsData.value.filter(c => !c.parent_id);
  
  switch (sortOrder.value) {
    case 'oldest':
      return topLevelComments.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    case 'top':
      return topLevelComments.sort((a, b) => b.score - a.score);
    case 'newest':
    default:
      return topLevelComments.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  }
});

// Forms
const commentForm = useForm({
  content: "",
});

const replyForm = useForm({
  content: '',
  parent_id: null
});

const editForm = useForm({
  content: ''
});

// Loading states
const isSubmittingComment = ref(false);
const isSubmittingReply = ref(false);

// Mobile detection
const isMobile = ref(false);
const checkMobile = () => {
  isMobile.value = window.innerWidth < 640; // sm breakpoint
};

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
});

// Comment voting
const voteOnComment = async (commentId, voteType) => {
  try {
    const response = await axios.post(`/comments/${commentId}/vote`, {
      vote_type: voteType
    });

    if (response.data.success) {
      // Update local comment data
      const comment = findCommentById(commentId);
      if (comment) {
        Object.assign(comment, response.data.comment);
      }
    }
  } catch (error) {
    console.error('Voting failed:', error);
  }
};

const findCommentById = (id) => {
  return commentsData.value.find(c => c.id === id);
};

const getCommentVoteType = (comment) => {
  return comment.user_vote?.vote_type || null;
};

const toggleReplies = (commentId) => {
  if (collapsedComments.value.has(commentId)) {
    collapsedComments.value.delete(commentId);
  } else {
    collapsedComments.value.add(commentId);
  }
};

const submitComment = async () => {
  if (isSubmittingComment.value) return;
  
  isSubmittingComment.value = true;
  try {
    const response = await axios.post(route("posts.comments.store", props.postId), {
      content: commentForm.content
    });

    if (response.data.success) {
      // Add new comment to local state
      commentsData.value.unshift(response.data.comment);
      commentForm.reset();
      
      // Emit event for parent component to update comment count
      emit('comments-updated', commentsData.value.length);
    }
  } catch (error) {
    console.error('Error submitting comment:', error);
  } finally {
    isSubmittingComment.value = false;
  }
};

const submitReply = async (parentId) => {
  if (isSubmittingReply.value) return;
  
  isSubmittingReply.value = true;
  try {
    const response = await axios.post(route('posts.comments.reply', props.postId), {
      content: replyForm.content,
      parent_id: parentId
    });

    if (response.data.success) {
      // Add new reply to local state
      commentsData.value.push(response.data.comment);
      replyForm.reset();
      replyingTo.value = null;
      
      // Ensure parent thread is expanded to show the new reply
      collapsedComments.value.delete(parentId);
      
      // Emit event for parent component to update comment count
      emit('comments-updated', commentsData.value.length);
    }
  } catch (error) {
    console.error('Error submitting reply:', error);
  } finally {
    isSubmittingReply.value = false;
  }
};

const startEdit = (comment) => {
  editingComment.value = comment.id;
  editForm.content = comment.content;
};

const cancelEdit = () => {
  editingComment.value = null;
  editForm.reset();
};

const submitEdit = async (commentId) => {
  try {
    const response = await axios.patch(route('comments.update', commentId), {
      content: editForm.content
    });

    if (response.data.success) {
      // Update local comment data
      const comment = findCommentById(commentId);
      if (comment) {
        comment.content = editForm.content;
        comment.updated_at = new Date().toISOString();
      }
      editingComment.value = null;
      editForm.reset();
    }
  } catch (error) {
    console.error('Error updating comment:', error);
  }
};

const deleteComment = (commentId) => {
  commentToDelete.value = commentId;
  showDeleteConfirm.value = true;
};

const confirmDelete = () => {
  if (commentToDelete.value) {
    useForm().delete(route("comments.destroy", commentToDelete.value), {
      preserveScroll: true,
    });
  }
  showDeleteConfirm.value = false;
  commentToDelete.value = null;
};

const cancelDelete = () => {
  showDeleteConfirm.value = false;
  commentToDelete.value = null;
};

const formatCount = (count) => {
  if (!count || count === 0) return '0';
  if (count < 1000) return count.toString();
  if (count < 1000000) return `${Math.floor(count / 100) / 10}K`;
  return `${Math.floor(count / 100000) / 10}M`;
};

// Watch for prop changes to update local state
watch(
  () => props.comments,
  (newComments) => {
    commentsData.value = [...newComments];
  },
  { deep: true }
);
</script>

<template>
  <div class="bg-white/95 dark:bg-gray-800/95 rounded-xl sm:rounded-2xl border border-gray-200/60 dark:border-gray-700/60 shadow-xl backdrop-blur-md">
    <div class="p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 lg:space-y-8">
      <!-- Header -->
      <div class="flex flex-col space-y-3 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between pb-4 sm:pb-6 border-b border-gray-200/80 dark:border-gray-700/80">
        <div class="text-center sm:text-left">
          <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-200 bg-clip-text text-transparent">
            Discussion
          </h2>
          <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium">
            {{ commentsCount }} {{ commentsCount === 1 ? 'comment' : 'comments' }}
          </p>
        </div>
        <div class="w-full sm:w-auto">
          <select v-model="sortOrder" class="w-full sm:w-auto appearance-none bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl px-3 sm:px-4 py-2 sm:py-2.5 text-sm text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 shadow-sm font-medium">
            <option value="newest">🕒 Newest First</option>
            <option value="oldest">⏰ Oldest First</option>
            <option value="top">🔥 Top Rated</option>
          </select>
        </div>
      </div>

      <!-- Comment Form -->
      <template v-if="$page.props.auth.user">
        <div class="bg-gradient-to-br from-blue-50/50 to-indigo-50/30 dark:from-gray-800/30 dark:to-gray-700/20 rounded-xl sm:rounded-2xl p-3 sm:p-4 lg:p-6 border border-blue-100/60 dark:border-gray-700/60">
          <div class="flex flex-col sm:flex-row sm:items-start space-y-3 sm:space-y-0 sm:space-x-4">
            <!-- Mobile: Avatar and name in header -->
            <div class="flex items-center space-x-3 sm:hidden">
              <UserAvatar :username="$page.props.auth.user.name" size="md" bgColor="blue" class="ring-2 ring-white dark:ring-gray-800 shadow-md" />
              <div class="flex items-center space-x-2">
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $page.props.auth.user.name }}</span>
                <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-full">You</span>
              </div>
            </div>
            <!-- Desktop: Avatar -->
            <div class="hidden sm:flex flex-shrink-0">
              <UserAvatar :username="$page.props.auth.user.name" size="lg" bgColor="blue" class="ring-3 ring-white dark:ring-gray-800 shadow-lg" />
            </div>
            <div class="flex-grow">
              <!-- Desktop: User info -->
              <div class="hidden sm:block mb-3">
                <div class="flex items-center space-x-2">
                  <span class="font-semibold text-gray-900 dark:text-white">{{ $page.props.auth.user.name }}</span>
                  <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-full">You</span>
                </div>
              </div>
              <div class="relative">
                <textarea v-model="commentForm.content" :rows="isMobile ? 3 : 4" placeholder="Share your thoughts... What do you think about this?"
                  class="w-full px-3 sm:px-4 py-3 sm:py-4 text-sm sm:text-base bg-white/80 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-600 rounded-xl sm:rounded-2xl resize-none focus:ring-2 focus:ring-blue-500/70 transition-all shadow-sm hover:shadow-md" 
                  style="min-height: 80px;"></textarea>
                <div class="absolute bottom-2 sm:bottom-3 right-2 sm:right-3 text-xs text-gray-400 pointer-events-none">
                  {{ commentForm.content.length }}/1000
                </div>
              </div>
              <!-- Mobile: Stack layout for footer -->
              <div class="flex flex-col space-y-3 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between mt-3 sm:mt-4">
                <div class="flex items-center justify-center sm:justify-start space-x-2 text-xs text-gray-500 dark:text-gray-400 order-2 sm:order-1">
                  <svg class="w-3 sm:w-4 h-3 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="hidden sm:inline">Be respectful and constructive</span>
                  <span class="sm:hidden">Be respectful</span>
                </div>
                <button @click="submitComment" :disabled="isSubmittingComment || !commentForm.content.trim() || commentForm.content.length > 1000"
                  class="w-full sm:w-auto flex items-center justify-center px-4 sm:px-6 py-2.5 sm:py-3 space-x-2 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 hover:scale-[1.02] shadow-md hover:shadow-lg transition-all order-1 sm:order-2">
                  <svg v-if="isSubmittingComment" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                  </svg>
                  <span>{{ isSubmittingComment ? "Posting..." : "Post Comment" }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- Login Prompt -->
      <template v-else>
        <div class="relative bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-gray-800 dark:to-gray-700 rounded-xl sm:rounded-2xl p-4 sm:p-6 lg:p-8 border border-indigo-100/60 dark:border-gray-600/60 shadow-lg">
          <div class="text-center">
            <div class="inline-flex items-center justify-center w-12 sm:w-16 h-12 sm:h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl sm:rounded-2xl shadow-lg mb-3 sm:mb-4">
              <svg class="w-6 sm:w-8 h-6 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
            </div>
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2">Join the Conversation</h3>
            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 mb-4 sm:mb-6 px-2">Share your thoughts and connect with others</p>
            <div class="flex flex-col space-y-3 sm:space-y-0 sm:flex-row sm:justify-center sm:gap-4">
              <Link :href="route('login')" class="flex items-center justify-center px-6 sm:px-8 py-3 sm:py-3.5 text-sm sm:text-base font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:scale-[1.02] shadow-lg hover:shadow-xl transition-all touch-manipulation">
                <svg class="w-4 sm:w-5 h-4 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Sign In
              </Link>
              <Link :href="route('register')" class="flex items-center justify-center px-6 sm:px-8 py-3 sm:py-3.5 text-sm sm:text-base font-semibold text-gray-700 dark:text-gray-200 bg-white/80 dark:bg-gray-700/60 rounded-xl border border-gray-200 dark:border-gray-600 hover:scale-[1.02] shadow-md hover:shadow-lg transition-all touch-manipulation">
                <svg class="w-4 sm:w-5 h-4 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Create Account
              </Link>
            </div>
          </div>
        </div>
      </template>

      <!-- Comments List -->
      <div v-if="sortedComments.length > 0" class="space-y-4 sm:space-y-6">
        <div v-for="comment in sortedComments" :key="comment.id" 
          class="group relative bg-white/70 dark:bg-gray-800/70 backdrop-blur-md rounded-xl sm:rounded-2xl border border-gray-200/50 dark:border-gray-700/50 hover:border-blue-300/60 dark:hover:border-blue-600/60 hover:shadow-2xl hover:sm:-translate-y-1 shadow-lg transition-all">
          <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-500 to-indigo-600 rounded-l-xl sm:rounded-l-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
          <div class="p-3 sm:p-4 lg:p-6">
            <div class="flex items-start space-x-3 sm:space-x-4">
              <UserAvatar :username="comment.user.name" :size="isMobile ? 'md' : 'lg'" bgColor="blue" class="ring-2 ring-white dark:ring-gray-800 shadow-md flex-shrink-0" />
              <div class="flex-grow min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-center gap-y-1 sm:gap-x-3 mb-2 sm:mb-3">
                  <span class="font-bold text-sm sm:text-base text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 cursor-pointer transition-colors truncate">{{ comment.user.name }}</span>
                  <div class="flex items-center space-x-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                    <span class="w-1 h-1 bg-gray-400 rounded-full hidden sm:block"></span>
                    <time class="font-medium">{{ timeAgo(comment.created_at) }}</time>
                    <span v-if="comment.created_at !== comment.updated_at" class="inline-flex items-center px-1.5 sm:px-2 py-0.5 text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 rounded-full">
                      <svg class="w-2.5 sm:w-3 h-2.5 sm:h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      edited
                    </span>
                  </div>
                </div>

                <!-- Comment Content -->
                <div v-if="editingComment !== comment.id" class="text-gray-800 dark:text-gray-200 mb-3 sm:mb-4 leading-relaxed">
                  <p class="text-sm sm:text-base lg:text-lg break-words">{{ comment.content }}</p>
                </div>

                <!-- Edit Form -->
                <div v-else class="mb-3 sm:mb-4">
                  <textarea v-model="editForm.content" rows="3" class="w-full px-3 py-2 text-sm sm:text-base text-gray-700 dark:text-gray-200 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                  <div class="flex flex-col sm:flex-row justify-end mt-2 space-y-2 sm:space-y-0 sm:space-x-2">
                    <button @click="cancelEdit" class="w-full sm:w-auto px-3 py-2 sm:py-1.5 text-xs sm:text-sm font-medium text-gray-600 hover:text-gray-700 transition-colors order-2 sm:order-1">Cancel</button>
                    <button @click="submitEdit(comment.id)" :disabled="editForm.processing" class="w-full sm:w-auto px-3 py-2 sm:py-1.5 text-xs sm:text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 order-1 sm:order-2">Save</button>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col space-y-3 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between pt-3 border-t border-gray-100/60 dark:border-gray-700/60">
                  <!-- Voting -->
                  <div v-if="$page.props.auth.user" class="flex items-center justify-center sm:justify-start bg-gray-50 dark:bg-gray-800 rounded-xl sm:rounded-2xl px-2 py-1.5 shadow-sm border border-gray-200/50 dark:border-gray-700/50">
                    <button @click="voteOnComment(comment.id, getCommentVoteType(comment) === 1 ? 0 : 1)" 
                      :class="['p-2 rounded-lg sm:rounded-xl transition-all touch-manipulation', getCommentVoteType(comment) === 1 ? 'bg-emerald-500 text-white shadow-md' : 'text-gray-500 hover:bg-emerald-50 hover:text-emerald-600']">
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                    </button>
                    <span class="px-2 sm:px-3 py-1 text-sm font-bold text-gray-800 dark:text-gray-200 min-w-[2rem] text-center">{{ comment.score || 0 }}</span>
                    <button @click="voteOnComment(comment.id, getCommentVoteType(comment) === -1 ? 0 : -1)" 
                      :class="['p-2 rounded-lg sm:rounded-xl transition-all touch-manipulation', getCommentVoteType(comment) === -1 ? 'bg-red-500 text-white shadow-md' : 'text-gray-500 hover:bg-red-50 hover:text-red-600']">
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                    </button>
                  </div>
                  <div v-else class="flex justify-center sm:justify-start">
                    <div class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-xl">
                      <span class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-300">{{ comment.score || 0 }} points</span>
                    </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="flex items-center justify-center sm:justify-end gap-1 sm:gap-2">
                    <button v-if="$page.props.auth.user" @click="replyingTo = replyingTo === comment.id ? null : comment.id" 
                      class="inline-flex items-center space-x-1 sm:space-x-2 text-xs font-medium text-blue-500/80 sm:opacity-0 sm:group-hover:opacity-100 transition-all hover:text-blue-600 bg-blue-50/50 px-2 sm:px-3 py-1.5 rounded-full touch-manipulation">
                      <svg class="w-3 h-3 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                      </svg>
                      <span>Reply</span>
                    </button>
                    <button v-if="$page.props.auth.user && $page.props.auth.user.id === comment.user_id" @click="startEdit(comment)" 
                      class="inline-flex items-center space-x-1 sm:space-x-2 text-xs font-medium text-gray-500/80 sm:opacity-0 sm:group-hover:opacity-100 transition-all hover:text-gray-600 px-2 sm:px-3 py-1.5 rounded-full touch-manipulation">
                      <svg class="w-3 h-3 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      <span>Edit</span>
                    </button>
                    <button v-if="$page.props.auth.user && $page.props.auth.user.id === comment.user_id" @click="deleteComment(comment.id)" 
                      class="inline-flex items-center space-x-1 sm:space-x-2 text-xs font-medium text-red-500/80 sm:opacity-0 sm:group-hover:opacity-100 transition-all hover:text-red-600 px-2 sm:px-3 py-1.5 rounded-full touch-manipulation">
                      <svg class="w-3 h-3 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      <span>Delete</span>
                    </button>
                  </div>
                </div>

                <!-- Reply Form -->
                <div v-if="replyingTo === comment.id && $page.props.auth.user" class="mt-3 sm:mt-4 pl-2 sm:pl-8 border-l-2 border-blue-100">
                  <div class="flex flex-col sm:flex-row sm:items-start space-y-3 sm:space-y-0 sm:space-x-4">
                    <!-- Mobile: User info header -->
                    <div class="flex items-center space-x-3 sm:hidden">
                      <UserAvatar :username="$page.props.auth.user.name" size="sm" bgColor="blue" />
                      <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $page.props.auth.user.name }}</span>
                    </div>
                    <!-- Desktop: Avatar -->
                    <div class="hidden sm:flex flex-shrink-0">
                      <UserAvatar :username="$page.props.auth.user.name" size="sm" bgColor="blue" />
                    </div>
                    <div class="flex-grow">
                      <textarea v-model="replyForm.content" rows="2" placeholder="Write a reply..." class="px-3 sm:px-4 py-2 w-full text-sm rounded-xl border border-gray-200 resize-none focus:ring-2 focus:ring-blue-500 transition-all"></textarea>
                      <div class="flex flex-col sm:flex-row justify-end mt-2 space-y-2 sm:space-y-0 sm:space-x-2">
                        <button @click="replyingTo = null" class="w-full sm:w-auto px-3 py-2 sm:py-1.5 text-xs sm:text-sm font-medium text-gray-600 hover:text-gray-700 transition-colors order-2 sm:order-1">Cancel</button>
                        <button @click="submitReply(comment.id)" :disabled="isSubmittingReply || !replyForm.content.trim()" 
                          class="w-full sm:w-auto inline-flex items-center justify-center px-3 py-2 sm:py-1.5 text-xs sm:text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-500 rounded-xl hover:from-blue-700 hover:to-blue-600 disabled:opacity-50 shadow-sm order-1 sm:order-2">
                          <span>{{ isSubmittingReply ? 'Posting...' : 'Post Reply' }}</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Replies -->
                <div v-if="commentsData.filter(r => r.parent_id === comment.id).length > 0" class="mt-3 sm:mt-4 space-y-3 sm:space-y-4 pl-2 sm:pl-8 border-l-2 border-gray-100">
                  <div v-for="reply in commentsData.filter(r => r.parent_id === comment.id)" :key="reply.id" 
                    class="group p-3 sm:p-4 rounded-lg sm:rounded-xl border border-gray-100/60 hover:border-blue-200/60 hover:shadow-md bg-white/90 dark:bg-gray-800/90 shadow-sm transition-all">
                    <div class="flex items-start space-x-2 sm:space-x-3">
                      <UserAvatar :username="reply.user.name" size="sm" bgColor="blue" class="flex-shrink-0" />
                      <div class="flex-grow min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-y-1 sm:gap-x-2 mb-1">
                          <span class="font-semibold text-xs sm:text-sm text-gray-900 dark:text-white truncate">{{ reply.user.name }}</span>
                          <span class="text-xs text-gray-500">{{ timeAgo(reply.created_at) }}</span>
                        </div>
                        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed break-words mb-2">{{ reply.content }}</div>
                        <div class="flex items-center justify-between sm:justify-start">
                          <div v-if="$page.props.auth.user" class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-full px-1.5 py-0.5">
                            <button @click="voteOnComment(reply.id, getCommentVoteType(reply) === 1 ? 0 : 1)" 
                              :class="['p-1 rounded-full transition-all touch-manipulation', getCommentVoteType(reply) === 1 ? 'bg-green-500 text-white' : 'text-gray-500 hover:bg-green-100 hover:text-green-600']">
                              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                              </svg>
                            </button>
                            <span class="px-1 text-xs font-medium text-gray-700 dark:text-gray-200 min-w-[1.5rem] text-center">{{ reply.score || 0 }}</span>
                            <button @click="voteOnComment(reply.id, getCommentVoteType(reply) === -1 ? 0 : -1)" 
                              :class="['p-1 rounded-full transition-all touch-manipulation', getCommentVoteType(reply) === -1 ? 'bg-red-500 text-white' : 'text-gray-500 hover:bg-red-100 hover:text-red-600']">
                              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                              </svg>
                            </button>
                          </div>
                          <!-- Mobile delete button for replies -->
                          <button v-if="$page.props.auth.user && $page.props.auth.user.id === reply.user_id" @click="deleteComment(reply.id)" 
                            class="sm:hidden inline-flex items-center space-x-1 text-xs font-medium text-red-500/80 px-2 py-1 rounded-full touch-manipulation">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Delete</span>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Comments -->
      <div v-else class="py-8 sm:py-12 text-center">
        <div class="p-4 sm:p-6 lg:p-8 mx-auto max-w-sm sm:max-w-md bg-gray-50 dark:bg-gray-700 rounded-xl">
          <svg class="mx-auto mb-3 sm:mb-4 w-8 sm:w-10 lg:w-12 h-8 sm:h-10 lg:h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
          <h3 class="mb-2 text-sm sm:text-base lg:text-lg font-medium text-gray-900 dark:text-white">No comments yet</h3>
          <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 px-2">Be the first to share what you think!</p>
        </div>
      </div>
    </div>

    <ConfirmModal :isOpen="showDeleteConfirm" title="Delete Comment" 
      message="Are you sure you want to delete this comment?" 
      confirmText="Delete" cancelText="Cancel" 
      @confirm="confirmDelete" @cancel="showDeleteConfirm = false" />
  </div>
</template>
