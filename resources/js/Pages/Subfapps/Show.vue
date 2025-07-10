<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import Pagination from "@/Components/Pagination.vue";
import PostVoteButtons from "@/Components/PostVoteButtons.vue";
import PostSortTabs from "@/Components/PostSortTabs.vue";
import { timeAgo } from "@/utils/dateUtils";

const coverImageInput = ref(null);
const avatarImageInput = ref(null);
const isHoveringCover = ref(false);
const isHoveringAvatar = ref(false);

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
  form.vote_type = voteType;
  form.post(route("posts.vote", postId), {
    preserveScroll: true,
  });
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
    <!-- Banner and Header -->
    <div
      class="relative overflow-hidden h-96 group"
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
            : 'background-image: linear-gradient(to right, rgb(59, 130, 246), rgb(79, 70, 229))'
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
          class="flex items-center px-4 py-2 space-x-2 text-sm font-medium text-white transition-colors duration-200 rounded-full bg-black/50 hover:bg-black/60"
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
      <div class="flex items-end h-full px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div
          class="flex items-center justify-between w-full px-4 py-4 border-t border-gray-200 rounded-t-lg shadow-sm bg-white/95 backdrop-blur-sm border-x sm:px-6 sm:py-5"
        >
          <!-- Left side with avatar and info -->
          <div class="flex items-center space-x-3 sm:space-x-4">
            <!-- Subfapp Icon with improved visibility -->
            <div
              class="relative flex items-center justify-center w-20 h-20 -mt-12 overflow-hidden bg-white border-2 border-white shadow-md sm:w-32 sm:h-32 sm:rounded-full sm:-mt-16 ring-1 ring-gray-200 group"
              @mouseenter="isHoveringAvatar = true"
              @mouseleave="isHoveringAvatar = false"
            >
              <img
                v-if="subfapp.icon"
                :src="`/storage/${subfapp.icon}`"
                :alt="subfapp.display_name"
                class="object-cover w-full h-full"
              />
              <span
                v-else
                class="text-xl font-bold text-gray-400 sm:text-3xl"
                >{{ subfapp.display_name[0] }}</span
              >

              <!-- Avatar Update Button (Only visible to creator when hovering) -->
              <div
                v-if="$page.props.auth.user?.id === subfapp.created_by"
                :class="[
                  'absolute inset-0 flex items-center justify-center bg-black/50 transition-opacity duration-200',
                  isHoveringAvatar ? 'opacity-100' : 'opacity-0',
                ]"
              >
                <button
                  @click="() => avatarImageInput.click()"
                  class="flex items-center gap-1 px-2 py-1 text-xs font-medium text-white transition-colors duration-200 rounded-full hover:text-blue-200"
                >
                  <svg
                    class="w-3.5 h-3.5"
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
            <!-- Subfapp Info with improved text -->
            <div class="flex-grow">
              <h1
                class="text-lg font-semibold leading-tight text-gray-900 sm:text-xl"
              >
                {{ subfapp.display_name }}
              </h1>
              <p class="text-sm text-gray-500">f/{{ subfapp.name }}</p>
            </div>

            <!-- Desktop Join/Leave Button -->
            <div class="items-center hidden lg:flex">
              <Link
                v-if="!hasJoined"
                :href="route('subfapp.join', subfapp.id)"
                method="post"
                as="button"
                class="bg-blue-500 hover:bg-blue-600 text-white font-medium px-4 py-1.5 rounded-full transition-colors duration-200 text-sm"
              >
                Join
              </Link>
              <Link
                v-else
                :href="route('subfapp.leave', subfapp.id)"
                method="delete"
                as="button"
                class="bg-red-500 hover:bg-red-600 text-white font-medium px-4 py-1.5 rounded-full transition-colors duration-200 text-sm"
              >
                Leave
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="flex flex-col gap-6 lg:flex-row">
        <!-- Left Column: Posts -->
        <div class="flex-1 order-2 space-y-4 lg:order-1">
          <!-- Posts List -->
          <!-- Sort Options -->
          <PostSortTabs :current-sort="currentSort" :subfapp-id="subfapp.id" />
          <div
            v-if="!hasJoined"
            class="p-6 text-center bg-white border border-gray-100 shadow-sm rounded-xl"
          >
            <p class="text-gray-600">Join this community to see its posts</p>
          </div>
          <div
            v-else-if="posts.data.length === 0"
            class="p-6 text-center bg-white border border-gray-100 shadow-sm rounded-xl"
          >
            <p class="text-gray-600">No posts yet</p>
          </div>
          <div
            v-else
            v-for="post in posts.data"
            :key="post.id"
            class="overflow-hidden transition-all duration-200 bg-white border-gray-100 shadow-sm sm:rounded-xl border-y sm:border hover:border-blue-200 hover:shadow-md group"
          >
            <div class="p-3 sm:p-6">
              <!-- Post Header -->
              <div
                class="flex flex-col mb-4 sm:flex-row sm:justify-between sm:items-center"
              >
                <div class="flex items-start gap-3 sm:items-center sm:gap-4">
                  <!-- User Avatar -->
                  <div
                    class="flex items-center justify-center w-8 h-8 overflow-hidden bg-gray-100 rounded-full ring-2 ring-blue-100"
                  >
                    <span class="text-sm font-medium text-gray-600">{{
                      post.user.name[0].toUpperCase()
                    }}</span>
                  </div>
                  <!-- User Info & Post Meta -->
                  <div class="flex items-center gap-1.5">
                    <span class="text-sm font-medium text-gray-900">
                      u/{{ post.user.name }}
                    </span>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs text-gray-500">{{
                      timeAgo(post.created_at)
                    }}</span>
                  </div>
                </div>
              </div>

              <!-- Post Content -->
              <div class="mt-3 space-y-4">
                <Link :href="route('posts.show', post.id)">
                  <h2
                    class="text-xl font-semibold text-gray-900 transition-colors duration-200 group-hover:text-blue-600 line-clamp-2"
                  >
                    {{ post.title }}
                  </h2>
                  <div
                    class="mt-2 text-sm leading-relaxed prose-sm prose text-gray-600 line-clamp-3 max-w-none"
                    v-html="post.content"
                  ></div>
                </Link>
                <!-- Tags -->
                <div v-if="post.tags?.length" class="flex flex-wrap gap-2 mt-3">
                  <span
                    v-for="tag in post.tags"
                    :key="tag.id"
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors"
                  >
                    #{{ tag.name }}
                  </span>
                </div>

                <!-- Image Gallery -->
                <div
                  :class="[
                    'mt-4',
                    post.images.length === 1
                      ? 'flex justify-center'
                      : 'grid grid-cols-2 gap-2',
                  ]"
                  v-if="post.images?.length"
                >
                  <Link
                    v-for="(image, index) in post.images.slice(0, 4)"
                    :key="image.id"
                    :href="route('posts.show', post.id)"
                    :class="[
                      'relative overflow-hidden rounded-lg group cursor-pointer',
                      post.images.length === 1
                        ? 'w-2/3 aspect-video'
                        : 'aspect-square',
                    ]"
                    @click.prevent="openImage(image)"
                  >
                    <img
                      :src="`/storage/${image.image_path}`"
                      :alt="`Post image ${image.id}`"
                      class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-110"
                      loading="lazy"
                    />
                    <!-- More Images Indicator -->
                    <div
                      v-if="index === 3 && post.images.length > 4"
                      class="absolute inset-0 flex items-center justify-center font-medium text-white bg-black bg-opacity-50"
                    >
                      <span>+{{ post.images.length - 4 }} more</span>
                    </div>
                  </Link>
                </div>
              </div>

              <!-- Post Actions -->
              <div
                class="flex items-center pt-4 mt-4 space-x-4 border-t border-gray-100"
              >
                <!-- Vote Buttons -->
                <div class="flex items-center space-x-1.5">
                  <PostVoteButtons :post="post" @vote="vote" />
                </div>

                <!-- Comments -->
                <Link
                  :href="route('posts.show', post.id)"
                  class="flex items-center space-x-2 text-gray-500 transition-colors duration-200 hover:text-blue-600"
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
                  <span class="text-sm"
                    >{{ post.comments_count || 0 }}
                    {{
                      post.comments_count === 1 ? "comment" : "comments"
                    }}</span
                  >
                </Link>

                <!-- Share -->
                <button
                  class="flex items-center space-x-2 text-gray-500 transition-colors duration-200 hover:text-blue-600"
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
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="posts.data.length > 0" class="mt-6">
            <Pagination :links="posts.links" />
          </div>
        </div>

        <!-- Right Column: Community Info -->
        <div class="order-1 w-full space-y-4 lg:w-80 lg:order-2">
          <!-- Mobile Join Button -->
          <div class="block lg:hidden">
            <div
              class="overflow-hidden bg-white border border-gray-100 shadow-sm sm:rounded-xl"
            >
              <div class="p-4">
                <Link
                  v-if="!hasJoined"
                  :href="route('subfapp.join', subfapp.id)"
                  method="post"
                  as="button"
                  class="w-full px-4 py-2 font-medium text-white transition-colors duration-200 bg-blue-500 rounded-full hover:bg-blue-600"
                >
                  Join Community
                </Link>
                <div v-else class="space-y-2">
                  <div class="text-sm text-center text-gray-600">
                    You are a member
                  </div>
                  <Link
                    :href="route('subfapp.leave', subfapp.id)"
                    method="delete"
                    as="button"
                    class="w-full px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-red-500 rounded-full hover:bg-red-600"
                  >
                    Leave Community
                  </Link>
                </div>
                <p class="mt-2 text-xs text-center text-gray-500">
                  Join to post and interact with the community
                </p>
              </div>
            </div>
          </div>

          <!-- About Community (hidden on mobile) -->
          <div
            class="sticky hidden overflow-hidden bg-white border border-gray-100 shadow-sm lg:block sm:rounded-xl top-4"
          >
            <div class="px-4 py-3 bg-blue-600 border-b border-blue-500">
              <h2 class="text-base font-semibold text-white">
                About Community
              </h2>
            </div>
            <div class="p-4 space-y-4">
              <p class="text-sm text-gray-600">{{ subfapp.description }}</p>

              <!-- Stats -->
              <div class="flex gap-4">
                <!-- Members -->
                <div class="flex-1 p-3 text-center rounded-lg bg-blue-50">
                  <div class="text-lg font-medium text-blue-900">
                    {{ membersCount }}
                  </div>
                  <div class="text-blue-600">Members</div>
                </div>
                <!-- Posts -->
                <div class="flex-1 p-3 text-center rounded-lg bg-blue-50">
                  <div class="text-lg font-medium text-blue-900">
                    {{ posts.total || 0 }}
                  </div>
                  <div class="text-blue-600">Posts</div>
                </div>
              </div>

              <!-- Created Info -->
              <div class="mt-4 space-y-2">
                <div class="flex items-center gap-2 text-sm text-gray-500">
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
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                  <span
                    >Created
                    {{
                      new Date(subfapp.created_at).toLocaleDateString()
                    }}</span
                  >
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500">
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
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                    />
                  </svg>
                  <span>Created by u/{{ subfapp.creator?.name }}</span>
                </div>
              </div>

              <!-- Community Rules -->
              <div class="pt-4 border-t border-gray-100">
                <h3 class="mb-3 text-base font-semibold text-gray-900">
                  Community Rules
                </h3>
                <ul class="space-y-2 text-sm text-gray-600">
                  <li>1. Be respectful to others</li>
                  <li>2. No hate speech or bullying</li>
                  <li>3. No spam or self-promotion</li>
                  <li>4. Follow Reddit's content policy</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Full Screen Modal -->
    <div
      v-if="selectedImage"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-75"
      @click="closeImage"
    >
      <img
        :src="selectedImage"
        alt="Full size image"
        class="object-contain max-w-full max-h-full"
      />
    </div>
  </MainLayout>
</template>
