<template>
  <div class="relative" ref="dropdown">
    <!-- Notifications Bell Button -->
    <button
      @click="toggleDropdown"
      :class="[
        'relative p-2 text-gray-600 rounded-full hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-200',
        isOpen ? 'bg-gray-100 text-gray-900' : ''
      ]"
    >
      <!-- Bell Icon -->
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
      </svg>
      
      <!-- Unread Count Badge -->
      <span
        v-if="unreadCount > 0"
        :class="[
          'absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[20px] h-5 text-xs font-bold text-white rounded-full',
          unreadCount > 99 ? 'px-1' : '',
          'bg-red-500 animate-pulse'
        ]"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown Panel -->
    <div
      v-show="isOpen"
      class="absolute right-0 top-12 w-96 bg-white rounded-xl shadow-xl border border-gray-200 z-50 max-h-96 overflow-hidden"
      @click.stop
    >
      <!-- Header -->
      <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
        <button
          v-if="unreadCount > 0"
          @click="markAllAsRead"
          class="text-sm text-orange-600 hover:text-orange-700 font-medium"
        >
          Mark all read
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="px-4 py-8 text-center">
        <div class="animate-spin w-6 h-6 border-2 border-orange-500 border-t-transparent rounded-full mx-auto"></div>
        <p class="text-sm text-gray-500 mt-2">Loading notifications...</p>
      </div>

      <!-- Notifications List -->
      <div v-else-if="notifications.length > 0" class="max-h-80 overflow-y-auto">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="[
            'px-4 py-3 border-b border-gray-50 hover:bg-gray-50 transition-colors cursor-pointer',
            !notification.is_read ? 'bg-blue-50/30' : ''
          ]"
          @click="handleNotificationClick(notification)"
        >
          <div class="flex items-start space-x-3">
            <!-- Notification Icon -->
            <div :class="[
              'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white text-sm',
              getIconBackground(notification.color)
            ]">
              <component :is="getIcon(notification.icon)" class="w-4 h-4" />
            </div>
            
            <!-- Notification Content -->
            <div class="flex-1 min-w-0">
              <p :class="[
                'text-sm font-medium leading-snug',
                notification.is_read ? 'text-gray-700' : 'text-gray-900'
              ]">
                {{ notification.title }}
              </p>
              <p :class="[
                'text-xs mt-1 leading-relaxed',
                notification.is_read ? 'text-gray-500' : 'text-gray-600'
              ]">
                {{ notification.message }}
              </p>
              <p class="text-xs text-gray-400 mt-1 flex items-center">
                <ClockIcon class="w-3 h-3 mr-1" />
                {{ notification.time_ago }}
              </p>
            </div>

            <!-- Unread Indicator -->
            <div v-if="!notification.is_read" class="flex-shrink-0">
              <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="px-4 py-8 text-center">
        <div class="w-12 h-12 mx-auto mb-4 text-gray-400">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
          </svg>
        </div>
        <p class="text-sm text-gray-500 mb-2">No notifications yet</p>
        <p class="text-xs text-gray-400">You'll see your notifications here when you have some</p>
      </div>

      <!-- Footer -->
      <div v-if="notifications.length > 0" class="px-4 py-3 bg-gray-50 border-t border-gray-100">
        <Link
          :href="route('notifications.index')"
          class="block text-center text-sm text-orange-600 hover:text-orange-700 font-medium"
          @click="closeDropdown"
        >
          View all notifications
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import {
  ArrowUpIcon,
  ArrowDownIcon,
  ChatBubbleLeftIcon,
  AtSymbolIcon,
  BellIcon,
  ClockIcon,
} from '@heroicons/vue/24/outline'

// Component state
const isOpen = ref(false)
const isLoading = ref(false)
const notifications = ref([])
const unreadCount = ref(0)
const dropdown = ref(null)

// Methods
const toggleDropdown = async () => {
  isOpen.value = !isOpen.value
  if (isOpen.value && notifications.value.length === 0) {
    await fetchNotifications()
  }
}

const closeDropdown = () => {
  isOpen.value = false
}

const fetchNotifications = async () => {
  try {
    isLoading.value = true
    const response = await axios.get('/api/notifications', {
      params: { limit: 15 }
    })
    notifications.value = response.data.notifications
    unreadCount.value = response.data.unread_count
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  } finally {
    isLoading.value = false
  }
}

const fetchUnreadCount = async () => {
  try {
    const response = await axios.get('/api/notifications/unread-count')
    unreadCount.value = response.data.count
  } catch (error) {
    console.error('Failed to fetch unread count:', error)
  }
}

const markAllAsRead = async () => {
  try {
    await axios.patch('/api/notifications/mark-all-read')
    notifications.value = notifications.value.map(n => ({ ...n, is_read: true }))
    unreadCount.value = 0
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  }
}

const handleNotificationClick = async (notification) => {
  // Mark as read if unread
  if (!notification.is_read) {
    try {
      await axios.patch(`/api/notifications/${notification.id}/read`)
      notification.is_read = true
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    } catch (error) {
      console.error('Failed to mark as read:', error)
    }
  }

  // Close dropdown and navigate
  closeDropdown()
  
  if (notification.action_url) {
    router.visit(notification.action_url)
  }
}

const getIcon = (iconType) => {
  const iconMap = {
    'arrow-up': ArrowUpIcon,
    'arrow-down': ArrowDownIcon,
    'chat-bubble': ChatBubbleLeftIcon,
    'at-symbol': AtSymbolIcon,
    'bell': BellIcon,
  }
  return iconMap[iconType] || BellIcon
}

const getIconBackground = (color) => {
  const colorMap = {
    'blue': 'bg-blue-500',
    'red': 'bg-red-500',
    'green': 'bg-green-500',
    'purple': 'bg-purple-500',
    'orange': 'bg-orange-500',
    'gray': 'bg-gray-500',
  }
  return colorMap[color] || 'bg-gray-500'
}

// Handle clicking outside to close dropdown
const handleClickOutside = (event) => {
  if (dropdown.value && !dropdown.value.contains(event.target)) {
    closeDropdown()
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  
  // Fetch initial unread count
  fetchUnreadCount()
  
  // Set up periodic refresh of unread count (every 30 seconds)
  const interval = setInterval(fetchUnreadCount, 30000)
  
  onUnmounted(() => {
    clearInterval(interval)
  })
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
/* Smooth animations */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>