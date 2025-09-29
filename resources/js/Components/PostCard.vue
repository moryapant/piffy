<template>
  <div
    class="overflow-hidden rounded-2xl border shadow-sm backdrop-blur-sm transition-all duration-200 bg-white/90 dark:bg-gray-800/90 border-gray-100/50 dark:border-gray-700/50 hover:border-blue-200/50 dark:hover:border-blue-600/50 hover:shadow-md group"
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
          <div class="flex flex-wrap gap-1 sm:gap-2 items-center text-xs sm:text-sm text-gray-600 dark:text-gray-300">
            <Link 
              :href="route('users.profile', post.user.id)"
              class="font-medium text-gray-900 dark:text-white hover:text-blue-600 transition-colors"
              @click.stop
            >
              {{ post.user.name }}
            </Link>
            <span class="text-gray-400 dark:text-gray-500">•</span>
            <span>{{ formatDate(post.created_at) }}</span>
          </div>
          <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 hidden sm:block">
            {{ post.community ? post.community.name : "General" }}
          </div>
        </div>

        <!-- Edit/Delete Buttons (for post owner and admin) -->
        <div v-if="$page.props.auth.user && (post.user.id === $page.props.auth.user.id || $page.props.auth.user.is_admin)" class="flex items-center space-x-2">
          <Link 
            :href="route('posts.edit', post.id)"
            class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-full transition-colors"
            title="Edit post"
            @click.stop
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </Link>
          <button 
            @click.stop="showDeleteConfirm = true"
            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-colors"
            title="Delete post"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Post Content -->
      <div class="space-y-4">
        <!-- Clickable Title and Content (links to post show page) -->
        <Link :href="route('posts.show', post.id)" class="block">
          <!-- Title -->
          <h2
            class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white transition-colors md:text-xl group-hover:text-blue-600 dark:group-hover:text-blue-400 leading-tight"
          >
            {{ post.title }}
          </h2>

          <!-- Content -->
          <div
            v-if="post.content"
            class="text-sm text-gray-600 dark:text-gray-300 sm:text-base line-clamp-3 sm:line-clamp-none mt-2"
            v-html="post.content"
          ></div>
        </Link>

        <!-- Images (opens gallery modal, not post show page) -->
        <div v-if="post.images?.length" class="mt-3 sm:mt-4">
          <ImageGallery :images="post.images" />
        </div>
      </div>

      <!-- Post Footer -->
      <div class="pt-3 sm:pt-4 mt-4 sm:mt-6 border-t border-gray-100 dark:border-gray-700">
        <PostInteractions 
          :post="post" 
          @vote="vote" 
        />
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <TransitionRoot appear :show="showDeleteConfirm" as="template">
      <Dialog as="div" @close="showDeleteConfirm = false" class="relative z-50">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/25" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild
              as="template"
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                  Delete Post
                </DialogTitle>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete this post? This action cannot be undone.
                  </p>
                </div>

                <div class="mt-4 flex justify-end space-x-3">
                  <button
                    type="button"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-500"
                    @click="showDeleteConfirm = false"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-red-500 shadow-sm hover:shadow transition-all duration-200"
                    @click="deletePost"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Delete
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { useForm, Link, router } from "@inertiajs/vue3";
import PostInteractions from "@/Components/PostInteractions.vue";
import ImageGallery from "@/Components/ImageGallery.vue";
import { ref } from 'vue';
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from '@headlessui/vue';

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  vote_type: null,
});

const showDeleteConfirm = ref(false);

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

const deletePost = () => {
  router.delete(route('posts.destroy', props.post.id), {
    onSuccess: () => {
      showDeleteConfirm.value = false;
    },
  });
};

</script>
