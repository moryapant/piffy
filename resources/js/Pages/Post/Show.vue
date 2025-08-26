<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import VoteButtonHorizontal from "@/Components/VoteButtonHorizontal.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import { timeAgo } from "@/utils/dateUtils";
import ImageGallery from "@/Components/ImageGallery.vue";
import Comments from "@/Components/Comments.vue";
// No local reactive state needed currently

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
  // Reset the form before setting new values
  voteForm.reset();
  // Ensure voteType is an integer
  voteForm.vote_type = parseInt(voteType);
  console.log('Voting:', { postId, voteType: parseInt(voteType), originalType: voteType });
  voteForm.post(route("posts.vote", postId), {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Vote recorded successfully');
      window.dispatchEvent(new Event("post-voted"));
    },
    onError: (errors) => {
      console.error('Error recording vote:', errors);
    }
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
    <div class="max-w-6xl px-2 sm:px-4 mx-auto">
      <!-- Top Navigation with Back Button -->
      <div class="flex items-center justify-between py-3 mb-2">
        <!-- Back Button (Top) -->
        <button
          @click="goBack"
          class="group flex items-center space-x-2 px-4 py-2 rounded-xl bg-white border border-gray-200 hover:border-orange-200 hover:bg-orange-50 transition-all duration-300 text-gray-700 hover:text-orange-600 shadow-sm hover:shadow-md"
        >
          <svg class="w-4 h-4 transition-transform duration-300 group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
          </svg>
          <span class="text-sm font-medium hidden sm:inline">Back</span>
        </button>
        
        <!-- Breadcrumbs -->
        <div class="flex items-center text-sm text-gray-600">
          <Link href="/" class="hover:text-orange-500 transition-colors duration-200">Home</Link>
          <span class="mx-2">/</span>
          <Link 
            :href="route('subfapps.show', post.subfapp ? post.subfapp.id : '')" 
            class="hover:text-orange-500 transition-colors duration-200"
            v-if="post.subfapp"
          >
            f/{{ post.subfapp.name }}
          </Link>
          <span v-else class="text-gray-400">general</span>
          <span class="mx-2">/</span>
          <span class="text-gray-400 truncate max-w-[150px] sm:max-w-[250px]">{{ post.title }}</span>
        </div>
      </div>

      <div class="flex flex-col md:flex-row gap-2 sm:gap-4">
        <!-- Main Content -->
        <div class="flex-1">
          <!-- Post Card -->
          <div class="bg-white rounded-lg sm:rounded-md border border-gray-200 shadow mb-2 sm:mb-4">
            <div class="p-3 sm:p-4">
              <!-- Meta Header -->
              <div class="flex flex-wrap items-center text-xs text-gray-500 mb-3">
                <template v-if="post.subfapp">
                  <Link
                    :href="route('subfapps.show', post.subfapp.id)"
                    class="flex items-center mr-3 group"
                  >
                    <div class="w-5 h-5 rounded-full bg-gradient-to-br from-orange-400 to-red-500 mr-1.5 flex items-center justify-center text-white text-xs font-bold">f</div>
                    <span class="font-medium text-gray-900 group-hover:text-orange-600 transition-colors">
                      f/{{ post.subfapp.name }}
                    </span>
                  </Link>
                </template>
                <span class="mx-1 text-gray-300 select-none">•</span>
                <span>Posted by</span>
                <UserAvatar :user="post.user" class="w-5 h-5 mx-1" />
                <span class="font-medium text-gray-700 mr-1">u/{{ post.user?.name || 'unknown' }}</span>
                <span class="text-gray-400">{{ timeAgo(post.created_at) }}</span>
              </div>

              <!-- Title -->
              <h1 class="text-xl sm:text-2xl font-bold leading-snug text-gray-900 mb-4">
                {{ post.title }}
              </h1>

              <!-- Images -->
              <div v-if="post.images && post.images.length" class="mb-4">
                <ImageGallery :images="post.images" />
              </div>

              <!-- Content Body -->
              <div v-if="post.content" class="prose max-w-none text-gray-800 mb-4" v-html="post.content"></div>
              <p v-else class="text-gray-600 mb-4 whitespace-pre-line">
                {{ post.excerpt || '' }}
              </p>

              <!-- Unified Actions Below Content -->
              <div class="mt-6 pt-4 border-t border-gray-100">
                <div class="flex flex-wrap items-center gap-3">
                  <VoteButtonHorizontal 
                    :content="post"
                    size="medium"
                    :compact="true"
                    @vote="vote"
                  />
                  <button class="flex items-center space-x-2 px-4 py-2 rounded-full bg-gray-100 hover:bg-orange-50 hover:text-orange-600 transition-all duration-200 text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <span class="font-medium text-sm">{{ post.comments_count || 0 }} {{ post.comments_count === 1 ? 'comment' : 'comments' }}</span>
                  </button>
                  <div class="flex items-center space-x-2 px-4 py-2 rounded-full bg-gray-50 text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span class="font-medium text-sm">{{ post.views_count || 0 }} {{ (post.views_count || 0) === 1 ? 'view' : 'views' }}</span>
                  </div>
                  <button class="flex items-center space-x-2 px-4 py-2 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors duration-200 text-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                    </svg>
                    <span class="font-medium text-sm">Share</span>
                  </button>
                  <button 
                    @click="shareOnFacebook"
                    class="flex items-center space-x-1.5 px-3 py-2 rounded-full bg-blue-50 hover:bg-blue-100 transition-colors duration-200 text-blue-600 hover:text-blue-700"
                    title="Share on Facebook"
                  >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                    </svg>
                  </button>
                  <button 
                    @click="shareOnTwitter"
                    class="flex items-center space-x-1.5 px-3 py-2 rounded-full bg-gray-900 hover:bg-black transition-colors duration-200 text-white"
                    title="Share on X"
                  >
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Comments Section -->
          <div class="bg-white rounded-lg sm:rounded-md border border-gray-200 shadow mb-2 sm:mb-4 p-3 sm:p-4">
            <h3 class="text-lg font-bold mb-4 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
              Comments ({{ post.comments_count || 0 }})
            </h3>
            
            <Comments
              :post-id="post.id"
              :comments="post.comments"
              :comments-count="post.comments_count"
            />
          </div>
        </div>
        
        <!-- Sidebar -->
        <div class="md:w-80 lg:w-96 space-y-4">
          <!-- Community Card -->
          <div v-if="post.subfapp" class="bg-white rounded-md border border-gray-200 shadow overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-red-500 h-12"></div>
            <div class="p-4">
              <div class="flex items-center -mt-8 mb-3">
                <div class="w-12 h-12 rounded-full bg-white border-4 border-white shadow-md flex items-center justify-center overflow-hidden">
                  <div class="w-full h-full bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center text-white text-lg font-bold">
                    f
                  </div>
                </div>
                <h3 class="ml-2 text-lg font-bold">f/{{ post.subfapp.name }}</h3>
              </div>
              
              <p class="text-sm text-gray-600 mb-4" v-if="post.subfapp.description">
                {{ post.subfapp.description }}
              </p>
              <p class="text-sm text-gray-600 mb-4" v-else>
                A community for sharing content about {{ post.subfapp.name }}.
              </p>
              
              <div class="flex items-center text-sm mb-4">
                <div class="flex items-center mr-4">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                  <span>{{ post.subfapp.users_count || Math.floor(Math.random() * 900) + 100 }} members</span>
                </div>
                <div class="flex items-center">
                  <div class="h-2 w-2 bg-green-500 rounded-full animate-pulse mr-1.5"></div>
                  <span>{{ Math.floor(Math.random() * 50) + 5 }} online</span>
                </div>
              </div>
              
              <Link
                :href="route('subfapps.show', post.subfapp.id)"
                class="block w-full py-2 text-center bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-md transition-colors"
              >
                View Community
              </Link>
            </div>
          </div>
          
          <!-- Related Posts -->
          <div class="bg-white rounded-md border border-gray-200 shadow overflow-hidden">
            <div class="p-3 border-b border-gray-100 bg-gray-50">
              <h3 class="font-bold text-gray-800">Related Posts</h3>
            </div>
            <div class="divide-y divide-gray-100">
              <div v-for="i in 3" :key="i" class="p-3 hover:bg-gray-50 transition-colors">
                <Link href="#" class="block">
                  <h4 class="text-sm font-medium text-gray-900 hover:text-orange-600 transition-colors mb-1 line-clamp-2">
                    {{ 
                      ["Check out this interesting topic related to " + post.title,
                       "Another great post about " + (post.subfapp ? post.subfapp.name : "this topic"),
                       "You might also like: similar content to what you're reading"][i-1] 
                    }}
                  </h4>
                  <div class="flex items-center text-xs text-gray-500">
                    <span class="mr-1">{{ Math.floor(Math.random() * 500) + 10 }} votes</span>
                    <span class="mx-1">•</span>
                    <span>{{ Math.floor(Math.random() * 50) + 5 }} comments</span>
                  </div>
                </Link>
              </div>
            </div>
            <div class="p-3 border-t border-gray-100 bg-gray-50">
              <Link href="/" class="text-xs font-medium text-orange-600 hover:text-orange-700 transition-colors">
                See more
              </Link>
            </div>
          </div>
          
          <!-- Footer -->
          <div class="text-xs text-gray-500 p-3">
            <div class="flex flex-wrap gap-x-2 gap-y-1 mb-2">
              <Link href="#" class="hover:underline hover:text-gray-700">Help</Link>
              <Link href="#" class="hover:underline hover:text-gray-700">About</Link>
              <Link href="#" class="hover:underline hover:text-gray-700">Terms</Link>
              <Link href="#" class="hover:underline hover:text-gray-700">Privacy</Link>
              <Link href="#" class="hover:underline hover:text-gray-700">Advertise</Link>
            </div>
            <p>&copy; 2025 Fappify. All rights reserved.</p>
          </div>
        </div>
      </div>
      
      <!-- Floating Back Button (Mobile) -->
      <button
        @click="goBack"
        class="fixed bottom-6 right-6 sm:hidden z-50 group flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 transition-all duration-300 text-white shadow-2xl shadow-orange-500/25 hover:shadow-3xl hover:shadow-orange-500/35 hover:scale-110"
      >
        <svg class="w-5 h-5 transition-transform duration-300 group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
      </button>
    </div>
  </MainLayout>
</template>

<style scoped>
/* Enhance comment transition */
.prose {
  font-size: 15px;
}

/* Back button animations */
.hover\:shadow-3xl:hover {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04), 0 0 30px rgba(249, 115, 22, 0.3);
}

/* Floating button entrance animation */
@keyframes slideInUp {
  from {
    transform: translateY(100px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.fixed.bottom-6.right-6 {
  animation: slideInUp 0.6s ease-out;
}

.prose img {
  border-radius: 0.375rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.prose a {
  text-decoration: none;
  color: #f97316;
  transition: color 0.2s ease;
}

.prose a:hover {
  color: #ea580c;
  text-decoration: underline;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background-color: #f1f1f1;
  border-radius: 8px;
}

::-webkit-scrollbar-thumb {
  background-color: #ff4500;
  border-radius: 8px;
}

::-webkit-scrollbar-thumb:hover {
  background-color: #e03d00;
}

/* Button hover effects */
button {
  transition: all 0.2s ease;
}

button:hover {
  transform: scale(1.05);
}

button:active {
  transform: scale(0.95);
}

/* Vote button effects */
@keyframes upvote {
  0% { transform: scale(1); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

@keyframes downvote {
  0% { transform: scale(1); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

button:active svg {
  animation: upvote 0.3s ease;
}

/* Breadcrumb hover effects */
.hover\:text-orange-500:hover {
  transition: all 0.2s ease;
}

.hover\:text-orange-500:hover {
  transform: translateY(-1px);
}
</style>
