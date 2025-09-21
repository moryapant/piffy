<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import PopularCommunities from "@/Components/PopularCommunities.vue";
import PostSortTabs from "@/Components/PostSortTabs.vue";
import InfiniteScroll from "@/Components/InfiniteScroll.vue";
import { ref, onMounted, onUnmounted, watch } from "vue";
import { timeAgo } from "@/utils/dateUtils";
import { router } from "@inertiajs/vue3";
import axios from 'axios';
import ImageGallery from "@/Components/ImageGallery.vue";
import PostInteractions from "@/Components/PostInteractions.vue";
import PostFlair from "@/Components/PostFlair.vue";

const props = defineProps({
  posts: {
    type: Object,
    required: true,
  },
  communities: {
    type: Array,
    required: true,
  },
  canLogin: Boolean,
  currentSort: String,
  canRegister: Boolean,
  stats: {
    type: Object,
    default: () => ({})
  }
});

// Handle infinite scroll load more
const handleLoadMore = ({ newItems, totalItems }) => {
  console.log(`Loaded ${newItems.length} new posts. Total: ${totalItems.length}`);
  // Update the reactive posts data with new items
  if (postsData.value && postsData.value.data) {
    postsData.value.data = totalItems;
    postsData.value.total = totalItems.length;
  }
};

const selectedImage = ref(null);
const currentPost = ref(null);
const currentImageIndex = ref(0);

const openImage = (image, e, post) => {
  e.preventDefault();
  selectedImage.value = image;
  currentPost.value = post;
  currentImageIndex.value = post.images.findIndex((img) => img.id === image.id);
};

const closeImage = () => {
  selectedImage.value = null;
  currentPost.value = null;
  currentImageIndex.value = 0;
};

const navigateImage = (direction) => {
  if (!currentPost.value || !currentPost.value.images) return;

  const newIndex = currentImageIndex.value + direction;
  if (newIndex >= 0 && newIndex < currentPost.value.images.length) {
    currentImageIndex.value = newIndex;
    selectedImage.value = currentPost.value.images[newIndex];
  }
};

// Store posts data reactively
const postsData = ref(props.posts);

const vote = async (postId, voteType) => {
  try {
    console.log('Voting:', { postId, voteType });
    
    // Make AJAX request to vote endpoint
    const response = await axios.post(route("posts.vote", postId), {
      vote_type: parseInt(voteType)
    }, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    });
    
    if (response.data.success) {
      // Update the post data reactively
      updatePostVoteData(postId, response.data);
      console.log('Vote recorded successfully');
    }
  } catch (error) {
    console.error('Error recording vote:', error);
    // Show error notification if needed
  }
};

// Function to update post vote data in the UI
const updatePostVoteData = (postId, voteData) => {
  // Find and update the post in the posts data
  const findAndUpdatePost = (posts) => {
    for (let post of posts) {
      if (post.id === postId) {
        post.upvotes = voteData.upvotes;
        post.downvotes = voteData.downvotes;
        post.score = voteData.score;
        post.user_vote = voteData.user_vote;
        return true;
      }
    }
    return false;
  };
  
  // Update in main posts array
  if (postsData.value && postsData.value.data) {
    findAndUpdatePost(postsData.value.data);
  }
  
  // Also update in the original props.posts if needed
  if (props.posts && props.posts.data) {
    findAndUpdatePost(props.posts.data);
  }
};

const visitRandomCommunity = () => {
  // Ensure communities is an array and has items
  if (props.communities && Array.isArray(props.communities) && props.communities.length > 0) {
    const randomIndex = Math.floor(Math.random() * props.communities.length);
    const randomCommunity = props.communities[randomIndex];
    if (randomCommunity && randomCommunity.id) {
      router.get(route('subfapps.show', randomCommunity.id));
      return;
    }
  }
  // Fallback if no valid communities found
  router.get(route('subfapps.index'));
};

const visitRandomPost = () => {
  // Navigate to a random post if posts are available
  if (postsData.value && postsData.value.data && postsData.value.data.length > 0) {
    const randomIndex = Math.floor(Math.random() * postsData.value.data.length);
    const randomPost = postsData.value.data[randomIndex];
    if (randomPost && randomPost.id) {
      router.get(route('posts.show', randomPost.id));
      return;
    }
  }
  // Fallback to home if no posts found
  router.get(route('home'));
};

