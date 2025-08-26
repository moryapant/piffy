<script setup>
import LikeDislikeModule from './LikeDislikeModule.vue'

defineProps({
  content: {
    type: Object,
    required: true
  },
  size: {
    type: String,
    default: 'medium'
  },
  compact: {
    type: Boolean,
    default: false
  }
})

defineEmits(['vote'])
</script>

<template>
  <LikeDislikeModule 
    :content="content"
    :size="size"
    layout="horizontal"
    :show-count="true"
    :compact="compact"
    @vote="(contentId, voteType) => $emit('vote', contentId, voteType)"
  />
</template>