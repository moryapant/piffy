<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import PostVoteButtons from "@/Components/PostVoteButtons.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import { timeAgo } from "@/utils/dateUtils";
import ImageGallery from "@/Components/ImageGallery.vue";
import Comments from "@/Components/Comments.vue";

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
});

const voteForm = useForm({
  vote_type: null,
});

const goBack = () => {
  if (window.history.length > 2) {
    window.history.back();
  } else {
    router.visit("/");
  }
};

const vote = (postId, voteType) => {
  voteForm.vote_type = voteType;
  voteForm.post(route("posts.vote", postId), {
    preserveScroll: true,
    onSuccess: () => {
      window.dispatchEvent(new Event("post-voted"));
    },
  });
};

const shareOnFacebook = () => {
  if (typeof window !== "undefined") {
    // Construct the absolute URL using window.location.origin and route helper
    const postUrl = new URL(
      route("posts.show", props.post.id),
      window.location.origin
    ).toString();

    // Create the Facebook share URL with proper encoding and metadata
    const shareUrl = new URL("https://www.facebook.com/sharer/sharer.php");
    shareUrl.searchParams.append("u", postUrl);
    shareUrl.searchParams.append("title", props.post.title);
    shareUrl.searchParams.append(
      "description",
      props.post.content?.substring(0, 300) || ""
    );
    if (props.post.images?.[0]) {
      shareUrl.searchParams.append(
        "picture",
        `${window.location.origin}/storage/${props.post.images[0].image_path}`
      );
    }

    // Open the share dialog in a popup window
    window.open(
      shareUrl.toString(),
      "facebook-share-dialog",
      "width=626,height=436,scrollbars=yes,centerscreen=yes"
    );
  }
};

const shareOnTwitter = () => {
  if (typeof window !== "undefined") {
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
  }
};
</script>

