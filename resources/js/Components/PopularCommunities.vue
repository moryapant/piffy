<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    communities: {
        type: Array,
        required: true
    }
});
</script>

<template>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 bg-gradient-to-r from-orange-50 to-red-50 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-900 flex items-center">
                <span class="text-base mr-1">🔥</span>
                Hottest Communities
            </h2>
        </div>

        <!-- Communities List -->
        <div class="divide-y divide-gray-100">
            <Link
                v-for="community in communities"
                :key="community.id"
                :href="route('subfapps.show', community.id)"
                class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors duration-200 group"
            >
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full overflow-hidden">
                        <img 
                            v-if="community.avtaar" 
                            :src="`/storage/${community.avtaar}`" 
                            :alt="community.display_name" 
                            class="w-full h-full object-cover"
                        >
                        <div 
                            v-else 
                            class="w-full h-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center"
                        >
                            <span class="text-white font-bold text-xs">{{ community.display_name ? community.display_name[0].toUpperCase() : community.name[0].toUpperCase() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Community Info -->
                <div class="ml-3 flex-grow min-w-0">
                    <h3 class="text-sm font-medium text-gray-900 group-hover:text-blue-600 truncate">
                        f/{{ community.name }}
                    </h3>
                    <div class="text-xs text-gray-500">
                        <p>{{ community.member_count || 0 }} members</p>
                        <p class="text-gray-400">{{ community.posts_count || 0 }} posts</p>
                    </div>
                </div>
            </Link>
        </div>

        <!-- View All Link -->
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            <Link
                :href="route('subfapps.index')"
                class="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200 flex items-center"
            >
                View All Communities
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </Link>
        </div>
    </div>
</template>