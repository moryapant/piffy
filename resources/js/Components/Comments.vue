<script setup>
import { ref } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import UserAvatar from "@/Components/UserAvatar.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue";
import { timeAgo } from "@/utils/dateUtils";

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

const replyingTo = ref(null);
const collapsedComments = ref(new Set());
const showDeleteConfirm = ref(false);
const commentToDelete = ref(null);

const toggleReplies = (commentId) => {
  if (collapsedComments.value.has(commentId)) {
    collapsedComments.value.delete(commentId);
  } else {
    collapsedComments.value.add(commentId);
  }
};

const replyForm = useForm({
  content: '',
  parent_id: null
});

const submitReply = (parentId) => {
  replyForm.parent_id = parentId;
  replyForm.post(route('posts.comments.reply', props.postId), {
    preserveScroll: true,
    onSuccess: () => {
      replyForm.reset();
      replyingTo.value = null;
      window.dispatchEvent(new Event('comment-added'));
    },
  });
};

const commentForm = useForm({
  content: "",
});

const submitComment = () => {
  commentForm.post(route("posts.comments.store", props.postId), {
    preserveScroll: true,
    onSuccess: () => {
      commentForm.reset();
      window.dispatchEvent(new Event("comment-added"));
    },
  });
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
</script>

<template>
  <div
    class="overflow-hidden bg-white/90 rounded-lg sm:rounded-2xl border border-gray-100/50 shadow-lg transition-all duration-300 backdrop-blur-sm hover:backdrop-blur-md"
  >
    <div class="p-3 sm:p-6 md:p-8 space-y-4 sm:space-y-6 md:space-y-8">
      <!-- Comment Form or Login Prompt -->
      <div>
        <div class="flex items-center justify-between mb-4 sm:mb-6 md:mb-8 pb-3 sm:pb-4 border-b border-gray-100/70">
          <h2 class="text-lg sm:text-xl md:text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600">Comments</h2>
          <span class="text-sm text-gray-500">{{ commentsCount }} {{ commentsCount === 1 ? 'comment' : 'comments' }}</span>
        </div>

        <!-- Show comment form for authenticated users -->
        <template v-if="$page.props.auth.user">
          <div class="flex items-start space-x-2 sm:space-x-3">
            <!-- User Avatar -->
            <UserAvatar
              :username="$page.props.auth.user.name"
              size="md"
              bgColor="blue"
              class="mt-1"
            />
            <!-- Comment Input -->
            <div class="flex-grow">
              <div class="relative group">
                <textarea
                  v-model="commentForm.content"
                  rows="3"
                  placeholder="What are your thoughts?"
                  class="px-3 sm:px-6 py-3 sm:py-4 w-full text-sm text-gray-700 rounded-xl sm:rounded-2xl border border-gray-200/50 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-transparent placeholder:text-gray-400/70 transition-all duration-300 bg-white/50 backdrop-blur-sm shadow-inner hover:shadow-md group-hover:border-blue-200/50"
                ></textarea>
                <div class="absolute inset-0 rounded-xl pointer-events-none transition-opacity duration-200 opacity-0 group-hover:opacity-100 bg-gradient-to-r from-blue-50/20 to-transparent"></div>
              </div>
              <div class="flex justify-end mt-3">
                <button
                  @click="submitComment"
                  :disabled="commentForm.processing || !commentForm.content.trim()"
                  class="flex items-center px-4 sm:px-6 py-2.5 sm:py-3 space-x-1 sm:space-x-2 text-xs sm:text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-500 rounded-lg sm:rounded-xl transition-all duration-300 hover:from-blue-700 hover:to-blue-600 disabled:opacity-50 disabled:cursor-not-allowed hover:scale-105 hover:shadow-lg shadow-sm shadow-blue-500/10 hover:shadow-blue-500/20"
                >
                  <svg v-if="commentForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>{{ commentForm.processing ? "Posting..." : "Post Comment" }}</span>
                </button>
              </div>
            </div>
          </div>
        </template>

        <!-- Show login prompt for non-authenticated users -->
        <template v-else>
          <div class="p-3 sm:p-4 md:p-6 text-center bg-gradient-to-b from-gray-50 to-white rounded-lg sm:rounded-xl border border-gray-100 shadow-sm">
            <div class="mb-4">
              <svg class="w-8 h-8 sm:w-12 sm:h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
            </div>
            <h3 class="mb-2 text-base sm:text-lg font-semibold text-gray-900">Join the Discussion</h3>
            <p class="mb-4 sm:mb-6 text-sm sm:text-base text-gray-600">Sign in to share your thoughts and engage with the community</p>
            <div class="flex flex-col sm:flex-row justify-center gap-2 sm:gap-3 md:gap-4">
              <Link
                :href="route('login')"
                class="px-4 sm:px-6 py-2 sm:py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-500 rounded-lg sm:rounded-xl transition-all duration-200 hover:from-blue-700 hover:to-blue-600 hover:shadow-md hover:scale-105"
              >
                Log in
              </Link>
              <Link
                :href="route('register')"
                class="px-4 sm:px-6 py-2 sm:py-2.5 text-sm font-medium text-gray-700 bg-white rounded-lg sm:rounded-xl border border-gray-200 transition-all duration-200 hover:border-blue-200 hover:text-blue-600 hover:shadow-md hover:scale-105"
              >
                Sign up
              </Link>
            </div>
          </div>
        </template>
      </div>

      <!-- Comments List -->
      <div
        v-if="comments && comments.length > 0"
        class="space-y-3 sm:space-y-4"
      >
        <div
          v-for="comment in comments.filter(c => !c.parent_id)"
          :key="comment.id"
          class="group p-3 sm:p-4 md:p-6 rounded-lg sm:rounded-2xl border border-gray-100/60 transition-all duration-300 hover:border-blue-200/60 hover:shadow-xl bg-gradient-to-r from-white/80 to-white/60 hover:from-blue-50/30 hover:to-white/70 backdrop-blur-sm hover:backdrop-blur-md shadow-sm hover:-translate-y-0.5"
        >
          <div class="flex items-start space-x-2 sm:space-x-3">
            <UserAvatar
              :username="comment.user.name"
              size="md"
              bgColor="blue"
              class="mt-1"
            />
            <div class="flex-grow min-w-0">
              <div class="flex flex-wrap items-center gap-x-1 sm:gap-x-2 mb-1 sm:mb-1.5">
                <span
                  class="font-semibold text-sm sm:text-base text-gray-900 cursor-pointer hover:text-blue-600 truncate max-w-[120px] sm:max-w-[200px]"
                  >{{ comment.user.name }}</span
                >
                <span class="text-xs sm:text-sm text-gray-500">·</span>
                <span class="text-xs sm:text-sm text-gray-500">{{ timeAgo(comment.created_at) }}</span>
              </div>
              <div class="prose prose-sm max-w-none text-sm sm:text-base text-gray-600 mt-1 sm:mt-1.5 leading-relaxed">
                {{ comment.content }}
              </div>
              <!-- Comment Actions -->
              <div class="flex flex-wrap items-center mt-2 sm:mt-3 gap-2 sm:gap-0 sm:space-x-4">
                <button
                  v-if="comments.filter(r => r.parent_id === comment.id).length > 0"
                  @click="toggleReplies(comment.id)"
                  class="inline-flex items-center sm:space-x-2 text-xs font-medium text-gray-500/80 opacity-0 group-hover:opacity-100 transition-all duration-300 hover:text-gray-600 bg-gray-50/50 px-2 sm:px-3 py-1.5 rounded-full border border-gray-100/50 hover:shadow-sm hover:border-gray-200/50"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="!collapsedComments.has(comment.id)" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  <span class="hidden sm:inline">{{ collapsedComments.has(comment.id) ? 'Show' : 'Hide' }} {{ comments.filter(r => r.parent_id === comment.id).length }} {{ comments.filter(r => r.parent_id === comment.id).length === 1 ? 'reply' : 'replies' }}</span>
                </button>
                <button
                  v-if="$page.props.auth.user"
                  @click="replyingTo = replyingTo === comment.id ? null : comment.id"
                  class="inline-flex items-center space-x-2 text-xs font-medium text-blue-500/80 opacity-0 group-hover:opacity-100 transition-all duration-300 hover:text-blue-600 bg-blue-50/50 px-3 py-1.5 rounded-full border border-blue-100/50 hover:shadow-sm hover:border-blue-200/50"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                  </svg>
                  <span>Reply</span>
                </button>
                <button
                  v-if="$page.props.auth.user && $page.props.auth.user.id === comment.user_id"
                  @click="deleteComment(comment.id)"
                  class="inline-flex items-center space-x-2 text-xs font-medium text-red-500/80 opacity-0 group-hover:opacity-100 transition-all duration-300 hover:text-red-600 bg-red-50/50 px-3 py-1.5 rounded-full border border-red-100/50 hover:shadow-sm hover:border-red-200/50"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  <span>Delete</span>
                </button>
              </div>

              <!-- Reply Form -->
              <div v-if="replyingTo === comment.id && $page.props.auth.user" class="mt-4 pl-8 border-l-2 border-blue-100">
                <div class="flex items-start space-x-4">
                  <UserAvatar
                    :username="$page.props.auth.user.name"
                    size="sm"
                    bgColor="blue"
                  />
                  <div class="flex-grow">
                    <div class="relative group">
                      <textarea
                        v-model="replyForm.content"
                        rows="2"
                        placeholder="Write a reply..."
                        class="px-4 py-2 w-full text-sm text-gray-700 rounded-xl border border-gray-200/50 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-transparent placeholder:text-gray-400/70 transition-all duration-300 bg-white/50 backdrop-blur-sm shadow-inner hover:shadow-md group-hover:border-blue-200/50"
                      ></textarea>
                    </div>
                    <div class="flex justify-end mt-2 space-x-2">
                      <button
                        @click="replyingTo = null"
                        class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-700 transition-colors"
                      >
                        Cancel
                      </button>
                      <button
                        @click="submitReply(comment.id)"
                        :disabled="replyForm.processing || !replyForm.content.trim()"
                        class="inline-flex items-center px-3 py-1.5 space-x-1 text-xs font-medium text-white bg-gradient-to-r from-blue-600 to-blue-500 rounded-xl transition-all duration-300 hover:from-blue-700 hover:to-blue-600 disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-md shadow-sm shadow-blue-500/10 hover:shadow-blue-500/20"
                      >
                        <svg v-if="replyForm.processing" class="animate-spin -ml-1 mr-1 h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ replyForm.processing ? 'Posting...' : 'Post Reply' }}</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Nested Replies -->
              <div v-if="comments.filter(r => r.parent_id === comment.id).length > 0 && !collapsedComments.has(comment.id)" class="mt-4 space-y-4 pl-8 border-l-2 border-gray-100">
                <div
                  v-for="reply in comments.filter(r => r.parent_id === comment.id)"
                  :key="reply.id"
                  class="group p-3 sm:p-4 rounded-xl border border-gray-100/60 transition-all duration-300 hover:border-blue-200/60 hover:shadow-md bg-gradient-to-r from-white/90 to-white/80 backdrop-blur-sm hover:backdrop-blur-md shadow-sm"
                >
                  <div class="flex items-start space-x-3">
                    <UserAvatar
                      :username="reply.user.name"
                      size="sm"
                      bgColor="blue"
                      class="mt-1"
                    />
                    <div class="flex-grow min-w-0">
                      <div class="flex flex-wrap items-center gap-x-2 mb-1">
                        <span class="font-semibold text-gray-900 cursor-pointer hover:text-blue-600 truncate max-w-[200px] text-sm">{{ reply.user.name }}</span>
                        <span class="text-xs text-gray-500">·</span>
                        <span class="text-xs text-gray-500">{{ timeAgo(reply.created_at) }}</span>
                      </div>
                      <div class="prose prose-sm max-w-none text-gray-600 mt-1 leading-relaxed text-sm">
                        {{ reply.content }}
                      </div>
                      <!-- Reply Actions -->
                      <div class="flex items-center mt-2 space-x-4">
                        <button
                          v-if="$page.props.auth.user && $page.props.auth.user.id === reply.user_id"
                          @click="deleteComment(reply.id)"
                          class="inline-flex items-center space-x-1 text-xs font-medium text-red-500/80 opacity-0 group-hover:opacity-100 transition-all duration-300 hover:text-red-600"
                        >
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

      <!-- No Comments State -->
      <div v-else class="py-8 sm:py-12 text-center">
        <div class="p-4 sm:p-8 mx-auto max-w-md bg-gray-50 rounded-lg sm:rounded-xl">
          <svg
            class="mx-auto mb-3 sm:mb-4 w-8 h-8 sm:w-12 sm:h-12 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
            />
          </svg>
          <h3 class="mb-1 text-sm sm:text-base font-medium text-gray-900">No comments yet</h3>
          <p class="text-xs sm:text-sm text-gray-500">
            Be the first to share what you think about this post!
          </p>
        </div>
      </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      :isOpen="showDeleteConfirm"
      title="Delete Comment"
      message="Are you sure you want to delete this comment? This action cannot be undone."
      confirmText="Delete"
      cancelText="Cancel"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />
  </div>
</template>
