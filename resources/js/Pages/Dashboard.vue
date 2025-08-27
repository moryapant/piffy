<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
  user: {
    type: Object,
    required: true,
  },
  stats: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 sm:text-2xl">
          My Dashboard
        </h2>
        <span
          class="inline-flex items-center px-3 py-1 text-xs font-medium text-indigo-700 bg-indigo-100 rounded-full sm:text-sm"
        >
          <span class="w-2 h-2 mr-2 bg-green-500 rounded-full"></span>
          Online
        </span>
      </div>
    </template>

    <div class="py-6 bg-gray-50 sm:py-12">
      <div class="mx-auto max-w-7xl">
        <!-- Profile Card -->
        <div class="overflow-hidden bg-white shadow-lg rounded-xl">
          <!-- Cover Image -->
          <div class="relative h-24 bg-gradient-to-r from-indigo-500 to-purple-600 sm:h-32 lg:h-40">
            <div class="absolute inset-0 bg-black opacity-10"></div>
          </div>
          
          <!-- Profile Content -->
          <div class="relative px-4 pb-6 -mt-12 sm:px-6 lg:px-8 sm:-mt-16 sm:pb-8">
            <!-- Profile Info Section -->
            <div class="flex flex-col items-center text-center sm:flex-row sm:text-left sm:items-end sm:gap-6">
              <!-- Avatar -->
              <div class="relative">
                <img
                  :src="
                    user.avatar
                      ? user.avatar.startsWith('http')
                        ? user.avatar
                        : `/storage/${user.avatar}`
                      : 'https://www.gravatar.com/avatar/?d=mp'
                  "
                  :alt="user.name"
                  class="object-cover border-4 border-white rounded-full shadow-xl w-24 h-24 sm:w-32 sm:h-32"
                />
                <div class="absolute bottom-0 right-0 w-6 h-6 bg-green-500 border-2 border-white rounded-full sm:w-8 sm:h-8"></div>
              </div>
              
              <!-- User Details -->
              <div class="flex-1 mt-4 sm:mt-0 sm:pb-4">
                <h2 class="text-xl font-bold text-gray-900 sm:text-2xl lg:text-3xl">
                  {{ user.name }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 sm:text-base">{{ user.email }}</p>
                <div class="flex items-center justify-center mt-2 space-x-2 text-xs text-gray-500 sm:justify-start sm:text-sm">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                  <span>Joined {{ user.created_at }}</span>
                </div>
              </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-4 mt-8 sm:gap-6 sm:grid-cols-2 xl:grid-cols-3">
              <!-- Posts Card -->
              <div class="p-4 transition-all duration-200 bg-white border border-gray-200 rounded-xl hover:shadow-md hover:border-gray-300 sm:p-6">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900 sm:text-lg">Total Posts</h3>
                    <p class="mt-1 text-2xl font-bold text-gray-900 sm:text-3xl">
                      {{ stats.total_posts }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500 sm:text-sm">Posts created</p>
                  </div>
                  <div class="flex-shrink-0 ml-4">
                    <svg class="w-6 h-6 text-indigo-500 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                      />
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Comments Card -->
              <div class="p-4 transition-all duration-200 bg-white border border-gray-200 rounded-xl hover:shadow-md hover:border-gray-300 sm:p-6">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900 sm:text-lg">Comments</h3>
                    <p class="mt-1 text-2xl font-bold text-gray-900 sm:text-3xl">
                      {{ stats.total_comments }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500 sm:text-sm">Comments made</p>
                  </div>
                  <div class="flex-shrink-0 ml-4">
                    <svg class="w-6 h-6 text-green-500 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
                      />
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Votes Card -->
              <div class="p-4 transition-all duration-200 bg-white border border-gray-200 rounded-xl hover:shadow-md hover:border-gray-300 sm:p-6 sm:col-span-2 xl:col-span-1">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900 sm:text-lg">Voting Activity</h3>
                    <p class="mt-1 text-2xl font-bold text-gray-900 sm:text-3xl">
                      {{ stats.total_votes }}
                    </p>
                    <div class="flex gap-6 mt-3 sm:gap-8">
                      <div class="text-center">
                        <span class="text-xs font-medium text-green-600 sm:text-sm">↑ {{ stats.upvotes_given }}</span>
                        <p class="text-xs text-gray-500">Upvotes</p>
                      </div>
                      <div class="text-center">
                        <span class="text-xs font-medium text-red-600 sm:text-sm">↓ {{ stats.downvotes_given }}</span>
                        <p class="text-xs text-gray-500">Downvotes</p>
                      </div>
                    </div>
                  </div>
                  <div class="flex-shrink-0 ml-4">
                    <svg class="w-6 h-6 text-blue-500 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 11l3-3m0 0l3 3m-3-3v8m0-13a9 9 0 110 18 9 9 0 010-18z"
                      />
                    </svg>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 sm:mt-8">
              <h3 class="mb-4 text-lg font-medium text-gray-900">Quick Actions</h3>
              <div class="grid gap-3 sm:gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a href="/posts/create" class="flex items-center justify-center p-3 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:p-4">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Create Post
                </a>
                <a href="/subfapps" class="flex items-center justify-center p-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:p-4">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  Browse Communities
                </a>
                <a href="/" class="flex items-center justify-center p-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:p-4">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m7 7l5 5l5-5" />
                  </svg>
                  View Feed
                </a>
                <a href="/profile" class="flex items-center justify-center p-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:p-4">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  Edit Profile
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
