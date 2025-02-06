<script setup>
defineProps({
    username: {
        type: String,
        required: true
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    bgColor: {
        type: String,
        default: 'gray' // gray, blue
    }
});

const sizeClasses = {
    sm: 'w-8 h-8 text-sm',
    md: 'w-10 h-10 text-base',
    lg: 'w-12 h-12 text-lg'
};

const bgColorClasses = {
    gray: 'bg-gray-200 text-gray-600',
    blue: 'bg-blue-100 text-blue-600'
};
</script>

<template>
    <div 
        :class="[
            'rounded-full flex items-center justify-center overflow-hidden flex-shrink-0 font-medium',
            sizeClasses[size],
            bgColorClasses[bgColor]
        ]"
    >
        <span>{{ username[0].toUpperCase() }}</span>
    </div>
</template>
