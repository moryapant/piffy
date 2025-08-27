<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 sm:text-2xl">
                    Profile Settings
                </h2>
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Manage your account settings</span>
                </div>
            </div>
        </template>

        <div class="py-6 bg-gray-50 sm:py-12">
            <div class="mx-auto max-w-7xl">
                <!-- Profile Grid Layout -->
                <div class="grid gap-6 lg:grid-cols-3 lg:gap-8">
                    <!-- Main Content -->
                    <div class="space-y-6 lg:col-span-2">
                        <!-- Profile Information Card -->
                        <div class="overflow-hidden bg-white shadow-lg rounded-xl">
                            <div class="px-4 py-5 bg-gradient-to-r from-indigo-500 to-purple-600 sm:px-6">
                                <h3 class="text-lg font-medium text-white">
                                    Profile Information
                                </h3>
                                <p class="mt-1 text-sm text-indigo-100">
                                    Update your account's profile information and email address.
                                </p>
                            </div>
                            <div class="px-4 py-5 sm:px-6">
                                <UpdateProfileInformationForm
                                    :must-verify-email="mustVerifyEmail"
                                    :status="status"
                                />
                            </div>
                        </div>

                        <!-- Security Card -->
                        <div class="overflow-hidden bg-white shadow-lg rounded-xl">
                            <div class="px-4 py-5 bg-gradient-to-r from-green-500 to-teal-600 sm:px-6">
                                <h3 class="text-lg font-medium text-white">
                                    Security Settings
                                </h3>
                                <p class="mt-1 text-sm text-green-100">
                                    Keep your account secure with a strong password.
                                </p>
                            </div>
                            <div class="px-4 py-5 sm:px-6">
                                <UpdatePasswordForm />
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Account Overview Card -->
                        <div class="overflow-hidden bg-white shadow-lg rounded-xl">
                            <div class="px-4 py-5 sm:px-6">
                                <h3 class="text-lg font-medium text-gray-900">Account Overview</h3>
                                <div class="mt-4 space-y-4">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span class="text-sm font-medium text-gray-700">Email Verified</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            <span class="text-sm font-medium text-gray-700">Account Secure</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions Card -->
                        <div class="overflow-hidden bg-white shadow-lg rounded-xl">
                            <div class="px-4 py-5 sm:px-6">
                                <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                                <div class="mt-4 space-y-2">
                                    <a href="/dashboard" class="flex items-center w-full p-3 text-sm font-medium text-gray-700 transition-colors bg-gray-50 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                                        </svg>
                                        View Dashboard
                                    </a>
                                    <a href="/" class="flex items-center w-full p-3 text-sm font-medium text-gray-700 transition-colors bg-gray-50 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        Go to Home
                                    </a>
                                    <a href="/posts/create" class="flex items-center w-full p-3 text-sm font-medium text-gray-700 transition-colors bg-gray-50 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Create Post
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Danger Zone Card -->
                        <div class="overflow-hidden bg-white shadow-lg rounded-xl">
                            <div class="px-4 py-5 bg-gradient-to-r from-red-500 to-pink-600 sm:px-6">
                                <h3 class="text-lg font-medium text-white">
                                    Danger Zone
                                </h3>
                                <p class="mt-1 text-sm text-red-100">
                                    Irreversible and destructive actions.
                                </p>
                            </div>
                            <div class="px-4 py-5 sm:px-6">
                                <DeleteUserForm />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
