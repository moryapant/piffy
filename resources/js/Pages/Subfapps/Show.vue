<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import PostVoteButtons from '@/Components/PostVoteButtons.vue';
import { timeAgo } from '@/utils/dateUtils';

const coverImageInput = ref(null);
const avatarImageInput = ref(null);
const isHoveringCover = ref(false);
const isHoveringAvatar = ref(false);

const coverForm = useForm({
    cover_image: null,
});

const avatarForm = useForm({
    avatar: null,
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

const props = defineProps({
    subfapp: {
        type: Object,
        required: true
    },
    posts: {
        type: Object,
        required: true
    },
    hasJoined: {
        type: Boolean,
        required: true
    },
    membersCount: {
        type: Number,
        required: true
    }
});
</script>

<template>
    <Head :title="subfapp.display_name" />

    <MainLayout>
        <!-- Banner and Header -->
        <div
            class="h-96 relative overflow-hidden group"
            @mouseenter="isHoveringCover = true"
            @mouseleave="isHoveringCover = false"
        >
            <!-- Cover Image or Gradient Background -->
            <div
                :class="[
                    'absolute inset-0 bg-cover bg-center transition-opacity duration-300',
                    { 'opacity-90': isHoveringCover && $page.props.auth.user?.id === subfapp.created_by }
                ]"
                :style="subfapp.cover_image ? `background-image: url(/storage/${subfapp.cover_image})` : 'background-image: linear-gradient(to right, rgb(59, 130, 246), rgb(79, 70, 229))'"
            ></div>
            <!-- Cover Update Button (Only visible to creator when hovering) -->
            <div
                v-if="$page.props.auth.user?.id === subfapp.created_by"
                :class="[
                    'absolute inset-0 flex items-center justify-center transition-opacity duration-300',
                    isHoveringCover ? 'opacity-100' : 'opacity-0'
                ]"
            >
                <button
                    @click="() => coverImageInput.click()"
                    class="bg-black/50 hover:bg-black/60 text-white px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 flex items-center space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Change Cover</span>
                </button>
                <!-- Hidden file inputs -->
                <input
                    ref="coverImageInput"
                    type="file"
                    accept=".jpg,.jpeg,.png,.webp"
                    class="hidden"
                    @change="(e) => {
                        coverForm.cover_image = e.target.files[0];
                        coverForm.post(route('subfapps.cover.update', subfapp.id), {
                            preserveScroll: true,
                            onSuccess: () => {
                                e.target.value = null;
                            }
                        });
                    }"
                />
                <input
                    ref="avatarImageInput"
                    type="file"
                    accept=".jpg,.jpeg,.png,.webp"
                    class="hidden"
                    @change="(e) => {
                        avatarForm.avatar = e.target.files[0];
                        avatarForm.post(route('subfapps.avatar.update', subfapp.id), {
                            preserveScroll: true,
                            onSuccess: () => {
                                e.target.value = null;
                            }
                        });
                    }"
                >
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-end">
                <div class="bg-white/95 backdrop-blur-sm rounded-t-lg shadow-sm px-8 py-6 flex items-center justify-between w-full border-t border-x border-gray-200">
                    <!-- Left side with avatar and info -->
                    <div class="flex items-center space-x-4">
                        <!-- Subfapp Icon with improved visibility -->
                        <div 
                            class="w-32 h-32 bg-white rounded-full flex items-center justify-center overflow-hidden -mt-16 border-4 border-white shadow-lg ring-2 ring-gray-100 relative group"
                            @mouseenter="isHoveringAvatar = true"
                            @mouseleave="isHoveringAvatar = false"
                        >
                            <img v-if="subfapp.icon" :src="`/storage/${subfapp.icon}`" :alt="subfapp.display_name" class="w-full h-full object-cover" />
                            <span v-else class="text-5xl font-bold text-gray-400">{{ subfapp.display_name[0] }}</span>
                            
                            <!-- Avatar Update Button (Only visible to creator when hovering) -->
                            <div
                                v-if="$page.props.auth.user?.id === subfapp.created_by"
                                :class="[
                                    'absolute inset-0 flex items-center justify-center bg-black/50 transition-opacity duration-200',
                                    isHoveringAvatar ? 'opacity-100' : 'opacity-0'
                                ]"
                            >
                                <button
                                    @click="() => avatarImageInput.click()"
                                    class="text-white px-2 py-1 rounded-full text-xs font-medium transition-colors duration-200 hover:text-blue-200 flex items-center gap-1"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Change</span>
                                </button>
                            </div>
                        </div>
                        <!-- Subfapp Info with improved text -->
                        <div class="ml-4">
                            <h1 class="text-4xl font-bold text-gray-900 leading-tight">{{ subfapp.display_name }}</h1>
                            <p class="text-sm font-medium text-gray-600">f/{{ subfapp.name }}</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex gap-6">
                <!-- Posts List -->
                <div class="flex-1 space-y-4">
                    <!-- Sort Options -->
                    <div class="flex items-center gap-4 -mb-2">
                        <button
                            v-for="(sort, index) in ['Hot', 'New', 'Top', 'Rising']"
                            :key="sort"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200"
                            :class="index === 0 ? 'bg-blue-50 text-blue-600 hover:bg-blue-100' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            <span v-if="sort === 'Hot'" class="text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                </svg>
                            </span>
                            <span v-if="sort === 'New'" class="text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </span>
                            <span v-if="sort === 'Top'" class="text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                            </span>
                            <span v-if="sort === 'Rising'" class="text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </span>
                            {{ sort }}
                        </button>
                    </div>
                    <div v-if="!hasJoined" class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm text-center">
                        <p class="text-gray-600">Join this community to see its posts</p>
                    </div>
                    <div v-else-if="posts.data.length === 0" class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm text-center">
                        <p class="text-gray-600">No posts yet</p>
                    </div>
                    <div v-else v-for="post in posts.data" :key="post.id"
                        class="overflow-hidden bg-white sm:rounded-xl border-y sm:border border-gray-100 shadow-sm transition-all duration-200 hover:border-blue-200 hover:shadow-md group">
                        <div class="p-3 sm:p-6">
                            <!-- Post Header -->
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
                                <div class="flex items-start sm:items-center gap-3 sm:gap-4">
                                    <!-- User Avatar -->
                                    <div class="w-8 h-8 bg-gray-100 ring-2 ring-blue-100 rounded-full flex items-center justify-center overflow-hidden">
                                        <span class="text-sm font-medium text-gray-600">{{ post.user.name[0].toUpperCase() }}</span>
                                    </div>
                                    <!-- User Info & Post Meta -->
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm font-medium text-gray-900">
                                            u/{{ post.user.name }}
                                        </span>
                                        <span class="text-xs text-gray-400">•</span>
                                        <span class="text-xs text-gray-500">{{ timeAgo(post.created_at) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Post Content -->
                            <div class="mt-3 space-y-4">
                                <Link :href="route('posts.show', post.id)">
                                    <h2 class="text-xl font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-200 line-clamp-2">{{ post.title }}</h2>
                                    <div class="text-gray-600 text-sm leading-relaxed line-clamp-3 mt-2 prose prose-sm max-w-none" v-html="post.content"></div>
                                </Link>
                                <!-- Tags -->
                                <div v-if="post.tags?.length" class="flex flex-wrap gap-2 mt-3">
                                    <span
                                        v-for="tag in post.tags"
                                        :key="tag.id"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors"
                                    >
                                        #{{ tag.name }}
                                    </span>
                                </div>

                                <!-- Image Gallery -->
                                <div
                                    :class="[
                                        'mt-4',
                                        post.images.length === 1 ? 'flex justify-center' : 'grid grid-cols-2 gap-2'
                                    ]"
                                    v-if="post.images?.length"
                                >
                                    <Link
                                        v-for="(image, index) in post.images.slice(0, 4)"
                                        :key="image.id"
                                        :href="route('posts.show', post.id)"
                                        :class="[
                                            'relative overflow-hidden rounded-lg group cursor-pointer',
                                            post.images.length === 1 ? 'w-2/3 aspect-video' : 'aspect-square'
                                        ]"
                                        @click.prevent="openImage(image)"
                                    >
                                        <img
                                            :src="`/storage/${image.image_path}`"
                                            :alt="`Post image ${image.id}`"
                                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                                            loading="lazy"
                                        />
                                        <!-- More Images Indicator -->
                                        <div
                                            v-if="index === 3 && post.images.length > 4"
                                            class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center text-white font-medium"
                                        >
                                            <span>+{{ post.images.length - 4 }} more</span>
                                        </div>
                                    </Link>
                                </div>
                            </div>

                            <!-- Post Actions -->
                            <div class="flex items-center space-x-4 mt-4 pt-4 border-t border-gray-100">
                                <!-- Vote Buttons -->
                                <div class="flex items-center space-x-1.5">
                                    <PostVoteButtons :post="post" @vote="vote" />
                                </div>

                                <!-- Comments -->
                                <Link :href="route('posts.show', post.id)"
                                    class="flex items-center space-x-2 text-gray-500 hover:text-blue-600 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <span class="text-sm">{{ post.comments_count || 0 }} {{ post.comments_count === 1 ? 'comment' : 'comments' }}</span>
                                </Link>

                                <!-- Share -->
                                <button class="flex items-center space-x-2 text-gray-500 hover:text-blue-600 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                    </svg>
                                    <span class="text-sm">Share</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="posts.data.length > 0" class="mt-6">
                        <Pagination :links="posts.links" />
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="w-80 hidden lg:block space-y-4 relative">
                    <!-- Join Community Card -->
                    <div class="bg-white sm:rounded-xl border border-gray-100 shadow-sm overflow-hidden sticky top-4">
                        <div class="p-4">
                            <Link
    v-if="!hasJoined"
    :href="route('subfapp.join', subfapp.id)"
    method="post"
    as="button"
    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium px-4 py-2 rounded-full transition-colors duration-200"
