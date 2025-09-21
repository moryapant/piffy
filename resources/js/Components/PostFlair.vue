<template>
  <div v-if="flair || showSelector" class="inline-flex items-center">
    <!-- Display Mode: Show existing flair -->
    <span
      v-if="flair && !showSelector"
      :class="[
        'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border transition-colors',
        'cursor-pointer hover:opacity-80',
        flairClasses
      ]"
      :style="flairStyles"
      :title="flair.description"
      @click="handleFlairClick"
    >
      <span class="flex-shrink-0">{{ flair.name }}</span>
      <XMarkIcon v-if="removable" class="ml-1 w-3 h-3 hover:text-red-600" @click.stop="$emit('remove')" />
    </span>

    <!-- Selector Mode: Dropdown for selecting flair -->
    <div v-if="showSelector" class="relative inline-block">
      <button
        ref="selectorButton"
        @click="toggleDropdown"
        :class="[
          'inline-flex items-center px-3 py-1.5 border rounded-md text-sm font-medium transition-colors',
          'hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
          selectedFlair
            ? 'border-blue-300 text-blue-700 bg-blue-50'
            : 'border-gray-300 text-gray-700 bg-white'
        ]"
        :style="selectedFlair ? getFlairStyles(selectedFlair) : null"
      >
        <TagIcon class="w-4 h-4 mr-2" />
        <span>{{ selectedFlair ? selectedFlair.name : 'Add Flair' }}</span>
        <ChevronDownIcon class="w-4 h-4 ml-2" />
      </button>

      <!-- Dropdown Menu -->
      <div
        v-if="isDropdownOpen"
        ref="dropdown"
        class="absolute z-50 mt-1 w-64 bg-white rounded-md shadow-lg border border-gray-200 max-h-80 overflow-y-auto"
        :class="dropdownPosition"
      >
        <!-- Search Input -->
        <div class="p-2 border-b border-gray-100">
          <input
            ref="searchInput"
            v-model="searchQuery"
            type="text"
            placeholder="Search flairs..."
            class="w-full px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
            @keydown.escape="closeDropdown"
          />
        </div>

        <!-- Flair Options -->
        <div class="py-1">
          <!-- No Flair Option -->
          <button
            @click="selectFlair(null)"
            class="flex items-center w-full px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
            :class="{ 'bg-blue-50 text-blue-700': !selectedFlair }"
          >
            <XMarkIcon class="w-4 h-4 mr-2 text-gray-400" />
            <span>No Flair</span>
          </button>

          <!-- Available Flairs -->
          <button
            v-for="availableFlair in filteredFlairs"
            :key="availableFlair.id"
            @click="selectFlair(availableFlair)"
            class="flex items-center w-full px-3 py-2 text-sm hover:bg-gray-100 transition-colors group"
            :class="{ 'bg-blue-50': selectedFlair?.id === availableFlair.id }"
          >
            <span
              :class="[
                'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border mr-3 flex-shrink-0'
              ]"
              :style="getFlairStyles(availableFlair)"
            >
              {{ availableFlair.name }}
            </span>
            <div class="flex-1 text-left">
              <div class="font-medium text-gray-900 group-hover:text-gray-700">
                {{ availableFlair.name }}
              </div>
              <div v-if="availableFlair.description" class="text-gray-500 text-xs truncate">
                {{ availableFlair.description }}
              </div>
            </div>
          </button>

          <!-- No Results -->
          <div v-if="filteredFlairs.length === 0 && searchQuery" class="px-3 py-2 text-sm text-gray-500 text-center">
            No flairs found matching "{{ searchQuery }}"
          </div>

          <!-- No Flairs Available -->
          <div v-if="availableFlairs.length === 0" class="px-3 py-2 text-sm text-gray-500 text-center">
            No flairs available for this community
          </div>
        </div>

        <!-- Create New Flair (Admin only) -->
        <div v-if="canManageFlairs" class="border-t border-gray-100 p-2">
          <button
            @click="showCreateForm = true"
            class="flex items-center w-full px-2 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded transition-colors"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            <span>Create New Flair</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Create Flair Modal -->
    <!-- TODO: Implement FlairCreationModal component -->
    <div v-if="showCreateForm" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg">
        <h3 class="text-lg font-medium mb-4">Create New Flair</h3>
        <p class="text-sm text-gray-600 mb-4">Flair creation modal will be implemented here.</p>
        <button @click="showCreateForm = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  XMarkIcon,
  TagIcon,
  ChevronDownIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'