<template>
  <Head :title="post.title" />

  <MainLayout>
    <div
      class="px-0 py-6 mx-auto space-y-4 max-w-3xl sm:py-8 sm:space-y-6 sm:px-6 lg:px-8 lg:max-w-4xl"
    >
      <!-- Back Button -->
      <div class="flex justify-end">
        <button
          @click="goBack"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white transition-all duration-200 bg-gradient-to-r from-blue-500 to-indigo-500 border border-transparent rounded-full shadow-sm hover:from-blue-600 hover:to-indigo-600 hover:scale-[1.02] hover:shadow-md active:scale-[0.98] group"
        >
          <svg
            class="w-3.5 h-3.5 transition-transform duration-200 group-hover:-translate-x-0.5"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="2.5"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"
            />
          </svg>
          <span class="tracking-wide">Back</span>
        </button>
      </div>

      <div
        class="overflow-hidden bg-white rounded-xl border border-gray-100 shadow-sm transition-all duration-200 hover:border-blue-200"
      >
        <!-- Post Header -->
        <div class="p-4 bg-white sm:p-6">
          <div
            class="flex justify-between items-start p-3 mb-4 bg-blue-600 rounded-xl border border-blue-500 shadow-sm"
          >
            <div class="flex gap-3 items-start">
              <!-- User Avatar and Main Info -->
              <UserAvatar
                :username="post.user.name"
                size="md"
                bgColor="blue"
                class="rounded-full ring-2 shadow-sm ring-blue-300/50 shrink-0"
              />
              <!-- Main Info Column -->
              <div class="flex flex-col min-w-0">
                <!-- Top Row: Subfapp and Tags -->
                <div class="flex flex-wrap gap-2 items-center">
                  <Link
                    :href="route('subfapps.show', post.subfapp.id)"
                    class="px-2 py-0.5 text-base font-bold text-blue-600 bg-blue-50 rounded-full transition-all duration-200 hover:text-blue-700 hover:bg-blue-100 hover:tracking-wide shrink-0"
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
                <div class="flex gap-2 items-center mt-1.5 text-sm">
                  <span
                    class="hidden px-1.5 text-xs tracking-wide text-blue-100 uppercase bg-blue-700 rounded sm:inline"
                    >Posted by</span
                  >
                  <Link
                    :href="route('users.profile', post.user.id)"
                    class="font-medium text-white cursor-pointer hover:text-blue-100 hover:underline decoration-blue-200 underline-offset-2"
                    >u/{{ post.user.name }}</Link
                  >
                  <span class="text-blue-200">•</span>
                  <span class="text-sm text-blue-100">{{
                    timeAgo(post.created_at)
                  }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Post Title & Content -->
          <div class="mb-6">
            <h1
              class="mb-4 text-2xl font-bold tracking-tight leading-tight text-gray-900 sm:text-3xl"
            >
              {{ post.title }}
            </h1>

            <div
              class="max-w-none prose prose-lg prose-blue prose-img:rounded-lg prose-headings:font-semibold prose-headings:text-gray-900 prose-p:text-gray-600 prose-a:text-blue-600 hover:prose-a:text-blue-700 prose-strong:font-bold prose-strong:text-gray-900 prose-code:text-blue-600 prose-code:bg-blue-50 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded-md prose-code:before:content-none prose-code:after:content-none prose-img:shadow-md prose-blockquote:border-blue-500 prose-blockquote:bg-blue-50 prose-blockquote:py-1 prose-blockquote:not-italic prose-ul:list-disc prose-ol:list-decimal"
              v-html="post.content"
            ></div>

            <!-- Image Gallery -->
            <div class="-mx-4 mt-6 sm:-mx-6">
              <ImageGallery v-if="post.images?.length" :images="post.images" />
            </div>
          </div>

          <!-- Social Share Card -->
          <div class="pt-6 mt-6 border-t">
            <h3 class="mb-4 text-lg font-semibold">Share this post</h3>
            <div class="flex space-x-4">
              <button
                @click="shareOnFacebook"
                class="flex items-center px-4 py-2 text-white bg-blue-600 rounded-lg transition-colors hover:bg-blue-700"
              >
                <svg
                  class="mr-2 w-5 h-5"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
                  />
                </svg>
                Share on Facebook
              </button>
              <button
                @click="shareOnTwitter"
                class="flex items-center px-4 py-2 text-white bg-sky-500 rounded-lg transition-colors hover:bg-sky-600"
              >
                <svg
                  class="mr-2 w-5 h-5"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"
                  />
                </svg>
                Share on Twitter
              </button>
            </div>
          </div>
        </div>

        <!-- Post Actions -->
        <div class="px-4 py-3 border-t border-gray-100 sm:px-6 sm:py-4">
          <div class="flex gap-4 items-center sm:gap-6">
            <!-- Vote Buttons -->
            <div class="flex items-center" @click.stop>
              <PostVoteButtons :post="post" @vote="vote" />
            </div>

            <!-- Edit Button (Only visible to post owner) -->
            <Link
              v-if="$page.props.auth.user?.id === post.user.id"
              :href="route('posts.edit', post.id)"
              class="inline-flex gap-1.5 items-center text-sm text-gray-600 transition-colors hover:text-blue-600"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-4 h-4"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"
                />
              </svg>
              <span>Edit</span>
            </Link>

            <!-- Comments -->
            <button
              class="flex gap-2 items-center text-gray-500 transition-colors duration-200 hover:text-gray-700"
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

            <!-- Save (Bookmark) -->
            <button
              class="flex justify-center items-center ml-auto w-8 h-8 text-gray-500 transition-colors duration-200 hover:text-gray-700"
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
              <span class="hidden text-sm font-medium sm:inline">Save</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Comments Section -->
      <Comments
        :post-id="post.id"
        :comments="post.comments"
        :comments-count="post.comments_count"
      />
    </div>
  </MainLayout>
</template>
