<script setup>
import Navbar from "@/Components/Navbar.vue";
import FlashMessage from "@/Components/FlashMessage.vue";
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />
    <!-- Main Content -->
    <main class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <slot />
    </main>
  </div>
</template>
