<template>
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">Create a New Post</h2>
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input
                    type="text"
                    id="title"
                    v-model="form.title"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    :class="{ 'border-red-500': form.errors.title }"
                    required
                />
                <p v-if="form.errors.title" class="mt-1 text-sm text-red-500">{{ form.errors.title }}</p>
            </div>

            <div>
                <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                <textarea
                    id="content"
                    v-model="form.content"
                    rows="5"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    :class="{ 'border-red-500': form.errors.content }"
                    required
                ></textarea>
                <p v-if="form.errors.content" class="mt-1 text-sm text-red-500">{{ form.errors.content }}</p>
            </div>

            <!-- Image Upload -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Images</label>
                <div class="flex flex-wrap gap-4 mb-4">
                    <div v-for="(preview, index) in imagePreviews" :key="index" class="relative">
                        <img :src="preview" class="w-32 h-32 object-cover rounded-lg border-2 border-gray-200" />
                        <button 
                            @click="removeImage(index)" 
                            type="button"
                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 focus:outline-none"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <label 
                        class="w-32 h-32 flex flex-col items-center justify-center border-2 border-gray-300 border-dashed rounded-lg cursor-pointer hover:border-blue-500 transition-colors duration-200"
                        :class="{ 'opacity-50 cursor-not-allowed': imagePreviews.length >= 10 }"
                    >
                        <input 
                            type="file" 
                            ref="fileInput"
                            @change="handleImageUpload" 
                            multiple 
                            accept=".jpg,.jpeg,.png,.webp"
                            class="hidden"
                            :disabled="imagePreviews.length >= 10"
                        >
                        <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="text-sm text-gray-500">Add Images</span>
                        <span class="text-xs text-gray-400 mt-1">({{ imagePreviews.length }}/10)</span>
                    </label>
                </div>
                <p class="text-sm text-gray-500">Upload up to 10 images (JPG, PNG, or WebP, max 5MB each)</p>
            </div>

            <div v-if="!subfappId">
                <label for="subfapp" class="block text-sm font-medium text-gray-700">Choose Subfapp</label>
                <select
                    id="subfapp"
                    v-model="form.subfapp_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    :class="{ 'border-red-500': form.errors.subfapp_id }"
                    required
                >
                    <option value="">Select a Subfapp</option>
                    <option v-for="subfapp in subfapps" :key="subfapp.id" :value="subfapp.id">
                        {{ subfapp.display_name }}
                    </option>
                </select>
                <p v-if="form.errors.subfapp_id" class="mt-1 text-sm text-red-500">{{ form.errors.subfapp_id }}</p>
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Creating...' : 'Create Post' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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
    title: '',
    content: '',
    subfapp_id: props.subfappId || '',
    images: [],
});

const imagePreviews = ref([]);

const handleImageUpload = (event) => {
    const files = Array.from(event.target.files || []);
    const remainingSlots = 10 - imagePreviews.value.length;
    const validFiles = files.slice(0, remainingSlots);

    validFiles.forEach(file => {
        if (file.size <= 5 * 1024 * 1024) { // 5MB limit
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreviews.value.push(e.target.result);
            };
            reader.readAsDataURL(file);
            form.images.push(file);
        } else {
            alert(`File ${file.name} exceeds 5MB limit`);
        }
    });
    event.target.value = ''; // Reset input
};

const removeImage = (index) => {
    imagePreviews.value.splice(index, 1);
    form.images.splice(index, 1);
};

const submit = () => {
    form.post(route('posts.store'), {
        onSuccess: () => {
            form.reset();
            imagePreviews.value = [];
        },
        forceFormData: true
    });
};
</script>
