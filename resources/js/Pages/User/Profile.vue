<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import { Head } from "@inertiajs/vue3";
import UserAvatar from "@/Components/UserAvatar.vue";
import { timeAgo } from "@/utils/dateUtils";
import ImageGallery from "@/Components/ImageGallery.vue";

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  posts: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <Head :title="user.name + '\'s Profile'" />

  <MainLayout>
    <div
      class="px-2 py-8 mx-auto min-h-screen bg-gray-50 max-w-8xl sm:px-6 lg:px-8"
    >
      <!-- User Profile Header -->
      <div class="p-6 mb-6 bg-white rounded-lg shadow">
        <div class="flex gap-4 items-center">
          <UserAvatar :username="user.name" size="lg" />
          <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ user.name }}</h1>
            <p class="text-gray-600">
              Member since {{ timeAgo(user.created_at) }}
            </p>
          </div>
        </div>
      </div>

      <!-- User Posts -->
      <div class="space-y-4">
        <h2 class="text-xl font-semibold text-gray-900">
          Posts by {{ user.name }}
        </h2>

        <div
          v-if="posts.data.length === 0"
          class="p-6 text-center bg-white rounded-lg"
        >
          <p class="text-gray-600">No posts yet</p>
        </div>

        <div v-else class="-mx-2 space-y-4 sm:space-y-6 sm:mx-0">
          <div
            v-for="post in posts.data"
            :key="post.id"
            class="overflow-hidden bg-white rounded-lg shadow transition-shadow hover:shadow-md"
          >
            <div class="p-3 sm:p-6">
              <!-- Post Header -->
              <div
                class="flex flex-col mb-4 sm:flex-row sm:justify-between sm:items-center"
              >
                <div class="flex gap-3 items-center">
                  <UserAvatar :username="post.user.name" />
                  <div>
                    <div class="flex gap-2 items-center">
                      <span class="font-medium text-gray-900">
                        {{ post.user.name }}
                      </span>
                      <span class="text-gray-500">&middot;</span>
                      <span class="text-sm text-gray-500">
                        {{ timeAgo(post.created_at) }}
                      </span>
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ post.subfapp ? "in " + post.subfapp.name : "" }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Post Content -->
              <div>
                <h2 class="mb-2 text-xl font-semibold text-gray-900">
                  <Link
                    :href="route('posts.show', post.id)"
                    class="hover:text-primary-600"
                  >
                    {{ post.title }}
                  </Link>
                </h2>
                <p class="text-gray-600">{{ post.body }}</p>
              </div>

              <!-- Image Gallery -->
              <div v-if="post.images?.length" class="mt-4">
                <ImageGallery :images="post.images" :post="post" />
              </div>

              <!-- Share Buttons -->
              <div class="flex items-center mt-4 space-x-4">
                <a
                  :href="`https://twitter.com/intent/tweet?text=${encodeURIComponent(
                    post.title
                  )}&url=${encodeURIComponent(
                    `https://fappify.in/posts/${post.id}`
                  )}`"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-full transition-colors duration-200 hover:bg-gray-200"
                >
                  <svg
                    class="mr-1.5 w-4 h-4"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"
                    />
                  </svg>
                  Share on Twitter
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div
          class="flex justify-center py-6"
          v-if="posts.links && posts.links.length > 3"
        >
          <nav
            class="inline-flex relative z-0 gap-1 rounded-lg shadow-md"
            aria-label="Pagination"
          >
            <Link
              v-for="(link, index) in posts.links"
              :key="index"
              :href="link.url"
              v-html="link.label"
              class="inline-flex relative items-center px-4 py-2 text-sm font-medium bg-white border"
              :class="{
                'text-gray-500 hover:bg-gray-50': !link.active && link.url,
                'z-10 border-primary-500 text-primary-600': link.active,
                'cursor-not-allowed bg-gray-100 text-gray-400': !link.url,
                'rounded-l-lg': index === 0,
                'rounded-r-lg': index === posts.links.length - 1,
              }"
            />
          </nav>
        </div>
      </div>
    </div>
  </MainLayout>
</template>
