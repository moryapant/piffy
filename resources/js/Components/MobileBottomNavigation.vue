<template>
  <!-- Mobile Bottom Navigation (only visible on mobile) -->
  <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 safe-area-pb z-40">
    <nav class="flex justify-around items-center h-16 px-2">
      <!-- Home -->
      <Link
        :href="route('home')"
        :class="[
          'flex flex-col items-center justify-center p-2 rounded-xl transition-all duration-200 min-w-[60px] min-h-[52px]',
          isCurrentRoute('home') ? 'text-orange-600 bg-orange-50' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'
        ]"
      >
        <HomeIcon class="w-6 h-6 mb-1" />
        <span class="text-xs font-medium">Home</span>
      </Link>

      <!-- Communities -->
      <Link
        :href="route('subfapps.index')"
        :class="[
          'flex flex-col items-center justify-center p-2 rounded-xl transition-all duration-200 min-w-[60px] min-h-[52px]',
          isCurrentRoute('subfapps.index') ? 'text-orange-600 bg-orange-50' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'
        ]"
      >
        <BuildingOfficeIcon class="w-6 h-6 mb-1" />
        <span class="text-xs font-medium">Communities</span>
      </Link>

      <!-- Create Post (Center FAB) -->
      <Link
        :href="route('posts.create')"
        v-if="$page.props.auth.user"
        class="flex flex-col items-center justify-center p-3 bg-orange-500 text-white rounded-full shadow-lg hover:bg-orange-600 transition-all duration-200 transform hover:scale-105 min-w-[56px] min-h-[56px] -mt-2"
      >
        <PlusIcon class="w-7 h-7" />
      </Link>
      
      <!-- Login button for guests -->
      <Link
        :href="route('login')"
        v-else
        class="flex flex-col items-center justify-center p-3 bg-gray-500 text-white rounded-full shadow-lg hover:bg-gray-600 transition-all duration-200 transform hover:scale-105 min-w-[56px] min-h-[56px] -mt-2"
      >
        <ArrowRightOnRectangleIcon class="w-7 h-7" />
      </Link>

      <!-- Notifications -->
      <div
        v-if="$page.props.auth.user"
        :class="[
          'flex flex-col items-center justify-center p-2 rounded-xl transition-all duration-200 min-w-[60px] min-h-[52px] relative',
          'text-gray-600 hover:text-orange-600 hover:bg-orange-50'
        ]"
      >
        <button @click="toggleNotifications" class="flex flex-col items-center">
          <BellIcon class="w-6 h-6 mb-1" />
          <span class="text-xs font-medium">Alerts</span>
          <!-- Unread badge -->
          <span
            v-if="unreadCount > 0"
            class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[18px] h-[18px] text-xs font-bold text-white bg-red-500 rounded-full"
          >
            {{ unreadCount > 9 ? '9+' : unreadCount }}
          </span>
        </button>
      </div>

      <!-- Profile -->
      <Link
        :href="$page.props.auth.user ? route('profile.edit') : route('login')"
        :class="[
          'flex flex-col items-center justify-center p-2 rounded-xl transition-all duration-200 min-w-[60px] min-h-[52px]',
          isCurrentRoute('profile.edit') ? 'text-orange-600 bg-orange-50' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'
        ]"
      >
        <UserIcon class="w-6 h-6 mb-1" />
        <span class="text-xs font-medium">Profile</span>
      </Link>
    </nav>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import {
  HomeIcon,
  BuildingOfficeIcon,
  PlusIcon,
  BellIcon,
  UserIcon,
  ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline'

// Component state
const unreadCount = ref(0)
const page = usePage()

// Check if current route matches
const isCurrentRoute = (routeName) => {
  return page.url.startsWith('/' + routeName) || 
         (routeName === 'home' && (page.url === '/' || page.url === ''))
}

// Fetch unread notifications count
const fetchUnreadCount = async () => {
  if (!page.props.auth?.user) return
  
  try {
    const response = await axios.get('/api/notifications/unread-count')
    unreadCount.value = response.data.count
  } catch (error) {
  }
}

// Handle notifications toggle (for mobile)
const toggleNotifications = () => {
  // For now, navigate to notifications page
  // Later, you could implement a mobile-optimized notifications modal
  window.location.href = '/notifications'
}

// Lifecycle
onMounted(() => {
  fetchUnreadCount()
  
  // Refresh unread count every 30 seconds
  const interval = setInterval(fetchUnreadCount, 30000)
  
  onUnmounted(() => {
    clearInterval(interval)
  })
})
</script>

<style scoped>
/* Safe area support for mobile devices */
.safe-area-pb {
  padding-bottom: env(safe-area-inset-bottom, 0);
}

/* Ensure proper touch targets */
.min-w-[60px] {
  min-width: 60px;
}

.min-h-[52px] {
  min-height: 52px;
}

.min-w-[56px] {
  min-width: 56px;
}

.min-h-[56px] {
  min-height: 56px;
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Touch-friendly interactions */
@media (hover: none) and (pointer: coarse) {
  .hover\\:scale-105:hover {
    transform: scale(1.05);
  }
}

/* Haptic feedback simulation for buttons */
button:active,
a:active {
  transform: scale(0.95);
  transition-duration: 100ms;
}

/* Add subtle shadow to the navigation bar */
.border-t {
  box-shadow: 0 -1px 3px 0 rgba(0, 0, 0, 0.1), 0 -1px 2px 0 rgba(0, 0, 0, 0.06);
}
</style>