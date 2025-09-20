<template>
  <div>
    <div v-if="!editorLoaded" class="p-4 border border-gray-300 rounded-md">
      <div class="flex items-center justify-center space-x-2">
        <div class="w-4 h-4 bg-gray-300 rounded-full animate-pulse"></div>
        <span class="text-gray-600">Loading editor...</span>
      </div>
    </div>
    <component
      v-else
      :is="RichTextEditor"
      v-model="modelValue"
      v-bind="$attrs"
      @update:modelValue="$emit('update:modelValue', $event)"
    />
  </div>
</template>

<script setup>
import { ref, defineAsyncComponent, onMounted } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])

const editorLoaded = ref(false)

// Lazy load the RichTextEditor component
const RichTextEditor = defineAsyncComponent({
  loader: () => import('./RichTextEditor.vue'),
  loadingComponent: null,
  errorComponent: null,
  delay: 200,
  timeout: 5000,
})

onMounted(async () => {
  // Add a small delay to ensure smooth UX
  await new Promise(resolve => setTimeout(resolve, 100))
  editorLoaded.value = true
})
</script>