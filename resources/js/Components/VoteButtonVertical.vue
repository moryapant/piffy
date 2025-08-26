<script setup>
import LikeDislikeModule from './LikeDislikeModule.vue'

defineProps({
  content: {
    type: Object,
    required: true
  },
  size: {
    type: String,
    default: 'large'
  }
})

defineEmits(['vote'])
</script>

<template>
  <LikeDislikeModule 
    :content="content"
    :size="size"
    layout="vertical"
    :show-count="true"
    :compact="false"
    @vote="(contentId, voteType) => $emit('vote', contentId, voteType)"
  />
</template>