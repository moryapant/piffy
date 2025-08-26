<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";
import { ref, computed } from "vue";
import { 
  MagnifyingGlassIcon, 
  ArrowTrendingUpIcon, 
  ClockIcon, 
  FireIcon,
  UserGroupIcon,
  RectangleStackIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  subfapps: {
    type: Object,
    required: true,
  },
});

const searchQuery = ref('');
const viewMode = ref('grid'); // 'grid' or 'list'
const sortBy = ref('popular'); // 'popular', 'newest', 'trending'

const selectedCategory = ref('all');

// Access page props
const page = usePage();

// Computed filtered communities
const filteredSubfapps = computed(() => {
  let filtered = props.subfapps.data;
  
  // Filter by search query
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(subfapp => 
      subfapp.name.toLowerCase().includes(query) ||
      subfapp.display_name.toLowerCase().includes(query) ||
      (subfapp.description && subfapp.description.toLowerCase().includes(query))
    );
  }
  
  // Sort by selected option
  if (sortBy.value === 'newest') {
    filtered = filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  } else if (sortBy.value === 'trending') {
    // Sort by a combination of recent posts and members
    filtered = filtered.sort((a, b) => (b.posts_count || 0) - (a.posts_count || 0));
  } else { // popular (default)
    filtered = filtered.sort((a, b) => (b.member_count || 0) - (a.member_count || 0));
  }
  
  return filtered;
});

// Join/Leave toggle
const toggleJoinStatus = (subfapp) => {
  if (!page.props.auth.user) {
    router.visit('/login');
    return;
  }
  
  const endpoint = subfapp.has_joined 
    ? route('subfapp.leave', subfapp.id)
    : route('subfapp.join', subfapp.id);
    
  const method = subfapp.has_joined ? 'delete' : 'post';
  
  router[method](endpoint, {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Refresh the page to update join status
      router.reload({ only: ['subfapps'] });
    }
  });
};
</script>

