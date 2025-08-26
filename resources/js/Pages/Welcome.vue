<script setup>
import PostVoteButtons from "@/Components/PostVoteButtons.vue";
import MainLayout from "@/Layouts/MainLayout.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import CommunitiesSidebar from "@/Components/CommunitiesSidebar.vue";
import PostSortTabs from "@/Components/PostSortTabs.vue";
import { ref, onMounted, onUnmounted, watch } from "vue";
import { timeAgo } from "@/utils/dateUtils";
import { router } from "@inertiajs/vue3";
import ImageGallery from "@/Components/ImageGallery.vue"; // This line was already present in your code

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
});

const currentPage = ref(1);

const goToPage = (page) => {
  router.get(
    route(route().current()),
    { page: page },
    {
      preserveState: true,
      preserveScroll: false,
      only: ["posts"],
      onFinish: () => {
        window.scrollTo({ top: 0, behavior: "smooth" });
      },
    }
  );
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

const form = useForm({
  vote_type: null,
});

const vote = (postId, voteType) => {
  form.vote_type = voteType;
  form.post(route("posts.vote", postId), {
    preserveScroll: true,
  });
};
</script>

<template>
  <Head title="Home" />

  <MainLayout>
    <div
      class="px-2 py-8 mx-auto min-h-screen bg-gray-50 max-w-8xl sm:px-6 lg:px-8"
    >
      <div class="lg:flex lg:gap-8">
        <!-- Main Content -->
        <div class="w-full lg:flex-1">
          <!-- Sort Tabs -->
          <div class="mb-4">
            <PostSortTabs :current-sort="currentSort" />
          </div>
          <!-- Header Section -->

          <!-- Posts List -->
          <div class="-mx-2 space-y-4 sm:space-y-6 sm:mx-0">
            <div
              v-for="post in posts.data"
              :key="post.id"
              class="overflow-hidden bg-white border-gray-100 shadow-sm transition-all duration-200 sm:rounded-xl border-y sm:border hover:border-blue-200 hover:shadow-md group"
            >
              <div class="p-3 sm:p-6">
                <!-- Post Header -->
                <div
                  class="flex flex-col mb-4 sm:flex-row sm:justify-between sm:items-center"
                >
                  <div
                    class="flex items-start space-x-3 sm:items-center sm:space-x-4"
                  >
                    <!-- User Avatar -->
                    <UserAvatar
                      :username="post.user.name"
                      size="md"
                      class="flex-shrink-0 ring-2 ring-white"
                    />
                    <!-- Post Meta Info -->
                    <div class="flex flex-col min-w-0">
                      <template v-if="post.subfapp">
                        <Link
                          :href="route('subfapps.show', post.subfapp.id)"
                          class="text-sm font-bold text-gray-900 truncate transition-colors duration-200 hover:text-blue-600"
                          @click.stop
                        >
                          f/{{ post.subfapp.name }}
                        </Link>
                      </template>
                      <template v-else>
                        <span class="text-sm font-bold text-gray-500 truncate">
                          No Community
                        </span>
                      </template>
                      <div
                        class="flex flex-wrap items-center mt-0.5 text-sm text-gray-500"
                      >
                        <span class="truncate"
                          >Posted by
                          <span
                            class="font-medium cursor-pointer hover:text-blue-600"
                            ><Link
                              :href="route('users.profile', post.user.id)"
                              class="hover:text-primary-600"
                              >u/{{ post.user.name }}</Link
                            ></span
                          ></span
                        >
                        <span class="hidden mx-2 sm:inline">·</span>
                        <span class="text-gray-400 sm:text-gray-500">{{
                          timeAgo(post.created_at)
                        }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Tags -->
                <div
                  v-if="post.tags?.length"
                  class="flex flex-wrap gap-1.5 -mx-1 mb-4 sm:gap-2 sm:mx-0"
                >
                  <span
                    v-for="tag in post.tags"
                    :key="tag.id"
                    class="px-2 sm:px-3 py-1 text-xs font-semibold text-blue-600 bg-blue-50 rounded-lg border border-blue-100 transition-all duration-200 transform cursor-pointer hover:bg-blue-100 hover:scale-105 truncate max-w-[150px]"
                  >
                    #{{ tag.name }}
                  </span>
                </div>

                <!-- Post Content -->
                <div class="space-y-4">
                  <div class="flex-grow">
                    <Link
                      :href="route('posts.show', post.id)"
                      class="block space-y-3 hover:no-underline"
                    >
                      <!-- Title with Highlight Bar -->
                      <div class="relative group/title">
                        <div
                          class="absolute top-0 bottom-0 -left-3 w-1 bg-gradient-to-b from-blue-500 to-indigo-600 rounded-full transition-all duration-300 transform origin-left scale-y-0 sm:-left-6 group-hover/title:scale-y-100"
                        ></div>
                        <h2
                          class="text-lg font-bold tracking-tight leading-snug text-gray-900 transition-colors duration-200 sm:text-xl group-hover:text-blue-600"
                        >
                          <span
                            class="bg-gradient-to-r from-gray-900 to-gray-700 bg-[length:0%_1px] bg-no-repeat bg-left-bottom group-hover:bg-[length:100%_1px] transition-all duration-500 pb-0.5"
                          >
                            {{ post.title }}
                          </span>
                        </h2>
                      </div>

                      <!-- Post Content -->
                      <div
                        class="max-w-none prose prose-sm prose-headings:font-semibold prose-headings:text-gray-900 prose-p:text-gray-600 prose-a:text-blue-600 hover:prose-a:text-blue-700 prose-strong:font-bold prose-strong:text-gray-900 prose-code:text-blue-600 prose-code:bg-blue-50 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded-md prose-code:before:content-none prose-code:after:content-none prose-img:rounded-lg prose-img:shadow-md prose-blockquote:border-blue-500 prose-blockquote:bg-blue-50 prose-blockquote:py-1 prose-blockquote:not-italic prose-ul:list-disc prose-ol:list-decimal line-clamp-3"
                        v-html="post.content"
                      ></div>
                    </Link>
                  </div>
                </div>

                <!-- Image Gallery -->
                <div v-if="post.images?.length" class="mt-4">
                  <ImageGallery :images="post.images" />
                </div>

                <!-- Post Actions -->
                <div
                  class="flex flex-wrap gap-4 items-center pt-4 mt-6 border-t border-gray-100 sm:gap-6"
                >
                  <!-- Vote Buttons -->
                  <div class="flex items-center space-x-2" @click.stop>
                    <PostVoteButtons :post="post" @vote="vote" />
                  </div>

                  <!-- Comments -->
                  <Link
                    :href="route('posts.show', post.id)"
                    class="flex items-center space-x-2 text-gray-500 transition-all duration-200 hover:text-blue-600 group"
                    @click.stop
                  >
                    <svg
                      class="w-5 h-5 transition-transform group-hover:scale-110"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                      />
                    </svg>
                    <span class="text-sm font-medium"
                      >{{ post.comments_count || 0 }}
                      {{
                        post.comments_count === 1 ? "comment" : "comments"
                      }}</span
                    >
                  </Link>
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
              <button
                v-for="link in posts.links"
                :key="link.label"
                @click="goToPage(link.url ? link.url.split('?page=')[1] : 1)"
                :disabled="!link.url"
                :class="[
                  'relative inline-flex items-center px-5 py-3 border text-base font-bold transition-all duration-200',
                  link.active
                    ? 'z-10 bg-blue-50 border-blue-500 text-blue-600 scale-110 shadow-sm'
                    : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 hover:text-blue-600',
                  !link.url && 'cursor-not-allowed opacity-50',
                  link.label.includes('Previous') ? 'rounded-l-lg' : '',
                  link.label.includes('Next') ? 'rounded-r-lg' : '',
                ]"
                v-html="link.label"
              ></button>
            </nav>
          </div>

          <!-- Empty State -->
          <div
            v-if="posts.data.length === 0"
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

        <!-- Right Sidebar -->
        <div class="hidden flex-shrink-0 space-y-6 w-80 lg:block">
          <CommunitiesSidebar :communities="communities" />
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
            :alt="`Post image ${selectedImage.id}`"
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