>
    Join Community
</Link>
<div v-else class="space-y-2">
    <div class="text-center text-sm text-gray-600">
        You are a member
    </div>
    <Link
        :href="route('subfapp.leave', subfapp.id)"
        method="delete"
        as="button"
        class="w-full bg-red-500 hover:bg-red-600 text-white font-medium px-4 py-2 rounded-full transition-colors duration-200 text-sm"
    >
        Leave Community
    </Link>
</div>
                            <p class="text-xs text-center text-gray-500 mt-2">Join to post and interact with the community</p>
                        </div>
                    </div>
                    <!-- About Community -->
                    <div class="bg-white sm:rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="bg-blue-600 px-4 py-3 border-b border-blue-500">
                            <h2 class="text-base font-semibold text-white">About Community</h2>
                        </div>
                        <div class="p-4 space-y-4">
                            <p class="text-sm text-gray-600">{{ subfapp.description }}</p>

                            <!-- Community Rules -->
                            <div class="border-t border-gray-100 pt-4">
                                <h3 class="text-base font-semibold text-gray-900 mb-3">Community Rules</h3>
                                <ul class="space-y-3">
                                    <li class="flex items-start gap-2">
                                        <span class="text-sm font-medium text-gray-700">1.</span>
                                        <p class="text-sm text-gray-600">Be respectful to others</p>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-sm font-medium text-gray-700">2.</span>
                                        <p class="text-sm text-gray-600">No spam or self-promotion</p>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-sm font-medium text-gray-700">3.</span>
                                        <p class="text-sm text-gray-600">Post relevant content only</p>
                                    </li>
                                </ul>
                            </div>

                            <!-- Community Stats -->
                            <div class="border-t border-gray-100 pt-4">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <!-- Members -->
                                    <div class="bg-blue-50 rounded-lg p-3 text-center">
                                        <div class="font-medium text-blue-900 text-lg">{{ membersCount }}</div>
                                        <div class="text-blue-600">Members</div>
                                    </div>
                                    <!-- Posts -->
                                    <div class="bg-blue-50 rounded-lg p-3 text-center">
                                        <div class="font-medium text-blue-900 text-lg">{{ posts.total || 0 }}</div>
                                        <div class="text-blue-600">Posts</div>
                                    </div>
                                </div>

                                <!-- Created Info -->
                                <div class="mt-4 space-y-2">
                                    <div class="flex items-center gap-2 text-sm text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Created {{ new Date(subfapp.created_at).toLocaleDateString() }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>Created by u/{{ subfapp.creator?.name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Full Screen Modal -->
                <div
                    v-if="selectedImage"
                    class="fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4"
                    @click="closeImage"
                >
                    <img :src="selectedImage" alt="Full size image" class="max-w-full max-h-full object-contain">
                </div>
            </div>
        </div>
    </MainLayout>
</template>
