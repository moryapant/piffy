<template>
  <div>
    <!-- Render existing items -->
    <slot :items="items" :loading="loading" />
    
    <!-- Load more trigger -->
    <div
      ref="loadMoreTrigger"
      class="flex items-center justify-center py-8"
    >
      <div v-if="loading" class="flex items-center space-x-2">
        <div class="animate-spin w-6 h-6 border-2 border-orange-500 border-t-transparent rounded-full"></div>
        <span class="text-sm text-gray-600">Loading more posts...</span>
      </div>
      <div v-else-if="hasMorePages && !loading" class="text-center">
        <button
          @click="loadMore"
          class="px-6 py-3 bg-orange-500 text-white rounded-full hover:bg-orange-600 transition-colors duration-200 font-medium"
        >
          Load More Posts
        </button>
      </div>
      <div v-else-if="!hasMorePages && items.length > 0" class="text-center">
        <div class="flex items-center justify-center space-x-2 text-gray-500">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span class="text-sm">You've reached the end!</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  // Initial data from server
  initialData: {
    type: Object,
    required: true,
  },
  // URL to load more data from
  loadMoreUrl: {
    type: String,
    required: true,
  },
  // Auto-load when element comes into view
  autoLoad: {
    type: Boolean,
    default: false,
  },
  // Threshold for intersection observer
  threshold: {
    type: Number,
    default: 0.1,
  },
})

const emit = defineEmits(['load-more', 'items-updated'])

// Component state
const items = ref([...props.initialData.data])
const loading = ref(false)
const currentPage = ref(props.initialData.current_page || 1)
const lastPage = ref(props.initialData.last_page || 1)
const loadMoreTrigger = ref(null)
const observer = ref(null)

// Computed properties
const hasMorePages = computed(() => currentPage.value < lastPage.value)

// Methods
const loadMore = async () => {
  if (loading.value || !hasMorePages.value) return

  try {
    loading.value = true
    
    const nextPage = currentPage.value + 1
    const url = new URL(window.location.origin + props.loadMoreUrl)
    url.searchParams.set('page', nextPage)
    
    // Preserve existing query parameters
    const currentParams = new URLSearchParams(window.location.search)
    for (const [key, value] of currentParams) {
      if (key !== 'page') {
        url.searchParams.set(key, value)
      }
    }

    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()
    
    if (data.data && Array.isArray(data.data)) {
      // Append new items to existing items
      const newItems = data.data
      items.value = [...items.value, ...newItems]
      
      // Update pagination info
      currentPage.value = data.current_page || nextPage
      lastPage.value = data.last_page || lastPage.value
      
      emit('load-more', { newItems, totalItems: items.value })
      emit('items-updated', items.value)
    }
  } catch (error) {
    console.error('Failed to load more items:', error)
    
    // Show user-friendly error message
    const errorMessage = document.createElement('div')
    errorMessage.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg z-50'
    errorMessage.innerHTML = `
      <div class="flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>Failed to load more content. Please try again.</span>
      </div>
    `
    document.body.appendChild(errorMessage)
    
    // Remove error message after 5 seconds
    setTimeout(() => {
      if (document.body.contains(errorMessage)) {
        document.body.removeChild(errorMessage)
      }
    }, 5000)
  } finally {
    loading.value = false
  }
}

// Set up intersection observer for auto-loading
const setupIntersectionObserver = () => {
  if (!props.autoLoad || !loadMoreTrigger.value) return

  observer.value = new IntersectionObserver(
    (entries) => {
      const entry = entries[0]
      if (entry.isIntersecting && hasMorePages.value && !loading.value) {
        loadMore()
      }
    },
    { threshold: props.threshold }
  )

  observer.value.observe(loadMoreTrigger.value)
}

// Clean up intersection observer
const cleanupIntersectionObserver = () => {
  if (observer.value) {
    observer.value.disconnect()
    observer.value = null
  }
}

// Handle window resize for responsive behavior
const handleResize = () => {
  // Re-setup intersection observer if needed
  if (props.autoLoad) {
    cleanupIntersectionObserver()
    setTimeout(setupIntersectionObserver, 100)
  }
}

// Lifecycle hooks
onMounted(() => {
  if (props.autoLoad) {
    setupIntersectionObserver()
  }
  
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  cleanupIntersectionObserver()
  window.removeEventListener('resize', handleResize)
})

// Expose methods to parent component
defineExpose({
  loadMore,
  items: items,
  loading: loading,
  hasMorePages: hasMorePages,
  reset: () => {
    items.value = [...props.initialData.data]
    currentPage.value = props.initialData.current_page || 1
    lastPage.value = props.initialData.last_page || 1
  }
})
</script>

<style scoped>
/* Loading spinner animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Smooth transitions */
.transition-colors {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Responsive button styling */
@media (max-width: 640px) {
  button {
    font-size: 0.875rem;
    padding: 0.75rem 1.25rem;
  }
}
</style>