<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick } from "vue";

const props = defineProps({
  images: {
    type: Array,
    required: true,
  },
});

const selectedImage = ref(null);
const modalRef = ref(null);

const getImageUrl = (image) => {
  return `/storage/${image.image_path}`;
};

// Compute aspect ratio for images
const computeAspectRatio = (image) => {
  if (image.width && image.height) {
    return image.width / image.height;
  }
  return null;
};

// Add aspect ratio to images
const processedImages = computed(() => {
  return props.images.map((image) => ({
    ...image,
    aspect_ratio: computeAspectRatio(image),
  }));
});

const currentImageIndex = computed(() => {
  if (!selectedImage.value) return -1;
  return processedImages.value.findIndex(
    (img) => img.id === selectedImage.value.id
  );
});

const openImage = (image, event = null) => {
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }
  selectedImage.value = image;
  nextTick(() => {
    if (modalRef.value) {
      modalRef.value.focus();
    }
  });
};

const closeImage = (event = null) => {
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }
  selectedImage.value = null;
};

const previousImage = (event = null) => {
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }
  if (currentImageIndex.value > 0) {
    selectedImage.value = processedImages.value[currentImageIndex.value - 1];
  }
};

const nextImage = (event = null) => {
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }
  if (currentImageIndex.value < processedImages.value.length - 1) {
    selectedImage.value = processedImages.value[currentImageIndex.value + 1];
  }
};

// Handle keyboard events
const handleKeydown = (e) => {
  if (!selectedImage.value) return;

  switch (e.key) {
    case "ArrowLeft":
      previousImage();
      break;
    case "ArrowRight":
      nextImage();
      break;
    case "Escape":
      closeImage();
      break;
  }
};

onMounted(() => {
  document.addEventListener("keydown", handleKeydown);
});

onUnmounted(() => {
  document.removeEventListener("keydown", handleKeydown);
});
</script>

<template>
  <div>
    <!-- Media Gallery Grid -->
    <div
      v-if="images?.length"
      class="grid gap-1 overflow-hidden bg-black rounded-xl"
      :class="{
        'grid-cols-1': images.length === 1,
        'grid-cols-2': images.length >= 2,
      }"
    >
      <div
        v-for="(image, index) in images.slice(0, Math.min(4, images.length))"
        :key="image.id"
        class="relative group cursor-pointer overflow-hidden bg-black flex items-center justify-center"
        :class="{
          'h-[500px]': images.length === 1,
          'h-[250px]': images.length >= 2,
        }"
        @click.stop="openImage(image, $event)"
      >
        <div class="relative flex items-center justify-center w-full h-full bg-black">
          <template v-if="image.type === 'video'">
            <video
              :src="getImageUrl(image)"
              :class="[
                'transition-all duration-500 group-hover:scale-105 group-hover:brightness-105',
                images.length === 1 ? 'max-h-[500px] w-auto object-contain' : 'w-full h-full object-cover'
              ]"
              controls
              autoplay
              loop
              muted
              playsinline
            />
          </template>
          <template v-else>
            <img
              :src="getImageUrl(image)"
              alt=""
              :class="[
                'transition-all duration-500 group-hover:scale-105 group-hover:brightness-105',
                images.length === 1 ? 'max-h-[500px] w-auto object-contain' : 'w-full h-full object-cover'
              ]"
              loading="lazy"
              style="font-size: 0; line-height: 0;"
            />
          </template>
        </div>

        <!-- Gradient Overlay -->
        <div
          class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-t from-black/20 to-transparent group-hover:opacity-100"
        ></div>

        <!-- More Images Indicator -->
        <div
          v-if="index === 3 && images.length > 4"
          class="absolute inset-0 flex items-center justify-center bg-black/60 backdrop-blur-[2px] transition-opacity duration-300"
        >
          <div
            class="text-center text-white transition-transform duration-300 transform group-hover:scale-110"
          >
            <span class="block text-3xl font-bold"
              >+{{ images.length - 4 }}</span
            >
            <span class="text-sm font-medium text-white/90">
              more {{ images.some(img => img.type === 'video') ? 'media' : 'photos' }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Full Screen Modal -->
    <Teleport to="body">
      <div
        v-if="selectedImage"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/95 backdrop-blur-sm"
        @click.self="closeImage($event)"
      @keydown.left="previousImage"
      @keydown.right="nextImage"
      tabindex="0"
      ref="modalRef"
    >
      <!-- Close Button -->
      <button
        class="absolute text-white transition-colors duration-200 top-6 right-6 hover:text-gray-300 focus:outline-none"
        @click.stop="closeImage($event)"
      >
        <svg
          class="w-8 h-8"
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

      <!-- Previous Arrow (Mobile & Desktop) -->
      <button
        v-if="currentImageIndex > 0"
        class="absolute left-3 top-1/2 -translate-y-1/2 p-2.5 text-white bg-black/50 hover:bg-black/70 rounded-full backdrop-blur-sm transition-all duration-200 focus:outline-none active:scale-95 z-20 touch-manipulation"
        @click.stop="previousImage($event)"
      >
        <svg
          class="w-5 h-5"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          stroke-width="2.5"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M15 19l-7-7 7-7"
          />
        </svg>
      </button>

      <!-- Next Arrow (Mobile & Desktop) -->
      <button
        v-if="currentImageIndex < images.length - 1"
        class="absolute right-3 top-1/2 -translate-y-1/2 p-2.5 text-white bg-black/50 hover:bg-black/70 rounded-full backdrop-blur-sm transition-all duration-200 focus:outline-none active:scale-95 z-20 touch-manipulation"
        @click.stop="nextImage($event)"
      >
        <svg
          class="w-5 h-5"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          stroke-width="2.5"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M9 5l7 7-7 7"
          />
        </svg>
      </button>

      <!-- Image Container -->
      <div
        class="relative max-h-[90vh] max-w-[90vw] flex items-center justify-center select-none bg-black rounded-lg"
      >
        <template v-if="selectedImage.type === 'video'">
          <video
            :src="getImageUrl(selectedImage)"
            class="max-h-[90vh] max-w-[90vw] object-contain select-none"
            controls
            autoplay
            loop
            muted
            playsinline
            @click.stop
          />
        </template>
        <template v-else>
          <img
            :src="getImageUrl(selectedImage)"
            alt=""
            class="max-h-[90vh] max-w-[90vw] object-contain select-none"
            @click.stop
          />
        </template>
        <!-- Image Counter -->
        <div
          v-if="images.length > 1"
          class="absolute px-4 py-2 text-sm font-medium text-white -translate-x-1/2 rounded-full bottom-4 left-1/2 bg-black/50 backdrop-blur-sm"
        >
          {{ currentImageIndex + 1 }} / {{ images.length }}
        </div>
      </div>
    </div>
    </Teleport>
  </div>
</template>


