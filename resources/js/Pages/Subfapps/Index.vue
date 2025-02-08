<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    subfapps: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <Head title="Explore Subfapps" />
    
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sm:gap-0 mb-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Explore Communities</h1>
                        <p class="mt-2 text-gray-600 text-sm sm:text-base">Discover and join vibrant communities on Fapp</p>
                    </div>
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('subfapps.create')"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 sm:px-6 py-2.5 sm:py-3 border border-transparent text-sm sm:text-base font-medium rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create Community
                    </Link>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4 mt-4">
                    <div class="bg-white rounded-lg shadow px-4 sm:px-6 py-3 sm:py-4 border border-gray-100">
                        <div class="text-lg sm:text-2xl font-bold text-blue-600">{{ subfapps.total }}</div>
                        <div class="text-sm sm:text-base text-gray-600">Communities</div>
                    </div>
                    <div class="bg-white rounded-lg shadow px-4 sm:px-6 py-3 sm:py-4 border border-gray-100">
                        <div class="text-lg sm:text-2xl font-bold text-green-600">{{ subfapps.data.reduce((sum, s) => sum + (s.posts_count || 0), 0) }}</div>
                        <div class="text-sm sm:text-base text-gray-600">Posts</div>
                    </div>
                    <div class="col-span-2 sm:col-span-1 bg-white rounded-lg shadow px-4 sm:px-6 py-3 sm:py-4 border border-gray-100">
                        <div class="text-lg sm:text-2xl font-bold text-purple-600">{{ subfapps.data.reduce((sum, s) => sum + (s.member_count || 0), 0) }}</div>
                        <div class="text-sm sm:text-base text-gray-600">Members</div>
                    </div>
                </div>
            </div>

            <!-- Subfapps Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <div v-for="subfapp in subfapps.data" :key="subfapp.id" 
                    class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 ease-in-out border border-gray-100 overflow-hidden group">
                    <Link :href="route('subfapps.show', subfapp.id)" class="block">
                        <!-- Header with Icon -->
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                            <div class="flex items-center space-x-3">
                                <div v-if="subfapp.avtaar" class="flex-shrink-0 h-12 w-12">
                                    <img :src="`/storage/${subfapp.avtaar}`" class="h-12 w-12 rounded-full object-cover" :alt="subfapp.display_name">
                                </div>
                                <div v-else class="flex-shrink-0 h-12 w-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-xl">{{ subfapp.display_name.charAt(0).toUpperCase() }}</span>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-200">
                                        f/{{ subfapp.name }}
                                    </h2>
                                    <p class="text-sm text-gray-600">{{ subfapp.display_name }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="px-4 sm:px-6 py-3 sm:py-4">
                            <p class="text-gray-600 text-sm line-clamp-2 mb-4">
                                {{ subfapp.description }}
                            </p>

                            <!-- Stats -->
                            <div class="flex items-center justify-between text-xs sm:text-sm">
                                <div class="flex items-center space-x-2 sm:space-x-4">
                                    <div class="flex items-center text-gray-500">
                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        {{ subfapp.posts_count || 0 }} posts
                                    </div>
                                    <div class="flex items-center text-gray-500">
                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        {{ subfapp.member_count || 0 }} members
                                    </div>
                                </div>
                                <div class="text-blue-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                <Pagination :links="subfapps.links" />
            </div>
        </div>
    </MainLayout>
</template>
