<script setup>
import { ref, computed, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  post: {
    type: Object,
    required: true
  },
  showLabels: {
    type: Boolean,
    default: true
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  orientation: {
    type: String,
    default: 'horizontal', // horizontal, vertical
    validator: (value) => ['horizontal', 'vertical'].includes(value)
  }
})

const page = usePage()
const showDropdown = ref(false)
const dropdownRef = ref(null)
const buttonRef = ref(null)

// Computed properties for post details
const postUrl = computed(() => {
  if (typeof window !== 'undefined') {
    return window.location.href
  }
  return `${page.props.app?.url || 'http://localhost'}/posts/${props.post.id}`
})

const postTitle = computed(() => props.post.title || 'Check out this post')
const postDescription = computed(() => {
  const content = props.post.content || props.post.excerpt || ''
  // Strip HTML and limit to 200 characters
  return content.replace(/<[^>]*>/g, '').substring(0, 200)
})

const postImage = computed(() => {
  if (props.post.images && props.post.images.length > 0) {
    const imagePath = props.post.images[0].image_path
    return `${page.props.app?.url || 'http://localhost'}/storage/${imagePath}`
  }
  return null
})

// Size classes
const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return {
        button: 'px-3 py-1.5',
        icon: 'w-4 h-4',
        text: 'text-xs',
        gap: 'gap-1'
      }
    case 'lg':
      return {
        button: 'px-4 py-3',
        icon: 'w-6 h-6',
        text: 'text-sm',
        gap: 'gap-3'
      }
    default: // md
      return {
        button: 'px-3 py-2 sm:py-1.5 md:px-4 md:py-2 lg:px-4 lg:py-2',
        icon: 'w-5 h-5 md:w-6 md:h-6 lg:w-6 lg:h-6',
        text: 'text-xs md:text-sm lg:text-sm',
        gap: 'gap-2'
      }
  }
})

// Share functions
const shareOnFacebook = () => {
  const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(postUrl.value)}`
  openShareWindow(url, 'Facebook')
}

const shareOnTwitter = () => {
  const text = `${postTitle.value}\n\n${postDescription.value}`
  const url = `https://twitter.com/intent/tweet?url=${encodeURIComponent(postUrl.value)}&text=${encodeURIComponent(text)}`
  openShareWindow(url, 'Twitter')
}

const shareOnLinkedIn = () => {
  const url = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(postUrl.value)}&title=${encodeURIComponent(postTitle.value)}&summary=${encodeURIComponent(postDescription.value)}`
  openShareWindow(url, 'LinkedIn')
}

const shareOnReddit = () => {
  const url = `https://reddit.com/submit?url=${encodeURIComponent(postUrl.value)}&title=${encodeURIComponent(postTitle.value)}`
  openShareWindow(url, 'Reddit')
}

const copyToClipboard = async () => {
  try {
    if (navigator.clipboard) {
      await navigator.clipboard.writeText(postUrl.value)
    } else {
      // Fallback for older browsers
      const textArea = document.createElement('textarea')
      textArea.value = postUrl.value
      document.body.appendChild(textArea)
      textArea.select()
      document.execCommand('copy')
      document.body.removeChild(textArea)
    }
    // You could add a toast notification here
    showDropdown.value = false
  } catch (error) {
    // Handle errors silently
  }
}

const shareNatively = async () => {
  const shareData = {
    title: postTitle.value,
    text: postDescription.value,
    url: postUrl.value
  }

  try {
    if (navigator.share && navigator.canShare && navigator.canShare(shareData)) {
      await navigator.share(shareData)
      showDropdown.value = false
    } else {
      // Fallback to copy link
      await copyToClipboard()
    }
  } catch (error) {
    // If sharing fails, try copying link
    await copyToClipboard()
  }
}

const openShareWindow = (url, platform) => {
  const width = 600
  const height = 400
  const left = Math.round((screen.width / 2) - (width / 2))
  const top = Math.round((screen.height / 2) - (height / 2))
  
  window.open(
    url, 
    `share-${platform}`,
    `width=${width},height=${height},left=${left},top=${top},menubar=no,toolbar=no,location=no,status=no`
  )
  showDropdown.value = false
}

// Toggle dropdown with position calculation
const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
  if (showDropdown.value) {
    // Force a recomputation of the position
    nextTick(() => {
      // Position is automatically calculated via computed property
    })
  }
}

