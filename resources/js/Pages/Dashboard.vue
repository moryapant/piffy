<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
  user: {
    type: Object,
    required: true
  },
  stats: {
    type: Object,
    required: true
  }
});
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">My Dashboard</h2>
        <span class="px-3 py-1 text-sm font-medium text-indigo-700 bg-indigo-100 rounded-full">
          Online
        </span>
      </div>
    </template>

    <div class="py-12 bg-gray-50">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Profile Card -->
        <div class="overflow-hidden bg-white shadow-lg sm:rounded-xl">
          <div class="relative h-32 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
          <div class="relative px-4 pb-8 -mt-16 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center">
              <img
                :src="user.avatar"
                :alt="user.name"
                class="w-32 h-32 border-4 border-white rounded-full shadow-xl"
              />
              <h2 class="mt-4 text-2xl font-bold text-gray-900">{{ user.name }}</h2>
              <p class="text-gray-600">{{ user.email }}</p>
              <div class="flex items-center mt-2 space-x-2 text-sm text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Joined {{ user.created_at }}</span>
              </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 mt-8 sm:grid-cols-2 lg:grid-cols-3">
              <!-- Posts Card -->
              <div class="p-6 transition-all duration-200 bg-white border rounded-xl hover:shadow-md">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-medium text-gray-900">Total Posts</h3>
                  <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_posts }}</p>
                <p class="mt-1 text-sm text-gray-500">Posts created</p>
              </div>

              <!-- Comments Card -->
              <div class="p-6 transition-all duration-200 bg-white border rounded-xl hover:shadow-md">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-medium text-gray-900">Comments</h3>
                  <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                  </svg>
                </div>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_comments }}</p>
                <p class="mt-1 text-sm text-gray-500">Comments made</p>
              </div>

              <!-- Votes Card -->
              <div class="p-6 transition-all duration-200 bg-white border rounded-xl hover:shadow-md">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-medium text-gray-900">Voting Activity</h3>
                  <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 11l3-3m0 0l3 3m-3-3v8m0-13a9 9 0 110 18 9 9 0 010-18z" />
                  </svg>
                </div>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_votes }}</p>
                <div class="flex justify-between mt-2">
                  <div class="text-center">
                    <span class="text-sm font-medium text-green-600">↑ {{ stats.upvotes_given }}</span>
                    <p class="text-xs text-gray-500">Upvotes</p>
                  </div>
                  <div class="text-center">
                    <span class="text-sm font-medium text-red-600">↓ {{ stats.downvotes_given }}</span>
                    <p class="text-xs text-gray-500">Downvotes</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
