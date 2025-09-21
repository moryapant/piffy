<template>
  <Head :title="`Manage Flairs - ${subfapp.name}`" />

  <MainLayout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
              Manage Flairs
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
              Customize post flairs for f/{{ subfapp.name }}
            </p>
          </div>
          <Link
            :href="route('subfapps.show', subfapp)"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Back to Community
          </Link>
        </div>
      </div>

      <!-- Create New Flair -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          Create New Flair
        </h2>
        
        <form @submit.prevent="createFlair" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Flair Name <span class="text-red-500">*</span>
            </label>
            <input
              id="name"
              v-model="newFlair.name"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              placeholder="e.g., Discussion, Question"
              maxlength="50"
              required
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
          </div>

          <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Description
            </label>
            <input
              id="description"
              v-model="newFlair.description"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              placeholder="Brief description of the flair"
              maxlength="255"
            />
          </div>

          <div>
            <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Text Color <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center space-x-2">
              <input
                id="color"
                v-model="newFlair.color"
                type="color"
                class="w-12 h-10 border border-gray-300 rounded-md"
                required
              />
              <input
                v-model="newFlair.color"
                type="text"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="#000000"
                pattern="^#[0-9A-Fa-f]{6}$"
                required
              />
            </div>
            <p v-if="form.errors.color" class="mt-1 text-sm text-red-500">{{ form.errors.color }}</p>
          </div>

          <div>
            <label for="background_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Background Color <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center space-x-2">
              <input
                id="background_color"
                v-model="newFlair.background_color"
                type="color"
                class="w-12 h-10 border border-gray-300 rounded-md"
                required
              />
              <input
                v-model="newFlair.background_color"
                type="text"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="#ffffff"
                pattern="^#[0-9A-Fa-f]{6}$"
                required
              />
            </div>
            <p v-if="form.errors.background_color" class="mt-1 text-sm text-red-500">{{ form.errors.background_color }}</p>
          </div>

          <div class="md:col-span-2">
            <!-- Preview -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Preview
              </label>
              <span
                v-if="newFlair.name"
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border"
                :style="{
                  color: newFlair.color,
                  backgroundColor: newFlair.background_color,
                  borderColor: newFlair.color
                }"
              >
                {{ newFlair.name }}
              </span>
              <span v-else class="text-gray-500 text-sm">Enter a flair name to see preview</span>
            </div>

            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="form.processing || !newFlair.name.trim()"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <PlusIcon v-if="!form.processing" class="w-4 h-4 mr-2" />
                <span v-if="form.processing" class="w-4 h-4 mr-2 animate-spin">⟳</span>
                {{ form.processing ? 'Creating...' : 'Create Flair' }}
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Existing Flairs -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          Existing Flairs ({{ flairs.length }})
        </h2>

        <div v-if="flairs.length === 0" class="text-center py-8">
          <TagIcon class="w-12 h-12 mx-auto text-gray-400 mb-4" />
          <p class="text-gray-500 dark:text-gray-400">
            No flairs created yet. Create your first flair above!
          </p>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="flair in flairs"
            :key="flair.id"
            class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg"
          >
            <div class="flex items-center space-x-4">
              <!-- Flair Preview -->
              <span
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border"
                :style="{
                  color: flair.color,
                  backgroundColor: flair.background_color,
                  borderColor: flair.color
                }"
              >
                {{ flair.name }}
              </span>
              
              <!-- Flair Info -->
              <div>
                <p class="font-medium text-gray-900 dark:text-white">{{ flair.name }}</p>
                <p v-if="flair.description" class="text-sm text-gray-500 dark:text-gray-400">
                  {{ flair.description }}
                </p>
                <div class="flex items-center space-x-4 mt-1">
                  <span class="text-xs text-gray-500">
                    Text: {{ flair.color }}
                  </span>
                  <span class="text-xs text-gray-500">
                    Background: {{ flair.background_color }}
                  </span>
                  <span
                    class="text-xs px-2 py-1 rounded"
                    :class="flair.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  >
                    {{ flair.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center space-x-2">
              <button
                @click="startEditing(flair)"
                class="p-2 text-gray-400 hover:text-blue-600 rounded-md hover:bg-gray-100"
                title="Edit flair"
              >
                <PencilIcon class="w-4 h-4" />
              </button>
              
              <button
                @click="toggleActive(flair)"
                class="p-2 rounded-md hover:bg-gray-100"
                :class="flair.is_active ? 'text-gray-400 hover:text-orange-600' : 'text-gray-400 hover:text-green-600'"
                :title="flair.is_active ? 'Deactivate flair' : 'Activate flair'"
              >
                <component :is="flair.is_active ? EyeSlashIcon : EyeIcon" class="w-4 h-4" />
              </button>
              
              <button
                @click="deleteFlair(flair)"
                class="p-2 text-gray-400 hover:text-red-600 rounded-md hover:bg-gray-100"
                title="Delete flair"
              >
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Modal -->
      <div
        v-if="editingFlair"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        @click.self="cancelEdit"
      >
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
          <h3 class="text-lg font-bold text-gray-900 mb-4">Edit Flair</h3>
          
          <form @submit.prevent="updateFlair" class="space-y-4">
            <div>
              <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">
                Flair Name <span class="text-red-500">*</span>
              </label>
              <input
                id="edit_name"
                v-model="editForm.name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                maxlength="50"
                required
              />
            </div>

            <div>
              <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-1">
                Description
              </label>
              <input
                id="edit_description"
                v-model="editForm.description"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                maxlength="255"
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="edit_color" class="block text-sm font-medium text-gray-700 mb-1">
                  Text Color
                </label>
                <input
                  id="edit_color"
                  v-model="editForm.color"
                  type="color"
                  class="w-full h-10 border border-gray-300 rounded-md"
                />
              </div>

              <div>
                <label for="edit_background_color" class="block text-sm font-medium text-gray-700 mb-1">
                  Background
                </label>
                <input
                  id="edit_background_color"
                  v-model="editForm.background_color"
                  type="color"
                  class="w-full h-10 border border-gray-300 rounded-md"
                />
              </div>
            </div>

            <!-- Preview -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
              <span
                v-if="editForm.name"
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border"
                :style="{
                  color: editForm.color,
                  backgroundColor: editForm.background_color,
                  borderColor: editForm.color
                }"
              >
                {{ editForm.name }}
              </span>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="cancelEdit"
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="editFormProcessing"
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
              >
                {{ editFormProcessing ? 'Updating...' : 'Update Flair' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import {
  PlusIcon,
  PencilIcon,
  TrashIcon,
  TagIcon,
  EyeIcon,
  EyeSlashIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  subfapp: {
    type: Object,
    required: true
  },
  flairs: {
    type: Array,
    required: true
  }
})

// Create flair form
const newFlair = reactive({
  name: '',
  description: '',
  color: '#3b82f6',
  background_color: '#dbeafe'
})

const form = useForm({
  name: '',
  description: '',
  color: '#3b82f6',
  background_color: '#dbeafe'
})

// Edit flair
const editingFlair = ref(null)
const editForm = reactive({
  name: '',
  description: '',
  color: '',
  background_color: '',
  is_active: true
})
const editFormProcessing = ref(false)

const createFlair = () => {
  form.name = newFlair.name
  form.description = newFlair.description
  form.color = newFlair.color
  form.background_color = newFlair.background_color

  form.post(route('subfapps.flairs.store', props.subfapp), {
    onSuccess: () => {
      // Reset form
      newFlair.name = ''
      newFlair.description = ''
      newFlair.color = '#3b82f6'
      newFlair.background_color = '#dbeafe'
      form.reset()
    }
  })
}

const startEditing = (flair) => {
  editingFlair.value = flair
  editForm.name = flair.name
  editForm.description = flair.description || ''
  editForm.color = flair.color
  editForm.background_color = flair.background_color
  editForm.is_active = flair.is_active
}

const cancelEdit = () => {
  editingFlair.value = null
  editForm.name = ''
  editForm.description = ''
  editForm.color = ''
  editForm.background_color = ''
  editForm.is_active = true
}

const updateFlair = () => {
  editFormProcessing.value = true
  
  router.patch(route('subfapps.flairs.update', [props.subfapp, editingFlair.value]), editForm, {
    onSuccess: () => {
      cancelEdit()
      editFormProcessing.value = false
    },
    onError: () => {
      editFormProcessing.value = false
    }
  })
}

const toggleActive = (flair) => {
  router.patch(route('subfapps.flairs.update', [props.subfapp, flair]), {
    ...flair,
    is_active: !flair.is_active
  })
}

const deleteFlair = (flair) => {
  if (confirm(`Are you sure you want to delete the "${flair.name}" flair? This action cannot be undone.`)) {
    router.delete(route('subfapps.flairs.destroy', [props.subfapp, flair]))
  }
}
</script>