// Calculate dropdown position
const dropdownPosition = computed(() => {
  if (!showDropdown.value || !buttonRef.value) return {}
  
  const buttonRect = buttonRef.value.getBoundingClientRect()
  const viewportHeight = window.innerHeight
  const viewportWidth = window.innerWidth
  
  // Default positioning below the button
  let top = buttonRect.bottom + 8
  let left = buttonRect.left
  
  // Check if dropdown would go off the bottom of the screen
  const dropdownHeight = 400 // approximate height
  if (top + dropdownHeight > viewportHeight) {
    // Position above the button instead
    top = buttonRect.top - dropdownHeight - 8
  }
  
  // Check if dropdown would go off the right side
  const dropdownWidth = 280
  if (left + dropdownWidth > viewportWidth) {
    left = viewportWidth - dropdownWidth - 16
  }
  
  // Check if dropdown would go off the left side
  if (left < 16) {
    left = 16
  }
  
  return {
    top: `${Math.max(top, 16)}px`,
    left: `${left}px`
  }
})

// Close dropdown when clicking outside
const closeDropdown = () => {
  showDropdown.value = false
}
</script>

<template>
  <div class="relative inline-block" @click.stop>
    <!-- Main Share Button -->
    <button
      ref="buttonRef"
      @click="toggleDropdown"
      :class="[
        'inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-600 focus:ring-offset-1 active:scale-95 touch-manipulation min-h-[44px] sm:min-h-[36px]',
        sizeClasses.button,
        sizeClasses.gap
      ]"
      :title="'Share this post'"
    >
      <svg :class="[sizeClasses.icon]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
      </svg>
      <span v-if="showLabels" :class="['font-medium whitespace-nowrap', sizeClasses.text]">
        Share
      </span>
    </button>

    <!-- Share Options Dropdown -->
    <Teleport to="body">
      <div v-if="showDropdown" class="fixed inset-0 z-[100]" @click="closeDropdown">
        <div class="absolute inset-0 bg-black/10"></div>
      </div>
    </Teleport>
    
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <div
          v-if="showDropdown"
          ref="dropdownRef"
          :class="[
            'fixed z-[101] bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden min-w-[280px]',
            'max-w-sm'
          ]"
          :style="dropdownPosition"
        >
        <!-- Native Share (Mobile) -->
        <button
          v-if="typeof navigator !== 'undefined' && navigator.share"
          @click="shareNatively"
          class="w-full flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left group"
        >
          <div class="flex items-center justify-center w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg mr-3 group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
          </div>
          <div>
            <p class="font-medium text-gray-900 dark:text-white">Share</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Use device share menu</p>
          </div>
        </button>

        <!-- Facebook -->
        <button
          @click="shareOnFacebook"
          class="w-full flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left group"
        >
          <div class="flex items-center justify-center w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg mr-3 group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
          </div>
          <div>
            <p class="font-medium text-gray-900 dark:text-white">Facebook</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Share to Facebook</p>
          </div>
        </button>

        <!-- X (Twitter) -->
        <button
          @click="shareOnTwitter"
          class="w-full flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left group"
        >
          <div class="flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg mr-3 group-hover:bg-gray-200 dark:group-hover:bg-gray-600">
            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="currentColor" viewBox="0 0 24 24">
              <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
            </svg>
          </div>
          <div>
            <p class="font-medium text-gray-900 dark:text-white">X (Twitter)</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Post to X</p>
          </div>
        </button>

        <!-- LinkedIn -->
        <button
          @click="shareOnLinkedIn"
          class="w-full flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left group"
        >
          <div class="flex items-center justify-center w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg mr-3 group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50">
            <svg class="w-5 h-5 text-blue-700 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
              <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
            </svg>
          </div>
          <div>
            <p class="font-medium text-gray-900 dark:text-white">LinkedIn</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Share on LinkedIn</p>
          </div>
        </button>

        <!-- Reddit -->
        <button
          @click="shareOnReddit"
          class="w-full flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left group"
        >
          <div class="flex items-center justify-center w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg mr-3 group-hover:bg-orange-200 dark:group-hover:bg-orange-900/50">
            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-7.004 4.87-3.874 0-7.004-2.176-7.004-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 0 1 4.028 12c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 0 1 .14-.197.35.35 0 0 1 .238-.042l2.906.617a1.214 1.214 0 0 1 1.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 0 0-.231.094.33.33 0 0 0 0 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.056 2.961-.913a.361.361 0 0 0 .029-.463.33.33 0 0 0-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 0 0-.232-.095z"/>
            </svg>
          </div>
          <div>
            <p class="font-medium text-gray-900 dark:text-white">Reddit</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Post to Reddit</p>
          </div>
        </button>

        <!-- Copy Link -->
        <button
          @click="copyToClipboard"
          class="w-full flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left group border-t border-gray-100 dark:border-gray-700"
        >
          <div class="flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg mr-3 group-hover:bg-gray-200 dark:group-hover:bg-gray-600">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <p class="font-medium text-gray-900 dark:text-white">Copy Link</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Copy post URL</p>
          </div>
        </button>
      </div>
      </Transition>
    </Teleport>
  </div>
</template>
