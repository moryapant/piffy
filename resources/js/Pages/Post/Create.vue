<template>
  <Head title="Create Post" />

  <MainLayout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Create a Post</h1>
          <Link
            :href="route('home')"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cancel
          </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Subfapp Selection -->
          <div>
            <label for="subfapp" class="block text-sm font-medium text-gray-700"
              >Choose a Community</label
            >
            <select
              id="subfapp"
              v-model="form.subfapp_id"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              :class="{ 'border-red-500': form.errors.subfapp_id }"
            >
              <option value="">Select a community</option>
              <option
                v-for="subfapp in subfapps"
                :key="subfapp.id"
                :value="subfapp.id"
              >
                f/{{ subfapp.name }}
              </option>
            </select>
            <p v-if="form.errors.subfapp_id" class="mt-1 text-sm text-red-600">
              {{ form.errors.subfapp_id }}
            </p>
          </div>

          <!-- Title -->
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700"
              >Title</label
            >
            <input
              type="text"
              id="title"
              v-model="form.title"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-500': form.errors.title }"
              placeholder="Give your post a title"
            />
            <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">
              {{ form.errors.title }}
            </p>
          </div>

          <!-- Content -->
          <div>
            <label for="content" class="block text-sm font-medium text-gray-700"
              >Content</label
            >
            <RichTextEditor v-model="form.content" class="mt-1" />
            <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">
              {{ form.errors.content }}
            </p>
          </div>

          <!-- Media Upload -->
          <div>
            <div class="flex space-x-4 mb-4">
              <button
                @click="activeTab = 'images'"
                type="button"
                class="text-sm font-medium"
                :class="{
                  'text-blue-600 border-b-2 border-blue-600':
                    activeTab === 'images',
                  'text-gray-500': activeTab !== 'images',
                }"
              >
                Images
              </button>
              <button
                @click="activeTab = 'videos'"
                type="button"
                class="text-sm font-medium"
                :class="{
                  'text-blue-600 border-b-2 border-blue-600':
                    activeTab === 'videos',
                  'text-gray-500': activeTab !== 'videos',
                }"
              >
                Videos
              </button>
            </div>
            <div
              class="mt-1 flex justify-center px-6 pt-5 pb-6 border border-dashed border-gray-300 rounded-md"
            >
              <div class="space-y-1 text-center">
                <svg
                  class="mx-auto h-12 w-12 text-gray-400"
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
                <div class="flex text-sm text-gray-600">
                  <label
                    for="media"
                    class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500"
                  >
                    <span
                      >Upload
                      {{ activeTab === "images" ? "images" : "videos" }}</span
                    >
                    <input
                      id="media"
                      type="file"
                      :multiple="activeTab === 'images'"
                      class="sr-only"
                      :accept="
                        activeTab === 'images'
                          ? '.jpg,.jpeg,.png,.gif,.webp'
                          : '.mp4'
                      "
                      @change="handleImageUpload"
                    />
                  </label>
                  <p class="pl-1">or drag and drop</p>
                </div>
                <p class="text-xs text-gray-500">
                  {{
                    activeTab === "images"
                      ? "PNG, JPG, GIF, WEBP up to 5MB"
                      : "MP4 up to 5MB"
                  }}
                </p>
              </div>
            </div>
            <p v-if="form.errors.images" class="mt-1 text-sm text-red-600">
              {{ form.errors.images }}
            </p>

            <!-- Media Preview -->
            <div
              v-if="imagePreviewUrls.length"
              class="mt-4 grid grid-cols-2 gap-4"
            >
              <div
                v-for="(url, index) in imagePreviewUrls"
                :key="index"
                class="relative"
              >
                <template v-if="form.images[index].type.startsWith('image/')">
                  <img :src="url" class="h-40 w-full object-cover rounded-lg" />
                </template>
                <template v-else>
                  <video class="h-40 w-full object-cover rounded-lg" controls>
                    <source :src="url" type="video/mp4" />
                  </video>
                </template>
                <button
                  @click="removeImage(index)"
                  type="button"
                  class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 focus:outline-none"
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

          <!-- Tags -->
          <div>
            <label for="tags" class="block text-sm font-medium text-gray-700"
              >Tags</label
            >
            <div class="mt-1">
              <div class="flex flex-wrap gap-2 mb-2">
                <span
                  v-for="(tag, index) in tags"
                  :key="index"
                  class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 text-sm rounded-full"
                >
                  #{{ tag }}
                  <button
                    type="button"
                    class="text-blue-500 hover:text-blue-700"
                    @click="removeTag(index)"
                  >
                    ×
                  </button>
                </span>
              </div>
              <input
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                :class="{ 'border-red-500': form.errors.tags }"
                placeholder="Add tags (press Enter or comma to add)"
                v-model="currentTag"
                @keydown.enter.prevent="addTag"
                @keydown.comma.prevent="addTag"
              />
              <p class="mt-1 text-sm text-gray-500">
                Add up to 5 tags to describe what your post is about
              </p>
              <p v-if="form.errors.tags" class="mt-1 text-sm text-red-600">
                {{ form.errors.tags }}
              </p>
            </div>
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              :disabled="form.processing"
            >
              {{ form.processing ? "Creating..." : "Create Post" }}
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

const activeTab = ref("images");
const currentTag = ref("");
const tags = ref([]);
const form = useForm({
  subfapp_id: "",
  title: "",
  content: "",
  tags: "",
  images: [],
});

watch(
  tags,
  (newTags) => {
    form.tags = newTags.join(",");
  },
  { immediate: true }
);

const imagePreviewUrls = ref([]);

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

    if (activeTab.value === "images" && !isImage) {
      alert(
        "Please upload only image files (JPG, PNG, GIF) in the Images tab."
      );
      return;
    }

    if (activeTab.value === "videos" && !isVideo) {
      alert("Please upload only MP4 files in the Videos tab.");
      return;
    }

    if (activeTab.value === "videos" && form.images.length > 0) {
      alert("You can only upload one video file.");
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
  form.post(route("posts.store"), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      imagePreviewUrls.value = [];
      tags.value = [];
      currentTag.value = "";
    },
  });
};
</script>
