<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import CreateSubfapp from '@/Components/CreateSubfapp.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Create Subfapp" />
    <MainLayout>
        <div class="max-w-2xl mx-auto mt-8">
            <CreateSubfapp />
        </div>
    </MainLayout>
</template>
