<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import Pagination from "@/Components/Pagination.vue";
import PostSortTabs from "@/Components/PostSortTabs.vue";
import { timeAgo } from "@/utils/dateUtils";
import ImageGallery from "@/Components/ImageGallery.vue";
import PostInteractions from "@/Components/PostInteractions.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue";

const coverImageInput = ref(null);
const avatarImageInput = ref(null);
const isHoveringCover = ref(false);
const isHoveringAvatar = ref(false);
const showDeleteModal = ref(false);
const isJoining = ref(false);
const isLeaving = ref(false);

const coverForm = useForm({
  cover_image: null,
});

const avatarForm = useForm({
  avatar: null,
});

const selectedImage = ref(null);

const openImage = (image, e) => {
  e.preventDefault();
  selectedImage.value = image;
};

const closeImage = () => {
  selectedImage.value = null;
};

const form = useForm({
  vote_type: null,
});

const vote = (postId, voteType) => {
  // Reset the form before setting new values
  form.reset();
  // Ensure voteType is an integer
  form.vote_type = parseInt(voteType);
  console.log('Voting:', { postId, voteType: parseInt(voteType), originalType: voteType });
  form.post(route("posts.vote", postId), {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Vote recorded successfully');
    },
    onError: (errors) => {
      console.error('Error recording vote:', errors);
    }
  });
};

const handleDeleteClick = () => {
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  showDeleteModal.value = false;
  // Use Inertia's router to delete the subfapp
  form.delete(route('subfapps.destroy', props.subfapp.id));
};

const cancelDelete = () => {
  showDeleteModal.value = false;
};

const props = defineProps({
  subfapp: {
    type: Object,
    required: true,
  },
  posts: {
    type: Object,
    required: true,
  },
  currentSort: {
    type: String,
    default: "hot",
  },
  hasJoined: {
    type: Boolean,
    required: true,
  },
  membersCount: {
    type: Number,
    required: true,
  },
});
</script>