// import FlairCreationModal from './FlairCreationModal.vue'

// Props
const props = defineProps({
  flair: {
    type: Object,
    default: null
  },
  availableFlairs: {
    type: Array,
    default: () => []
  },
  selectedFlairId: {
    type: [Number, String],
    default: null
  },
  subfappId: {
    type: [Number, String],
    default: null
  },
  showSelector: {
    type: Boolean,
    default: false
  },
  removable: {
    type: Boolean,
    default: false
  },
  canManageFlairs: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'sm', // 'xs', 'sm', 'md', 'lg'
    validator: (value) => ['xs', 'sm', 'md', 'lg'].includes(value)
  }
})

// Emits
const emit = defineEmits(['select', 'remove', 'click'])

// Refs
const selectorButton = ref(null)
const dropdown = ref(null)
const searchInput = ref(null)

// State
const isDropdownOpen = ref(false)
const searchQuery = ref('')
const showCreateForm = ref(false)
const dropdownPosition = ref('left-0')

// Computed
const selectedFlair = computed(() => {
  if (props.selectedFlairId) {
    return props.availableFlairs.find(f => f.id == props.selectedFlairId)
  }
  return null
})

const filteredFlairs = computed(() => {
  if (!searchQuery.value) return props.availableFlairs
  
  const query = searchQuery.value.toLowerCase()
  return props.availableFlairs.filter(flair =>
    flair.name.toLowerCase().includes(query) ||
    (flair.description && flair.description.toLowerCase().includes(query))
  )
})

const flairClasses = computed(() => {
  const sizeClasses = {
    xs: 'px-1.5 py-0.5 text-xs',
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-1 text-sm',
    lg: 'px-3 py-1.5 text-base'
  }
  return sizeClasses[props.size] || sizeClasses.sm
})

const flairStyles = computed(() => {
  if (!props.flair) return {}
  return getFlairStyles(props.flair)
})

// Methods
const getFlairStyles = (flair) => {
  if (!flair) return {}
  
  return {
    color: flair.color || '#374151',
    backgroundColor: flair.background_color || '#f3f4f6',
    borderColor: flair.color || '#d1d5db'
  }
}

const toggleDropdown = () => {
  if (isDropdownOpen.value) {
    closeDropdown()
  } else {
    openDropdown()
  }
}

const openDropdown = async () => {
  isDropdownOpen.value = true
  await nextTick()
  
  // Position dropdown
  if (selectorButton.value && dropdown.value) {
    const rect = selectorButton.value.getBoundingClientRect()
    const viewportWidth = window.innerWidth
    
    // Check if dropdown would overflow on the right
    if (rect.left + 256 > viewportWidth) { // 256px is dropdown width (w-64)
      dropdownPosition.value = 'right-0'
    } else {
      dropdownPosition.value = 'left-0'
    }
  }
  
  // Focus search input
  if (searchInput.value) {
    searchInput.value.focus()
  }
}

const closeDropdown = () => {
  isDropdownOpen.value = false
  searchQuery.value = ''
}

const selectFlair = (flair) => {
  emit('select', flair?.id || null)
  closeDropdown()
}

const handleFlairClick = () => {
  if (props.flair) {
    emit('click', props.flair)
  }
}

const handleFlairCreated = (newFlair) => {
  showCreateForm.value = false
  // The parent component should handle updating availableFlairs
  emit('select', newFlair.id)
}

const handleClickOutside = (event) => {
  if (
    isDropdownOpen.value &&
    selectorButton.value &&
    dropdown.value &&
    !selectorButton.value.contains(event.target) &&
    !dropdown.value.contains(event.target)
  ) {
    closeDropdown()
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Watch for escape key
watch(isDropdownOpen, (newValue) => {
  if (newValue) {
    const handleEscape = (event) => {
      if (event.key === 'Escape') {
        closeDropdown()
        document.removeEventListener('keydown', handleEscape)
      }
    }
    document.addEventListener('keydown', handleEscape)
  }
})
</script>

<style scoped>
/* Custom scrollbar for dropdown */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Animation for dropdown */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* Focus styles */
button:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Mobile optimizations */
@media (max-width: 640px) {
  .w-64 {
    width: calc(100vw - 2rem);
    max-width: 16rem;
  }
}
</style>