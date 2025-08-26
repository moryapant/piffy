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
      <div class="pt-3 sm:pt-4 mt-4 sm:mt-6 border-t border-gray-100">
        <PostInteractions 
          :post="post" 
          @vote="vote" 
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import PostInteractions from "@/Components/PostInteractions.vue";

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

</script>
