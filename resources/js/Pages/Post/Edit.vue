<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import RichTextEditor from "@/Components/RichTextEditor.vue";
import ImageGallery from "@/Components/ImageGallery.vue";
import { ref, computed, onBeforeUnmount, onMounted } from 'vue';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
    subfapps: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    title: props.post.title || '',
    content: props.post.content || '',
    subfapp_id: props.post.subfapp_id || '',
    images: [],
    removedImages: [],
    _method: 'PUT',
});

// For debugging
const currentImages = computed(() => {
    console.log('Current images:', props.post.images);
    const images = props.post.images || [];
    // Map images to include the full URL
    const processedImages = images.map(img => ({
        ...img,
        url: `/storage/${img.image_path}`
    }));
    console.log('Processed images:', processedImages);
    return processedImages;
});

// Initialize form with post data
onMounted(() => {
    if (props.post) {
        form.title = props.post.title || '';
        form.content = props.post.content || '';
        form.subfapp_id = props.post.subfapp_id || '';
        console.log('Mounted with post:', props.post);
    }
});

// Track which images are marked for removal
const markedForRemoval = ref(new Set());

// Computed property for checking if an image is marked for removal
const isImageMarkedForRemoval = (imageId) => markedForRemoval.value?.has(imageId) || false;

const imageInput = ref(null);
const imageUrls = ref([]);

const handleImageUpload = (event) => {
    const files = event.target.files;
    if (!files.length) return;

    // Add new images to form
    Array.from(files).forEach(file => {
        form.images.push(file);
        imageUrls.value.push(URL.createObjectURL(file));
    });

    // Reset the input so the same file can be selected again
    event.target.value = '';
};

// Cleanup object URLs when component is unmounted
onBeforeUnmount(() => {
    imageUrls.value.forEach(url => window.URL.revokeObjectURL(url));
});

const removeImage = (imageId) => {
    if (markedForRemoval.value.has(imageId)) {
        markedForRemoval.value.delete(imageId);
        form.removedImages = Array.from(markedForRemoval.value);
    } else {
        markedForRemoval.value.add(imageId);
        form.removedImages = Array.from(markedForRemoval.value);
    }
};

const removeNewImage = (index) => {
    // Revoke the URL to prevent memory leaks
    URL.revokeObjectURL(imageUrls.value[index]);
    
    // Remove the image from both arrays
    imageUrls.value.splice(index, 1);
    form.images.splice(index, 1);
};

const submit = () => {
    console.log('Submitting form with images:', form.images);
    console.log('Removed images:', form.removedImages);

    // Submit using Inertia
    form.post(route('posts.update', props.post.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            console.log('Form submitted successfully');
            form.images = [];
            form.removedImages = [];
            markedForRemoval.value.clear();
            imageUrls.value = [];
            if (imageInput.value) {
                imageInput.value.value = '';
            }
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        }
    });
};
</script>

<template>
    <Head title="Edit Post" />

    <MainLayout>
        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Post</h1>

                    <form @submit.prevent="submit" class="space-y-6" enctype="multipart/form-data">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input
                                type="text"
                                id="title"
                                v-model="form.title"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                :disabled="form.processing"
                            />
                            <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                        </div>

                        <!-- Subfapp -->
                        <div>
                            <label for="subfapp" class="block text-sm font-medium text-gray-700">Subfapp</label>
                            <select
                                id="subfapp"
                                v-model="form.subfapp_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                :disabled="form.processing"
                            >
                                <option v-for="subfapp in subfapps" :key="subfapp.id" :value="subfapp.id">
                                    f/{{ subfapp.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.subfapp_id" class="mt-1 text-sm text-red-600">{{ form.errors.subfapp_id }}</p>
                        </div>

                        <!-- Content -->
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                            <RichTextEditor
                                v-model="form.content"
                                :disabled="form.processing"
                                class="mt-1"
                            />
                            <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
                        </div>

                        <!-- Debug output -->
                        <pre v-if="props.post" class="hidden">{{ props.post }}</pre>
                        
                        <!-- Image Upload -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-lg font-semibold text-gray-800">Images</label>
                                <span class="text-sm text-gray-500">Supported formats: JPEG, PNG, GIF, WEBP</span>
                            </div>
                            
                            <!-- Upload Area -->
                            <div class="relative group">
                                <input
                                    ref="imageInput"
                                    type="file"
                                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                    multiple
                                    @change="handleImageUpload"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                    :disabled="form.processing"
                                />
                                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-500 transition-colors duration-200">
                                    <div class="space-y-2">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="text-sm text-gray-600">
                                            <span class="font-medium text-blue-600 hover:text-blue-500">Click to upload</span>
                                            <span> or drag and drop</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Error Messages -->
                            <div v-if="form.errors.images || Object.keys(form.errors).some(key => key.startsWith('images.'))" 
                                class="bg-red-50 border-l-4 border-red-400 p-4 rounded-md">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p v-if="form.errors.images" class="text-sm text-red-600">{{ form.errors.images }}</p>
                                        <p v-for="(error, key) in form.errors" :key="key" 
                                           v-if="key.startsWith('images.')" 
                                           class="text-sm text-red-600">
                                            {{ error }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Images -->
                        <div v-if="currentImages.length > 0" class="space-y-4 mt-8">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-800">Current Images</h3>
                                <span class="px-2.5 py-0.5 text-sm font-medium bg-blue-100 text-blue-800 rounded-full">{{ currentImages.length }}</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                                <div v-for="image in currentImages" :key="image.id" 
                                     class="relative group aspect-w-16 aspect-h-9 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
                                    <img 
                                        :src="image.url" 
                                        :alt="'Image ' + image.id"
                                        class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105"
                                        :class="{ 'opacity-40 scale-100': isImageMarkedForRemoval(image.id) }"
                                    />
                                    <div 
                                        class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200 bg-gradient-to-t from-black/60 to-black/20"
                                        :class="{ 'opacity-100': isImageMarkedForRemoval(image.id) }"
                                    >
                                        <button 
                                            type="button"
                                            @click="removeImage(image.id)"
                                            class="px-4 py-2 text-sm font-medium rounded-lg shadow-sm transition-all duration-200 transform hover:scale-105"
                                            :class="isImageMarkedForRemoval(image.id) ? 
                                                'bg-green-500 hover:bg-green-600 text-white' : 
                                                'bg-red-500 hover:bg-red-600 text-white'"
                                        >
                                            {{ isImageMarkedForRemoval(image.id) ? 'Keep' : 'Remove' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Preview New Images -->
                        <div v-if="imageUrls.length" class="space-y-4 mt-8">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-800">New Images</h3>
                                <span class="px-2.5 py-0.5 text-sm font-medium bg-green-100 text-green-800 rounded-full">{{ imageUrls.length }}</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                                <div v-for="(url, index) in imageUrls" :key="index" 
                                     class="relative group aspect-w-16 aspect-h-9 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
                                    <img 
                                        :src="url" 
                                        :alt="'New image ' + (index + 1)"
                                        class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105"
                                    />
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200 bg-gradient-to-t from-black/60 to-black/20">
                                        <button 
                                            type="button"
                                            @click="removeNewImage(index)"
                                            class="px-4 py-2 text-sm font-medium rounded-lg shadow-sm bg-red-500 hover:bg-red-600 text-white transition-all duration-200 transform hover:scale-105"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                    <button 
                                        type="button"
                                        @click="removeNewImage(index)"
                                        class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                :disabled="form.processing"
                            >
                                Update Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
