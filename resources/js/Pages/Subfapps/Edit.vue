<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
  subfapp: {
    type: Object,
    required: true,
  },
});

const form = useForm({
    display_name: props.subfapp.display_name || '',
    description: props.subfapp.description || '',
    type: props.subfapp.type || 'public',
    icon: null,
});

const uploadedIcon = ref(null);
const iconPreview = ref(props.subfapp.icon ? `/storage/${props.subfapp.icon}` : null);

// Initialize form state on mount
onMounted(() => {
    // Form initialized
});

// Clear display_name error when user types
watch(() => form.display_name, (newValue) => {
    if (form.errors.display_name && newValue && newValue.trim().length > 0) {
        // Force clear the specific error
        delete form.errors.display_name;
    }
});

// Handle icon upload
const handleIconUpload = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    if (file.size > 2 * 1024 * 1024) {
        alert('Image size should not exceed 2MB');
        return;
    }
    
    // File selected for upload
    
    uploadedIcon.value = file;
    form.icon = file; // Set file in form
    
    // Clear any previous errors
    if (form.errors.icon) {
        delete form.errors.icon;
    }
    
    // Create preview URL
    const reader = new FileReader();
    reader.onload = (e) => {
        iconPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const submit = () => {
    // Clear all errors before submitting
    form.clearErrors();
    
    // If there's a file, we need to use FormData manually
    if (form.icon) {
        const formData = new FormData();
        formData.append('display_name', form.display_name || '');
        formData.append('description', form.description || '');
        formData.append('type', form.type || 'public');
        formData.append('icon', form.icon);
        formData.append('_method', 'PATCH');
        
        // Use router.post with FormData
        router.post(route('subfapps.update', props.subfapp.id), formData, {
            onSuccess: () => {
                // Form submitted successfully
            },
            onError: (errors) => {
                // Handle form submission errors
            },
            onFinish: () => {
                // Form submission finished
            }
        });
    } else {
        form.patch(route('subfapps.update', props.subfapp.id), {
            onSuccess: () => {
                // Form submitted successfully
            },
            onError: (errors) => {
                // Handle form submission errors
            },
            onFinish: () => {
                // Form submission finished
            }
        });
    }
};
</script>

<template>
    <Head :title="`Edit ${subfapp.display_name}`" />
    <MainLayout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-900 rounded-t-md shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-1">
                <div class="flex items-center justify-between">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Edit {{ subfapp.display_name }}</h1>
                    <Link
                        :href="route('subfapps.show', subfapp.id)"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-full shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Cancel
                    </Link>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white dark:bg-gray-900 rounded-b-md shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Community Name (Read-only) -->
                    <div>
                        <label for="name" class="block text-base font-medium text-gray-900 dark:text-gray-100 mb-2">Name</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">f/</span>
                            </div>
                            <input
                                type="text"
                                id="name"
                                :value="subfapp.name"
                                class="pl-8 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm py-3 bg-gray-50 dark:bg-gray-700"
                                readonly
                            />
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Community names cannot be changed
                        </p>
                    </div>

                    <!-- Display Name -->
                    <div>
                        <label for="display_name" class="block text-base font-medium text-gray-900 dark:text-gray-100 mb-2">
                            Display Name
                            <button 
                                type="button" 
                                @click="() => { form.errors = {}; }" 
                                class="ml-2 text-xs text-blue-500 hover:text-blue-700"
                                v-if="Object.keys(form.errors).length > 0"
                            >
                                Clear Errors
                            </button>
                        </label>
                        <input
                            type="text"
                            id="display_name"
                            v-model="form.display_name"
                            class="block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-3"
                            :class="{ 
                                'border-red-500 dark:border-red-500': form.errors.display_name,
                                'border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white': !form.errors.display_name
                            }"
                            required
                            placeholder="Community Display Name"
                            @input="(event) => { 
                                if (form.errors.display_name && event.target.value && event.target.value.trim().length > 0) {
                                    delete form.errors.display_name;
                                }
                            }"
                        />
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            This is how your community name will be shown publicly
                        </p>
                        <p v-if="form.errors.display_name" class="mt-1 text-sm text-red-500">
                            {{ form.errors.display_name }}
                        </p>
                    </div>

                    <!-- Community Icon -->
                    <div>
                        <label for="icon" class="block text-base font-medium text-gray-900 dark:text-gray-100 mb-2">Community Icon</label>
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div 
                                    class="h-24 w-24 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-700 flex items-center justify-center"
                                >
                                    <img v-if="iconPreview" :src="iconPreview" class="h-full w-full object-cover" />
                                    <div v-else class="flex items-center justify-center w-full h-full rounded-full bg-gradient-to-br from-orange-400 to-pink-500">
                                        <span class="text-2xl font-black text-white">{{ subfapp.display_name[0] }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow pt-1">
                                <label
                                    for="community-icon"
                                    class="inline-block px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200 text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer"
                                >
                                    Upload New Icon
                                    <input
                                        id="community-icon"
                                        type="file"
                                        class="sr-only"
                                        accept="image/*"
                                        @change="handleIconUpload"
                                    />
                                </label>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    An icon helps people recognize your community. 256x256px PNG or JPG recommended.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Community Type -->
                    <div>
                        <label for="type" class="block text-base font-medium text-gray-900 dark:text-gray-100 mb-2">Community Type</label>
                        <select
                            id="type"
                            v-model="form.type"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 py-3"
                            :class="{ 'border-red-500': form.errors.type }"
                            required
                        >
                            <option value="public">Public</option>
                            <option value="restricted">Restricted</option>
                            <option value="private">Private</option>
                            <option value="hidden">Hidden</option>
                        </select>
                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400 space-y-1">
                            <div v-if="form.type === 'public'" class="text-green-600 dark:text-green-400">
                                <strong>Public:</strong> Anyone can view and join this community
                            </div>
                            <div v-if="form.type === 'restricted'" class="text-yellow-600 dark:text-yellow-400">
                                <strong>Restricted:</strong> Anyone can view, but must request to join
                            </div>
                            <div v-if="form.type === 'private'" class="text-orange-600 dark:text-orange-400">
                                <strong>Private:</strong> Only members can view and must be invited to join
                            </div>
                            <div v-if="form.type === 'hidden'" class="text-red-600 dark:text-red-400">
                                <strong>Hidden:</strong> Only members can view, invisible to non-members
                            </div>
                        </div>
                        <p v-if="form.errors.type" class="mt-1 text-sm text-red-500">{{ form.errors.type }}</p>
                    </div>

                    <!-- Community Description -->
                    <div>
                        <label for="description" class="block text-base font-medium text-gray-900 dark:text-gray-100 mb-2">Description</label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 py-3"
                            :class="{ 'border-red-500': form.errors.description }"
                            placeholder="Tell people what your community is about"
                        ></textarea>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            This is how potential members will learn about your community
                        </p>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                        <Link
                            :href="route('subfapps.show', subfapp.id)"
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
                            {{ form.processing ? "Saving..." : "Save Changes" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </MainLayout>
</template>