<!-- PostVoteButtons.vue -->
<template>
    <div class="flex items-center space-x-2">
        <!-- Like Button Group -->
        <div class="flex items-center">
            <button 
                @click.stop.prevent="$emit('vote', post.id, 1)"
                :class="{
                    'bg-gray-100': post.user_vote && post.user_vote.vote_type === 1
                }"
                class="flex items-center space-x-1 px-3 py-1.5 rounded-full hover:bg-gray-100 transition-all duration-200 group focus:outline-none"
                title="Like"
            >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path 
                        d="M7 22H4C3.46957 22 2.96086 21.7893 2.58579 21.4142C2.21071 21.0391 2 20.5304 2 20V13C2 12.4696 2.21071 11.9609 2.58579 11.5858C2.96086 11.2107 3.46957 11 4 11H7M14 9V5C14 4.20435 13.6839 3.44129 13.1213 2.87868C12.5587 2.31607 11.7956 2 11 2L7 11V22H18.28C18.7623 22.0055 19.2304 21.8364 19.5979 21.524C19.9654 21.2116 20.2077 20.7769 20.28 20.3L21.66 11.3C21.7035 11.0134 21.6842 10.7207 21.6033 10.4423C21.5225 10.1638 21.3821 9.90629 21.1919 9.68751C21.0016 9.46873 20.7661 9.29393 20.5016 9.17522C20.2371 9.0565 19.9499 8.99672 19.66 9H14Z"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        :class="{
                            '!stroke-blue-600 !fill-blue-600': post.user_vote && post.user_vote.vote_type === 1
                        }"
                    />
                </svg>
                <span class="text-sm font-medium">{{ post.upvotes }}</span>
            </button>

            <!-- Dislike Button -->
            <button 
                @click.stop.prevent="$emit('vote', post.id, -1)"
                :class="{
                    'bg-gray-100': post.user_vote && post.user_vote.vote_type === -1
                }"
                class="flex items-center space-x-1 px-3 py-1.5 rounded-full hover:bg-gray-100 transition-all duration-200 group focus:outline-none ml-1"
                title="Dislike"
            >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path 
                        d="M17 2H20C20.5304 2 21.0391 2.21071 21.4142 2.58579C21.7893 2.96086 22 3.46957 22 4V11C22 11.5304 21.7893 12.0391 21.4142 12.4142C21.0391 12.7893 20.5304 13 20 13H17M10 15V19C10 19.7956 10.3161 20.5587 10.8787 21.1213C11.4413 21.6839 12.2044 22 13 22L17 13V2H5.72C5.23765 1.99450 4.76961 2.16359 4.40205 2.47599C4.03449 2.78840 3.79229 3.22309 3.72 3.7L2.34 12.7C2.29651 12.9866 2.31583 13.2793 2.39666 13.5577C2.47749 13.8362 2.61791 14.0937 2.80815 14.3125C2.99839 14.5313 3.23393 14.7061 3.49843 14.8248C3.76294 14.9435 4.05009 15.0033 4.34 15H10Z"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        :class="{
                            '!stroke-red-600 !fill-red-600': post.user_vote && post.user_vote.vote_type === -1
                        }"
                    />
                </svg>
                <span class="text-sm font-medium">{{ post.downvotes }}</span>
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    post: {
        type: Object,
        required: true
    }
});

defineEmits(['vote']);
</script>
