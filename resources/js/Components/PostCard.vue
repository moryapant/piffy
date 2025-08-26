<template>
  <div
    class="overflow-hidden rounded-2xl border shadow-sm backdrop-blur-sm transition-all duration-200 bg-white/90 border-gray-100/50 hover:border-blue-200/50 hover:shadow-md group"
  >
    <div class="p-4 sm:p-6">
      <!-- Post Header -->
      <div class="flex items-start mb-4 space-x-3 sm:items-center sm:space-x-4">
        <!-- User Avatar -->
        <div
          class="flex justify-center items-center w-10 h-10 text-lg font-semibold text-white bg-gradient-to-br from-blue-500 to-indigo-500 rounded-full"
        >
          {{ post.user.name.charAt(0).toUpperCase() }}
        </div>

        <!-- Post Meta -->
        <div class="flex-grow">
          <div class="flex gap-2 items-center text-sm text-gray-600">
            <span class="font-medium text-gray-900">{{ post.user.name }}</span>
            <span class="text-gray-400">•</span>
            <span>{{ formatDate(post.created_at) }}</span>
          </div>
          <div class="mt-0.5 text-xs text-gray-500">
            {{ post.community ? post.community.name : "General" }}
          </div>
        </div>
      </div>

      <!-- Post Content -->
      <div class="space-y-4">
        <!-- Title -->
        <h2
          class="text-lg font-semibold text-gray-900 transition-colors sm:text-xl group-hover:text-blue-600"
        >
          {{ post.title }}
        </h2>

        <!-- Content -->
        <div
          class="text-sm text-gray-600 sm:text-base"
          v-html="post.content"
        ></div>

        <!-- Images -->
        <div v-if="post.images?.length" class="grid grid-cols-2 gap-2 mt-4">
          <div
            v-for="image in post.images"
            :key="image.id"
            class="overflow-hidden relative bg-gray-100 rounded-lg aspect-video"
          >
            <img
              :src="image.url"
              :alt="post.title"
              class="object-cover absolute inset-0 w-full h-full"
            />
          </div>
        </div>
      </div>

      <!-- Post Footer -->
      <div
        class="flex flex-wrap gap-4 items-center pt-4 mt-6 border-t border-gray-100 sm:gap-6"
      >
        <!-- Vote Buttons -->
        <div class="flex items-center space-x-1.5">
          <button
            class="flex gap-1 items-center px-2 py-1 text-sm font-medium rounded-lg transition-colors"
            :class="{
              'text-blue-600 bg-blue-50': post.user_vote === 1,
              'text-gray-500 hover:text-blue-600 hover:bg-gray-50':
                post.user_vote !== 1,
            }"
            @click="vote(1)"
          >
            <svg
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 15l7-7 7 7"
              />
            </svg>
            <span>{{ post.score || 0 }}</span>
          </button>
          <button
            class="flex gap-1 items-center px-2 py-1 text-sm font-medium rounded-lg transition-colors"
            :class="{
              'text-red-600 bg-red-50': post.user_vote === -1,
              'text-gray-500 hover:text-red-600 hover:bg-gray-50':
                post.user_vote !== -1,
            }"
            @click="vote(-1)"
          >
            <svg
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7"
              />
            </svg>
          </button>
        </div>

        <!-- Comments Count -->
        <button
          class="flex gap-1.5 items-center text-sm text-gray-500 transition-colors hover:text-blue-600"
        >
          <svg
            class="w-5 h-5"
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
          <span>{{ post.comments_count || 0 }} comments</span>
        </button>

        <!-- Share Button -->
        <button
          class="flex gap-1.5 items-center ml-auto text-sm text-gray-500 transition-colors hover:text-blue-600"
          @click="shareOnFacebook"
        >
          <svg
            class="w-5 h-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"
            />
          </svg>
          <span>Share</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  vote_type: null,
});

const vote = (type) => {
  form.vote_type = type;
  form.post(route("posts.vote", props.post.id), {
    preserveScroll: true,
  });
};

const formatDate = (date) => {
  const options = { year: "numeric", month: "short", day: "numeric" };
  return new Date(date).toLocaleDateString("en-US", options);
};

const shareOnFacebook = () => {
  // Construct the absolute URL using window.location.origin and route helper
  const postUrl = new URL(
    route("posts.show", props.post.id),
    window.location.origin
  ).toString();

  // Create the Facebook share URL with proper encoding and metadata
  const shareUrl = new URL("https://www.facebook.com/sharer/sharer.php");
  shareUrl.searchParams.append("u", postUrl);

  // Add Open Graph meta parameters
  if (props.post.images?.length > 0) {
    const imageUrl = new URL(
      props.post.images[0].url,
      window.location.origin
    ).toString();
    shareUrl.searchParams.append("picture", imageUrl);
  }

  // Open the share dialog in a popup window
  window.open(
    shareUrl.toString(),
    "facebook-share-dialog",
    "width=626,height=436,scrollbars=yes,centerscreen=yes"
  );
};
</script>