<template>
  <Head title="Explore Communities" />

  <MainLayout>
    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 dark:from-blue-800 dark:to-indigo-900">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="max-w-3xl">
          <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">Explore Communities</h1>
          <p class="text-lg text-blue-100 mb-6">
            Discover communities you'll love. Find where you belong.
          </p>
          
          <!-- Search Bar -->
          <div class="relative flex items-center max-w-lg">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            </div>
            <input 
              type="text" 
              v-model="searchQuery"
              class="block w-full pl-10 pr-3 py-3 border border-transparent rounded-full focus:outline-none focus:ring-2 focus:ring-white focus:border-white bg-white/10 backdrop-blur-sm text-white placeholder:text-blue-200 shadow-sm"
              placeholder="Search for communities"
            />
          </div>
        </div>
      </div>
    </div>

    <div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-50 dark:bg-blue-900/30 p-3 rounded-full">
                  <UserGroupIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" aria-hidden="true" />
                </div>
                <div class="ml-5">
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Communities</p>
                  <div class="flex items-baseline">
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                      {{ subfapps.total }}
                    </p>
                    <p class="ml-2 text-sm font-medium text-green-600 dark:text-green-400">
                      +5%
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-50 dark:bg-green-900/30 p-3 rounded-full">
                  <RectangleStackIcon class="h-6 w-6 text-green-600 dark:text-green-400" aria-hidden="true" />
                </div>
                <div class="ml-5">
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Posts</p>
                  <div class="flex items-baseline">
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                      {{ subfapps.data.reduce((sum, s) => sum + (s.posts_count || 0), 0) }}
                    </p>
                    <p class="ml-2 text-sm font-medium text-green-600 dark:text-green-400">
                      +12%
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-50 dark:bg-purple-900/30 p-3 rounded-full">
                  <UserGroupIcon class="h-6 w-6 text-purple-600 dark:text-purple-400" aria-hidden="true" />
                </div>
                <div class="ml-5">
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Members</p>
                  <div class="flex items-baseline">
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                      {{ subfapps.data.reduce((sum, s) => sum + (s.member_count || 0), 0) }}
                    </p>
                    <p class="ml-2 text-sm font-medium text-green-600 dark:text-green-400">
                      +8%
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters and Controls -->
        <div class="mb-6 flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between">
          <!-- Sort Filters -->
          <div class="flex items-center justify-center sm:justify-start">
            <div class="flex items-center bg-white dark:bg-gray-800 rounded-full p-1 border border-gray-200 dark:border-gray-700 w-full sm:w-auto">
              <button
                @click="sortBy = 'popular'"
                class="flex-1 sm:flex-none px-2 sm:px-4 py-1.5 text-xs sm:text-sm font-medium rounded-full transition-colors duration-200"
                :class="sortBy === 'popular' ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'"
              >
                <FireIcon class="h-3 w-3 sm:h-4 sm:w-4 inline sm:mr-1" />
                <span class="hidden sm:inline">Popular</span>
              </button>
              <button
                @click="sortBy = 'newest'"
                class="flex-1 sm:flex-none px-2 sm:px-4 py-1.5 text-xs sm:text-sm font-medium rounded-full transition-colors duration-200"
                :class="sortBy === 'newest' ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'"
              >
                <ClockIcon class="h-3 w-3 sm:h-4 sm:w-4 inline sm:mr-1" />
                <span class="hidden sm:inline">New</span>
              </button>
              <button
                @click="sortBy = 'trending'"
                class="flex-1 sm:flex-none px-2 sm:px-4 py-1.5 text-xs sm:text-sm font-medium rounded-full transition-colors duration-200"
                :class="sortBy === 'trending' ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'"
              >
                <ArrowTrendingUpIcon class="h-3 w-3 sm:h-4 sm:w-4 inline sm:mr-1" />
                <span class="hidden sm:inline">Trending</span>
              </button>
            </div>
          </div>
          
          <!-- Create Community Button -->
          <div class="flex justify-center sm:justify-end">
            <Link
              v-if="$page.props.auth.user"
              :href="route('subfapps.create')"
              class="inline-flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-full shadow-sm transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <svg
                class="w-3 h-3 sm:w-4 sm:h-4 sm:mr-1.5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                />
              </svg>
              <span class="hidden sm:inline ml-1.5">Create Community</span>
              <span class="sm:hidden">Create</span>
            </Link>
          </div>
        </div>

        <!-- Communities Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
          <div
            v-for="subfapp in filteredSubfapps"
            :key="subfapp.id"
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-all duration-200"
          >
            <!-- Community Cover Image or Gradient -->
            <div 
              class="h-24 bg-gradient-to-r from-blue-500 to-indigo-600 dark:from-blue-700 dark:to-indigo-800"
              :style="subfapp.cover_image ? `background-image: url(/storage/${subfapp.cover_image}); background-size: cover; background-position: center;` : ''"
            ></div>
            
            <div class="p-4 relative">
              <!-- Avatar -->
              <div class="absolute -top-8 left-4">
                <div v-if="subfapp.icon" class="h-16 w-16 rounded-full border-4 border-white dark:border-gray-800 overflow-hidden shadow-md bg-white dark:bg-gray-800">
                  <img 
                    :src="`/storage/${subfapp.icon}`"
                    class="h-full w-full object-cover" 
                    :alt="subfapp.display_name" 
                  />
                </div>
                <div v-else class="h-16 w-16 rounded-full border-4 border-white dark:border-gray-800 flex items-center justify-center bg-blue-600 text-white shadow-md">
                  <span class="text-xl font-bold">{{ subfapp.display_name.charAt(0).toUpperCase() }}</span>
                </div>
              </div>
              
              <!-- Join/Leave button -->
              <div class="flex justify-end mb-2" v-if="$page.props.auth.user">
                <button 
                  :class="[
                    'px-4 py-1 text-sm font-medium rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200',
                    subfapp.has_joined 
                      ? 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500' 
                      : 'border border-blue-600 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 focus:ring-blue-500'
                  ]"
                  @click.prevent="toggleJoinStatus(subfapp)"
                >
                  {{ subfapp.has_joined ? 'Leave' : 'Join' }}
                </button>
              </div>
              <div class="flex justify-end mb-2" v-else>
                <Link :href="route('login')" class="px-4 py-1 text-sm font-medium rounded-full border border-blue-600 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                  Join
                </Link>
              </div>
              
              <!-- Community Info -->
              <Link :href="route('subfapps.show', subfapp.id)" class="block mt-2">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                  f/{{ subfapp.name }}
                  <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200">
                    {{ subfapp.member_count || 0 }} members
                  </span>
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                  {{ subfapp.display_name }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 line-clamp-2">
                  {{ subfapp.description || "No description available" }}
                </p>
              
                <div class="mt-4 flex items-center justify-between text-sm border-t border-gray-100 dark:border-gray-700 pt-3">
                  <div class="flex items-center space-x-4 text-gray-500 dark:text-gray-400">
                    <div class="flex items-center">
                      <RectangleStackIcon class="h-4 w-4 mr-1" />
                      <span>{{ subfapp.posts_count || 0 }} posts</span>
                    </div>
                    <div class="flex items-center">
                      <UserGroupIcon class="h-4 w-4 mr-1" />
                      <span>{{ subfapp.member_count || 0 }} members</span>
                    </div>
                  </div>
                  <ChevronRightIcon class="h-5 w-5 text-gray-400" />
                </div>
              </Link>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
          <Pagination :links="subfapps.links" />
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<style scoped>
.fade-in {
  animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
  0% { opacity: 0; transform: translateY(10px); }
  100% { opacity: 1; transform: translateY(0); }
}

/* Hover effects for community cards */
.community-card {
  transition: all 0.25s ease;
}

.community-card:hover {
  transform: translateY(-2px);
}

/* Pulse animation for trending communities */
.pulse {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
  100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
}

/* Skeleton loading effect */
.skeleton {
  background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
