<script setup>
import { computed } from 'vue'

const props = defineProps({
  // The content object (post, comment, etc.)
  content: {
    type: Object,
    required: true
  },
  
  // Size variant: 'small', 'medium', 'large'
  size: {
    type: String,
    default: 'medium',
    validator: (value) => ['small', 'medium', 'large'].includes(value)
  },
  
  // Layout: 'vertical', 'horizontal'
  layout: {
    type: String,
    default: 'vertical',
    validator: (value) => ['vertical', 'horizontal'].includes(value)
  },
  
  // Show vote count
  showCount: {
    type: Boolean,
    default: true
  },
  
  // Compact mode (minimal styling)
  compact: {
    type: Boolean,
    default: false
  },
  
  // Content type for analytics
  contentType: {
    type: String,
    default: 'post'
  }
})

const emit = defineEmits(['vote'])

// Computed styles based on size
const sizeClasses = computed(() => {
  const sizes = {
    small: {
      container: 'space-y-1',
      button: 'w-7 h-7 p-1.5',
      icon: 'w-5 h-5',
      count: 'text-xs font-medium',
      spacing: 'space-y-0.5'
    },
    medium: {
      container: 'space-y-2',
      button: 'w-10 h-10 p-2',
      icon: 'w-6 h-6',
      count: 'text-sm font-medium',
      spacing: 'space-y-1'
    },
    large: {
      container: 'space-y-3',
      button: 'w-14 h-14 p-3',
      icon: 'w-8 h-8',
      count: 'text-base font-bold',
      spacing: 'space-y-1.5'
    }
  }
  return sizes[props.size]
})

// Computed layout classes
const layoutClasses = computed(() => {
  if (props.layout === 'horizontal') {
    const base = 'inline-flex items-center rounded-full';
    // Size-aware padding so larger icon sizes have room
    const sizePadding = {
      small: 'px-2 py-0.5',
      medium: 'px-2.5 py-1',
      large: 'px-3 py-1.5'
    }[props.size] || 'px-2 py-0.5';
    // Compact variant matches action pills (no border)
    const compactContainer = `bg-gray-100 ${sizePadding}`;
    const regularContainer = `bg-gray-100 border border-gray-200 ${sizePadding}`;
    return {
      container: `${base} ${props.compact ? compactContainer : regularContainer}`,
      buttonGroup: 'flex items-center',
      spacing: ''
    };
  }
  return {
    container: 'flex flex-col items-center',
    buttonGroup: 'flex flex-col items-center',
    spacing: sizeClasses.value.spacing
  };
})

// Vote counts
const upvotes = computed(() => props.content.upvotes || 0)
const downvotes = computed(() => props.content.downvotes || 0)
const totalScore = computed(() => upvotes.value - downvotes.value)

// User vote state
const userVote = computed(() => props.content.user_vote?.vote_type || null)
const hasUpvoted = computed(() => userVote.value === 1)
const hasDownvoted = computed(() => userVote.value === -1)

// Handle vote action
const handleVote = (voteType) => {
  // If user clicked the same vote type, remove the vote (toggle off)
  const newVoteType = userVote.value === voteType ? 0 : voteType
  emit('vote', props.content.id, newVoteType)
}
</script>

