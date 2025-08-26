<script setup>
import Navbar from '@/Components/Navbar.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <Navbar />

        <!-- Main Content -->
        <main class="max-w-[1600px] mx-auto px-2 sm:px-4 lg:px-6 py-6">
            <slot />
        </main>
    </div>
</template>
