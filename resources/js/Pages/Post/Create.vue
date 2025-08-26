<template>
  <Head title="Create Post" />

  <MainLayout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 relative">
      <!-- Top Navigation Header (Reddit-like) -->
      <div class="mb-4 bg-white dark:bg-gray-900 rounded-t-md shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Create a post</h1>
        <div class="mt-3 flex overflow-x-auto pb-1 border-b border-gray-200 dark:border-gray-700">
          <button
            v-for="tab in postTypes"
            :key="tab.id"
            @click="activePostType = tab.id"
            class="flex items-center px-4 py-2 whitespace-nowrap mr-4 text-sm font-medium"
            :class="[
              activePostType === tab.id 
                ? 'text-blue-600 border-b-2 border-blue-600 dark:text-blue-400 dark:border-blue-400'
                : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300'
            ]"
            type="button"
          >
            <component :is="tab.icon" class="w-5 h-5 mr-2" aria-hidden="true" />
            {{ tab.name }}
          </button>
        </div>
      </div>

      <!-- Main Content -->
      <div class="bg-white dark:bg-gray-900 rounded-b-md shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Subfapp Selection -->
          <div class="mb-4">
            <select
              id="subfapp"
              v-model="form.subfapp_id"
              class="block w-full pl-3 pr-10 py-3 text-base border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-md"
              :class="{ 'border-red-500': form.errors.subfapp_id }"
            >
              <option value="">Choose a community</option>
              <option
                v-for="subfapp in subfapps"
                :key="subfapp.id"
                :value="subfapp.id"
              >
                f/{{ subfapp.name }}
              </option>
            </select>
            <p v-if="form.errors.subfapp_id" class="mt-1 text-sm text-red-500">
              {{ form.errors.subfapp_id }}
            </p>
          </div>

          <!-- Title -->
          <div>
            <input
              type="text"
              id="title"
              v-model="form.title"
              class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-lg py-3"
              :class="{ 'border-red-500': form.errors.title }"
              placeholder="Title"
            />
            <p v-if="form.errors.title" class="mt-1 text-sm text-red-500">
              {{ form.errors.title }}
            </p>
          </div>

          <!-- Post Type Specific Content -->
          <div v-if="activePostType === 'text'">
            <RichTextEditor 
              v-model="form.content" 
              class="mt-2 min-h-[200px]" 
              placeholder="Text (optional)"
            />
            <p v-if="form.errors.content" class="mt-1 text-sm text-red-500">
              {{ form.errors.content }}
            </p>
          </div>

          <div v-else-if="['image', 'video'].includes(activePostType)" class="rounded-md">
            <div
              class="flex flex-col justify-center items-center px-6 pt-8 pb-8 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200"
            >
              <svg
                v-if="!imagePreviewUrls.length"
                class="mx-auto h-16 w-16 text-gray-400 mb-4"
                stroke="currentColor"
                fill="none"
                viewBox="0 0 48 48"
              >
                <path
                  d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>

              <div v-if="imagePreviewUrls.length" class="w-full">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                  <div
                    v-for="(url, index) in imagePreviewUrls"
                    :key="index"
                    class="relative rounded-md overflow-hidden"
                  >
                    <template v-if="form.images[index].type.startsWith('image/')">
                      <img 
                        :src="url" 
                        class="h-48 w-full object-cover" 
                      />
                    </template>
                    <template v-else>
                      <video class="h-48 w-full object-cover" controls>
                        <source :src="url" type="video/mp4" />
                      </video>
                    </template>
                    <button
                      @click="removeImage(index)"
                      type="button"
                      class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1.5 hover:bg-red-600 focus:outline-none shadow-lg"
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
                          d="M6 18L18 6M6 6l12 12"
                        />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <div class="flex items-center space-x-2">
                <label
                  :for="activePostType === 'image' ? 'image-upload' : 'video-upload'"
                  class="cursor-pointer px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200 text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                  <span>Upload {{ activePostType === 'image' ? 'images' : 'video' }}</span>
                  <input
                    :id="activePostType === 'image' ? 'image-upload' : 'video-upload'"
                    type="file"
                    :multiple="activePostType === 'image'"
                    class="sr-only"
                    :accept="activePostType === 'image' ? '.jpg,.jpeg,.png,.gif,.webp' : '.mp4'"
                    @change="handleImageUpload"
                  />
                </label>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                  {{ activePostType === 'image' ? 'Images up to 5MB' : 'Video up to 10MB' }}
                </span>
              </div>
              <p v-if="form.errors.images" class="mt-3 text-sm text-red-500">
                {{ form.errors.images }}
              </p>
            </div>
          </div>

          <div v-else-if="activePostType === 'link'" class="space-y-4">
            <input
              type="url"
              v-model="linkUrl"
              class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-3"
              placeholder="URL"
            />
          </div>

          <!-- Tags -->
          <div>
            <div class="flex flex-wrap gap-2 mb-2">
              <span
                v-for="(tag, index) in tags"
                :key="index"
                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-sm rounded-full"
              >
                #{{ tag }}
                <button
                  type="button"
                  class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 ml-1"
                  @click="removeTag(index)"
                >
                  ×
                </button>
              </span>
            </div>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                </svg>
              </div>
              <input
                type="text"
                class="pl-10 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2.5"
                :class="{ 'border-red-500': form.errors.tags }"
                placeholder="Add tags (press Enter or comma to add)"
                v-model="currentTag"
                @keydown.enter.prevent="addTag"
                @keydown.comma.prevent="addTag"
              />
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              Add up to 5 tags to describe what your post is about
            </p>
            <p v-if="form.errors.tags" class="mt-1 text-sm text-red-500">
              {{ form.errors.tags }}
            </p>
          </div>

          <div class="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <Link
              :href="route('home')"
              class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-full shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Cancel
            </Link>
            <button
              type="submit"
              class="inline-flex justify-center items-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-full text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-75 disabled:cursor-not-allowed transition-all duration-200"
              :disabled="form.processing"
            >
              <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ form.processing ? "Posting..." : "Post" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, watch } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