<template>
  <!-- Vertical Layout (unchanged) -->
  <div v-if="layout === 'vertical'" :class="[layoutClasses.container, props.compact ? 'space-y-0' : layoutClasses.spacing]">
    <div :class="layoutClasses.buttonGroup">
      <!-- Upvote/Like Button -->
      <button 
        @click.stop.prevent="handleVote(1)"
        :class="[
          sizeClasses.button,
          'rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1',
          hasUpvoted 
            ? 'bg-blue-500 text-white shadow-lg hover:bg-blue-600 focus:ring-blue-500 pulse-glow' 
            : 'bg-gray-100 text-gray-600 hover:bg-blue-50 hover:text-blue-500 focus:ring-blue-200'
        ]"
        :title="hasUpvoted ? 'Remove like' : 'Like'"
      >
        <svg 
          :class="[sizeClasses.icon, 'transition-transform duration-200']" 
          :style="{ transform: hasUpvoted ? 'scale(1.1)' : 'scale(1)' }"
          viewBox="0 0 24 24" 
          fill="currentColor"
        >
          <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/>
        </svg>
      </button>

      <!-- Vote Count Display -->
      <div 
        v-if="showCount" 
        :class="[
          sizeClasses.count,
          'text-center min-w-[2rem] py-0.5',
          totalScore > 0 ? 'text-blue-600' : totalScore < 0 ? 'text-red-600' : 'text-gray-600'
        ]"
      >
        {{ totalScore }}
      </div>

      <!-- Downvote/Dislike Button -->
      <button 
        @click.stop.prevent="handleVote(-1)"
        :class="[
          sizeClasses.button,
          'rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1',
          hasDownvoted 
            ? 'bg-red-500 text-white shadow-lg hover:bg-red-600 focus:ring-red-500' 
            : 'bg-gray-100 text-gray-600 hover:bg-red-50 hover:text-red-500 focus:ring-red-200'
        ]"
        :title="hasDownvoted ? 'Remove dislike' : 'Dislike'"
      >
        <svg 
          :class="[sizeClasses.icon, 'transition-transform duration-200']" 
          :style="{ transform: hasDownvoted ? 'scale(1.1)' : 'scale(1)' }"
          viewBox="0 0 24 24" 
          fill="currentColor"
        >
          <path d="M18 9.5a1.5 1.5 0 11-3 0v-6a1.5 1.5 0 013 0v6zM14 9.667v-5.43a2 2 0 00-1.106-1.79l-.05-.025A4 4 0 0011.057 2H5.64a2 2 0 00-1.962 1.608l-1.2 6A2 2 0 004.44 12H8v4a2 2 0 002 2 1 1 0 001-1v-.667a4 4 0 01.8-2.4l1.4-1.866a4 4 0 00.8-2.4z"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- Horizontal Layout (new design matching the image) -->
  <div v-else :class="[layoutClasses.container, props.compact ? 'px-1 py-0.5 space-x-1' : 'px-2 py-1 space-x-2']">
    <!-- Upvote/Like Button -->
    <button 
      @click.stop.prevent="handleVote(1)"
      :class="[
        'flex items-center justify-center rounded-full transition-all duration-200',
        sizeClasses.button,
        hasUpvoted 
          ? 'text-white bg-blue-500 shadow-md hover:bg-blue-600' 
          : 'text-gray-500 hover:text-blue-500 hover:bg-blue-50'
      ]"
      :title="hasUpvoted ? 'Remove like' : 'Like'"
    >
      <svg :class="[sizeClasses.icon]" fill="currentColor" viewBox="0 0 24 24">
        <path d="M7.493 18.75c-.425 0-.82-.236-.975-.632A7.48 7.48 0 016 15.375c0-1.75.599-3.358 1.602-4.634.151-.192.373-.309.6-.397.473-.183.89-.514 1.212-.924a9.042 9.042 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75 2.25 2.25 0 012.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558-.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H14.23c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23h-.777zM2.331 10.977a11.969 11.969 0 00-.831 4.398 12 12 0 00.52 3.507c.26.85 1.084 1.368 1.973 1.368H4.9c.445 0 .72-.498.523-.898a8.963 8.963 0 01-.924-3.977c0-1.708.476-3.305 1.302-4.666.245-.403-.028-.959-.5-.959H4.25c-.832 0-1.612.453-1.918 1.227z"/>
      </svg>
    </button>
    
    <!-- Vote Score -->
    <div 
      v-if="showCount"
      :class="[
        sizeClasses.count,
        'font-semibold text-center select-none',
        props.compact ? 'px-1 min-w-[1.5rem]' : 'px-2 min-w-[2rem]',
        totalScore > 0 ? 'text-blue-600' : totalScore < 0 ? 'text-red-600' : 'text-gray-600'
      ]"
    >
      {{ totalScore }}
    </div>
    
    <!-- Downvote/Dislike Button -->
    <button 
      @click.stop.prevent="handleVote(-1)"
      :class="[
        'flex items-center justify-center rounded-full transition-all duration-200',
        sizeClasses.button,
        hasDownvoted 
          ? 'text-white bg-red-500 shadow-md hover:bg-red-600' 
          : 'text-gray-500 hover:text-red-500 hover:bg-red-50'
      ]"
      :title="hasDownvoted ? 'Remove dislike' : 'Dislike'"
    >
      <svg :class="[sizeClasses.icon]" fill="currentColor" viewBox="0 0 24 24">
        <path d="M15.73 5.25h1.035A7.465 7.465 0 0118 9.375a7.465 7.465 0 01-1.235 4.125h-.148c-.806 0-1.534.446-2.031 1.08a9.04 9.04 0 01-2.861 2.4c-.723.384-1.35.956-1.653 1.715a4.498 4.498 0 00-.322 1.672V21a.75.75 0 01-.75.75 2.25 2.25 0 01-2.25-2.25c0-1.152.26-2.243.723-3.218C7.74 15.724 7.366 15 6.748 15H3.622c-1.026 0-1.945-.694-2.054-1.715A12.134 12.134 0 011.5 12c0-2.848.992-5.464 2.649-7.521.388-.482.987-.729 1.605-.729H9.77a4.5 4.5 0 011.423.23l3.114 1.04a4.5 4.5 0 001.423.23zM21.669 14.023c.536-1.362.831-2.845.831-4.398 0-1.22-.182-2.398-.52-3.507-.26-.85-1.084-1.368-1.973-1.368H19.1c-.445 0-.72.498-.523.898.591 1.2.924 2.55.924 3.977a8.958 8.958 0 01-1.302 4.666c-.245.403.028.959.5.959h1.053c.832 0 1.612-.453 1.918-1.227z"/>
      </svg>
    </button>
  </div>
</template>

<style scoped>
/* Pulse animation for active votes */
.pulse-glow {
  animation: pulse-glow 2s ease-in-out infinite;
}

@keyframes pulse-glow {
  0% { box-shadow: 0 0 5px rgba(59, 130, 246, 0.3); }
  50% { box-shadow: 0 0 15px rgba(59, 130, 246, 0.6), 0 0 25px rgba(59, 130, 246, 0.4); }
  100% { box-shadow: 0 0 5px rgba(59, 130, 246, 0.3); }
}

/* Button hover effects */
button {
  transform-origin: center;
}

button:active {
  transform: scale(0.95);
}

/* Smooth transitions */
button svg {
  transition: all 0.2s ease;
}
</style>