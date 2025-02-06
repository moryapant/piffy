<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import PostVoteButtons from "@/Components/PostVoteButtons.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import { timeAgo } from "@/utils/dateUtils";
import ImageGallery from "@/Components/ImageGallery.vue";

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
});

const voteForm = useForm({
  vote_type: null,
});

const commentForm = useForm({
  content: "",
});

const vote = (postId, voteType) => {
  voteForm.vote_type = voteType;
  voteForm.post(route("posts.vote", postId), {
    preserveScroll: true,
    onSuccess: () => {
      window.dispatchEvent(new Event("post-voted"));
    },
  });
};

const submitComment = () => {
  commentForm.post(route("posts.comments.store", props.post.id), {
    preserveScroll: true,
    onSuccess: () => {
      commentForm.reset();
      window.dispatchEvent(new Event("comment-added"));
    },
  });
};

const deleteComment = (commentId) => {
  if (confirm("Are you sure you want to delete this comment?")) {
    useForm().delete(route("comments.destroy", commentId), {
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <Head :title="post.title" />

  <MainLayout>
    <div class="py-6 sm:py-8 mx-auto space-y-4 sm:space-y-6 px-0 sm:px-6 lg:px-8 max-w-3xl lg:max-w-4xl">
      <div
        class="overflow-hidden bg-white rounded-xl border border-gray-100 shadow-sm transition-all duration-200 hover:border-blue-200"
      >
        <!-- Post Header -->
        <div class="p-4 sm:p-6 bg-white">
          <div class="flex justify-between items-start mb-4 bg-blue-600 p-3 rounded-xl border border-blue-500 shadow-sm">
            <div class="flex items-start gap-3">
              <!-- User Avatar and Main Info -->
              <UserAvatar :username="post.user.name" size="md" bgColor="blue" class="ring-2 ring-blue-300/50 rounded-full shadow-sm shrink-0" />
              <!-- Main Info Column -->
              <div class="flex flex-col min-w-0">
                <!-- Top Row: Subfapp and Tags -->
                <div class="flex items-center flex-wrap gap-2">
                  <Link
                    :href="route('subfapps.show', post.subfapp.id)"
                    class="text-base font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full transition-all duration-200 hover:text-blue-700 hover:bg-blue-100 hover:tracking-wide shrink-0"
                  >
                    f/{{ post.subfapp.name }}
                  </Link>
                  <div v-if="post.tags?.length" class="flex flex-wrap gap-1.5">
                    <span
                      v-for="tag in post.tags"
                      :key="tag.id"
                      class="px-2 py-0.5 text-xs font-medium text-orange-900 bg-orange-100 rounded-full transition-colors hover:bg-orange-200 hover:text-orange-950"
                    >
                      #{{ tag.name }}
                    </span>
                  </div>
                </div>
                <!-- Bottom Row: Posted by and Time -->
                <div class="flex items-center gap-2 mt-1.5 text-sm">
                  <span class="hidden sm:inline text-blue-100 text-xs tracking-wide uppercase bg-blue-700 px-1.5 rounded">Posted by</span>
                  <span class="font-medium text-white cursor-pointer hover:text-blue-100 hover:underline decoration-blue-200 underline-offset-2">u/{{ post.user.name }}</span>
                  <span class="text-blue-200">•</span>
                  <span class="text-blue-100 text-sm">{{ timeAgo(post.created_at) }}</span>
                </div>
              </div>

            </div>
          </div>

          <!-- Post Title & Content -->
          <div class="mb-6">
            <h1 class="mb-4 text-2xl sm:text-3xl font-bold leading-tight text-gray-900 tracking-tight">
              {{ post.title }}
            </h1>

            <div
              class="max-w-none prose prose-lg prose-blue prose-img:rounded-lg prose-headings:font-semibold prose-headings:text-gray-900 prose-p:text-gray-600 prose-a:text-blue-600 hover:prose-a:text-blue-700 prose-strong:font-bold prose-strong:text-gray-900 prose-code:text-blue-600 prose-code:bg-blue-50 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded-md prose-code:before:content-none prose-code:after:content-none prose-img:shadow-md prose-blockquote:border-blue-500 prose-blockquote:bg-blue-50 prose-blockquote:py-1 prose-blockquote:not-italic prose-ul:list-disc prose-ol:list-decimal"
              v-html="post.content"
            ></div>

            <!-- Image Gallery -->
            <ImageGallery v-if="post.images?.length" :images="post.images" class="mt-6" />
          </div>
        </div>

        <!-- Post Actions -->
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-100">
          <div class="flex items-center gap-4 sm:gap-6">
            <!-- Vote Buttons -->
            <div class="flex items-center" @click.stop>
              <PostVoteButtons :post="post" @vote="vote" />
            </div>

            <!-- Comments -->
            <button
              class="flex items-center gap-2 text-gray-500 hover:text-gray-700 transition-colors duration-200"
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
              <span class="text-sm">{{ post.comments_count || 0 }}</span>
            </button>

            <!-- Share -->
            <button
              class="flex items-center gap-2 text-gray-500 hover:text-gray-700 transition-colors duration-200"
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
              <span class="text-sm">Share</span>
            </button>

            <!-- Save (Bookmark) -->
            <button
              class="flex items-center justify-center w-8 h-8 text-gray-500 hover:text-gray-700 transition-colors duration-200 ml-auto"
            >
              <svg
                class="w-4 h-4 transition-colors duration-200 group-hover:text-blue-500"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
                />
              </svg>
              <span class="text-sm font-medium hidden sm:inline">Save</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Comments Section -->
      <div
        class="overflow-hidden bg-white sm:rounded-xl border-y sm:border border-gray-100 shadow-sm transition-all duration-200 hover:border-blue-200"
      >
        <div class="p-3 sm:p-6 space-y-4 sm:space-y-6">
          <!-- Comment Form or Login Prompt -->
          <div>
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Comments</h2>
              <span class="text-sm text-gray-500">{{ post.comments_count || 0 }} {{ post.comments_count === 1 ? 'comment' : 'comments' }}</span>
            </div>

            <!-- Show comment form for authenticated users -->
            <template v-if="$page.props.auth.user">
              <div class="flex items-start space-x-4">
                <!-- User Avatar -->
                <UserAvatar
                  :username="$page.props.auth.user.name"
                  size="sm"
                  bgColor="blue"
                />
                <!-- Comment Input -->
                <div class="flex-grow">
                  <div class="relative group">
                    <textarea
                      v-model="commentForm.content"
                      rows="3"
                      placeholder="What are your thoughts?"
                      class="px-4 py-3 w-full text-sm text-gray-700 rounded-xl border border-gray-200 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder:text-gray-400 transition-all duration-200 group-hover:border-blue-200"
                    ></textarea>
                    <div class="absolute inset-0 rounded-xl pointer-events-none transition-opacity duration-200 opacity-0 group-hover:opacity-100 bg-gradient-to-r from-blue-50/20 to-transparent"></div>
                  </div>
                  <div class="flex justify-end mt-3">
                    <button
                      @click="submitComment"
                      :disabled="commentForm.processing || !commentForm.content.trim()"
                      class="flex items-center px-6 py-2.5 space-x-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-500 rounded-xl transition-all duration-200 hover:from-blue-700 hover:to-blue-600 disabled:opacity-50 disabled:cursor-not-allowed hover:scale-105 hover:shadow-md"
                    >
                      <svg v-if="commentForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <span>{{ commentForm.processing ? "Posting..." : "Post Comment" }}</span>
                    </button>
                  </div>
                </div>
              </div>
            </template>

            <!-- Show login prompt for non-authenticated users -->
            <template v-else>
              <div class="p-4 sm:p-6 text-center bg-gradient-to-b from-gray-50 to-white sm:rounded-xl border-y sm:border border-gray-100 shadow-sm">
                <div class="mb-4">
                  <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                  </svg>
                </div>
                <h3 class="mb-2 text-lg font-semibold text-gray-900">Join the Discussion</h3>
                <p class="mb-6 text-gray-600">Sign in to share your thoughts and engage with the community</p>
                <div class="flex justify-center gap-3 sm:gap-4">
                  <Link
                    :href="route('login')"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-500 rounded-xl transition-all duration-200 hover:from-blue-700 hover:to-blue-600 hover:shadow-md hover:scale-105"
                  >
                    Log in
                  </Link>
                  <Link
                    :href="route('register')"
                    class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white rounded-xl border border-gray-200 transition-all duration-200 hover:border-blue-200 hover:text-blue-600 hover:shadow-md hover:scale-105"
                  >
                    Sign up
                  </Link>
                </div>
              </div>
            </template>
          </div>

          <!-- Comments List -->
          <div
            v-if="post.comments && post.comments.length > 0"
            class="space-y-4"
          >
            <div
              v-for="comment in post.comments"
              :key="comment.id"
              class="group p-4 sm:p-6 rounded-xl border border-gray-100 transition-all duration-200 hover:border-blue-200 hover:shadow-sm bg-gradient-to-r hover:from-blue-50/30 hover:to-transparent"
            >
              <div class="flex items-start space-x-4">
                <UserAvatar
                  :username="comment.user.name"
                  size="sm"
                  bgColor="blue"
                />
                <div class="flex-grow min-w-0">
                  <div class="flex flex-wrap items-center gap-x-2 mb-1.5">
                    <span
                      class="font-semibold text-gray-900 cursor-pointer hover:text-blue-600 truncate max-w-[200px]"
                      >{{ comment.user.name }}</span
                    >
                    <span class="text-sm text-gray-500">·</span>
                    <span class="text-sm text-gray-500">{{ timeAgo(comment.created_at) }}</span>
                  </div>
                  <div class="prose prose-sm max-w-none text-gray-600">
                    {{ comment.content }}
                  </div>
                  <!-- Comment Actions -->
                  <div class="flex items-center mt-3 space-x-4">
                    <button
                      v-if="$page.props.auth.user && $page.props.auth.user.id === comment.user_id"
                      @click="deleteComment(comment.id)"
                      class="inline-flex items-center space-x-1.5 text-xs font-medium text-red-500 opacity-0 group-hover:opacity-100 transition-all duration-200 hover:text-red-600"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      <span>Delete</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- No Comments State -->
          <div v-else class="py-12 text-center">
            <div class="p-8 mx-auto max-w-md bg-gray-50 rounded-xl">
              <svg
                class="mx-auto mb-4 w-12 h-12 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1.5"
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                />
              </svg>
              <h3 class="mb-1 font-medium text-gray-900">No comments yet</h3>
              <p class="text-sm text-gray-500">
                Be the first to share what you think!
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

  </MainLayout>
</template>