import RichTextEditor from "@/Components/RichTextEditor.vue";

// Import icons for post types
import {
  DocumentTextIcon,
  PhotoIcon,
  FilmIcon,
  LinkIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  subfapps: {
    type: Array,
    required: true,
  },
  auth: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
});

// Post type configuration
const postTypes = [
  { id: 'text', name: 'Post', icon: DocumentTextIcon },
  { id: 'image', name: 'Images', icon: PhotoIcon },
  { id: 'video', name: 'Video', icon: FilmIcon },
  { id: 'link', name: 'Link', icon: LinkIcon },
];

const activePostType = ref("text");
const currentTag = ref("");
const tags = ref([]);
const linkUrl = ref("");
const imagePreviewUrls = ref([]);

const form = useForm({
  subfapp_id: "",
  title: "",
  content: "",
  tags: "",
  images: [],
  link_url: "",
});

// Watch for tag changes
watch(
  tags,
  (newTags) => {
    form.tags = newTags.join(",");
  },
  { immediate: true }
);

// Watch for link URL changes
watch(
  linkUrl,
  (newUrl) => {
    form.content = newUrl;
  }
);

// Watch for post type changes
watch(
  activePostType,
  (newType) => {
    // Reset form based on selected tab
    if (newType !== 'text') {
      form.content = "";
    }
    if (newType !== 'link') {
      linkUrl.value = "";
    }
    if (newType !== 'image' && newType !== 'video') {
      form.images = [];
      imagePreviewUrls.value = [];
    }
  }
);

const addTag = () => {
  const value = currentTag.value.trim();
  if (value && tags.value.length < 5 && !tags.value.includes(value)) {
    tags.value.push(value);
    currentTag.value = "";
    form.tags = tags.value.join(",");
  }
};

const removeTag = (index) => {
  tags.value.splice(index, 1);
  form.tags = tags.value.join(",");
};

const handleImageUpload = (e) => {
  const files = Array.from(e.target.files);
  const isImageType = activePostType.value === 'image';
  
  // Limit files based on post type
  if (!isImageType && files.length > 1) {
    alert("You can only upload one video file for video posts");
    return;
  }

  files.forEach((file) => {
    const isImage = file.type.startsWith("image/");
    const isVideo = file.type === "video/mp4";
    const maxSize = isImage ? 5 * 1024 * 1024 : 10 * 1024 * 1024; // 5MB for images, 10MB for videos

    if (file.size > maxSize) {
      alert(
        `File ${file.name} is too large. Maximum size is ${
          isImage ? "5MB" : "10MB"
        }.`
      );
      return;
    }

    if (isImageType && !isImage) {
      alert(
        "Please upload only image files (JPG, PNG, GIF) in the Images tab."
      );
      return;
    }

    if (!isImageType && !isVideo) {
      alert("Please upload only MP4 files in the Video tab.");
      return;
    }

    if (!isImageType && form.images.length > 0) {
      // Replace the existing video
      form.images = [file];
      imagePreviewUrls.value = [];
      
      // Create preview URL
      const reader = new FileReader();
      reader.onload = (e) => {
        imagePreviewUrls.value.push(e.target.result);
      };
      reader.readAsDataURL(file);
      return;
    }

    form.images.push(file);

    // Create preview URL
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreviewUrls.value.push(e.target.result);
    };
    reader.readAsDataURL(file);
  });
};

const removeImage = (index) => {
  form.images.splice(index, 1);
  imagePreviewUrls.value.splice(index, 1);
};

const submit = () => {
  // Set link URL if in link mode
  if (activePostType.value === 'link') {
    form.content = linkUrl.value;
  }
  
  form.post(route("posts.store"), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      imagePreviewUrls.value = [];
      tags.value = [];
      currentTag.value = "";
      linkUrl.value = "";
    },
  });
};
</script>
