<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import PostInteractions from "@/Components/PostInteractions.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import { timeAgo } from "@/utils/dateUtils";
import ImageGallery from "@/Components/ImageGallery.vue";
import Comments from "@/Components/Comments.vue";
import { ref } from 'vue';
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from '@headlessui/vue';

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
  relatedPosts: {
    type: Array,
    default: () => [],
  },
});

const voteForm = useForm({
  vote_type: null,
});

const showDeleteConfirm = ref(false);

const goBack = () => {
  if (window.history.length > 2) {
    window.history.back();
  } else {
    router.visit("/");
  }
};

const vote = (postId, voteType) => {
  // Reset the form before setting new values
  voteForm.reset();
  // Ensure voteType is an integer
  voteForm.vote_type = parseInt(voteType);
  console.log('Voting:', { postId, voteType: parseInt(voteType), originalType: voteType });
  voteForm.post(route("posts.vote", postId), {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Vote recorded successfully');
      window.dispatchEvent(new Event("post-voted"));
    },
    onError: (errors) => {
      console.error('Error recording vote:', errors);
    }
  });
};

// Removed share functions as share buttons have been eliminated per design update.

const deletePost = () => {
  router.delete(route('posts.destroy', props.post.id), {
    onSuccess: () => {
      showDeleteConfirm.value = false;
      router.visit('/'); // Redirect to home after deletion
    },
  });
};
</script>

