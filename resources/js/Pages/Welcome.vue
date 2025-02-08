<script setup>
import PostVoteButtons from '@/Components/PostVoteButtons.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import CommunitiesSidebar from '@/Components/CommunitiesSidebar.vue';
import PostSortTabs from '@/Components/PostSortTabs.vue';
import { ref } from 'vue';
import ImageGallery from '@/Components/ImageGallery.vue';
import Pagination from '@/Components/Pagination.vue';
import { timeAgo } from '@/utils/dateUtils';

const props = defineProps({
    posts: {
        type: Object,
        required: true
    },
    communities: {
        type: Array,
        required: true
    },
    canLogin: Boolean,
    currentSort: String,
    canRegister: Boolean
});

const selectedImage = ref(null);

const openImage = (image, e) => {
    e.preventDefault();
    selectedImage.value = image;
};

const closeImage = () => {
    selectedImage.value = null;
};

const form = useForm({
    vote_type: null
});

const vote = (postId, voteType) => {
    form.vote_type = voteType;
    form.post(route('posts.vote', postId), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Home" />

    <MainLayout>
        <div class="py-8 mx-auto max-w-8xl min-h-screen bg-gray-50 px-2 sm:px-6 lg:px-8">
            <div class="lg:flex lg:gap-8">
                <!-- Main Content -->
                <div class="w-full lg:flex-1">
                    <!-- Sort Tabs -->
                    <div class="mb-4">
                        <PostSortTabs
                            :current-sort="currentSort"
                        />
                    </div>
            <!-- Header Section -->
         


            <!-- Posts List -->
            <div class="space-y-4 sm:space-y-6 -mx-2 sm:mx-0">
                <div v-for="post in posts.data" :key="post.id"
                    class="overflow-hidden bg-white sm:rounded-xl border-y sm:border border-gray-100 shadow-sm transition-all duration-200 hover:border-blue-200 hover:shadow-md group">
                    <div class="p-3 sm:p-6">
                        <!-- Post Header -->
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
                            <div class="flex items-start sm:items-center space-x-3 sm:space-x-4">
                                <!-- User Avatar -->
                                <UserAvatar
                                    :username="post.user.name"
                                    size="md"
                                    class="ring-2 ring-white flex-shrink-0"
                                />
                                <!-- Post Meta Info -->
                                <div class="flex flex-col min-w-0">
                                    <Link :href="route('subfapps.show', post.subfapp.id)"
                                        class="text-sm font-bold text-gray-900 transition-colors duration-200 hover:text-blue-600 truncate"
                                        @click.stop
                                    >
                                        f/{{ post.subfapp.name }}
                                    </Link>
                                    <div class="flex flex-wrap items-center mt-0.5 text-sm text-gray-500">
                                        <span class="truncate">Posted by <span class="font-medium cursor-pointer hover:text-blue-600">u/{{ post.user.name }}</span></span>
                                        <span class="mx-2 hidden sm:inline">·</span>
                                        <span class="text-gray-400 sm:text-gray-500">{{ timeAgo(post.created_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div v-if="post.tags?.length" class="flex flex-wrap gap-1.5 sm:gap-2 mb-4 -mx-1 sm:mx-0">
                            <span
                                v-for="tag in post.tags"
                                :key="tag.id"
                                class="px-2 sm:px-3 py-1 text-xs font-semibold text-blue-600 bg-blue-50 rounded-lg border border-blue-100 transition-all duration-200 transform cursor-pointer hover:bg-blue-100 hover:scale-105 truncate max-w-[150px]"
                            >
                                #{{ tag.name }}
                            </span>
                        </div>

                        <!-- Post Content -->
                        <div class="space-y-4">
                            <div class="flex-grow">
                                <Link
                                    :href="route('posts.show', post.id)"
                                    class="block hover:no-underline space-y-3"
                                >
                                    <!-- Title with Highlight Bar -->
                                    <div class="relative group/title">
                                        <div class="absolute -left-3 sm:-left-6 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-500 to-indigo-600 rounded-full transform origin-left transition-all duration-300 scale-y-0 group-hover/title:scale-y-100"></div>
                                        <h2 class="text-lg sm:text-xl font-bold leading-snug tracking-tight text-gray-900 group-hover:text-blue-600 transition-colors duration-200">
                                            <span class="bg-gradient-to-r from-gray-900 to-gray-700 bg-[length:0%_1px] bg-no-repeat bg-left-bottom group-hover:bg-[length:100%_1px] transition-all duration-500 pb-0.5">
                                                {{ post.title }}
                                            </span>
                                        </h2>
                                    </div>

                                    <!-- Post Content -->
                                    <div
                                        class="prose prose-sm max-w-none prose-headings:font-semibold prose-headings:text-gray-900 prose-p:text-gray-600 prose-a:text-blue-600 hover:prose-a:text-blue-700 prose-strong:font-bold prose-strong:text-gray-900 prose-code:text-blue-600 prose-code:bg-blue-50 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded-md prose-code:before:content-none prose-code:after:content-none prose-img:rounded-lg prose-img:shadow-md prose-blockquote:border-blue-500 prose-blockquote:bg-blue-50 prose-blockquote:py-1 prose-blockquote:not-italic prose-ul:list-disc prose-ol:list-decimal line-clamp-3"
                                        v-html="post.content"
                                    ></div>
                                </Link>
                            </div>
                        </div>

                            <!-- Image Gallery -->
                            <div
                                v-if="post.images?.length"
                                class="mt-4 rounded-lg overflow-hidden"
                            >
                                <!-- Mobile View (Single Image) -->
                                <div class="block sm:hidden">
                                    <Link
                                        :href="route('posts.show', post.id)"
                                        class="relative block overflow-hidden rounded-lg group"
                                        @click="(e) => openImage(post.images[0], e)"
                                    >
                                        <div class="aspect-[16/10] relative overflow-hidden">
                                            <img
                                                :src="`/storage/${post.images[0].image_path}`"
                                                :alt="`Post image ${post.images[0].id}`"
                                                class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105"
                                                loading="lazy"
                                            />
                                        </div>

                                        <!-- Image Counter Indicator -->
                                        <div
                                            v-if="post.images.length > 1"
                                            class="absolute top-3 right-3 px-2.5 py-1.5 text-sm font-medium text-white bg-black/60 rounded-lg backdrop-blur-sm flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ post.images.length }}
                                        </div>
                                    </Link>
                                </div>

                                <!-- Desktop Grid -->
                                <div class="hidden sm:grid gap-2" 
                                    :class="{
                                        'grid-cols-1': post.images.length === 1,
                                        'grid-cols-2': post.images.length === 2,
                                        'grid-cols-2 grid-rows-2': post.images.length >= 3
                                    }">
                                    <Link
                                        v-for="(image, index) in post.images.slice(0, Math.min(4, post.images.length))"
                                        :key="image.id"
                                        :href="route('posts.show', post.id)"
                                        class="relative overflow-hidden rounded-lg group transition-transform duration-300 hover:scale-[1.02] aspect-square"
                                        @click="(e) => openImage(image, e)"
                                    >
                                        <img
                                            :src="`/storage/${image.image_path}`"
                                            :alt="`Post image ${image.id}`"
                                            class="object-cover w-full h-full"
                                            loading="lazy"
                                        />
                                        <!-- More Images Indicator -->
                                        <div
                                            v-if="index === 3 && post.images.length > 4"
                                            class="absolute inset-0 flex items-center justify-center bg-black/60 backdrop-blur-[2px]">
                                            <div class="text-center text-white">
                                                <span class="block text-2xl font-bold">+{{ post.images.length - 4 }}</span>
                                                <span class="text-sm font-medium text-white/90">more photos</span>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                            </div>

                        <!-- Post Actions -->
                        <div class="flex flex-wrap items-center gap-4 sm:gap-6 pt-4 mt-6 border-t border-gray-100">
                            <!-- Vote Buttons -->
                            <div class="flex items-center space-x-2" @click.stop>
                                <PostVoteButtons :post="post" @vote="vote" />
                            </div>

                            <!-- Comments -->
                            <Link :href="route('posts.show', post.id)"
                                class="flex items-center space-x-2 text-gray-500 transition-all duration-200 hover:text-blue-600 group"
                                @click.stop
                            >
                                <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span class="text-sm font-medium">{{ post.comments_count || 0 }} {{ post.comments_count === 1 ? 'comment' : 'comments' }}</span>
                            </Link>

                            <!-- Share -->
                            <button
                                class="flex items-center space-x-2 text-gray-500 transition-all duration-200 hover:text-blue-600 group"
                                @click.stop
                            >
                                <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                                <span class="text-sm font-medium">Share</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="posts.data.length === 0" class="p-12 text-center bg-white rounded-xl shadow-sm">
                <svg class="mx-auto w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No posts yet</h3>
                <p class="mt-2 text-gray-500">Get started by creating a new post.</p>
                <div class="mt-8">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('posts.create')"
                        class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-blue-600 rounded-xl border border-transparent shadow-sm transition-all duration-200 transform hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 hover:scale-105"
                    >
                        <svg class="mr-2 -ml-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Post
                    </Link>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                <Pagination :links="posts.links" />
                </div>
                </div>

                <!-- Right Sidebar -->
                <div class="hidden lg:block w-80 flex-shrink-0 space-y-6">
                    <CommunitiesSidebar :communities="communities" />
                </div>
            </div>
        </div>

        <!-- Full Screen Modal -->
        <div
            v-if="selectedImage"
            class="flex fixed inset-0 z-50 justify-center items-center p-4 bg-black bg-opacity-90 backdrop-blur-sm"
            @click="closeImage"
        >
            <button
                class="absolute top-6 right-6 text-white transition-colors duration-200 hover:text-gray-300 focus:outline-none"
                @click="closeImage"
            >
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <img
                :src="`/storage/${selectedImage.image_path}`"
                :alt="`Post image ${selectedImage.id}`"
                class="max-h-[90vh] max-w-[90vw] object-contain rounded-lg shadow-2xl"
                @click.stop
            />
        </div>
    </MainLayout>
</template>