const scrollToLatestPosts = () => {
  // Scroll to the posts section
  const postsSection = document.querySelector('.bg-white.rounded-xl.border.border-gray-200.shadow-sm');
  if (postsSection) {
    postsSection.scrollIntoView({ behavior: 'smooth' });
  }
};

const focusSearch = () => {
  // Look for search input in the navigation or create a simple search prompt
  const searchInput = document.querySelector('input[type="search"]');
  if (searchInput) {
    searchInput.focus();
  } else {
    // If no search input exists, prompt for search term and redirect
    const searchTerm = prompt('What would you like to search for?');
    if (searchTerm) {
      // Navigate to home with search parameter (you can implement search functionality later)
      router.get(route('home'), { search: searchTerm });
    }
  }
};

const handleRandomCommunityClick = (event) => {
  event.preventDefault();
  event.stopPropagation();
  console.log('Random community button clicked');
  visitRandomCommunity();
};

const recentActivities = ref([]);
const isLoadingActivities = ref(true);

const trendingPosts = ref([]);
const isLoadingTrending = ref(true);

const fetchRecentActivities = async () => {
  try {
    isLoadingActivities.value = true;
    const response = await axios.get('/api/recent-activity');
    recentActivities.value = response.data.activities;
  } catch (error) {
    console.error('Failed to fetch recent activities:', error);
    // Set some fallback data
    recentActivities.value = [];
  } finally {
    isLoadingActivities.value = false;
  }
};

const fetchTrendingPosts = async () => {
  try {
    isLoadingTrending.value = true;
    const response = await axios.get('/api/trending-posts');
    trendingPosts.value = response.data.posts;
  } catch (error) {
    console.error('Failed to fetch trending posts:', error);
    // Fallback to first 5 posts from main feed
    trendingPosts.value = postsData.value.data.slice(0, 5).map((post, index) => ({
      id: post.id,
      title: post.title,
      score: post.score || 0,
      trending_score: 0,
      created_at: post.created_at,
      user: post.user,
      subfapp: post.subfapp
    }));
  } finally {
    isLoadingTrending.value = false;
  }
};

const handleFlairClick = (flair) => {
  // Navigate to filtered view by flair (you can implement this later)
  console.log('Flair clicked:', flair);
  // router.get(route('home', { flair: flair.id }));
};

onMounted(() => {
  fetchRecentActivities();
  fetchTrendingPosts();
  
  // Refresh activities and trending posts every 30 seconds
  const activitiesInterval = setInterval(fetchRecentActivities, 30000);
  const trendingInterval = setInterval(fetchTrendingPosts, 30000);
  
  onUnmounted(() => {
    clearInterval(activitiesInterval);
    clearInterval(trendingInterval);
  });
});
</script>

