<script setup>
import { computed } from 'vue';

// Allow either a raw username OR a user object; at least one must be present.
const props = defineProps({
    username: {
        type: String,
        required: false,
    },
    user: {
        type: Object,
        required: false,
        default: null,
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    bgColor: {
        type: String,
        default: 'gray', // gray, blue
    },
});

const sizeClasses = {
    sm: 'w-8 h-8 text-sm',
    md: 'w-10 h-10 text-base',
    lg: 'w-12 h-12 text-lg',
};

const bgColorClasses = {
    gray: 'bg-gray-200 text-gray-600',
    blue: 'bg-blue-100 text-blue-600',
};

const displayName = computed(() => props.username || props.user?.name || '');
const initial = computed(() => (displayName.value?.trim()?.[0] || '?').toUpperCase());
const avatarUrl = computed(() => props.user?.avatar || null);
</script>

<template>
    <div
        :class="[
            'rounded-full flex items-center justify-center overflow-hidden flex-shrink-0 font-medium',
            sizeClasses[size],
            bgColorClasses[bgColor]
        ]"
    >
        <template v-if="avatarUrl">
            <img :src="avatarUrl" :alt="displayName" class="w-full h-full object-cover" />
        </template>
        <template v-else>
            <span>{{ initial }}</span>
        </template>
    </div>
</template>
