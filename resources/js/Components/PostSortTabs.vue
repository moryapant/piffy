<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, h } from 'vue';

const props = defineProps({
  currentSort: {
    type: String,
    default: 'hot'
  },
  subfappId: {
    type: [Number, String],
    default: null
  }
});

const isActive = (sort) => computed(() => props.currentSort === sort);

const sortOptions = [
  {
    label: 'Hot',
    labelLg: 'Hot (6h)',
    value: 'hot',
    icon: (props) => h('svg', {
      ...props,
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      innerHTML: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />`
    })
  },
  {
    label: 'New',
    labelLg: 'New',
    value: 'new',
    icon: (props) => h('svg', {
      ...props,
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      innerHTML: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />`
    })
  },
  {
    label: 'Top',
    labelLg: 'Top Votes (6h)',
    value: 'top',
    icon: (props) => h('svg', {
      ...props,
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      innerHTML: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M5 19l7-7 7 7" />`
    })
  },
  {
    label: 'Active',
    labelLg: 'Most Active (6h)',
    value: 'rising',
    icon: (props) => h('svg', {
      ...props,
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      innerHTML: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />`
    })
  }
];
</script>

<template>
  <div class="relative rounded-2xl bg-gradient-to-br from-white/80 to-white/10 backdrop-blur-lg border border-white/60 shadow-xl shadow-blue-500/5">
    <div class="flex items-stretch p-1.5 sm:p-2 gap-1 sm:gap-2 w-full">
      <Link
        v-for="option in sortOptions"
        :key="option.value"
        :href="subfappId ? route('subfapps.show', { subfapp: subfappId, sort: option.value }) : route('home', { sort: option.value })"
        :preserve-scroll="true"
        class="group flex items-center gap-1.5 sm:gap-2 px-2 sm:px-3 py-2 sm:py-2.5 text-xs sm:text-sm font-medium rounded-xl transition-all duration-300 flex-1 justify-center relative overflow-hidden"
        :class="{
          'bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 hover:scale-[1.02] hover:-translate-y-0.5': isActive(option.value),
          'text-gray-600 hover:text-gray-900 hover:bg-white/80': !isActive(option.value)
        }"
      >
        <!-- Animated Background Glow -->
        <div
          v-if="isActive(option.value)"
          class="absolute inset-0 bg-gradient-to-r from-blue-400/30 via-indigo-400/0 to-blue-400/30 blur-xl animate-gradient-x"
        />

        <!-- Hover Shine Effect -->
        <div
          class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
          :class="{
            'bg-gradient-to-r from-transparent via-white/10 to-transparent shine-effect': isActive(option.value),
            'bg-gradient-to-r from-transparent via-blue-100/30 to-transparent shine-effect': !isActive(option.value)
          }"
        />

        <!-- Icon -->
        <component
          :is="option.icon"
          :class="{
            'text-white group-hover:scale-110': isActive(option.value),
            'text-blue-500 group-hover:text-blue-600 group-hover:scale-110': !isActive(option.value)
          }"
          class="w-4 h-4 transition-all duration-300 transform"
        />

        <!-- Label -->
        <span
          :class="{
            'font-semibold': isActive(option.value),
            'group-hover:bg-gradient-to-r group-hover:from-blue-600 group-hover:to-indigo-600 group-hover:bg-clip-text group-hover:text-transparent': !isActive(option.value)
          }"
          class="relative transition-all duration-300"
        >
          <span class="hidden md:inline">{{ option.labelLg }}</span>
          <span class="md:hidden">{{ option.label }}</span>
        </span>
      </Link>
    </div>
  </div>
</template>

<style>
.shine-effect {
  animation: shine 2s infinite;
  transform: skewX(-20deg);
}

@keyframes shine {
  from { transform: translateX(-100%) skewX(-20deg); }
  to { transform: translateX(200%) skewX(-20deg); }
}

@keyframes gradient-x {
  0%, 100% { transform: translateX(-50%); }
  50% { transform: translateX(50%); }
}

.animate-gradient-x {
  animation: gradient-x 3s ease infinite;
}
</style>