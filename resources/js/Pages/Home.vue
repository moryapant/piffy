<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import { Head } from "@inertiajs/vue3";
import PostSortTabs from "@/Components/PostSortTabs.vue";
import PostCard from "@/Components/PostCard.vue";
import CommunitiesSidebar from "@/Components/CommunitiesSidebar.vue";

defineProps({
  posts: Object,
  currentSort: String,
  timeframe: String,
  communities: Array,
});
</script>

<template>
  <Head title="Home" />

  <MainLayout>
    <div class="py-6 sm:py-8 mx-auto space-y-4 sm:space-y-6 px-4 sm:px-6 lg:px-8">
      <!-- Main Content -->
      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Posts Section -->
        <div class="flex-grow space-y-4">
          <!-- Sort Tabs -->
          <PostSortTabs
            :current-sort="currentSort"
            :timeframe="timeframe"
          />

          <!-- Posts List -->
          <div v-if="posts.data.length > 0" class="space-y-4">
            <PostCard
              v-for="post in posts.data"
              :key="post.id"
              :post="post"
            />
          </div>
          <div v-else class="text-center py-12">
            <div class="p-8 bg-white/90 rounded-2xl border border-gray-100/50 shadow-lg backdrop-blur-sm">
              <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
              </svg>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">No Posts Yet</h3>
              <p class="text-gray-600">Be the first one to share something interesting!</p>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:w-80 xl:w-96 space-y-4">
          <CommunitiesSidebar :communities="communities" />
        </div>
      </div>
    </div>
  </MainLayout>
</template>