<template>
  <Head title="Home" />

  <MainLayout>
    <div
      class="mx-auto min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50"
    >
      <div class="flex gap-6">
        <!-- Left Sidebar (Hidden on mobile, visible on md+) -->
        <div class="hidden md:flex md:flex-col md:w-64 xl:w-72 flex-shrink-0 space-y-4">
          <!-- Navigation Menu -->
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
              <h2 class="text-base font-black text-gray-900 flex items-center">
                <span class="text-xl mr-2">🏠</span>
                Home
              </h2>
            </div>
            <nav class="py-2">
              <Link href="/" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-900 hover:bg-orange-50 hover:text-orange-600 transition-colors duration-200">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                </svg>
                Home
              </Link>
              <Link href="/posts?sort=top" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors duration-200">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a3 3 0 01-3-3V9a3 3 0 013-3h8z" />
                </svg>
                Popular
              </Link>
              <Link :href="route('subfapps.index')" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors duration-200">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-4h2a2 2 0 012 2v6a2 2 0 01-2 2h-2m-4 0V9a2 2 0 00-2-2M9 11V9a2 2 0 012-2h2a2 2 0 012 2v10l-4-4-4 4V11z" />
                </svg>
                All Communities
              </Link>
              <template v-if="$page.props.auth.user">
                <hr class="my-2 border-gray-200">
                <div class="px-4 py-2">
                  <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Create</h3>
                </div>
                <Link :href="route('posts.create')" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors duration-200">
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Create Post
                </Link>
                <Link :href="route('subfapps.create')" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors duration-200">
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  Create Community
                </Link>
              </template>
            </nav>
          </div>

          <!-- Recent Activity -->
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
              <h2 class="text-base font-black text-gray-900 flex items-center">
                <span class="text-xl mr-2">⚡</span>
                Recent Activity
              </h2>
            </div>
            <div class="divide-y divide-gray-100 max-h-64 overflow-y-auto">
              <!-- Loading state -->
              <div v-if="isLoadingActivities" class="px-4 py-8 text-center">
                <div class="animate-spin w-6 h-6 border-2 border-green-500 border-t-transparent rounded-full mx-auto"></div>
                <p class="text-xs text-gray-500 mt-2">Loading activities...</p>
              </div>
              
              <!-- Activities list -->
              <div v-else-if="recentActivities && recentActivities.length > 0" v-for="activity in recentActivities.slice(0, 8)" :key="'activity-' + activity.id" class="px-4 py-3 hover:bg-gray-50 transition-colors duration-200 cursor-pointer">
                <div class="flex items-start space-x-3">
                  <div class="flex-shrink-0 mt-0.5">
                    <div class="flex items-center justify-center">
                      <span class="text-sm">{{ activity.activity_emoji || '⚡' }}</span>
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-700 leading-snug">
                      <span class="font-medium">{{ activity.activity_description }}</span>
                      <template v-if="activity.user">
                        <br>
                        <span class="text-gray-500">by </span>
                        <span 
                          class="font-semibold transition-colors duration-200"
                          :class="{
                            'text-orange-600': activity.activity_color === 'orange',
                            'text-blue-600': activity.activity_color === 'blue', 
                            'text-green-600': activity.activity_color === 'green',
                            'text-purple-600': activity.activity_color === 'purple',
                            'text-indigo-600': activity.activity_color === 'indigo',
                            'text-gray-600': activity.activity_color === 'gray'
                          }"
                        >
                          {{ activity.user.name }}
                        </span>
                      </template>
                    </p>
                    <p class="text-xs text-gray-500 mt-1.5 flex items-center">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      {{ activity.time_ago }}
                    </p>
                  </div>
                  <!-- Activity type indicator -->
                  <div class="flex-shrink-0">
                    <div 
                      class="w-2 h-2 rounded-full animate-pulse"
                      :class="{
                        'bg-orange-500': activity.activity_color === 'orange',
                        'bg-blue-500': activity.activity_color === 'blue',
                        'bg-green-500': activity.activity_color === 'green', 
                        'bg-purple-500': activity.activity_color === 'purple',
                        'bg-indigo-500': activity.activity_color === 'indigo',
                        'bg-gray-400': activity.activity_color === 'gray'
                      }"
                    ></div>
                  </div>
                </div>
              </div>
              
              <!-- Empty state -->
              <div v-else class="px-4 py-8 text-center">
                <div class="text-gray-400 mb-2">
                  <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                  </svg>
                </div>
                <p class="text-xs text-gray-500">No recent activity</p>
                <p class="text-xs text-gray-400 mt-1">Start browsing to see activity here!</p>
              </div>
            </div>
          </div>

          <!-- Quick Links -->
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200">
              <h2 class="text-base font-black text-gray-900 flex items-center">
                <span class="text-xl mr-2">🔗</span>
                Quick Links
              </h2>
            </div>
            <div class="py-2">
              <a href="https://bollyfappers.com" target="_blank" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-600 transition-colors duration-200">
                <span class="text-lg mr-3">🎬</span>
                Bollyfappers.com
              </a>
              <a href="https://www.bollyfappers.com/ase" target="_blank" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-600 transition-colors duration-200">
                <span class="text-lg mr-3">⭐</span>
                Actress stock exchange
              </a>
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="w-full md:flex-1 space-y-6">
          <!-- Reddit-style Header Banner -->
          <div class="relative overflow-hidden bg-gradient-to-r from-orange-500 via-red-500 to-pink-600 rounded-2xl shadow-2xl">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative px-6 py-8 text-center">
              <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mr-4 shadow-lg">
                  <svg class="w-10 h-10 text-orange-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-7.004 4.87-3.874 0-7.004-2.176-7.004-4.87 0-.183.015-.366.043-.534C4.564 12.205 4.16 11.6 4.16 10.906c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 0 1 .14-.197.35.35 0 0 1 .238-.042l2.906.617a1.214 1.214 0 0 1 1.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 0 0-.231.094.33.33 0 0 0 0 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.056 2.961-.913a.361.361 0 0 0 .029-.463.33.33 0 0 0-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 0 0-.232-.095z"/>
                  </svg>
                </div>
                <div class="text-left">
                  <h1 class="text-3xl md:text-5xl font-black text-white mb-1 leading-tight">
                    Fappify
                  </h1>
                  <p class="text-lg text-white/90 font-medium">
                    The Front Page of the Internet
                  </p>
                </div>
              </div>
              <p class="text-lg text-white/95 mb-6 max-w-2xl mx-auto">
                Dive into anything. Join communities, share your thoughts, and discover content that matters to you.
              </p>
              <div class="flex flex-col sm:flex-row gap-4 justify-center items-center relative z-10">
                <!-- Create Post Button -->
                <a
                  :href="route('posts.create')"
                  class="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-orange-600 bg-white rounded-full shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-110 hover:bg-gray-50 cursor-pointer z-20 min-w-[180px] no-underline"
                  style="pointer-events: auto !important; z-index: 100 !important; position: relative;"
                  @click.stop
                >
                  <span class="flex items-center gap-2">
                    <span class="text-xl">✍️</span>
                    <span>Create Post</span>
                  </span>
                  <div class="absolute inset-0 rounded-full bg-gradient-to-r from-orange-400 to-red-400 opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
                </a>
                
                <!-- Random Communities Button -->
                <button
                  type="button"
                  @click="handleRandomCommunityClick"
                  class="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white border-2 border-white rounded-full hover:bg-white hover:text-orange-600 transition-all duration-300 transform hover:scale-110 cursor-pointer z-20 min-w-[200px]"
                  style="pointer-events: auto !important; z-index: 100 !important; position: relative;"
                >
                  <span class="flex items-center gap-2">
                    <span class="text-xl">🎲</span>
                    <span>Random Communities</span>
                  </span>
                  <div class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-400 to-purple-400 opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
                </button>
                
                <!-- Auth buttons for non-logged in users -->
                <template v-if="!$page.props.auth.user">
                  <div class="flex gap-3 mt-4 relative z-10">
                    <a
                      :href="route('register')"
                      class="inline-flex items-center px-5 py-2 text-sm font-bold text-orange-600 bg-white/90 rounded-full hover:bg-white transition-all duration-300 shadow-md hover:shadow-lg cursor-pointer no-underline"
                      style="pointer-events: auto !important;"
                    >
                      <span class="mr-1">🚀</span> Join Fappify
                    </a>
                    <a
                      :href="route('login')"
                      class="inline-flex items-center px-5 py-2 text-sm font-bold text-white border border-white rounded-full hover:bg-white hover:text-orange-600 transition-all duration-300 cursor-pointer no-underline"
                      style="pointer-events: auto !important;"
                    >
                      Sign In
                    </a>
                  </div>
                </template>
              </div>
            </div>
            <!-- Interactive decorative background -->
            <div class="absolute inset-0 opacity-20 hover:opacity-30 transition-opacity duration-300" style="z-index: 1;">
              <!-- Top Communities Button -->
              <button 
                @click="router.get(route('subfapps.index'))"
                class="absolute top-4 left-4 w-12 h-12 border-2 border-white rounded-full hover:bg-white/20 hover:scale-110 transition-all duration-300 flex items-center justify-center group cursor-pointer"
                style="pointer-events: auto; z-index: 2;"
                title="Browse All Communities"
              >
                <span class="text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity duration-300">🏘️</span>
              </button>
              
              <!-- Random Post Button -->
              <button 
                @click="visitRandomPost"
                class="absolute top-8 right-8 w-6 h-6 border border-white rounded-full hover:bg-white/20 hover:scale-110 transition-all duration-300 flex items-center justify-center group cursor-pointer"
                style="pointer-events: auto; z-index: 2;"
                title="Random Post"
              >
                <span class="text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity duration-300">🎲</span>
              </button>
              
              <!-- Latest Posts Button -->
              <button 
                @click="scrollToLatestPosts"
                class="absolute bottom-4 left-1/3 w-8 h-8 border border-white rounded-full hover:bg-white/20 hover:scale-110 transition-all duration-300 flex items-center justify-center group cursor-pointer"
                style="pointer-events: auto; z-index: 2;"
                title="Latest Posts"
              >
                <span class="text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity duration-300">📝</span>
              </button>
              
              <!-- Search Button -->
              <button 
                @click="focusSearch"
                class="absolute bottom-8 right-4 w-4 h-4 bg-white rounded-full hover:scale-125 transition-all duration-300 flex items-center justify-center group cursor-pointer opacity-80 hover:opacity-100"
                style="pointer-events: auto; z-index: 2;"
                title="Search"
              >
                <span class="text-orange-600 text-xs opacity-0 group-hover:opacity-100 transition-opacity duration-300">🔍</span>
              </button>
            </div>
          </div>

          <!-- Reddit-style Quick Stats Cards -->
          <div class="hidden sm:grid sm:grid-cols-4 gap-4">
            <!-- Total Posts -->
            <div class="bg-white rounded-xl border-l-4 border-orange-500 shadow-lg p-4 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
              <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 bg-orange-100 rounded-full mr-3">
                  <span class="text-lg">📝</span>
                </div>
                <div>
                  <div class="text-xl font-black text-gray-900">{{ stats.total_posts || '0' }}</div>
                  <div class="text-xs text-gray-600 font-semibold uppercase tracking-wider hidden sm:block">Posts</div>
                </div>
              </div>
            </div>

            <!-- Communities -->
            <div class="bg-white rounded-xl border-l-4 border-blue-500 shadow-lg p-4 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
              <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-full mr-3">
                  <span class="text-lg">🏘️</span>
                </div>
                <div>
                  <div class="text-xl font-black text-gray-900">{{ stats.total_communities || '0' }}</div>
                  <div class="text-xs text-gray-600 font-semibold uppercase tracking-wider hidden sm:block">Communities</div>
                </div>
              </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white rounded-xl border-l-4 border-green-500 shadow-lg p-4 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
              <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-full mr-3">
                  <span class="text-lg">👥</span>
                </div>
                <div>
                  <div class="text-xl font-black text-gray-900">{{ stats.active_users || '0' }}</div>
                  <div class="text-xs text-gray-600 font-semibold uppercase tracking-wider hidden sm:block">Users</div>
                </div>
              </div>
            </div>

            <!-- Online Now -->
            <div class="bg-white rounded-xl border-l-4 border-purple-500 shadow-lg p-4 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
              <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 bg-purple-100 rounded-full mr-3">
                  <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                </div>
                <div>
                  <div class="text-xl font-black text-gray-900">{{ stats.online_users || '1' }}</div>
                  <div class="text-xs text-gray-600 font-semibold uppercase tracking-wider hidden sm:block">Online</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Reddit-style Sort Tabs -->
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-2 mb-6">
            <PostSortTabs :current-sort="currentSort" />
          </div>


          <!-- Reddit-style Posts Feed with Infinite Scroll -->
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between p-4 border-b border-gray-100">
              <h2 class="text-xl font-black text-gray-900 flex items-center">
                <span class="text-2xl mr-2">🟠</span>
                Latest Posts
              </h2>
              <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500">{{ postsData.total || postsData.data.length }} posts</span>
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse" title="Live feed"></div>
              </div>
            </div>

            <!-- Infinite Scroll Posts -->
            <InfiniteScroll
              :initial-data="postsData"
              :load-more-url="route('home')"
              :auto-load="true"
              @load-more="handleLoadMore"
            >
              <template #default="{ items, loading }">
                <div class="divide-y divide-gray-100">
                  <div
                    v-for="post in items"
                    :key="post.id"
                    class="relative hover:bg-gray-50 transition-all duration-300 group rounded-xl mb-3 border border-gray-200 shadow-sm overflow-hidden bg-white"
                  >
                    <div class="p-3">
                      <!-- Post Content -->
                      <div class="flex-1 min-w-0">
                        <!-- Post Header (Reddit-style) -->
                        <div class="flex items-center flex-wrap text-xs text-gray-500 mb-2">
                          <template v-if="post.subfapp">
                            <Link
                              :href="route('subfapps.show', post.subfapp.id)"
                              class="flex items-center mr-2"
                              @click.stop
                            >
                              <div class="w-5 h-5 rounded-full bg-gradient-to-br from-orange-400 to-red-500 mr-1.5 flex items-center justify-center text-white text-xs font-bold">f</div>
                              <span class="font-medium text-black hover:text-orange-600 transition-colors duration-200">
                                f/{{ post.subfapp.name }}
                              </span>
                            </Link>
                          </template>
                          <template v-else>
                            <span class="flex items-center mr-2">
                              <div class="w-5 h-5 rounded-full bg-gray-300 mr-1.5 flex items-center justify-center text-white text-xs font-bold">f</div>
                              <span class="font-medium text-gray-800">
                                f/general
                              </span>
                            </span>
                          </template>
                          
                          <span class="mr-2 flex items-center">
                            Posted by
                            <Link
                              :href="route('users.profile', post.user.id)"
                              class="font-medium hover:text-orange-600 transition-colors duration-200 ml-1"
                              @click.stop
                            >
                              u/{{ post.user.name }}
                            </Link>
                          </span>
                          
                          <span class="flex items-center">
                            <span class="inline-block w-1 h-1 rounded-full bg-gray-400 mr-1"></span> 
                            {{ timeAgo(post.created_at) }}
                          </span>
                          
                          <template v-if="post.user_vote || (post.tags && post.tags.length > 0)">
                            <div class="flex items-center ml-2">
                              <template v-if="post.user_vote && post.user_vote.vote_type === 1">
                                <span class="bg-orange-100 text-orange-600 text-xs px-1.5 py-0.5 rounded-sm font-medium mr-1">UPVOTED</span>
                              </template>
                              <template v-if="post.user_vote && post.user_vote.vote_type === -1">
                                <span class="bg-blue-100 text-blue-600 text-xs px-1.5 py-0.5 rounded-sm font-medium mr-1">DOWNVOTED</span>
                              </template>
                            </div>
                          </template>
                        </div>

                        <!-- Post Flair -->
                        <div v-if="post.flair" class="mb-2">
                          <PostFlair
                            :flair="post.flair"
                            size="sm"
                            @click="handleFlairClick"
                          />
                        </div>

                        <!-- Tags (Reddit-style) -->
                        <div
                          v-if="post.tags?.length"
                          class="flex flex-wrap gap-1 mb-2"
                        >
                          <span
                            v-for="tag in post.tags"
                            :key="tag.id"
                            class="px-2 py-0.5 text-xs font-medium text-orange-700 bg-orange-50 rounded-full border border-orange-100 hover:bg-orange-100 transition-all duration-200 cursor-pointer"
                          >
                            #{{ tag.name }}
                          </span>
                        </div>

                        <!-- Post Title -->
                        <Link
                          :href="route('posts.show', post.id)"
                          class="block hover:no-underline"
                        >
                          <h2 class="text-lg font-bold text-gray-900 hover:text-orange-600 transition-colors duration-200 leading-tight mb-2">
                            {{ post.title }}
                          </h2>
                        </Link>

                        <!-- Post Content -->
                        <Link
                          v-if="post.content && (!post.images || post.images.length === 0)"
                          :href="route('posts.show', post.id)"
                          class="block hover:no-underline"
                        >
                          <div class="text-sm text-gray-600 line-clamp-3 overflow-hidden mb-3" v-html="post.content"></div>
                        </Link>
                        
                        <!-- Image Gallery -->
                        <div v-if="post.images?.length" class="mb-3 mt-2">
                          <Link
                            :href="route('posts.show', post.id)"
                            class="block hover:no-underline"
                          >
                            <div class="relative rounded-md overflow-hidden bg-gray-100">
                              <ImageGallery :images="post.images" class="max-h-[400px] w-full object-cover" />
                            </div>
                          </Link>
                        </div>

                        <!-- Post Actions (with inline vote buttons) -->
                        <div class="mt-2">
                          <PostInteractions 
                            :post="post" 
                            @vote="vote" 
                          />
                        </div>
                        
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </InfiniteScroll>
          </div>


          <!-- Empty State -->
          <div
            v-if="postsData.data.length === 0"
            class="p-12 text-center bg-white rounded-xl shadow-sm"
          >
            <svg
              class="mx-auto w-16 h-16 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No posts yet</h3>
            <p class="mt-2 text-gray-500">
              Get started by creating a new post.
            </p>
            <div class="mt-8">
              <Link
                v-if="$page.props.auth.user"
                :href="route('posts.create')"
                class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-blue-600 rounded-xl border border-transparent shadow-sm transition-all duration-200 transform hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 hover:scale-105"
              >
                <svg
                  class="mr-2 -ml-1 w-5 h-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4v16m8-8H4"
                  />
                </svg>
                New Post
              </Link>
            </div>
          </div>
        </div>

        <!-- Right Sidebar (Hidden on mobile/tablet, visible on lg+) -->
        <div class="hidden lg:flex lg:flex-col lg:w-80 xl:w-96 flex-shrink-0 space-y-4">
          <!-- Trending Communities -->
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gradient-to-r from-orange-50 to-red-50 border-b border-gray-200">
              <h2 class="text-base font-black text-gray-900 flex items-center">
                <span class="text-xl mr-2">🔥</span>
                Trending Today
              </h2>
            </div>
            <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
              <!-- Loading state -->
              <div v-if="isLoadingTrending" class="px-4 py-8 text-center">
                <div class="animate-spin w-6 h-6 border-2 border-orange-500 border-t-transparent rounded-full mx-auto"></div>
                <p class="text-xs text-gray-500 mt-2">Loading trending posts...</p>
              </div>
              
              <!-- Trending Posts -->
              <div v-else-if="trendingPosts && trendingPosts.length > 0" v-for="(post, index) in trendingPosts" :key="'trending-' + post.id" class="px-4 py-3 hover:bg-orange-50 transition-colors duration-200">
                <div class="flex items-center space-x-3">
                  <div class="flex-shrink-0">
                    <div class="w-6 h-6 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                      {{ index + 1 }}
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="text-xs">
                      <Link :href="route('posts.show', post.id)" class="font-semibold text-gray-900 hover:text-orange-600 transition-colors duration-200 line-clamp-2">
                        {{ post.title.length > 60 ? post.title.substring(0, 60) + '...' : post.title }}
                      </Link>
                    </div>
                    <div class="text-xs text-gray-500 mt-1 flex items-center space-x-2">
                      <span class="font-medium">{{ post.subfapp ? `f/${post.subfapp.name}` : 'f/general' }}</span>
                      <span>•</span>
                      <span class="flex items-center">
                        <span class="text-orange-600">▲</span>
                        <span class="ml-1 font-bold">{{ post.score || 0 }}</span>
                      </span>
                      <template v-if="post.trending_score && post.trending_score > 0">
                        <span>•</span>
                        <span class="text-xs text-orange-500 font-semibold">🔥 {{ Math.round(post.trending_score) }}</span>
                      </template>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Empty state -->
              <div v-else class="px-4 py-8 text-center">
                <div class="text-gray-400 mb-2">
                  <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                  </svg>
                </div>
                <p class="text-xs text-gray-500">No trending posts</p>
                <p class="text-xs text-gray-400 mt-1">Check back later for trending content!</p>
              </div>
              
              <!-- View More Link -->
              <div class="px-4 py-3 bg-gradient-to-r from-orange-50 to-red-50">
                <Link href="/" class="text-xs font-bold text-orange-600 hover:text-orange-700 transition-colors duration-200 flex items-center">
                  See More Trending 🔥
                </Link>
              </div>
            </div>
          </div>

          <PopularCommunities :communities="communities" />
        </div>
      </div>
    </div>

    <!-- Full Screen Modal -->
    <div
      v-if="selectedImage && currentPost"
      class="flex fixed inset-0 z-50 justify-center items-center p-4 bg-black bg-opacity-90 backdrop-blur-sm"
      @click="closeImage"
    >
      <!-- Close Button -->
      <button
        class="absolute top-6 right-6 text-white transition-colors duration-200 hover:text-gray-300 focus:outline-none"
        @click="closeImage"
      >
        <svg
          class="w-8 h-8"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>

      <!-- Previous Arrow (Mobile & Desktop) -->
      <button
        v-if="currentPost.images.length > 1 && currentImageIndex > 0"
        class="absolute left-3 top-1/2 z-20 p-2.5 text-white rounded-full backdrop-blur-sm transition-all duration-200 -translate-y-1/2 bg-black/50 hover:bg-black/70 focus:outline-none active:scale-95 touch-manipulation"
        @click.stop="navigateImage(-1)"
      >
        <svg
          class="w-5 h-5"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          stroke-width="2.5"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M15 19l-7-7 7-7"
          />
        </svg>
      </button>

      <!-- Next Arrow (Mobile & Desktop) -->
      <button
        v-if="
          currentPost.images.length > 1 &&
          currentImageIndex < currentPost.images.length - 1
        "
        class="absolute right-3 top-1/2 z-20 p-2.5 text-white rounded-full backdrop-blur-sm transition-all duration-200 -translate-y-1/2 bg-black/50 hover:bg-black/70 focus:outline-none active:scale-95 touch-manipulation"
        @click.stop="navigateImage(1)"
      >
        <svg
          class="w-5 h-5"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          stroke-width="2.5"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M9 5l7 7-7 7"
          />
        </svg>
      </button>

      <!-- Media Container -->
      <div class="relative max-h-[90vh] max-w-[90vw] select-none">
        <template v-if="selectedImage.type === 'video'">
          <video
            ref="videoPlayer"
            class="max-h-[90vh] max-w-[90vw] object-contain rounded-lg shadow-2xl"
            controls
            autoplay
            loop
            @click.stop
          >
            <source
              :src="`/storage/${selectedImage.image_path}`"
              type="video/mp4"
            />
            Your browser does not support the video tag.
          </video>
        </template>
        <template v-else>
          <img
            :src="`/storage/${selectedImage.image_path}`"
            alt=""
            class="max-h-[90vh] max-w-[90vw] object-contain rounded-lg shadow-2xl"
            @click.stop
          />
        </template>
        <!-- Media Counter -->
        <div
          v-if="currentPost.images.length > 1"
          class="absolute bottom-4 left-1/2 px-4 py-2 text-sm font-medium text-white rounded-full backdrop-blur-sm -translate-x-1/2 bg-black/50"
        >
          {{ currentImageIndex + 1 }} / {{ currentPost.images.length }}
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<style scoped>
/* Reddit-style post card hover effects */
.divide-y > div {
  transform: translateY(0);
  transition: all 0.3s ease;
}

.divide-y > div:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px -2px rgba(0, 0, 0, 0.1);
  border-color: #ff4500;
}

/* Smooth animations for vote buttons */
button {
  transition: all 0.2s ease;
}

button:hover {
  transform: scale(1.1);
}

button:active {
  transform: scale(0.95);
}

/* Fancy hover effect for post titles */
h2 {
  position: relative;
  display: inline-block;
}

h2:after {
  content: '';
  position: absolute;
  width: 0;
  height: 2px;
  bottom: -2px;
  left: 0;
  background-color: #ff4500;
  transition: width 0.3s ease;
}

h2:hover:after {
  width: 100%;
}

/* Pulse animation for online indicators */
@keyframes pulse {
  0% { opacity: 0.6; transform: scale(0.9); }
  50% { opacity: 1; transform: scale(1.1); }
  100% { opacity: 0.6; transform: scale(0.9); }
}

.animate-pulse {
  animation: pulse 2s infinite;
}

/* Custom scrollbar for Reddit-like feel */
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
</style>