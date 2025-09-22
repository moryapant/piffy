<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  post: {
    type: Object,
    required: true
  },
  showLabels: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  platforms: {
    type: Array,
    default: () => ['facebook', 'twitter'], // Available: facebook, twitter, linkedin, reddit
    validator: (values) => values.every(v => ['facebook', 'twitter', 'linkedin', 'reddit'].includes(v))
  }
})

const page = usePage()

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

// Size classes
const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return {
        button: 'p-2',
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
        button: 'px-3 py-2',
        icon: 'w-5 h-5',
        text: 'text-sm',
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
}

// Platform configurations
const platformConfig = {
  facebook: {
    name: 'Facebook',
    color: 'bg-blue-600 hover:bg-blue-700 text-white',
    icon: 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z',
    action: shareOnFacebook
  },
  twitter: {
    name: 'X (Twitter)',
    color: 'bg-gray-900 hover:bg-gray-800 text-white',
    icon: 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z',
    action: shareOnTwitter
  },
  linkedin: {
    name: 'LinkedIn',
    color: 'bg-blue-700 hover:bg-blue-800 text-white',
    icon: 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z',
    action: shareOnLinkedIn
  },
  reddit: {
    name: 'Reddit',
    color: 'bg-orange-600 hover:bg-orange-700 text-white',
    icon: 'M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-7.004 4.87-3.874 0-7.004-2.176-7.004-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 0 1 4.028 12c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 0 1 .14-.197.35.35 0 0 1 .238-.042l2.906.617a1.214 1.214 0 0 1 1.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 0 0-.231.094.33.33 0 0 0 0 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.056 2.961-.913a.361.361 0 0 0 .029-.463.33.33 0 0 0-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 0 0-.232-.095z',
    action: shareOnReddit
  }
}
</script>

<template>
  <div :class="['inline-flex items-center', sizeClasses.gap]" @click.stop>
    <button
      v-for="platform in platforms"
      :key="platform"
      @click="platformConfig[platform].action()"
      :class="[
        'inline-flex items-center rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2',
        sizeClasses.button,
        sizeClasses.gap,
        platformConfig[platform].color
      ]"
      :title="`Share on ${platformConfig[platform].name}`"
    >
      <svg :class="[sizeClasses.icon]" fill="currentColor" viewBox="0 0 24 24">
        <path :d="platformConfig[platform].icon" />
      </svg>
      <span 
        v-if="showLabels" 
        :class="['font-medium', sizeClasses.text]"
      >
        {{ platformConfig[platform].name }}
      </span>
    </button>
  </div>
</template>