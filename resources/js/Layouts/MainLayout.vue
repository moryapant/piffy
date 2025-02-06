<script setup>
import Navbar from '@/Components/Navbar.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <Navbar />

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>
    </div>
</template>
