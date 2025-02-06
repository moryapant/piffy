<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const flash = computed(() => page.props.flash);
</script>

<template>
    <div v-if="flash.success" class="fixed top-20 right-4 z-50">
        <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ flash.success }}
        </div>
    </div>
</template>
