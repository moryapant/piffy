<script setup>
import { ref } from 'vue'
import LikeDislikeModule from './LikeDislikeModule.vue'
import VoteButtonVertical from './VoteButtonVertical.vue'
import VoteButtonHorizontal from './VoteButtonHorizontal.vue'

// Sample content for demonstration
const sampleContent = ref({
  id: 1,
  upvotes: 42,
  downvotes: 3,
  score: 39,
  user_vote: { vote_type: 1 } // User has upvoted
})

const sampleContent2 = ref({
  id: 2,
  upvotes: 15,
  downvotes: 8,
  score: 7,
  user_vote: { vote_type: -1 } // User has downvoted
})

const sampleContent3 = ref({
  id: 3,
  upvotes: 23,
  downvotes: 12,
  score: 11,
  user_vote: null // No vote
})

const handleVote = (contentId, voteType) => {
  // In real app, this would make an API call
}
</script>

<template>
  <div class="p-8 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">Like/Dislike Module Demo</h1>
      
      <!-- Size Variants -->
      <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Size Variants (Vertical)</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          
          <!-- Small -->
          <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-lg font-medium mb-4">Small</h3>
            <div class="flex justify-center">
              <VoteButtonVertical 
                :content="sampleContent"
                size="small"
                @vote="handleVote"
              />
            </div>
            <p class="text-xs text-gray-500 mt-2 text-center">For compact areas, comments</p>
          </div>

          <!-- Medium -->
          <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-lg font-medium mb-4">Medium (Default)</h3>
            <div class="flex justify-center">
              <VoteButtonVertical 
                :content="sampleContent2"
                size="medium"
                @vote="handleVote"
              />
            </div>
            <p class="text-xs text-gray-500 mt-2 text-center">For post listings, general use</p>
          </div>

          <!-- Large -->
          <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-lg font-medium mb-4">Large</h3>
            <div class="flex justify-center">
              <VoteButtonVertical 
                :content="sampleContent3"
                size="large"
                @vote="handleVote"
              />
            </div>
            <p class="text-xs text-gray-500 mt-2 text-center">For main content pages</p>
          </div>
        </div>
      </section>

      <!-- Layout Variants -->
      <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Layout Variants</h2>
        
        <!-- Vertical vs Horizontal -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Vertical Layout -->
          <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-lg font-medium mb-4">Vertical Layout</h3>
            <div class="flex justify-center">
              <LikeDislikeModule 
                :content="sampleContent"
                layout="vertical"
                size="medium"
                @vote="handleVote"
              />
            </div>
            <p class="text-xs text-gray-500 mt-4 text-center">Stack buttons vertically with count in middle</p>
          </div>

          <!-- Horizontal Layout -->
          <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-lg font-medium mb-4">Horizontal Layout</h3>
            <div class="flex justify-center">
              <LikeDislikeModule 
                :content="sampleContent2"
                layout="horizontal"
                size="medium"
                @vote="handleVote"
              />
            </div>
            <p class="text-xs text-gray-500 mt-4 text-center">Arrange buttons inline with count between</p>
          </div>
        </div>
      </section>

      <!-- Special Cases -->
      <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Special Cases</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Compact Mode -->
          <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-lg font-medium mb-4">Compact Mode</h3>
            <div class="flex justify-center">
              <VoteButtonHorizontal 
                :content="sampleContent3"
                size="small"
                :compact="true"
                @vote="handleVote"
              />
            </div>
            <p class="text-xs text-gray-500 mt-4 text-center">Minimal spacing for tight layouts</p>
          </div>

          <!-- No Count -->
          <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-lg font-medium mb-4">No Vote Count</h3>
            <div class="flex justify-center">
              <LikeDislikeModule 
                :content="sampleContent"
                layout="horizontal"
                :show-count="false"
                @vote="handleVote"
              />
            </div>
            <p class="text-xs text-gray-500 mt-4 text-center">Just buttons without count display</p>
          </div>
        </div>
      </section>

      <!-- Reddit-Style Demo -->
      <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Reddit-Style Post Example</h2>
        
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="flex">
            <!-- Vote Section -->
            <div class="bg-gray-50 border-r border-gray-200 p-4 flex items-center">
              <VoteButtonVertical 
                :content="sampleContent"
                size="large"
                @vote="handleVote"
              />
            </div>
            
            <!-- Post Content -->
            <div class="flex-1 p-6">
              <div class="mb-2">
                <span class="text-sm text-gray-500">Posted by u/demo-user • 2 hours ago</span>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-3">
                Sample Post Title - Classic Thumbs Up/Down Design
              </h3>
              <p class="text-gray-600 mb-4">
                This demonstrates the like/dislike module using the original thumbs up and thumbs down icons. 
                The vertical voting buttons use the classic thumbs design that was working well before.
              </p>
              
              <!-- Mobile Actions -->
              <div class="md:hidden border-t border-gray-100 pt-4">
                <div class="flex items-center justify-between">
                  <VoteButtonHorizontal 
                    :content="sampleContent"
                    size="small"
                    :compact="true"
                    @vote="handleVote"
                  />
                  <button class="text-gray-500 text-sm">💬 12 comments</button>
                  <button class="text-gray-500 text-sm">🔗 Share</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Usage Code -->
      <section>
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Usage Examples</h2>
        
        <div class="bg-gray-900 rounded-xl p-6 text-green-400 font-mono text-sm overflow-x-auto">
<pre><!-- Vertical Layout (Reddit-style) -->
&lt;VoteButtonVertical 
  :content="post"
  size="large"
  @vote="handleVote"
/&gt;

<!-- Horizontal Layout (Inline) -->
&lt;VoteButtonHorizontal 
  :content="post"
  size="small"
  :compact="true"
  @vote="handleVote"
/&gt;

<!-- Custom Configuration -->
&lt;LikeDislikeModule 
  :content="content"
  layout="vertical"
  size="medium"
  :show-count="true"
  :compact="false"
  @vote="handleVote"
/&gt;</pre>
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped>
pre {
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>