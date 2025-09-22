<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import PostInteractions from "@/Components/PostInteractions.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import PostFlair from "@/Components/PostFlair.vue";
import SocialShare from "@/Components/SocialShare.vue";
import { timeAgo } from "@/utils/dateUtils";
import ImageGallery from "@/Components/ImageGallery.vue";
import CommentsEnhanced from "@/Components/CommentsEnhanced.vue";
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
  voteForm.post(route("posts.vote", postId), {
    preserveScroll: true,
    onSuccess: () => {
      window.dispatchEvent(new Event("post-voted"));
    },
    onError: (errors) => {
      // Handle errors silently or with user-friendly notifications
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

// Share functionality
const sharePost = async () => {
  const shareData = {
    title: props.post.title,
    text: props.post.excerpt || props.post.title,
    url: window.location.href
  };

  try {
    if (navigator.share && navigator.canShare && navigator.canShare(shareData)) {
      await navigator.share(shareData);
    } else if (navigator.clipboard) {
      await navigator.clipboard.writeText(window.location.href);
      // You could add a toast notification here if you have one
    } else {
      // Fallback for older browsers
      const textArea = document.createElement('textarea');
      textArea.value = window.location.href;
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand('copy');
      document.body.removeChild(textArea);
    }
  } catch (error) {
    // Fallback to clipboard
    try {
      await navigator.clipboard.writeText(window.location.href);
    } catch (clipboardError) {
      // Handle clipboard errors silently
    }
  }
};
</script>

<template>
  <Head :title="post.title" />

  <MainLayout>
    <!-- Breadcrumb Navigation Bar -->
    <div class="bg-white border-b border-gray-100 sticky top-0 z-40">
      <div class="max-w-6xl mx-auto px-3 sm:px-4">
        <nav aria-label="Breadcrumb" class="py-2.5 sm:py-3">
          <ol class="flex items-center text-xs sm:text-sm text-gray-500 space-x-1.5 sm:space-x-2 overflow-x-auto scrollbar-none">
            <li class="flex-shrink-0">
              <Link href="/" class="hover:text-orange-600 transition-colors font-medium flex items-center py-1 px-2 sm:px-0 rounded-md hover:bg-gray-50 sm:hover:bg-transparent">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M4.5 10.5V21h6v-6h3v6h6v-10.5" />
                </svg>
                <span class="hidden sm:inline">Home</span>
              </Link>
            </li>
            <li class="text-gray-300 flex-shrink-0">/</li>
            <li v-if="post.subfapp" class="flex-shrink-0">
              <Link :href="route('subfapps.show', post.subfapp.id)" class="hover:text-orange-600 transition-colors py-1 px-2 sm:px-0 rounded-md hover:bg-gray-50 sm:hover:bg-transparent flex items-center">
                <div class="w-3 h-3 sm:w-4 sm:h-4 rounded-full bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center text-[8px] sm:text-[10px] text-white font-bold mr-1">f</div>
                <span class="truncate max-w-[80px] sm:max-w-[120px]">f/{{ post.subfapp.name }}</span>
              </Link>
            </li>
            <li v-else class="flex-shrink-0">
              <span class="text-gray-400 py-1 px-2 sm:px-0">General</span>
            </li>
            <li class="text-gray-300 flex-shrink-0">/</li>
            <li class="text-gray-900 font-medium truncate max-w-[120px] sm:max-w-[200px] flex-shrink-0" :title="post.title">
              {{ post.title }}
            </li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="max-w-6xl px-2 sm:px-4 mx-auto">
      <!-- Enhanced Top Navigation with Back Button & Quick Actions -->
      <div class="py-4 mb-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-2 sm:gap-3 min-w-0">
            <!-- Back Button -->
            <button
              @click="goBack"
              aria-label="Go back"
              class="group relative flex items-center gap-2 px-3 py-2 sm:px-4 rounded-xl bg-white border border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition-all duration-300 text-gray-700 hover:text-orange-600 shadow-sm hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-400 focus-visible:ring-offset-2 flex-shrink-0"
            >
              <svg class="w-4 h-4 transition-transform duration-300 group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
              </svg>
              <span class="hidden sm:inline text-sm font-medium">Back</span>
            </button>

            <!-- Post Type Badge -->
            <div class="flex items-center gap-1.5 sm:gap-2 min-w-0">
              <div v-if="post.images && post.images.length" class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="hidden sm:inline">Image Post</span>
                <span class="sm:hidden">IMG</span>
              </div>
              <div v-else-if="post.content" class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="hidden sm:inline">Text Post</span>
                <span class="sm:hidden">TXT</span>
              </div>
              
              <!-- Community Badge -->
              <div v-if="post.subfapp" class="inline-flex items-center px-2 py-1 bg-orange-100 text-orange-800 text-xs font-semibold rounded-full max-w-[120px] sm:max-w-none">
                <div class="w-3 h-3 rounded-full bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center text-[8px] text-white font-bold mr-1">f</div>
                <span class="truncate">f/{{ post.subfapp.name }}</span>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="flex items-center gap-2">
            <!-- Social Share Component -->
            <SocialShare 
              :post="post" 
              :showLabels="true"
              size="sm"
            />
            
            <!-- Save Button -->
            <button class="inline-flex items-center px-3 py-1.5 text-xs sm:text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-full hover:bg-gray-50 hover:border-gray-400 transition-colors duration-200">
              <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 sm:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
              </svg>
              <span class="hidden sm:inline">Save</span>
            </button>
            
            <!-- Time Display -->
            <div class="flex items-center text-xs sm:text-sm text-gray-500 bg-gray-50 px-3 py-1.5 rounded-full">
              <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="hidden sm:inline mr-1">Posted</span>
              <span>{{ timeAgo(post.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
        <!-- Main Content -->
        <div class="flex-1 min-w-0">
          <!-- Post Card -->
          <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-200 shadow-lg hover:shadow-xl transition-shadow duration-300 mb-4 sm:mb-6 overflow-hidden">
            <!-- Post Header Strip -->
            <div class="bg-gradient-to-r from-orange-50 to-red-50 border-b border-orange-100 px-4 py-3">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <UserAvatar :user="post.user" class="w-8 h-8 sm:w-10 sm:h-10 ring-2 ring-white shadow-sm" />
                  <div class="min-w-0">
                    <div class="flex items-center space-x-2">
                      <Link :href="route('users.profile', post.user.id)" class="font-semibold text-gray-900 hover:text-orange-600 transition-colors truncate">
                        u/{{ post.user?.name || 'unknown' }}
                      </Link>
                      <div v-if="post.subfapp" class="inline-flex items-center px-2 py-0.5 bg-orange-500 text-white text-xs font-bold rounded-full">
                        <div class="w-3 h-3 rounded-full bg-white/20 flex items-center justify-center text-[8px] mr-1">f</div>
                        f/{{ post.subfapp.name }}
                      </div>
                    </div>
                    <div class="flex items-center text-xs text-gray-500 space-x-2">
                      <span>{{ timeAgo(post.created_at) }}</span>
                      <span v-if="post.is_trending" class="inline-flex items-center px-1.5 py-0.5 bg-red-100 text-red-800 rounded-full">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M13.5 4.06c0-1.336-1.616-2.005-2.56-1.06l-4.5 4.5H4.508c-1.141 0-2.318.664-2.66 1.905A9.76 9.76 0 001.5 12c0 .898.121 1.768.35 2.595.341 1.24 1.518 1.905 2.659 1.905h1.93l4.5 4.5c.945.945 2.561.276 2.561-1.06V4.06z" />
                        </svg>
                        Trending
                      </span>
                    </div>
                  </div>
                </div>
                
                <!-- Post Owner Actions -->
                <div v-if="$page.props.auth.user && post.user.id === $page.props.auth.user.id" class="flex items-center space-x-1">
                  <Link :href="route('posts.edit', post.id)" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-full transition-colors" title="Edit post">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </Link>
                  <button @click="showDeleteConfirm = true" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-full transition-colors" title="Delete post">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
            
            <div class="p-4 sm:p-6">
              <!-- Post Flair -->
              <div v-if="post.flair" class="mb-4">
                <PostFlair
                  :flair="post.flair"
                  size="lg"
                />
              </div>

              <!-- Title -->
              <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight text-gray-900 mb-6">
                {{ post.title }}
              </h1>

              <!-- Images -->
              <div v-if="post.images && post.images.length" class="mb-6 -mx-4 sm:-mx-6">
                <ImageGallery :images="post.images" />
              </div>

              <!-- Content Body -->
              <div v-if="post.content" class="prose prose-lg max-w-none text-gray-800 mb-8" v-html="post.content"></div>
              <p v-else class="text-gray-600 mb-8 text-lg leading-relaxed whitespace-pre-line">
                {{ post.excerpt || '' }}
              </p>


              <!-- Actions: Enhanced voting UI -->
              <div class="border-t border-gray-200 pt-6">
                <PostInteractions 
                  :post="post" 
                  @vote="vote" 
                />
              </div>
            </div>
          </div>
          
          <!-- Comments Section -->
          <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-200 shadow-lg mb-4 sm:mb-6 overflow-hidden">
            <!-- Comments Header -->
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 border-b border-blue-100 px-4 sm:px-6 py-4">
              <div class="flex items-center justify-between">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center">
                  <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                  </svg>
                  Comments
                </h3>
                <div class="flex items-center">
                  <div class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                    {{ post.comments_count || 0 }}
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Comments Content -->
            <div class="p-4 sm:p-6">
              <CommentsEnhanced
                :post-id="post.id"
                :comments="post.comments"
                :comments-count="post.comments_count"
              />
            </div>
          </div>
        </div>
        
        <!-- Sidebar -->
        <div class="lg:w-80 xl:w-96 space-y-4 lg:space-y-6">
          <!-- Community Card -->
          <div v-if="post.subfapp" class="bg-white rounded-xl sm:rounded-2xl border border-gray-200 shadow-lg overflow-hidden sticky top-20">
            <!-- Cover Header -->
            <div class="bg-gradient-to-r from-orange-500 via-red-500 to-pink-500 h-16 relative overflow-hidden">
              <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
              <div class="absolute inset-0 opacity-20">
                <div class="absolute top-2 left-4 w-6 h-6 bg-white/20 rounded-full"></div>
                <div class="absolute top-6 right-6 w-3 h-3 bg-white/10 rounded-full"></div>
                <div class="absolute bottom-2 left-8 w-4 h-4 bg-white/15 rounded-full"></div>
              </div>
            </div>
            
            <div class="p-5 pt-8">
              <!-- Community Info -->
              <div class="flex items-start -mt-6 mb-6">
                <div class="w-14 h-14 rounded-xl bg-white border-3 border-white shadow-lg flex items-center justify-center overflow-hidden flex-shrink-0">
                  <div v-if="post.subfapp.icon" class="w-full h-full">
                    <img :src="`/storage/${post.subfapp.icon}`" :alt="post.subfapp.name" class="w-full h-full object-cover rounded-lg" />
                  </div>
                  <div v-else class="w-full h-full bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center text-white text-lg font-black rounded-lg">
                    f
                  </div>
                </div>
                <div class="ml-3 mt-3 min-w-0 flex-1">
                  <h3 class="text-lg font-black text-gray-900 mb-1 truncate">f/{{ post.subfapp.name }}</h3>
                  <p class="text-sm text-gray-600 truncate">{{ post.subfapp.display_name || post.subfapp.name }}</p>
                </div>
              </div>
              
              <!-- Description -->
              <p class="text-sm text-gray-700 mb-6 leading-relaxed line-clamp-3" v-if="post.subfapp.description">
                {{ post.subfapp.description }}
              </p>
              <p class="text-sm text-gray-700 mb-6 leading-relaxed" v-else>
                A community for sharing content about {{ post.subfapp.name }}.
              </p>
              
              <!-- Community Stats -->
              <div class="grid grid-cols-2 gap-3 mb-6">
                <div class="text-center p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl border border-orange-200">
                  <div class="text-2xl font-black text-orange-600 mb-1">{{ post.subfapp.users_count || 0 }}</div>
                  <div class="text-xs font-bold text-orange-700 uppercase tracking-wide">Members</div>
                </div>
                <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200">
                  <div class="text-2xl font-black text-green-600 mb-1">{{ Math.max(1, Math.floor((post.subfapp.users_count || 0) * 0.1)) }}</div>
                  <div class="text-xs font-bold text-green-700 uppercase tracking-wide">Online</div>
                </div>
              </div>
              
              <!-- View Community Button -->
              <Link
                :href="route('subfapps.show', post.subfapp.id)"
                class="block w-full py-3.5 text-center bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-bold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-[1.02] hover:-translate-y-0.5"
              >
                <div class="flex items-center justify-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <span>View Community</span>
                </div>
              </Link>
            </div>
          </div>
          
          <!-- Related Posts -->
          <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-200 shadow-lg overflow-hidden" v-if="relatedPosts.length > 0">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-blue-50">
              <h3 class="font-bold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Related Posts
              </h3>
            </div>
            <div class="divide-y divide-gray-100">
              <div v-for="relatedPost in relatedPosts" :key="relatedPost.id" class="p-4 hover:bg-gray-50 transition-colors group">
                <Link :href="route('posts.show', relatedPost.id)" class="block">
                  <h4 class="text-sm font-semibold text-gray-900 group-hover:text-purple-600 transition-colors mb-2 line-clamp-2 leading-relaxed">
                    {{ relatedPost.title }}
                  </h4>
                  <div class="flex items-center text-xs text-gray-500 space-x-3">
                    <div class="flex items-center">
                      <svg class="w-3 h-3 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                      </svg>
                      <span>{{ relatedPost.score }}</span>
                    </div>
                    <div class="flex items-center">
                      <svg class="w-3 h-3 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                      </svg>
                      <span>{{ relatedPost.comments_count }}</span>
                    </div>
                    <div v-if="relatedPost.subfapp" class="flex items-center">
                      <div class="w-3 h-3 rounded-full bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center text-[8px] text-white font-bold mr-1">f</div>
                      <span class="truncate max-w-[80px]">f/{{ relatedPost.subfapp.name }}</span>
                    </div>
                  </div>
                </Link>
              </div>
            </div>
            <div class="px-4 sm:px-6 py-3 border-t border-gray-100 bg-gray-50">
              <Link href="/" class="inline-flex items-center text-sm font-medium text-purple-600 hover:text-purple-700 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                See more posts
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
      
      <!-- Mobile Floating Action Buttons -->
      <div class="fixed bottom-6 right-6 z-50 lg:hidden flex flex-col space-y-3">
        <!-- Share Button (Mobile) -->
        <button
          @click="sharePost"
          class="group flex items-center justify-center w-12 h-12 rounded-full bg-blue-500 hover:bg-blue-600 transition-all duration-300 text-white shadow-xl shadow-blue-500/25 hover:shadow-2xl hover:shadow-blue-500/35 hover:scale-110"
          title="Share post"
        >
          <svg class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
          </svg>
        </button>
        
        <!-- Floating Back Button -->
        <button
          @click="goBack"
          class="group flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 transition-all duration-300 text-white shadow-2xl shadow-orange-500/25 hover:shadow-3xl hover:shadow-orange-500/35 hover:scale-110"
          title="Go back"
        >
          <svg class="w-6 h-6 transition-transform duration-300 group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
          </svg>
        </button>
      </div>
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
