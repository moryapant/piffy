<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import CreatePost from '@/Components/CreatePost.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    subfapps: {
        type: Array,
        required: true
    }
});
</script>

<template>
    <Head title="Create Post" />
    
    <MainLayout>
        <div class="max-w-4xl mx-auto mt-8">
            <CreatePost :subfapps="subfapps" />
        </div>
    </MainLayout>
</template>
