<!-- PostVoteButtons.vue -->
<template>
    <VoteButtonHorizontal 
        :content="post"
        size="medium"
        :compact="false"
        @vote="(contentId, voteType) => $emit('vote', contentId, voteType)"
    />
</template>

<script setup>
import VoteButtonHorizontal from './VoteButtonHorizontal.vue';

defineProps({
    post: {
        type: Object,
        required: true
    }
});

defineEmits(['vote']);
</script>
