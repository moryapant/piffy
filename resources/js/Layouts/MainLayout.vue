<script setup>
import Navbar from '@/Components/Navbar.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import MobileBottomNavigation from '@/Components/MobileBottomNavigation.vue';
import { useTheme } from '@/Composables/useTheme.js';

// Initialize theme on app start
const { initTheme } = useTheme();
initTheme();
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
        <Navbar />

        <!-- Main Content -->
        <main class="max-w-[1600px] mx-auto px-2 sm:px-4 lg:px-6 py-6 pb-20 lg:pb-6">
            <slot />
        </main>

        <!-- Mobile Bottom Navigation -->
        <MobileBottomNavigation />
    </div>
</template>
