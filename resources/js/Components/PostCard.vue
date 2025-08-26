<template>
  <div
    class="overflow-hidden rounded-2xl border shadow-sm backdrop-blur-sm transition-all duration-200 bg-white/90 border-gray-100/50 hover:border-blue-200/50 hover:shadow-md group"
  >
    <div class="p-3 sm:p-4 md:p-6">
      <!-- Post Header -->
      <div class="flex items-start mb-3 sm:mb-4 space-x-2 sm:space-x-3 md:items-center md:space-x-4">
        <!-- User Avatar -->
        <div
          class="flex-shrink-0 flex justify-center items-center w-8 h-8 sm:w-10 sm:h-10 text-sm sm:text-lg font-semibold text-white bg-gradient-to-br from-blue-500 to-indigo-500 rounded-full"
        >
          {{ post.user.name.charAt(0).toUpperCase() }}
        </div>

        <!-- Post Meta -->
        <div class="flex-grow">
          <div class="flex flex-wrap gap-1 sm:gap-2 items-center text-xs sm:text-sm text-gray-600">
            <span class="font-medium text-gray-900">{{ post.user.name }}</span>
            <span class="text-gray-400">•</span>
            <span>{{ formatDate(post.created_at) }}</span>
          </div>
          <div class="mt-0.5 text-xs text-gray-500 hidden sm:block">
            {{ post.community ? post.community.name : "General" }}
          </div>
        </div>
      </div>

      <!-- Post Content -->
      <div class="space-y-4">
        <!-- Title -->
        <h2
          class="text-base sm:text-lg font-semibold text-gray-900 transition-colors md:text-xl group-hover:text-blue-600 leading-tight"
        >
          {{ post.title }}
        </h2>

        <!-- Content -->
        <div
          class="text-sm text-gray-600 sm:text-base line-clamp-3 sm:line-clamp-none"
          v-html="post.content"
        ></div>

        <!-- Images -->
        <div v-if="post.images?.length" class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-3 sm:mt-4">
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
      <div class="flex flex-wrap gap-2 items-center pt-3 sm:pt-4 mt-4 sm:mt-6 border-t border-gray-100">
        <!-- Vote Buttons Container -->
        <VoteButtonHorizontal 
          :content="post"
          size="small"
          :compact="true"
          @vote="vote"
        />

        <!-- Comments Count -->
        <button class="flex items-center space-x-1.5 px-3 py-1.5 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors duration-200 text-gray-700">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
          <span class="font-medium text-xs">{{ post.comments_count || 0 }} {{ (post.comments_count || 0) === 1 ? 'comment' : 'comments' }}</span>
        </button>

        <!-- Views Count -->
        <div class="flex items-center space-x-1.5 px-3 py-1.5 rounded-full bg-gray-50 text-gray-600">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          <span class="font-medium text-xs">{{ post.views_count || 0 }} {{ (post.views_count || 0) === 1 ? 'view' : 'views' }}</span>
        </div>

        <!-- Share Facebook -->
        <button 
          @click="shareOnFacebook"
          class="flex items-center space-x-1.5 px-3 py-1.5 rounded-full bg-blue-100 hover:bg-blue-200 transition-colors duration-200 text-blue-700"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
          </svg>
          <span class="font-medium text-xs">Facebook</span>
        </button>

        <!-- Share X/Twitter -->
        <button 
          @click="shareOnTwitter"
          class="flex items-center space-x-1.5 px-3 py-1.5 rounded-full bg-black hover:bg-gray-800 transition-colors duration-200 text-white"
        >
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
          </svg>
          <span class="font-medium text-xs">X</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import VoteButtonHorizontal from "@/Components/VoteButtonHorizontal.vue";

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  vote_type: null,
});

const vote = (postId, voteType) => {
  form.vote_type = voteType;
  form.post(route("posts.vote", postId), {
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

const shareOnTwitter = () => {
  const postUrl = new URL(
    route("posts.show", props.post.id),
    window.location.origin
  ).toString();

  const shareText = props.post.title;
  const shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(
    shareText
  )}&url=${encodeURIComponent(postUrl)}`;

  window.open(
    shareUrl,
    "twitter-share-dialog",
    "width=626,height=436,scrollbars=yes,centerscreen=yes"
  );
};
</script>
