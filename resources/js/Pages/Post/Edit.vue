<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import RichTextEditor from "@/Components/RichTextEditor.vue";
import ImageGallery from "@/Components/ImageGallery.vue";
import { ref, computed, onBeforeUnmount, onMounted } from 'vue';
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from '@headlessui/vue';

const showDeleteConfirm = ref(false);

const deletePost = () => {
    router.delete(route('posts.destroy', props.post.id), {
        onSuccess: () => {
            showDeleteConfirm.value = false;
        },
    });
};

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
                        <div class="space-y-2">
                            <div class="flex flex-col space-y-1">
                                <h2 class="text-xl font-semibold text-gray-900">Upload Images</h2>
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
                                <div class="border border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-500 transition-colors duration-200">
                                    <div class="space-y-3">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <div class="text-sm">
                                            <span class="text-blue-500 hover:text-blue-600 font-medium">Click to upload</span>
                                            <span class="text-gray-600"> or drag and drop</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Error Messages -->
                            <div v-if="form.errors.images || Object.keys(form.errors).some(key => key.startsWith('images.'))" 
                                class="bg-red-50 border-l-4 border-red-400 p-3 sm:p-4 rounded-md">
                                <div class="flex flex-col sm:flex-row gap-2 sm:gap-0">
                                    <div class="flex-shrink-0 hidden sm:block">
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
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <h3 class="text-lg font-semibold text-gray-800">Current Images</h3>
                                <span class="px-2.5 py-0.5 text-sm font-medium bg-blue-100 text-blue-800 rounded-full self-start sm:self-auto">{{ currentImages.length }}</span>
                            </div>
                            <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
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
                                            class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm font-medium rounded-lg shadow-sm transition-all duration-200 transform hover:scale-105"
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
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <h3 class="text-lg font-semibold text-gray-800">New Images</h3>
                                <span class="px-2.5 py-0.5 text-sm font-medium bg-green-100 text-green-800 rounded-full self-start sm:self-auto">{{ imageUrls.length }}</span>
                            </div>
                            <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
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

                        <!-- Action Buttons -->
                        <div class="flex justify-between items-center">
                            <button 
                                type="button" 
                                @click="showDeleteConfirm = true"
                                class="group inline-flex items-center px-4 py-2 text-sm font-medium bg-red-50 text-red-700 hover:bg-red-100 rounded-lg transition-all duration-200 border border-red-200 hover:border-red-300 shadow-sm hover:shadow"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-red-500 group-hover:text-red-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Delete Post
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                :disabled="form.processing"
                            >
                                Update Post
                            </button>
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
                    </form>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