<template>
  <Head :title="post.title" />

  <MainLayout>
    <div class="max-w-6xl px-2 sm:px-4 mx-auto">
      <!-- Enhanced Top Navigation with Back Button & Breadcrumb -->
      <div class="py-3 mb-3">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-3 min-w-0">
            <!-- Back Button -->
            <button
              @click="goBack"
              aria-label="Go back"
              class="group relative flex items-center gap-2 px-3 sm:px-4 py-2 rounded-xl bg-white/70 backdrop-blur border border-gray-200 hover:border-orange-300 hover:bg-orange-50/80 transition-all duration-300 text-gray-700 hover:text-orange-600 shadow-sm hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-400 focus-visible:ring-offset-2"
            >
              <svg class="w-4 h-4 transition-transform duration-300 group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
              </svg>
              <span class="hidden sm:inline text-sm font-medium">Back</span>
            </button>

            <!-- Breadcrumb -->
            <nav aria-label="Breadcrumb" class="min-w-0 overflow-hidden">
              <ol class="flex items-center text-xs sm:text-[13px] text-gray-500 whitespace-nowrap overflow-x-auto scrollbar-thin scrollbar-track-transparent scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 pr-1">
                <li class="flex items-center">
                  <Link href="/" class="flex items-center gap-1 hover:text-orange-600 transition-colors font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M4.5 10.5V21h6v-6h3v6h6v-10.5" />
                    </svg>
                    <span>Home</span>
                  </Link>
                </li>
                <li class="flex items-center mx-1 text-gray-300" aria-hidden="true">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                  </svg>
                </li>
                <li v-if="post.subfapp" class="flex items-center min-w-0 max-w-[140px] sm:max-w-[180px]">
                  <Link
                    :href="route('subfapps.show', post.subfapp.id)"
                    class="flex items-center gap-1 hover:text-orange-600 transition-colors truncate"
                  >
                    <div class="w-4 h-4 rounded-full bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center text-[10px] text-white font-bold">f</div>
                    <span class="truncate">f/{{ post.subfapp.name }}</span>
                  </Link>
                </li>
                <li v-else class="text-gray-400">general</li>
                <li class="flex items-center mx-1 text-gray-300" aria-hidden="true">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                  </svg>
                </li>
                <li class="text-gray-400 font-medium truncate max-w-[140px] sm:max-w-[220px]" :title="post.title">{{ post.title }}</li>
              </ol>
            </nav>
          </div>

          <!-- Meta (created time) -->
          <div class="flex items-center text-xs sm:text-sm text-gray-400 pl-1 sm:pl-0">
            <span class="hidden sm:inline mr-1">Posted</span>{{ timeAgo(post.created_at) }}
          </div>
        </div>
      </div>

      <div class="flex flex-col md:flex-row gap-2 sm:gap-4">
        <!-- Main Content -->
        <div class="flex-1">
          <!-- Post Card -->
          <div class="bg-white rounded-lg sm:rounded-md border border-gray-200 shadow mb-2 sm:mb-4">
            <div class="p-3 sm:p-4">
              <!-- Meta Header -->
              <div class="flex flex-wrap items-center justify-between text-xs text-gray-500 mb-3">
                <div class="flex flex-wrap items-center">
                  <template v-if="post.subfapp">
                    <Link
                      :href="route('subfapps.show', post.subfapp.id)"
                      class="flex items-center mr-3 group"
                    >
                      <div class="w-5 h-5 rounded-full bg-gradient-to-br from-orange-400 to-red-500 mr-1.5 flex items-center justify-center text-white text-xs font-bold">f</div>
                      <span class="font-medium text-gray-900 group-hover:text-orange-600 transition-colors">
                        f/{{ post.subfapp.name }}
                      </span>
                    </Link>
                  </template>
                  <span class="mx-1 text-gray-300 select-none">•</span>
                  <span>Posted by</span>
                  <UserAvatar :user="post.user" class="w-5 h-5 mx-1" />
                  <span class="font-medium text-gray-700 mr-1">u/{{ post.user?.name || 'unknown' }}</span>
                  <span class="text-gray-400">{{ timeAgo(post.created_at) }}</span>
                </div>

                <!-- Edit/Delete Buttons (only for post owner) -->
                <div v-if="$page.props.auth.user && post.user.id === $page.props.auth.user.id" class="flex items-center space-x-2">
                  <Link 
                    :href="route('posts.edit', post.id)"
                    class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-full transition-colors"
                    title="Edit post"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </Link>
                  <button 
                    @click="showDeleteConfirm = true"
                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-colors"
                    title="Delete post"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Title -->
              <h1 class="text-xl sm:text-2xl font-bold leading-snug text-gray-900 mb-4">
                {{ post.title }}
              </h1>

              <!-- Images -->
              <div v-if="post.images && post.images.length" class="mb-4">
                <ImageGallery :images="post.images" />
              </div>

              <!-- Content Body -->
              <div v-if="post.content" class="prose max-w-none text-gray-800 mb-4" v-html="post.content"></div>
              <p v-else class="text-gray-600 mb-4 whitespace-pre-line">
                {{ post.excerpt || '' }}
              </p>

              <!-- Actions: standardized for all devices -->
              <div class="mt-6 pt-4 border-t border-gray-100">
                <PostInteractions 
                  :post="post" 
                  @vote="vote" 
                />
              </div>
            </div>
          </div>
          
          <!-- Comments Section -->
          <div class="bg-white rounded-lg sm:rounded-md border border-gray-200 shadow mb-2 sm:mb-4 p-3 sm:p-4">
            <h3 class="text-lg font-bold mb-4 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
              Comments ({{ post.comments_count || 0 }})
            </h3>
            
            <Comments
              :post-id="post.id"
              :comments="post.comments"
              :comments-count="post.comments_count"
            />
          </div>
        </div>
        
        <!-- Sidebar -->
        <div class="md:w-80 lg:w-96 space-y-4">
          <!-- Community Card -->
          <div v-if="post.subfapp" class="bg-white rounded-md border border-gray-200 shadow overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-red-500 h-12"></div>
            <div class="p-4">
              <div class="flex items-center -mt-8 mb-3">
                <div class="w-12 h-12 rounded-full bg-white border-4 border-white shadow-md flex items-center justify-center overflow-hidden">
                  <div class="w-full h-full bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center text-white text-lg font-bold">
                    f
                  </div>
                </div>
                <h3 class="ml-2 text-lg font-bold">f/{{ post.subfapp.name }}</h3>
              </div>
              
              <p class="text-sm text-gray-600 mb-4" v-if="post.subfapp.description">
                {{ post.subfapp.description }}
              </p>
              <p class="text-sm text-gray-600 mb-4" v-else>
                A community for sharing content about {{ post.subfapp.name }}.
              </p>
              
              <div class="flex items-center text-sm mb-4">
                <div class="flex items-center mr-4">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                  <span>{{ post.subfapp.users_count || 0 }} {{ (post.subfapp.users_count || 0) === 1 ? 'member' : 'members' }}</span>
                </div>
                <div class="flex items-center">
                  <div class="h-2 w-2 bg-green-500 rounded-full animate-pulse mr-1.5"></div>
                  <span>{{ Math.max(1, Math.floor((post.subfapp.users_count || 0) * 0.1)) }} online</span>
                </div>
              </div>
              
              <Link
                :href="route('subfapps.show', post.subfapp.id)"
                class="block w-full py-2 text-center bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-md transition-colors"
              >
                View Community
              </Link>
            </div>
          </div>
          
          <!-- Related Posts -->
          <div class="bg-white rounded-md border border-gray-200 shadow overflow-hidden" v-if="relatedPosts.length > 0">
            <div class="p-3 border-b border-gray-100 bg-gray-50">
              <h3 class="font-bold text-gray-800">Related Posts</h3>
            </div>
            <div class="divide-y divide-gray-100">
              <div v-for="relatedPost in relatedPosts" :key="relatedPost.id" class="p-3 hover:bg-gray-50 transition-colors">
                <Link :href="route('posts.show', relatedPost.id)" class="block">
                  <h4 class="text-sm font-medium text-gray-900 hover:text-orange-600 transition-colors mb-1 line-clamp-2">
                    {{ relatedPost.title }}
                  </h4>
                  <div class="flex items-center text-xs text-gray-500">
                    <span class="mr-1">{{ relatedPost.score }} {{ relatedPost.score === 1 ? 'vote' : 'votes' }}</span>
                    <span class="mx-1">•</span>
                    <span>{{ relatedPost.comments_count }} {{ relatedPost.comments_count === 1 ? 'comment' : 'comments' }}</span>
                    <template v-if="relatedPost.subfapp">
                      <span class="mx-1">•</span>
                      <span>f/{{ relatedPost.subfapp.name }}</span>
                    </template>
                  </div>
                </Link>
              </div>
            </div>
            <div class="p-3 border-t border-gray-100 bg-gray-50">
              <Link href="/" class="text-xs font-medium text-orange-600 hover:text-orange-700 transition-colors">
                See more
              </Link>
            </div>
          </div>
          
          <!-- Footer -->
          <div class="text-xs text-gray-500 p-3">
            <div class="flex flex-wrap gap-x-2 gap-y-1 mb-2">
              <Link href="#" class="hover:underline hover:text-gray-700">Help</Link>
              <Link href="#" class="hover:underline hover:text-gray-700">About</Link>
              <Link href="#" class="hover:underline hover:text-gray-700">Terms</Link>
              <Link href="#" class="hover:underline hover:text-gray-700">Privacy</Link>
              <Link href="#" class="hover:underline hover:text-gray-700">Advertise</Link>
            </div>
            <p>&copy; 2025 Fappify. All rights reserved.</p>
          </div>
        </div>
      </div>
      
      <!-- Floating Back Button (Mobile) -->
      <button
        @click="goBack"
        class="fixed bottom-6 right-6 sm:hidden z-50 group flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 transition-all duration-300 text-white shadow-2xl shadow-orange-500/25 hover:shadow-3xl hover:shadow-orange-500/35 hover:scale-110"
      >
        <svg class="w-5 h-5 transition-transform duration-300 group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
      </button>
    </div>

    <!-- Delete Confirmation Modal -->
    <TransitionRoot appear :show="showDeleteConfirm" as="template">
      <Dialog as="div" @close="showDeleteConfirm = false" class="relative z-50">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/25" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild
              as="template"
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                  Delete Post
                </DialogTitle>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete this post? This action cannot be undone.
                  </p>
                </div>

                <div class="mt-4 flex justify-end space-x-3">
                  <button
                    type="button"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-500"
                    @click="showDeleteConfirm = false"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-red-500 shadow-sm hover:shadow transition-all duration-200"
                    @click="deletePost"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Delete
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </MainLayout>
</template>

