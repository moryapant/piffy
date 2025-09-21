<template>
  <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
    <!-- Editor Header -->
    <div class="mb-6 pb-4 border-b border-gray-100">
      <h2 class="text-xl font-semibold text-gray-800 mb-2">
        {{ isEditing ? 'Edit Post' : 'Create New Post' }}
      </h2>
      <p class="text-sm text-gray-600">
        Share your thoughts with the {{ subfappName || 'community' }}
      </p>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Community Selection (if creating new post) -->
      <div v-if="!isEditing && !subfappId" class="space-y-2">
        <label for="subfapp_select" class="block text-sm font-medium text-gray-700">
          Choose Community
        </label>
        <select
          id="subfapp_select"
          v-model="form.subfapp_id"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
          required
        >
          <option value="">Select a community...</option>
          <option 
            v-for="subfapp in availableSubfapps" 
            :key="subfapp.id" 
            :value="subfapp.id"
          >
            {{ subfapp.name }}
          </option>
        </select>
      </div>

      <!-- Post Title -->
      <div class="space-y-2">
        <label for="title" class="block text-sm font-medium text-gray-700">
          Post Title <span class="text-red-500">*</span>
        </label>
        <input
          id="title"
          v-model="form.title"
          type="text"
          placeholder="What's your post about?"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-lg"
          :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.title }"
          required
          maxlength="300"
        />
        <div class="flex justify-between items-center">
          <p v-if="errors.title" class="text-sm text-red-600">{{ errors.title }}</p>
          <p class="text-xs text-gray-500 ml-auto">{{ form.title?.length || 0 }}/300</p>
        </div>
      </div>

      <!-- Flair Selection -->
      <div v-if="form.subfapp_id || subfappId" class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
          Post Flair <span class="text-gray-400">(Optional)</span>
        </label>
        <PostFlair
          :show-selector="true"
          :available-flairs="availableFlairs"
          :selected-flair-id="form.flair_id"
          :subfapp-id="form.subfapp_id || subfappId"
          :can-manage-flairs="false"
          @select="handleFlairSelect"
        />
      </div>

      <!-- Post Type Tabs -->
      <div class="border-b border-gray-200">
        <nav class="flex space-x-8">
          <button
            type="button"
            @click="postType = 'text'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm transition-colors',
              postType === 'text'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <DocumentTextIcon class="w-4 h-4 inline-block mr-1" />
            Text
          </button>
          <button
            type="button"
            @click="postType = 'image'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm transition-colors',
              postType === 'image'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <PhotoIcon class="w-4 h-4 inline-block mr-1" />
            Image
          </button>
          <button
            type="button"
            @click="postType = 'link'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm transition-colors',
              postType === 'link'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <LinkIcon class="w-4 h-4 inline-block mr-1" />
            Link
          </button>
        </nav>
      </div>

      <!-- Text Content (for text and image posts) -->
      <div v-if="postType === 'text' || postType === 'image'" class="space-y-2">
        <label for="content" class="block text-sm font-medium text-gray-700">
          Content
        </label>
        
        <!-- Rich Text Editor Toolbar -->
        <div class="border border-gray-300 rounded-lg">
          <div class="flex flex-wrap items-center gap-1 p-2 bg-gray-50 border-b border-gray-200 rounded-t-lg">
            <button
              type="button"
              @click="toggleBold"
              :class="['p-2 rounded hover:bg-gray-200 transition-colors', { 'bg-gray-300': isBold }]"
              title="Bold"
            >
              <BoldIcon class="w-4 h-4" />
            </button>
            <button
              type="button"
              @click="toggleItalic"
              :class="['p-2 rounded hover:bg-gray-200 transition-colors', { 'bg-gray-300': isItalic }]"
              title="Italic"
            >
              <ItalicIcon class="w-4 h-4" />
            </button>
            <button
              type="button"
              @click="toggleUnderline"
              :class="['p-2 rounded hover:bg-gray-200 transition-colors', { 'bg-gray-300': isUnderline }]"
              title="Underline"
            >
              <UnderlineIcon class="w-4 h-4" />
            </button>
            <div class="w-px h-6 bg-gray-300 mx-1"></div>
            <button
              type="button"
              @click="insertList('ul')"
              class="p-2 rounded hover:bg-gray-200 transition-colors"
              title="Bullet List"
            >
              <ListBulletIcon class="w-4 h-4" />
            </button>
            <button
              type="button"
              @click="insertList('ol')"
              class="p-2 rounded hover:bg-gray-200 transition-colors"
              title="Numbered List"
            >
              <NumberedListIcon class="w-4 h-4" />
            </button>
            <div class="w-px h-6 bg-gray-300 mx-1"></div>
            <button
              type="button"
              @click="insertLink"
              class="p-2 rounded hover:bg-gray-200 transition-colors"
              title="Insert Link"
            >
              <LinkIcon class="w-4 h-4" />
            </button>
          </div>
          
          <div
            ref="contentEditor"
            contenteditable="true"
            @input="updateContent"
            @keydown="handleKeydown"
            class="min-h-[200px] max-h-[400px] overflow-y-auto p-4 focus:outline-none focus:ring-0"
            :class="{ 'border-red-300': errors.content }"
            placeholder="Write your post content here..."
            style="word-wrap: break-word;"
          ></div>
        </div>
        
        <p v-if="errors.content" class="text-sm text-red-600">{{ errors.content }}</p>
      </div>

      <!-- Link URL (for link posts) -->
      <div v-if="postType === 'link'" class="space-y-2">
        <label for="link_url" class="block text-sm font-medium text-gray-700">
          Link URL <span class="text-red-500">*</span>
        </label>
        <input
          id="link_url"
          v-model="form.link_url"
          type="url"
          placeholder="https://example.com"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
          :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.link_url }"
          required
        />
        <p v-if="errors.link_url" class="text-sm text-red-600">{{ errors.link_url }}</p>
      </div>

      <!-- Image Upload (for image posts) -->
      <div v-if="postType === 'image'" class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
          Upload Images
        </label>
        
        <div
          @drop="handleDrop"
          @dragover.prevent
          @dragenter.prevent
          class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors"
          :class="{ 'border-blue-400 bg-blue-50': isDragging }"
        >
          <input
            ref="imageInput"
            type="file"
            multiple
            accept="image/*"
            @change="handleImageUpload"
            class="hidden"
          />
          
          <PhotoIcon class="w-12 h-12 mx-auto text-gray-400 mb-2" />
          <p class="text-sm text-gray-600 mb-2">
            Drag and drop images here, or 
            <button
              type="button"
              @click="$refs.imageInput?.click()"
              class="text-blue-600 hover:text-blue-700 underline"
            >
              browse
            </button>
          </p>
          <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB each</p>
        </div>

        <!-- Image Previews -->
        <div v-if="imageFiles.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
          <div
            v-for="(image, index) in imageFiles"
            :key="index"
            class="relative group"
          >
            <img
              :src="image.preview"
              :alt="`Preview ${index + 1}`"
              class="w-full h-24 object-cover rounded-lg border border-gray-200"
            />
            <button
              type="button"
              @click="removeImage(index)"
              class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600"
            >
              <XMarkIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Post Options -->
      <div class="space-y-4">
        <div class="flex items-center">
          <input
            id="nsfw"
            v-model="form.nsfw"
            type="checkbox"
            class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
          />
          <label for="nsfw" class="ml-2 block text-sm text-gray-700">
            Mark as NSFW (Not Safe For Work)
          </label>
        </div>
        
        <div class="flex items-center">
          <input
            id="spoiler"
            v-model="form.spoiler"
            type="checkbox"
            class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
          />
          <label for="spoiler" class="ml-2 block text-sm text-gray-700">
            Mark as spoiler
          </label>
        </div>
      </div>

      <!-- Submit Buttons -->
      <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
        <button
          type="submit"
          :disabled="isSubmitting || !isFormValid"
          class="flex-1 px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <span v-if="isSubmitting" class="flex items-center justify-center">
            <LoadingSpinner class="w-5 h-5 mr-2" />
            {{ isEditing ? 'Updating...' : 'Publishing...' }}
          </span>
          <span v-else>
            {{ isEditing ? 'Update Post' : 'Publish Post' }}
          </span>
        </button>
        
        <button
          type="button"
          @click="$emit('cancel')"
          class="px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
        >
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  DocumentTextIcon,
  PhotoIcon,
  LinkIcon,
  XMarkIcon,
  ListBulletIcon,
} from '@heroicons/vue/24/outline'
import PostFlair from './PostFlair.vue'
// import FlairCreationModal from './FlairCreationModal.vue'
// Props
const props = defineProps({
  post: {
    type: Object,
    default: null
  },
  subfappId: {
    type: [String, Number],
    default: null
  },
  subfappName: {
    type: String,
    default: ''
  },
  availableSubfapps: {
    type: Array,
    default: () => []
  },
  availableFlairs: {
    type: Array,
    default: () => []
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

// Emits
const emit = defineEmits(['submit', 'cancel'])

// Refs
const contentEditor = ref(null)
const imageInput = ref(null)

// State
const postType = ref('text')
const isDragging = ref(false)
const isSubmitting = ref(false)
const imageFiles = ref([])

// Form data
const form = reactive({
  title: '',
  content: '',
  subfapp_id: props.subfappId || '',
  flair_id: null,
  link_url: '',
  nsfw: false,
  spoiler: false,
})

// Editor state
const isBold = ref(false)
const isItalic = ref(false)
const isUnderline = ref(false)

// Computed
const isEditing = computed(() => !!props.post)
const isFormValid = computed(() => {
  if (!form.title.trim()) return false
  if (postType.value === 'link' && !form.link_url.trim()) return false
  if (!form.subfapp_id && !props.subfappId) return false
  return true
})

// Icons for toolbar (simple SVG paths)
const BoldIcon = { template: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"/></svg>' }
const ItalicIcon = { template: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"/></svg>' }
const UnderlineIcon = { template: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 17c3.31 0 6-2.69 6-6V3h-2.5v8c0 1.93-1.57 3.5-3.5 3.5S8.5 12.93 8.5 11V3H6v8c0 3.31 2.69 6 6 6zm-7 2v2h14v-2H5z"/></svg>' }
const NumberedListIcon = { template: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M2 17h2v.5H3v1h1v.5H2v1h3v-4H2v1zm1-9h1V4H2v1h1v3zm-1 3h1.8L2 13.1v.9h3v-1H3.2L5 10.9V10H2v1zm5-6v2h14V5H7zm0 14h14v-2H7v2zm0-6h14v-2H7v2z"/></svg>' }
const LoadingSpinner = { template: '<svg class="animate-spin" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>' }

// Initialize form if editing
onMounted(() => {
  if (isEditing.value && props.post) {
    form.title = props.post.title || ''
    form.content = props.post.content || ''
    form.subfapp_id = props.post.subfapp_id || props.subfappId || ''
    form.flair_id = props.post.flair_id || null
    form.link_url = props.post.link_url || ''
    form.nsfw = props.post.nsfw || false
    form.spoiler = props.post.spoiler || false
    
    // Set post type based on existing post
    if (props.post.link_url) {
      postType.value = 'link'
    } else if (props.post.images?.length > 0) {
      postType.value = 'image'
    } else {
      postType.value = 'text'
    }
    
    // Set content in editor
    nextTick(() => {
      if (contentEditor.value) {
        contentEditor.value.innerHTML = form.content
      }
    })
  }
})

// Editor methods
const updateContent = () => {
  if (contentEditor.value) {
    form.content = contentEditor.value.innerHTML
  }
}

const toggleBold = () => {
  document.execCommand('bold')
  isBold.value = document.queryCommandState('bold')
}

const toggleItalic = () => {
  document.execCommand('italic')
  isItalic.value = document.queryCommandState('italic')
}

const toggleUnderline = () => {
  document.execCommand('underline')
  isUnderline.value = document.queryCommandState('underline')
}

const insertList = (type) => {
  const command = type === 'ul' ? 'insertUnorderedList' : 'insertOrderedList'
  document.execCommand(command)
}

const insertLink = () => {
  const url = prompt('Enter the link URL:')
  if (url) {
    document.execCommand('createLink', false, url)
  }
}

const handleKeydown = (event) => {
  // Handle keyboard shortcuts
  if (event.ctrlKey || event.metaKey) {
    switch (event.key.toLowerCase()) {
      case 'b':
        event.preventDefault()
        toggleBold()
        break
      case 'i':
        event.preventDefault()
        toggleItalic()
        break
      case 'u':
        event.preventDefault()
        toggleUnderline()
        break
    }
  }
}

// Image handling methods
const handleImageUpload = (event) => {
  const files = Array.from(event.target.files || [])
  processImageFiles(files)
}

const handleDrop = (event) => {
  event.preventDefault()
  isDragging.value = false
  const files = Array.from(event.dataTransfer.files || [])
  processImageFiles(files.filter(file => file.type.startsWith('image/')))
}

const processImageFiles = (files) => {
  files.forEach(file => {
    if (file.size > 10 * 1024 * 1024) { // 10MB limit
      alert(`File ${file.name} is too large. Maximum size is 10MB.`)
      return
    }
    
    const reader = new FileReader()
    reader.onload = (e) => {
      imageFiles.value.push({
        file,
        preview: e.target.result,
        name: file.name
      })
    }
    reader.readAsDataURL(file)
  })
}

const removeImage = (index) => {
  imageFiles.value.splice(index, 1)
}

// Flair handling
const handleFlairSelect = (flairId) => {
  form.flair_id = flairId
}

// Form submission
const handleSubmit = async () => {
  if (!isFormValid.value || isSubmitting.value) return
  
  isSubmitting.value = true
  
  try {
    const formData = new FormData()
    
    // Add form fields
    Object.keys(form).forEach(key => {
      if (form[key] !== null && form[key] !== undefined) {
        formData.append(key, form[key])
      }
    })
    
    // Add post type
    formData.append('post_type', postType.value)
    
    // Add images if any
    imageFiles.value.forEach((imageFile, index) => {
      formData.append(`images[${index}]`, imageFile.file)
    })
    
    emit('submit', formData)
    
  } catch (error) {
    console.error('Error submitting form:', error)
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
/* Rich text editor styles */
[contenteditable]:empty:before {
  content: attr(placeholder);
  color: #9ca3af;
  pointer-events: none;
}

[contenteditable] {
  outline: none;
}

[contenteditable] p {
  margin: 0 0 1rem 0;
}

[contenteditable] p:last-child {
  margin-bottom: 0;
}

[contenteditable] ul,
[contenteditable] ol {
  margin: 1rem 0;
  padding-left: 2rem;
}

[contenteditable] li {
  margin: 0.5rem 0;
}

[contenteditable] a {
  color: #3b82f6;
  text-decoration: underline;
}

[contenteditable] strong {
  font-weight: 600;
}

[contenteditable] em {
  font-style: italic;
}

[contenteditable] u {
  text-decoration: underline;
}

/* Toolbar button active states */
.bg-gray-300 {
  background-color: #d1d5db;
}

/* Mobile improvements */
@media (max-width: 640px) {
  .grid-cols-2 {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Animation for active scale */
.active\:scale-95:active {
  transform: scale(0.95);
}

/* Drag and drop visual feedback */
.border-blue-400 {
  border-color: #60a5fa;
}

.bg-blue-50 {
  background-color: #eff6ff;
}
</style>