<template>
  <Head :title="subfapp.display_name" />

  <MainLayout>
    <!-- Breadcrumb Navigation -->
    <div class="bg-white border-b border-gray-100">
      <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <nav aria-label="Breadcrumb" class="py-2.5 sm:py-3">
          <ol class="flex items-center text-xs sm:text-sm text-gray-500 space-x-1.5 sm:space-x-2 overflow-x-auto scrollbar-none">
            <li class="flex-shrink-0">
              <Link href="/" class="hover:text-orange-600 transition-colors font-medium flex items-center py-1 px-2 sm:px-0 rounded-md hover:bg-gray-50 sm:hover:bg-transparent">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M4.5 10.5V21h6v-6h3v6h6v-10.5" />
                </svg>
                <span class="hidden sm:inline">Home</span>
              </Link>
            </li>
            <li class="text-gray-300 flex-shrink-0">/</li>
            <li class="flex-shrink-0">
              <Link href="/subfapps" class="hover:text-orange-600 transition-colors py-1 px-2 sm:px-0 rounded-md hover:bg-gray-50 sm:hover:bg-transparent">
                <span class="hidden sm:inline">Communities</span>
                <span class="sm:hidden">All</span>
              </Link>
            </li>
            <li class="text-gray-300 flex-shrink-0">/</li>
            <li class="text-gray-900 font-medium truncate max-w-[120px] sm:max-w-[200px] flex-shrink-0" :title="subfapp.display_name">
              f/{{ subfapp.name }}
            </li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Banner and Header -->
    <div
      class="overflow-hidden relative h-[300px] sm:h-[420px] lg:h-96 group"
      @mouseenter="isHoveringCover = true"
      @mouseleave="isHoveringCover = false"
    >
      <!-- Cover Image or Gradient Background -->
      <div
        :class="[
          'absolute inset-0 bg-cover bg-center transition-opacity duration-300',
          {
            'opacity-90':
              isHoveringCover &&
              $page.props.auth.user?.id === subfapp.created_by,
          },
        ]"
        :style="
          subfapp.cover_image
            ? `background-image: url(/storage/${subfapp.cover_image})`
            : 'background-image: linear-gradient(135deg, #ff4500 0%, #ff6b35 25%, #ff8c42 50%, #ffa726 75%, #ffb74d 100%)'
        "
      ></div>
      <!-- Cover Update Button (Only visible to creator when hovering) -->
      <div
        v-if="$page.props.auth.user?.id === subfapp.created_by"
        :class="[
          'absolute inset-0 flex items-center justify-center transition-opacity duration-300',
          isHoveringCover ? 'opacity-100' : 'opacity-0',
        ]"
      >
        <button
          @click="() => coverImageInput.click()"
          class="flex items-center px-6 py-3 space-x-2 text-sm font-medium text-white rounded-full transition-all duration-200 bg-black/50 hover:bg-black/60 backdrop-blur-sm"
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
              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
            />
          </svg>
          <span>Change Cover</span>
        </button>
        <!-- Hidden file inputs -->
        <input
          ref="coverImageInput"
          type="file"
          accept=".jpg,.jpeg,.png,.webp"
          class="hidden"
          @change="
            (e) => {
              coverForm.cover_image = e.target.files[0];
              coverForm.post(route('subfapps.cover.update', subfapp.id), {
                preserveScroll: true,
                onSuccess: () => {
                  e.target.value = null;
                },
              });
            }
          "
        />
        <input
          ref="avatarImageInput"
          type="file"
          accept=".jpg,.jpeg,.png,.webp"
          class="hidden"
          @change="
            (e) => {
              avatarForm.avatar = e.target.files[0];
              avatarForm.post(route('subfapps.avatar.update', subfapp.id), {
                preserveScroll: true,
                onSuccess: () => {
                  e.target.value = null;
                },
              });
            }
          "
        />
      </div>
      <div class="flex items-end px-2 sm:px-4 mx-auto max-w-7xl h-full sm:px-6 lg:px-8">
        <div
          class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between px-3 py-4 w-full rounded-xl sm:rounded-2xl border border-white/30 shadow-xl backdrop-blur-md bg-white/95 sm:px-8 sm:py-7"
        >
          <!-- Left Section (Avatar + Info) -->
          <!-- Left side with avatar and info -->
          <div class="flex items-center space-x-3 sm:space-x-6 min-w-0">
            <!-- Subfapp Icon with improved visibility -->
            <div
              class="flex overflow-hidden relative justify-center items-center -mt-12 w-20 h-20 bg-white border-3 border-white rounded-full shadow-2xl ring-2 ring-orange-200 sm:w-36 sm:h-36 sm:-mt-20 sm:border-4 sm:ring-4 group"
              @mouseenter="isHoveringAvatar = true"
              @mouseleave="isHoveringAvatar = false"
            >
              <img
                v-if="subfapp.icon"
                :src="`/storage/${subfapp.icon}`"
                :alt="subfapp.display_name"
                class="object-cover w-full h-full rounded-full"
              />
              <div
                v-else
                class="flex items-center justify-center w-full h-full rounded-full bg-gradient-to-br from-orange-400 to-pink-500"
              >
                <span
                  class="text-xl font-black text-white sm:text-4xl"
                  >{{ subfapp.display_name[0] }}</span
                >
              </div>

              <!-- Avatar Update Button (Only visible to creator when hovering) -->
              <div
                v-if="$page.props.auth.user?.id === subfapp.created_by"
                :class="[
                  'absolute inset-0 flex items-center justify-center bg-black/50 rounded-full transition-opacity duration-200',
                  isHoveringAvatar ? 'opacity-100' : 'opacity-0',
                ]"
              >
                <button
                  @click="() => avatarImageInput.click()"
                  class="flex gap-2 items-center px-3 py-2 text-sm font-medium text-white rounded-full transition-all duration-200 hover:text-orange-200 backdrop-blur-sm"
                >
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
                      d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                  </svg>
                  <span>Change</span>
                </button>
              </div>
            </div>
            <!-- Subfapp Info with improved text & breadcrumb -->
            <div class="flex-grow min-w-0">
              <nav aria-label="Breadcrumb" class="mb-1 hidden sm:block">
                <ol class="flex items-center text-[11px] font-medium text-gray-500 gap-1">
                  <li>
                    <Link href="/" class="hover:text-orange-600 transition-colors">Home</Link>
                  </li>
                  <li class="text-gray-300" aria-hidden="true">/</li>
                  <li class="text-gray-400 truncate max-w-[140px]">Communities</li>
                  <li class="text-gray-300" aria-hidden="true">/</li>
                  <li class="text-gray-900 truncate max-w-[180px]" :title="subfapp.display_name">f/{{ subfapp.name }}</li>
                </ol>
              </nav>
              <h1 class="text-lg font-black leading-tight gradient-text-animated sm:text-3xl truncate" :title="subfapp.display_name">
                {{ subfapp.display_name }}
              </h1>
              <p class="text-xs sm:text-base font-semibold text-fuchsia-600">f/{{ subfapp.name }}</p>
              <div class="flex flex-wrap items-center gap-3 mt-2 sm:mt-3 text-xs sm:text-sm text-gray-500">
                <div class="flex items-center gap-1 bg-orange-50 px-2 py-1 rounded-full">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                  <span class="font-semibold text-orange-700">{{ membersCount }} {{ membersCount === 1 ? 'member' : 'members' }}</span>
                </div>
                <div class="flex items-center gap-1 bg-green-50 px-2 py-1 rounded-full">
                  <div class="h-2 w-2 bg-green-500 rounded-full animate-pulse"></div>
                  <span class="font-semibold text-green-700">{{ Math.max(1, Math.floor(membersCount * 0.1)) }} online</span>
                </div>
                <div class="flex items-center gap-1 bg-blue-50 px-2 py-1 rounded-full">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m0 0v8a2 2 0 002 2h8a2 2 0 002-2V8" />
                  </svg>
                  <span class="font-semibold text-blue-700">{{ posts.total || 0 }} posts</span>
                </div>
                <div class="flex items-center gap-1 bg-purple-50 px-2 py-1 rounded-full">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <span class="font-semibold text-purple-700">{{ subfapp.views_count || 0 }} views</span>
                </div>
              </div>
            </div>

            <!-- Desktop Join/Leave Button -->
            <div class="hidden items-center space-x-3 md:flex">
              <!-- Owner Controls -->
              <div v-if="$page.props.auth.user?.id === subfapp.created_by" class="flex items-center space-x-2">
                <Link
                  :href="route('subfapps.edit', subfapp.id)"
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-full shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Edit
                </Link>
                <Link
                  :href="route('subfapps.flairs.index', subfapp.id)"
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-purple-700 bg-white border border-purple-300 rounded-full shadow-sm hover:bg-purple-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                  </svg>
                  Flairs
                </Link>
                <button
                  @click="handleDeleteClick"
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-300 rounded-full shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  Delete
                </button>
              </div>
              
              <!-- Join/Leave Buttons -->
              <Link
                v-if="!hasJoined && $page.props.auth.user?.id !== subfapp.created_by"
                :href="route('subfapp.join', subfapp.id)"
                method="post"
                as="button"
                class="inline-flex items-center px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-orange-500 to-pink-500 rounded-full transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 hover:from-orange-600 hover:to-pink-600"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Join Community
              </Link>
              <Link
                v-else-if="hasJoined && $page.props.auth.user?.id !== subfapp.created_by"
                :href="route('subfapp.leave', subfapp.id)"
                method="delete"
                as="button"
                class="inline-flex items-center px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-red-500 to-pink-500 rounded-full transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 hover:from-red-600 hover:to-pink-600"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Leave Community
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sticky Quick Action Bar -->
    <div class="sticky top-0 z-40 bg-white/95 backdrop-blur-sm border-b border-gray-200 shadow-sm">
      <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between py-2.5 sm:py-3">
          <div class="flex items-center space-x-2 sm:space-x-4 min-w-0">
            <!-- Community Quick Info -->
            <div class="flex items-center space-x-2 min-w-0">
              <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gradient-to-br from-orange-400 to-pink-500 flex items-center justify-center text-white text-xs sm:text-sm font-bold flex-shrink-0">
                {{ subfapp.display_name[0] }}
              </div>
              <div class="min-w-0">
                <h2 class="text-sm font-semibold text-gray-900 truncate">f/{{ subfapp.name }}</h2>
                <p class="text-xs text-gray-500 hidden sm:block">{{ membersCount }} members</p>
              </div>
            </div>
          </div>
          
          <!-- Quick Actions -->
          <div class="flex items-center space-x-1.5 sm:space-x-2">
            <!-- Create Post Button -->
            <Link
              v-if="hasJoined"
              :href="route('posts.create', { subfapp: subfapp.id })"
              class="inline-flex items-center px-2.5 py-1.5 sm:px-3 text-xs sm:text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-pink-500 rounded-full hover:from-orange-600 hover:to-pink-600 transition-all duration-200 shadow-sm hover:shadow-md"
            >
              <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 sm:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <span class="hidden sm:inline ml-1">Post</span>
            </Link>
            
            <!-- Join/Leave Button -->
            <Link
              v-if="!hasJoined && $page.props.auth.user?.id !== subfapp.created_by"
              :href="route('subfapp.join', subfapp.id)"
              method="post"
              as="button"
              :disabled="isJoining"
              @before="isJoining = true"
              @finish="isJoining = false"
              class="inline-flex items-center px-2.5 py-1.5 sm:px-3 text-xs sm:text-sm font-medium text-orange-600 border border-orange-600 rounded-full hover:bg-orange-50 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="!isJoining" class="w-3.5 h-3.5 sm:w-4 sm:h-4 sm:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <svg v-else class="w-3.5 h-3.5 sm:w-4 sm:h-4 sm:mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span class="hidden sm:inline ml-1">{{ isJoining ? 'Joining...' : 'Join' }}</span>
              <span class="sm:hidden">{{ isJoining ? '...' : '+' }}</span>
            </Link>
            <Link
              v-else-if="hasJoined && $page.props.auth.user?.id !== subfapp.created_by"
              :href="route('subfapp.leave', subfapp.id)"
              method="delete"
              as="button"
              :disabled="isLeaving"
              @before="isLeaving = true"
              @finish="isLeaving = false"
              class="inline-flex items-center px-2.5 py-1.5 sm:px-3 text-xs sm:text-sm font-medium text-red-600 border border-red-600 rounded-full hover:bg-red-50 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="!isLeaving" class="w-3.5 h-3.5 sm:w-4 sm:h-4 sm:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              <svg v-else class="w-3.5 h-3.5 sm:w-4 sm:h-4 sm:mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span class="hidden sm:inline ml-1">{{ isLeaving ? 'Leaving...' : 'Leave' }}</span>
              <span class="sm:hidden">{{ isLeaving ? '...' : '×' }}</span>
            </Link>
            
            <!-- More Options Menu -->
            <button class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition-colors duration-200">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
  <div class="px-2 py-4 mx-auto max-w-7xl sm:px-6 lg:px-8 bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50 min-h-screen sm:py-6">
      <div class="flex flex-col gap-4 lg:gap-6 lg:flex-row">
        <!-- Left Column: Posts -->
        <div class="flex-1 order-2 space-y-4 sm:space-y-6 lg:order-1">
          <!-- Sort Options -->
          <PostSortTabs :current-sort="currentSort" :subfapp-id="subfapp.id" />
          
          <!-- Join Required Message -->
          <div
            v-if="!hasJoined"
            class="p-8 text-center bg-white rounded-2xl border-2 border-orange-200 shadow-lg"
          >
            <div class="flex justify-center mb-4">
              <div class="w-16 h-16 bg-gradient-to-br from-orange-400 to-pink-500 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Join f/{{ subfapp.name }}</h3>
            <p class="text-gray-600 mb-4">You need to join this community to see and interact with its posts</p>
            <Link
              :href="route('subfapp.join', subfapp.id)"
              method="post"
              as="button"
              class="inline-flex items-center px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-orange-500 to-pink-500 rounded-full transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 hover:from-orange-600 hover:to-pink-600"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Join Community
            </Link>
          </div>
          
          <!-- No Posts Message -->
          <div
            v-else-if="posts.data.length === 0"
            class="p-8 text-center bg-white rounded-2xl border border-gray-200 shadow-lg"
          >
            <div class="flex justify-center mb-4">
              <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">No posts yet</h3>
            <p class="text-gray-600 mb-4">Be the first to share something in this community!</p>
            <Link
              :href="route('posts.create')"
              class="inline-flex items-center px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-blue-500 to-purple-500 rounded-full transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 hover:from-blue-600 hover:to-purple-600"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Create First Post
            </Link>
          </div>
          <!-- Posts List -->
          <div class="space-y-4 sm:space-y-5">
            <div
              v-for="post in posts.data"
              :key="post.id"
              class="bg-white rounded-2xl border border-gray-200 shadow-md hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 hover:border-orange-300 group overflow-hidden relative"
            >
              <!-- Trending Badge -->
              <div v-if="post.is_trending" class="absolute top-4 right-4 z-10">
                <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white text-xs font-bold px-2 py-1 rounded-full flex items-center">
                  <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M13.5 4.06c0-1.336-1.616-2.005-2.56-1.06l-4.5 4.5H4.508c-1.141 0-2.318.664-2.66 1.905A9.76 9.76 0 001.5 12c0 .898.121 1.768.35 2.595.341 1.24 1.518 1.905 2.659 1.905h1.93l4.5 4.5c.945.945 2.561.276 2.561-1.06V4.06zM18.584 5.106a.75.75 0 011.06 0 11.5 11.5 0 010 16.272.75.75 0 11-1.06-1.061 10 10 0 000-14.15.75.75 0 010-1.06z" />
                  </svg>
                  Trending
                </div>
              </div>
              <div class="p-3 sm:p-6">
                <!-- Post Header -->
                <div class="flex items-center mb-3 sm:mb-4 space-x-2 sm:space-x-3">
                  <!-- User Avatar -->
                  <div class="flex overflow-hidden justify-center items-center w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-orange-400 to-pink-500 rounded-full ring-1 sm:ring-2 ring-orange-200 flex-shrink-0">
                    <span class="text-xs sm:text-sm font-bold text-white">{{
                      post.user.name[0].toUpperCase()
                    }}</span>
                  </div>
                  
                  <!-- User Info & Post Meta -->
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-1.5 sm:space-x-2">
                      <Link
                        :href="route('users.profile', post.user.id)"
                        class="text-xs sm:text-sm font-semibold text-gray-900 hover:text-orange-600 transition-colors duration-200 truncate"
                        @click.stop
                      >
                        u/{{ post.user.name }}
                      </Link>
                      <span class="text-xs text-gray-400 hidden sm:inline">•</span>
                      <span class="text-xs text-gray-500 flex-shrink-0">{{
                        timeAgo(post.created_at)
                      }}</span>
                    </div>
                  </div>
                </div>

                <!-- Post Content -->
                <div class="space-y-2.5 sm:space-y-4">
                  <Link :href="route('posts.show', post.id)" class="block">
                    <h2 class="text-base sm:text-xl font-bold text-gray-900 transition-colors duration-200 group-hover:text-orange-600 leading-snug mb-2 line-clamp-2 sm:line-clamp-none">
                      {{ post.title }}
                    </h2>
                    <div
                      v-if="post.content"
                      class="text-sm leading-relaxed text-gray-600 line-clamp-2 sm:line-clamp-3 mb-2 sm:mb-3"
                      v-html="post.content"
                    ></div>
                  </Link>
                  
                  <!-- Tags -->
                  <div v-if="post.tags?.length" class="flex flex-wrap gap-2">
                    <span
                      v-for="tag in post.tags"
                      :key="tag.id"
                      class="inline-flex items-center px-3 py-1 text-xs font-semibold text-orange-700 bg-orange-50 rounded-full border border-orange-200 transition-all duration-200 hover:bg-orange-100 hover:border-orange-300"
                    >
                      #{{ tag.name }}
                    </span>
                  </div>

                  <!-- Image Gallery -->
                  <div v-if="post.images?.length" class="rounded-xl overflow-hidden">
                    <ImageGallery :images="post.images" />
                  </div>
                </div>

                <!-- Post Actions (Standardized) -->
                <div class="pt-3 sm:pt-4 mt-3 sm:mt-4 border-t border-gray-100">
                  <PostInteractions 
                    :post="post" 
                    @vote="vote" 
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="posts.data.length > 0" class="mt-6">
            <Pagination :links="posts.links" />
          </div>
        </div>

        <!-- Right Column: Community Info -->
        <div class="order-1 space-y-4 w-full lg:w-80 lg:order-2 lg:space-y-6">
          <!-- Mobile Join Button -->
          <div class="block lg:hidden">
            <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-200 shadow-lg overflow-hidden">
              <div class="p-4 sm:p-6">
                <Link
                  v-if="!hasJoined"
                  :href="route('subfapp.join', subfapp.id)"
                  method="post"
                  as="button"
                  class="inline-flex items-center justify-center px-6 py-3 w-full text-sm font-bold text-white bg-gradient-to-r from-orange-500 to-pink-500 rounded-full transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 hover:from-orange-600 hover:to-pink-600"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  Join Community
                </Link>
                <div v-else class="space-y-3">
                  <div class="text-center">
                    <div class="inline-flex items-center px-4 py-2 bg-green-50 rounded-full border border-green-200">
                      <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      <span class="text-sm font-semibold text-green-700">You're a member!</span>
                    </div>
                  </div>
                  <Link
                    :href="route('subfapp.leave', subfapp.id)"
                    method="delete"
                    as="button"
                    class="inline-flex items-center justify-center px-6 py-3 w-full text-sm font-bold text-white bg-gradient-to-r from-red-500 to-pink-500 rounded-full transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 hover:from-red-600 hover:to-pink-600"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Leave Community
                  </Link>
                </div>
                <p class="mt-3 text-xs text-center text-gray-500">
                  Join to post and interact with the community
                </p>
              </div>
            </div>
          </div>

          <!-- Quick Community Stats Card -->
          <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-200 shadow-lg overflow-hidden mb-4 lg:mb-6">
            <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-blue-500 to-purple-500">
              <h2 class="text-base sm:text-lg font-bold text-white flex items-center">
                <span class="text-lg sm:text-xl mr-2">📊</span>
                Community Stats
              </h2>
            </div>
            <div class="p-4 sm:p-6">
              <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <div class="text-center p-2.5 sm:p-3 bg-gradient-to-br from-orange-50 to-pink-50 rounded-lg sm:rounded-xl border border-orange-200">
                  <div class="text-xl sm:text-2xl font-black text-orange-600">{{ membersCount }}</div>
                  <div class="text-xs font-semibold text-orange-700">Members</div>
                </div>
                <div class="text-center p-2.5 sm:p-3 bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg sm:rounded-xl border border-blue-200">
                  <div class="text-xl sm:text-2xl font-black text-blue-600">{{ posts.total || 0 }}</div>
                  <div class="text-xs font-semibold text-blue-700">Posts</div>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-3 sm:gap-4 mt-3 sm:mt-4">
                <div class="text-center p-2.5 sm:p-3 bg-gradient-to-br from-green-50 to-teal-50 rounded-lg sm:rounded-xl border border-green-200">
                  <div class="text-xl sm:text-2xl font-black text-green-600">{{ subfapp.views_count || 0 }}</div>
                  <div class="text-xs font-semibold text-green-700">Views</div>
                </div>
                <div class="text-center p-2.5 sm:p-3 bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg sm:rounded-xl border border-purple-200">
                  <div class="text-xl sm:text-2xl font-black text-purple-600">{{ Math.max(1, Math.floor(membersCount * 0.1)) }}</div>
                  <div class="text-xs font-semibold text-purple-700">Online</div>
                </div>
              </div>
            </div>
          </div>

          <!-- About Community -->
          <div class="sticky top-20 bg-white rounded-2xl border border-gray-200 shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-500 to-pink-500">
              <h2 class="text-lg font-bold text-white flex items-center">
                <span class="text-xl mr-2">🏠</span>
                About Community
              </h2>
            </div>
            <div class="p-6 space-y-6">
              <p class="text-sm text-gray-700 leading-relaxed">{{ subfapp.description || "Welcome to this community! Share your thoughts and connect with others." }}</p>

              <!-- Community Rules/Guidelines -->
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h4 class="font-semibold text-yellow-800 mb-2 flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 15.5c-.77.833.192 2.5 1.732 2.5z" />
                  </svg>
                  Community Guidelines
                </h4>
                <ul class="text-xs text-yellow-700 space-y-1">
                  <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>Be respectful and civil in discussions</span>
                  </li>
                  <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>No spam or self-promotion</span>
                  </li>
                  <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>Keep posts relevant to the community topic</span>
                  </li>
                </ul>
              </div>

              <!-- Created Info -->
              <div class="space-y-3 pt-4 border-t border-gray-100">
                <div class="flex items-center text-sm text-gray-600">
                  <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">Created</div>
                    <div class="text-xs text-gray-500">{{ new Date(subfapp.created_at).toLocaleDateString() }}</div>
                  </div>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                  <div class="w-8 h-8 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">Creator</div>
                    <Link
                      v-if="subfapp.creator"
                      :href="route('users.profile', subfapp.creator.id)"
                      class="text-xs text-gray-500 hover:text-orange-600 transition-colors duration-200"
                      @click.stop
                    >
                      u/{{ subfapp.creator.name }}
                    </Link>
                    <div v-else class="text-xs text-gray-500">Unknown</div>
                  </div>
                </div>
              </div>

              <!-- Community Rules -->
              <div class="pt-4 border-t border-gray-100">
                <h3 class="mb-4 text-base font-bold text-gray-900 flex items-center">
                  <span class="text-lg mr-2">📋</span>
                  Community Rules
                </h3>
                <ul class="space-y-3">
                  <li class="flex items-start text-sm">
                    <span class="inline-flex items-center justify-center w-5 h-5 bg-orange-100 text-orange-600 rounded-full text-xs font-bold mr-3 mt-0.5">1</span>
                    <span class="text-gray-700">Be respectful to others</span>
                  </li>
                  <li class="flex items-start text-sm">
                    <span class="inline-flex items-center justify-center w-5 h-5 bg-orange-100 text-orange-600 rounded-full text-xs font-bold mr-3 mt-0.5">2</span>
                    <span class="text-gray-700">No hate speech or bullying</span>
                  </li>
                  <li class="flex items-start text-sm">
                    <span class="inline-flex items-center justify-center w-5 h-5 bg-orange-100 text-orange-600 rounded-full text-xs font-bold mr-3 mt-0.5">3</span>
                    <span class="text-gray-700">No spam or self-promotion</span>
                  </li>
                  <li class="flex items-start text-sm">
                    <span class="inline-flex items-center justify-center w-5 h-5 bg-orange-100 text-orange-600 rounded-full text-xs font-bold mr-3 mt-0.5">4</span>
                    <span class="text-gray-700">Follow site content policy</span>
                  </li>
                </ul>
              </div>

              <!-- Quick Actions -->
              <div class="pt-4 border-t border-gray-100">
                <h3 class="mb-3 text-base font-bold text-gray-900 flex items-center">
                  <span class="text-lg mr-2">⚡</span>
                  Quick Actions
                </h3>
                <div class="grid gap-2">
                  <Link
                    :href="route('posts.create')"
                    class="flex items-center px-3 py-2 text-sm font-medium text-orange-700 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors duration-200"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Create New Post
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Floating Action Button -->
    <Link
      v-if="hasJoined && $page.props.auth.user"
      :href="route('posts.create', { subfapp: subfapp.id })"
      class="fixed bottom-6 right-6 z-50 sm:hidden group flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-r from-orange-500 to-pink-500 hover:from-orange-600 hover:to-pink-600 transition-all duration-300 text-white shadow-2xl shadow-orange-500/25 hover:shadow-3xl hover:shadow-orange-500/35 hover:scale-110"
    >
      <svg class="w-6 h-6 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
      </svg>
    </Link>

    <!-- Mobile Join/Leave FAB for non-members -->
    <div v-if="!hasJoined && $page.props.auth.user?.id !== subfapp.created_by" class="fixed bottom-6 right-6 z-50 sm:hidden">
      <Link
        :href="route('subfapp.join', subfapp.id)"
        method="post"
        as="button"
        class="group flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 transition-all duration-300 text-white shadow-2xl shadow-blue-500/25 hover:shadow-3xl hover:shadow-blue-500/35 hover:scale-110"
      >
        <svg class="w-6 h-6 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
      </Link>
    </div>

    <!-- Full Screen Modal -->
    <div
      v-if="selectedImage"
      class="flex fixed inset-0 z-50 justify-center items-center p-4 bg-black bg-opacity-75"
      @click="closeImage"
    >
      <img
        :src="selectedImage"
        alt="Full size image"
        class="object-contain max-w-full max-h-full"
      />
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      :isOpen="showDeleteModal"
      title="Delete Community"
      :message="`Are you sure you want to permanently delete f/${subfapp.name}? This action cannot be undone and will remove all posts, comments, and data associated with this community.`"
      confirmText="Delete Community"
      cancelText="Cancel"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />
  </MainLayout>
