<script setup>
import { Link } from "@inertiajs/vue3";
import { computed } from 'vue';
import SocialShare from '@/Components/SocialShare.vue';

const props = defineProps({
  // Post data
  post: {
    type: Object,
    required: true,
  },
  // Show share button (future feature)
  showShare: {
    type: Boolean,
    default: false
  },
  // Custom classes
  customClasses: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['vote']);

const handleVote = (voteType) => {
  // If user clicked the same vote type, remove the vote (toggle off)
  const userVote = props.post.user_vote?.vote_type || null;
  const newVoteType = userVote === voteType ? 0 : voteType;
  emit('vote', props.post.id, newVoteType);
};

// Format numbers for display (1000+ -> 1K+)
const formatCount = (count) => {
  if (!count || count === 0) return '0';
  if (count < 1000) return count.toString();
  if (count < 1000000) return `${Math.floor(count / 100) / 10}K`;
  return `${Math.floor(count / 100000) / 10}M`;
};

// Vote calculations
const upvotes = computed(() => props.post.upvotes || 0);
const downvotes = computed(() => props.post.downvotes || 0);
const totalScore = computed(() => upvotes.value - downvotes.value);

// User vote state
const userVote = computed(() => props.post.user_vote?.vote_type || null);
const hasUpvoted = computed(() => userVote.value === 1);
const hasDownvoted = computed(() => userVote.value === -1);
</script>

<template>
  <div :class="[
    'flex items-center flex-wrap gap-2',
    customClasses
  ]">
    
    <!-- Vote Buttons Container - Mobile First Touch-Friendly -->
    <div class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-1 sm:px-2 sm:py-1 md:px-3 md:py-1.5 lg:px-3 lg:py-1.5 transition-colors duration-200">
      <!-- Upvote Button -->
      <button 
        @click.stop.prevent="handleVote(1)"
        :class="[
          'flex items-center justify-center rounded-full transition-all duration-200',
          'focus:outline-none focus:ring-2 focus:ring-offset-1',
          'w-12 h-12 sm:w-10 sm:h-10 md:w-10 md:h-10 lg:w-12 lg:h-12', // Larger touch targets on mobile
          'touch-manipulation', // Improve touch responsiveness
          hasUpvoted 
            ? 'bg-blue-500 text-white shadow-md hover:bg-blue-600 focus:ring-blue-500 active:scale-95' 
            : 'text-gray-500 dark:text-gray-300 hover:text-blue-500 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 focus:ring-blue-200 dark:focus:ring-blue-800 active:scale-95'
        ]"
        :title="hasUpvoted ? 'Remove like' : 'Like'"
      >
        <svg :class="['fill-current', 'w-5 h-5 sm:w-4 sm:h-4 md:w-5 md:h-5 lg:w-5 lg:h-5']" viewBox="0 0 24 24">
          <path d="M7.493 18.75c-.425 0-.82-.236-.975-.632A7.48 7.48 0 016 15.375c0-1.75.599-3.358 1.602-4.634.151-.192.373-.309.6-.397.473-.183.89-.514 1.212-.924a9.042 9.042 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75 2.25 2.25 0 012.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558-.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H14.23c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23h-.777zM2.331 10.977a11.969 11.969 0 00-.831 4.398 12 12 0 00.52 3.507c.26.85 1.084 1.368 1.973 1.368H4.9c.445 0 .72-.498.523-.898a8.963 8.963 0 01-.924-3.977c0-1.708.476-3.305 1.302-4.666.245-.403-.028-.959-.5-.959H4.25c-.832 0-1.612.453-1.918 1.227z"/>
        </svg>
      </button>
      
      <!-- Vote Score -->
      <div :class="[
        'font-semibold text-center select-none',
        'px-3 min-w-[3rem] sm:px-2 sm:min-w-[2rem] md:px-3 md:min-w-[3rem] lg:px-3 lg:min-w-[3.5rem]',
        'text-sm sm:text-xs md:text-sm lg:text-sm', // Larger text on mobile for readability
        totalScore > 0 ? 'text-blue-600 dark:text-blue-400' : totalScore < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-300'
      ]">
        {{ totalScore }}
      </div>
      
      <!-- Downvote Button -->
      <button 
        @click.stop.prevent="handleVote(-1)"
        :class="[
          'flex items-center justify-center rounded-full transition-all duration-200',
          'focus:outline-none focus:ring-2 focus:ring-offset-1',
          'w-12 h-12 sm:w-10 sm:h-10 md:w-10 md:h-10 lg:w-12 lg:h-12', // Larger touch targets on mobile
          'touch-manipulation', // Improve touch responsiveness
          hasDownvoted 
            ? 'bg-red-500 text-white shadow-md hover:bg-red-600 focus:ring-red-500 active:scale-95' 
            : 'text-gray-500 dark:text-gray-300 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 focus:ring-red-200 dark:focus:ring-red-800 active:scale-95'
        ]"
        :title="hasDownvoted ? 'Remove dislike' : 'Dislike'"
      >
        <svg :class="['fill-current', 'w-5 h-5 sm:w-4 sm:h-4 md:w-5 md:h-5 lg:w-5 lg:h-5']" viewBox="0 0 24 24">
          <path d="M15.73 5.25h1.035A7.465 7.465 0 0118 9.375a7.465 7.465 0 01-1.235 4.125h-.148c-.806 0-1.534.446-2.031 1.08a9.04 9.04 0 01-2.861 2.4c-.723.384-1.35.956-1.653 1.715a4.498 4.498 0 00-.322 1.672V21a.75.75 0 01-.75.75 2.25 2.25 0 01-2.25-2.25c0-1.152.26-2.243.723-3.218C7.74 15.724 7.366 15 6.748 15H3.622c-1.026 0-1.945-.694-2.054-1.715A12.134 12.134 0 011.5 12c0-2.848.992-5.464 2.649-7.521.388-.482.987-.729 1.605-.729H9.77a4.5 4.5 0 011.423.23l3.114 1.04a4.5 4.5 0 001.423.23zM21.669 14.023c.536-1.362.831-2.845.831-4.398 0-1.22-.182-2.398-.52-3.507-.26-.85-1.084-1.368-1.973-1.368H19.1c-.445 0-.72.498-.523.898.591 1.2.924 2.55.924 3.977a8.958 8.958 0 01-1.302 4.666c-.245.403.028.959.5.959h1.053c.832 0 1.612-.453 1.918-1.227z"/>
        </svg>
      </button>
    </div>

    <!-- Comments Button -->
    <Link
      :href="route('posts.show', post.id)"
      class="flex items-center gap-2 px-3 py-2 sm:py-1.5 md:px-4 md:py-2 lg:px-4 lg:py-2 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-600 focus:ring-offset-1 touch-manipulation active:scale-95 min-h-[44px] sm:min-h-[36px]"
      @click.stop
    >
      <!-- Comment Icon -->
      <svg class="w-5 h-5 md:w-6 md:h-6 lg:w-6 lg:h-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
      </svg>
      
      <!-- Comment Count -->
      <span class="font-medium whitespace-nowrap text-xs md:text-sm lg:text-sm">
        {{ formatCount(post.comments_count || 0) }}
        <span class="hidden md:inline">
          {{ (post.comments_count || 0) === 1 ? ' comment' : ' comments' }}
        </span>
      </span>
    </Link>

    <!-- Social Share Component -->
    <SocialShare 
      :post="post" 
      :showLabels="true"
      size="md"
    />

    <!-- Views Count -->
    <div class="flex items-center gap-2 px-3 py-1.5 md:px-4 md:py-2 lg:px-4 lg:py-2 rounded-full bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 select-none transition-colors duration-200">
      <!-- Eye Icon -->
      <svg class="w-6 h-6 md:w-7 md:h-7 lg:w-7 lg:h-7 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
      </svg>
      
      <!-- Views Count -->
      <span class="font-medium whitespace-nowrap text-sm md:text-base lg:text-base">
        {{ formatCount(post.views_count || 0) }}
        <span class="hidden md:inline">
          {{ (post.views_count || 0) === 1 ? ' view' : ' views' }}
        </span>
      </span>
    </div>

    <!-- NSFW Tag (if applicable) -->
    <span 
      v-if="post.nsfw" 
      class="px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-600 border border-red-200 flex-shrink-0"
    >
      NSFW
    </span>
  </div>
</template>

<style scoped>
/* Ensure consistent button styling */
button:focus,
a:focus {
  outline: none;
}

/* Smooth hover transitions */
button,
a {
  transition: all 0.2s ease;
}

/* Mobile touch improvements */
@media (max-width: 768px) {
  button,
  a {
    min-height: 44px; /* Minimum touch target size */
    touch-action: manipulation; /* Prevent zoom on double-tap */
  }
}

/* Responsive spacing adjustments */
@media (min-width: 640px) {
  .gap-2 {
    gap: 0.5rem;
  }
}

@media (min-width: 768px) {
  .gap-2 {
    gap: 0.75rem;
  }
}
</style>