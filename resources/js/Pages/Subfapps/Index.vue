<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";

defineProps({
  subfapps: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <Head title="Explore Subfapps" />

  <MainLayout>
    <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <!-- Header Section -->
      <div class="mb-6 sm:mb-8">
        <div
          class="flex flex-col items-start justify-between gap-3 mb-4 sm:flex-row sm:items-center sm:gap-4"
        >
          <div>
            <h1 class="mb-1 text-2xl font-bold text-gray-900 sm:text-3xl">
              Explore Communities
            </h1>
            <p class="text-sm text-gray-600 sm:text-base">
              Discover and join vibrant communities on Fapp
            </p>
          </div>
          <Link
            v-if="$page.props.auth.user"
            :href="route('subfapps.create')"
            class="w-full sm:w-auto inline-flex items-center justify-center px-4 sm:px-6 py-2.5 sm:py-3 border border-transparent text-sm sm:text-base font-medium rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg
              class="w-4 h-4 sm:w-5 sm:h-5 mr-1.5 sm:mr-2"
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
            Create Community
          </Link>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 gap-3 mt-4 sm:grid-cols-3 sm:gap-4">
          <div
            class="px-4 py-3 bg-white border border-gray-100 rounded-lg shadow sm:px-6 sm:py-4"
          >
            <div class="flex items-center justify-between">
              <div>
                <div class="text-lg font-bold text-blue-600 sm:text-2xl">
                  {{ subfapps.total }}
                </div>
                <div class="text-sm text-gray-600 sm:text-base">
                  Communities
                </div>
              </div>
              <svg
                class="w-8 h-8 text-blue-100 sm:w-10 sm:h-10"
                fill="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                />
              </svg>
            </div>
          </div>
          <div
            class="px-4 py-3 bg-white border border-gray-100 rounded-lg shadow sm:px-6 sm:py-4"
          >
            <div class="flex items-center justify-between">
              <div>
                <div class="text-lg font-bold text-green-600 sm:text-2xl">
                  {{
                    subfapps.data.reduce(
                      (sum, s) => sum + (s.posts_count || 0),
                      0
                    )
                  }}
                </div>
                <div class="text-sm text-gray-600 sm:text-base">Posts</div>
              </div>
              <svg
                class="w-8 h-8 text-green-100 sm:w-10 sm:h-10"
                fill="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"
                />
              </svg>
            </div>
          </div>
          <div
            class="px-4 py-3 bg-white border border-gray-100 rounded-lg shadow sm:px-6 sm:py-4"
          >
            <div class="flex items-center justify-between">
              <div>
                <div class="text-lg font-bold text-purple-600 sm:text-2xl">
                  {{
                    subfapps.data.reduce(
                      (sum, s) => sum + (s.member_count || 0),
                      0
                    )
                  }}
                </div>
                <div class="text-sm text-gray-600 sm:text-base">Members</div>
              </div>
              <svg
                class="w-8 h-8 text-purple-100 sm:w-10 sm:h-10"
                fill="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Subfapps Grid -->
      <div
        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 sm:gap-6"
      >
        <div
          v-for="subfapp in subfapps.data"
          :key="subfapp.id"
          class="overflow-hidden transition-all duration-200 bg-white border border-gray-100 rounded-lg shadow-sm hover:shadow-md group"
        >
          <Link :href="route('subfapps.show', subfapp.id)" class="block">
            <!-- Header with Icon -->
            <div class="p-2.5 bg-white">
              <div class="flex items-start space-x-2.5">
                <div v-if="subfapp.avtaar" class="flex-shrink-0 w-10 h-10">
                  <img
                    :src="`/storage/${subfapp.avtaar}`"
                    class="object-cover w-full h-full rounded-md shadow-sm"
                    :alt="subfapp.display_name"
                  />
                </div>
                <div
                  v-else
                  class="flex items-center justify-center flex-shrink-0 w-10 h-10 rounded-md shadow-sm bg-gradient-to-br from-blue-500 to-blue-600"
                >
                  <span class="text-base font-bold text-white">
                    {{ subfapp.display_name.charAt(0).toUpperCase() }}
                  </span>
                </div>
                <div class="flex-1 min-w-0">
                  <h2 class="text-sm font-semibold leading-snug text-gray-900">
                    f/{{ subfapp.name }}
                  </h2>
                  <p class="mb-1 text-xs text-gray-600">
                    {{ subfapp.display_name }}
                  </p>
                  <p class="text-xs text-gray-500 line-clamp-2">
                    {{ subfapp.description }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Stats Bar -->
            <div class="px-2.5 py-2 bg-gray-50 border-t border-gray-100">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="flex items-center text-gray-500">
                    <svg
                      class="w-3.5 h-3.5 mr-1 text-gray-400"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                      />
                    </svg>
                    <span class="text-xs">{{ subfapp.posts_count || 0 }}</span>
                  </div>
                  <div class="flex items-center text-gray-500">
                    <svg
                      class="w-3.5 h-3.5 mr-1 text-gray-400"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                      />
                    </svg>
                    <span class="text-xs">{{ subfapp.member_count || 0 }}</span>
                  </div>
                </div>
                <div class="text-blue-500">
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 5l7 7-7 7"
                    />
                  </svg>
                </div>
              </div>
            </div>
          </Link>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-8">
        <Pagination :links="subfapps.links" />
      </div>
    </div>
  </MainLayout>
</template>
