<template>
    <GuestLayout>
        <Head title="Google Sign In" />

        <div class="mb-4 text-sm text-gray-600">
            Please wait while we redirect you to Google Sign In...
        </div>

        <div class="mt-4 flex items-center justify-center">
            <button @click="signInWithGoogle" class="flex items-center gap-3 rounded-lg border border-gray-300 bg-white px-6 py-2 text-sm font-medium text-gray-800 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-100">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 48 48">
                    <defs><path id="a" d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"/></defs>
                    <clipPath id="b"><use xlink:href="#a" overflow="visible"/></clipPath><path clip-path="url(#b)" fill="#FBBC05" d="M0 37V11l17 13z"/><path clip-path="url(#b)" fill="#EA4335" d="M0 11l17 13 7-6.1L48 14V0H0z"/>
                    <path clip-path="url(#b)" fill="#34A853" d="M0 37l30-23 7.9 1L48 0v48H0z"/><path clip-path="url(#b)" fill="#4285F4" d="M48 48L17 24l-4-3 35-10z"/>
                </svg>
                Sign in with Google
            </button>
        </div>
    </GuestLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { signInWithGoogle } from '@/firebase';
import { onMounted } from 'vue';

const props = defineProps({
    status: {
        type: String,
    },
});

onMounted(() => {
    // Automatically trigger Google sign-in when the component mounts
    signInWithGoogle().catch((error) => {
        console.error('Google sign-in error:', error);
    });
});
</script>