</template>

<style scoped>
/* Custom animations and styling for the subfapp page */
.gradient-border {
  position: relative;
  background: linear-gradient(135deg, #ff4500, #ff6b35, #ff8c42, #ffa726);
  padding: 2px;
  border-radius: 1rem;
}

.gradient-border::before {
  content: '';
  position: absolute;
  inset: 0;
  padding: 2px;
  background: linear-gradient(135deg, #ff4500, #ff6b35, #ff8c42, #ffa726);
  border-radius: inherit;
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask-composite: exclude;
}

/* Enhance hover animations */
@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-10px) rotate(2deg); }
}

.float-animation:hover {
  animation: float 3s ease-in-out infinite;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 8px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #ff4500, #ff6b35);
  border-radius: 8px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #e03d00, #e5632f);
}

/* Pulse animation for interactive elements */
@keyframes pulse-glow {
  0% { box-shadow: 0 0 5px rgba(255, 69, 0, 0.3); }
  50% { box-shadow: 0 0 20px rgba(255, 69, 0, 0.6), 0 0 30px rgba(255, 107, 53, 0.4); }
  100% { box-shadow: 0 0 5px rgba(255, 69, 0, 0.3); }
}

.pulse-glow {
  animation: pulse-glow 2s ease-in-out infinite;
}

/* Gradient text animation */
@keyframes gradient-shift {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

.gradient-text-animated {
  background: linear-gradient(-45deg, #ff4500, #ff6b35, #ff8c42, #ffa726);
  background-size: 400% 400%;
  animation: gradient-shift 3s ease infinite;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
</style>
