<template>
  <div class="p-6 bg-white rounded-lg shadow">
    <h2 class="mb-4 text-2xl font-bold">Create a New Post</h2>
    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label for="title" class="block text-sm font-medium text-gray-700"
          >Title</label
        >
        <input
          type="text"
          id="title"
          v-model="form.title"
          class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          :class="{ 'border-red-500': form.errors.title }"
          required
        />
        <p v-if="form.errors.title" class="mt-1 text-sm text-red-500">
          {{ form.errors.title }}
        </p>
      </div>

      <div>
        <label for="content" class="block text-sm font-medium text-gray-700"
          >Content</label
        >
        <textarea
          id="content"
          v-model="form.content"
          rows="5"
          class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          :class="{ 'border-red-500': form.errors.content }"
          required
        ></textarea>
        <p v-if="form.errors.content" class="mt-1 text-sm text-red-500">
          {{ form.errors.content }}
        </p>
      </div>

      <!-- Media Upload -->
      <div>
        <div class="flex mb-4 space-x-4">
          <button
            @click="activeTab = 'images'"
            type="button"
            class="text-sm font-medium"
            :class="{
              'text-blue-600': activeTab === 'images',
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
              'text-blue-600': activeTab === 'videos',
              'text-gray-500': activeTab !== 'videos',
            }"
          >
            Videos
          </button>
        </div>
        <div
          class="p-8 text-center rounded-lg border border-gray-300 border-dashed"
          :class="{ 'bg-gray-50': isDragging }"
          @dragenter.prevent="isDragging = true"
          @dragleave.prevent="isDragging = false"
          @dragover.prevent
          @drop.prevent="handleFileDrop"
        >
          <div
            class="flex flex-wrap gap-4 mb-4"
            v-if="getFilteredPreviews.length > 0"
          >
            <div
              v-for="preview in getFilteredPreviews"
              :key="preview.index"
              class="relative"
            >
              <template v-if="preview.type === 'video'">
                <video
                  class="object-cover w-32 h-32 rounded-lg border border-gray-200"
                  controls
                >
                  <source :src="preview.url" type="video/mp4" />
                </video>
              </template>
              <template v-else>
                <img
                  :src="preview.url"
                  class="object-cover w-32 h-32 rounded-lg border border-gray-200"
                />
              </template>
              <button
                @click="removeImage(preview.index)"
                type="button"
                class="absolute -top-2 -right-2 p-1 text-white bg-red-500 rounded-full hover:bg-red-600 focus:outline-none"
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
          <div class="text-center">
            <svg
              class="mx-auto w-12 h-12 text-gray-400"
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
            <div class="mt-4">
              <label class="cursor-pointer">
                <span class="text-blue-600 hover:text-blue-700"
                  >Upload
                  {{ activeTab === "images" ? "images" : "videos" }}</span
                >
                <input
                  type="file"
                  ref="fileInput"
                  @change="handleImageUpload"
                  multiple
                  :accept="
                    activeTab === 'images'
                      ? '.jpg,.jpeg,.png,.gif,.webp'
                      : '.mp4'
                  "
                  class="hidden"
                  :disabled="imagePreviews.length >= 10"
                />
              </label>
              <span class="text-gray-500"> or drag and drop</span>
            </div>
            <p class="mt-2 text-xs text-gray-500">
              {{ activeTab === "images" ? "PNG, JPG, GIF, WEBP" : "MP4" }} up to
              {{ activeTab === "images" ? "5" : "10" }}MB
              <span class="ml-2"
                >({{ getFilteredPreviews.length }}/10 files)</span
              >
            </p>
          </div>
        </div>
      </div>

      <div v-if="!subfappId">
        <label for="subfapp" class="block text-sm font-medium text-gray-700"
          >Choose Subfapp</label
        >
        <select
          id="subfapp"
          v-model="form.subfapp_id"
          class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          :class="{ 'border-red-500': form.errors.subfapp_id }"
          required
        >
          <option value="">Select a Subfapp</option>
          <option
            v-for="subfapp in subfapps"
            :key="subfapp.id"
            :value="subfapp.id"
          >
            {{ subfapp.display_name }}
          </option>
        </select>
        <p v-if="form.errors.subfapp_id" class="mt-1 text-sm text-red-500">
          {{ form.errors.subfapp_id }}
        </p>
      </div>

      <div class="flex justify-end">
        <button
          type="submit"
          class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md border border-transparent shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          :disabled="form.processing"
        >
          {{ form.processing ? "Creating..." : "Create Post" }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
  subfappId: {
    type: Number,
    required: false,
  },
  subfapps: {
    type: Array,
    required: true,
  },
});

const form = useForm({
  title: "",
  content: "",
  subfapp_id: props.subfappId || "",
  images: [],
});

const imagePreviews = ref([]);
const previewTypes = ref([]);
const isDragging = ref(false);
const activeTab = ref("images");

const getFilteredPreviews = computed(() => {
  return imagePreviews.value
    .map((url, index) => ({
      url,
      type: previewTypes.value[index],
      index,
    }))
    .filter(
      (item) =>
        (activeTab.value === "images" && item.type === "image") ||
        (activeTab.value === "videos" && item.type === "video")
    );
});

const processFiles = (files) => {
  const remainingSlots = 10 - imagePreviews.value.length;
  const validFiles = Array.from(files).slice(0, remainingSlots);

  validFiles.forEach((file) => {
    const imageTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
    const videoTypes = ["video/mp4"];

    if (activeTab.value === "images" && !imageTypes.includes(file.type)) {
      alert(
        `Please upload only image files (JPG, PNG, GIF, WEBP) in the Images tab.`
      );
      return;
    }
    if (activeTab.value === "videos" && !videoTypes.includes(file.type)) {
      alert(`Please upload only MP4 files in the Videos tab.`);
      return;
    }
    if (file.size > 10 * 1024 * 1024) {
      // 10MB limit
      alert(`File ${file.name} exceeds 10MB limit`);
      return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreviews.value.push(e.target.result);
      previewTypes.value.push(
        file.type.startsWith("video/") ? "video" : "image"
      );
    };
    reader.readAsDataURL(file);
    form.images.push(file);
  });
};

const handleImageUpload = (event) => {
  processFiles(event.target.files);
  event.target.value = ""; // Reset input
};

const handleFileDrop = (event) => {
  isDragging.value = false;
  processFiles(event.dataTransfer.files);
};

const removeImage = (index) => {
  imagePreviews.value.splice(index, 1);
  previewTypes.value.splice(index, 1);
  form.images.splice(index, 1);
};

const submit = () => {
  form.post(route("posts.store"), {
    onSuccess: () => {
      form.reset();
      imagePreviews.value = [];
    },
    forceFormData: true,
  });
};
</script>
