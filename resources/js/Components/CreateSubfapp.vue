<template>
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">Create a New Subfapp</h2>
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input
                    type="text"
                    id="name"
                    v-model="form.name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    :class="{ 'border-red-500': form.errors.name }"
                    required
                    @input="generateDisplayName"
                />
                <p class="mt-1 text-sm text-gray-500">This will be used in the URL: f/{{ form.name }}</p>
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
            </div>

            <div>
                <label for="display_name" class="block text-sm font-medium text-gray-700">Display Name</label>
                <input
                    type="text"
                    id="display_name"
                    v-model="form.display_name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    :class="{ 'border-red-500': form.errors.display_name }"
                    required
                />
                <p v-if="form.errors.display_name" class="mt-1 text-sm text-red-500">{{ form.errors.display_name }}</p>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                    id="description"
                    v-model="form.description"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    :class="{ 'border-red-500': form.errors.description }"
                ></textarea>
                <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</p>
            </div>

            <div>
                <label for="icon" class="block text-sm font-medium text-gray-700">Icon URL (optional)</label>
                <input
                    type="url"
                    id="icon"
                    v-model="form.icon"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    :disabled="form.processing"
                >
                    Create Subfapp
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    display_name: '',
    description: '',
    icon: '',
});

const generateDisplayName = () => {
    if (!form.display_name) {
        form.display_name = form.name;
    }
};

const submit = () => {
    form.post(route('subfapps.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>
