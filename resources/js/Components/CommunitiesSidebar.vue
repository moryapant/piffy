<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    communities: {
        type: Array,
        required: true
    }
});
</script>

<template class="w-64">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Popular Displays</h2>
        </div>

        <!-- Display List -->
        <div class="divide-y divide-gray-100">
            <Link
                v-for="community in communities"
                :key="community.id"
                :href="route('subfapps.show', community.id)"
                class="flex items-center px-6 py-4 hover:bg-gray-50 transition-colors duration-200 group"
            >
                <!-- Avatar Icon -->
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full overflow-hidden">
                        <img v-if="community.avtaar" :src="`/storage/${community.avtaar}`" :alt="community.display_name" class="w-full h-full object-cover">
                        <div v-else class="w-full h-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                            <span class="text-white font-bold text-lg">{{ community.display_name[0].toUpperCase() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Display Info -->
                <div class="ml-4 flex-grow">
                    <h3 class="text-sm font-medium text-gray-900 group-hover:text-blue-600">
                        f/{{ community.name }}
                    </h3>
                    <div class="text-sm text-gray-500">
                        <p class="truncate">{{ community.member_count || 0 }} members</p>
                        <p class="text-xs text-gray-400">{{ community.posts_count || 0 }} posts</p>
                    </div>
                </div>
            </Link>
        </div>

        <!-- View All Link -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <Link
                :href="route('subfapps.index')"
                class="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200"
            >
                View All Communities →
            </Link>
        </div>
    </div>
</template>