<style scoped>
/* Enhance comment transition */
.prose {
  font-size: 15px;
}

/* Hide scrollbar for horizontal action bar when overflow-x-auto is used */
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none; /* IE and Edge */
  scrollbar-width: none; /* Firefox */
}

/* Back button animations */
.hover\:shadow-3xl:hover {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04), 0 0 30px rgba(249, 115, 22, 0.3);
}

/* Floating button entrance animation */
@keyframes slideInUp {
  from {
    transform: translateY(100px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.fixed.bottom-6.right-6 {
  animation: slideInUp 0.6s ease-out;
}

.prose img {
  border-radius: 0.375rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.prose a {
  text-decoration: none;
  color: #f97316;
  transition: color 0.2s ease;
}

.prose a:hover {
  color: #ea580c;
  text-decoration: underline;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background-color: #f1f1f1;
  border-radius: 8px;
}

::-webkit-scrollbar-thumb {
  background-color: #ff4500;
  border-radius: 8px;
}

::-webkit-scrollbar-thumb:hover {
  background-color: #e03d00;
}

/* Button hover effects */
button {
  transition: all 0.2s ease;
}

button:hover {
  transform: scale(1.05);
}

button:active {
  transform: scale(0.95);
}

/* Vote button effects */
@keyframes upvote {
  0% { transform: scale(1); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

@keyframes downvote {
  0% { transform: scale(1); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

button:active svg {
  animation: upvote 0.3s ease;
}

/* Breadcrumb hover effects */
.hover\:text-orange-500:hover {
  transition: all 0.2s ease;
}

.hover\:text-orange-500:hover {
  transform: translateY(-1px);
}
</style>
