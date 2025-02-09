<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';

const props = defineProps({
    images: {
        type: Array,
        required: true
    }
});

const selectedImage = ref(null);
const modalRef = ref(null);

const getImageUrl = (image) => {
    return `/storage/${image.image_path}`;
};

const currentImageIndex = computed(() => {
    if (!selectedImage.value) return -1;
    return props.images.findIndex(img => img.id === selectedImage.value.id);
});

const openImage = (image) => {
    selectedImage.value = image;
    nextTick(() => {
        if (modalRef.value) {
            modalRef.value.focus();
        }
    });
};

const closeImage = () => {
    selectedImage.value = null;
};

const previousImage = () => {
    if (currentImageIndex.value > 0) {
        selectedImage.value = props.images[currentImageIndex.value - 1];
    }
};

const nextImage = () => {
    if (currentImageIndex.value < props.images.length - 1) {
        selectedImage.value = props.images[currentImageIndex.value + 1];
    }
};

// Handle keyboard events
const handleKeydown = (e) => {
    if (!selectedImage.value) return;
    
    switch (e.key) {
        case 'ArrowLeft':
            previousImage();
            break;
        case 'ArrowRight':
            nextImage();
            break;
        case 'Escape':
            closeImage();
            break;
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div>
        <!-- Image Gallery Grid -->
        <div v-if="images?.length" class="grid gap-2 sm:gap-4"
            :class="{
                'grid-cols-1 max-w-2xl mx-auto': images.length === 1,
                'grid-cols-2': images.length === 2,
                'grid-cols-2 grid-rows-2': images.length >= 3
            }">
            <div 
                v-for="(image, index) in images.slice(0, Math.min(4, images.length))" 
                :key="image.id" 
                class="relative aspect-square group cursor-pointer overflow-hidden rounded-lg transition-transform duration-300 hover:scale-[1.02]"
                @click="openImage(image)"
            >
                <img 
                    :src="getImageUrl(image)" 
                    :alt="`Post image ${image.id}`"
                    class="w-full h-full object-cover"
                    loading="lazy"
                    @error="e => console.error('Image load error:', getImageUrl(image), e)"
                    @load="() => console.log('Image loaded:', getImageUrl(image))"
                />
                <!-- More Images Indicator -->
                <div
                    v-if="index === 3 && images.length > 4"
                    class="absolute inset-0 flex items-center justify-center bg-black/60 backdrop-blur-[2px]">
                    <div class="text-center text-white">
                        <span class="block text-2xl font-bold">+{{ images.length - 4 }}</span>
                        <span class="text-sm font-medium text-white/90">more photos</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full Screen Modal -->
        <div 
            v-if="selectedImage"
            class="fixed inset-0 bg-black/95 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            @click="closeImage"
            @keydown.left="previousImage"
            @keydown.right="nextImage"
            tabindex="0"
            ref="modalRef"
        >
            <!-- Close Button -->
            <button 
                class="absolute top-4 right-4 text-white hover:text-gray-300 focus:outline-none"
                @click="closeImage"
            >
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Previous Button -->
            <button 
                v-if="currentImageIndex > 0"
                class="absolute left-4 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 focus:outline-none transition-transform duration-200 hover:scale-110"
                @click.stop="previousImage"
            >
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Next Button -->
            <button 
                v-if="currentImageIndex < images.length - 1"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 focus:outline-none transition-transform duration-200 hover:scale-110"
                @click.stop="nextImage"
            >
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- Current Image -->
            <div class="relative max-h-[90vh] max-w-[90vw]">
                <img 
                    :src="`/storage/${selectedImage.image_path}`" 
                    :alt="`Post image ${selectedImage.id}`"
                    class="object-contain max-h-[90vh] max-w-[90vw] select-none"
                    @click.stop
                />
                <!-- Image Counter -->
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black/70 text-white px-4 py-2 rounded-full text-sm font-medium">
                    {{ currentImageIndex + 1 }} / {{ images.length }}
                </div>
            </div>
        </div>
    </div>
</template>


