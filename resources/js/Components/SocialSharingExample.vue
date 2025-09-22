<template>
  <div class="p-8 space-y-8 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto space-y-12">
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Social Sharing Components</h1>
        <p class="text-lg text-gray-600">Examples of how to use the Facebook and X (Twitter) share buttons</p>
      </div>

      <!-- SocialShare Component Examples -->
      <div class="bg-white rounded-2xl p-8 shadow-lg">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">SocialShare Component (Dropdown)</h2>
        <div class="space-y-8">
          
          <!-- Example 1: Default with label -->
          <div>
            <h3 class="text-lg font-semibold mb-4">Default with Label</h3>
            <div class="flex items-center space-x-4">
              <SocialShare 
                :post="examplePost" 
                :showLabels="true"
                size="md"
              />
              <span class="text-gray-500">← Click to see dropdown with all social platforms</span>
            </div>
          </div>

          <!-- Example 2: Small without label -->
          <div>
            <h3 class="text-lg font-semibold mb-4">Small Size (No Label)</h3>
            <div class="flex items-center space-x-4">
              <SocialShare 
                :post="examplePost" 
                :showLabels="false"
                size="sm"
              />
              <span class="text-gray-500">← Compact version for tight spaces</span>
            </div>
          </div>

          <!-- Example 3: Large with label -->
          <div>
            <h3 class="text-lg font-semibold mb-4">Large Size</h3>
            <div class="flex items-center space-x-4">
              <SocialShare 
                :post="examplePost" 
                :showLabels="true"
                size="lg"
              />
              <span class="text-gray-500">← Bigger for prominent placement</span>
            </div>
          </div>
        </div>
      </div>

      <!-- SocialButtons Component Examples -->
      <div class="bg-white rounded-2xl p-8 shadow-lg">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">SocialButtons Component (Inline)</h2>
        <div class="space-y-8">
          
          <!-- Example 1: Facebook and X only -->
          <div>
            <h3 class="text-lg font-semibold mb-4">Facebook & X (Twitter) Only</h3>
            <div class="flex items-center space-x-4">
              <SocialButtons 
                :post="examplePost" 
                :platforms="['facebook', 'twitter']"
                :showLabels="false"
                size="md"
              />
              <span class="text-gray-500">← Direct sharing buttons</span>
            </div>
          </div>

          <!-- Example 2: All platforms with labels -->
          <div>
            <h3 class="text-lg font-semibold mb-4">All Platforms with Labels</h3>
            <div class="flex items-center space-x-4 flex-wrap">
              <SocialButtons 
                :post="examplePost" 
                :platforms="['facebook', 'twitter', 'linkedin', 'reddit']"
                :showLabels="true"
                size="sm"
              />
            </div>
            <p class="text-gray-500 mt-2">← All available social platforms</p>
          </div>

          <!-- Example 3: Custom platform selection -->
          <div>
            <h3 class="text-lg font-semibold mb-4">LinkedIn & Reddit Only</h3>
            <div class="flex items-center space-x-4">
              <SocialButtons 
                :post="examplePost" 
                :platforms="['linkedin', 'reddit']"
                :showLabels="true"
                size="md"
              />
              <span class="text-gray-500">← Professional & community focused</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Integration Examples -->
      <div class="bg-white rounded-2xl p-8 shadow-lg">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Usage Examples</h2>
        
        <div class="space-y-8">
          <div class="bg-gray-50 p-6 rounded-xl">
            <h3 class="text-lg font-semibold mb-4">1. In PostInteractions Component</h3>
            <pre class="bg-gray-800 text-gray-100 p-4 rounded-lg text-sm overflow-x-auto"><code>&lt;!-- Already integrated --&gt;
&lt;PostInteractions :post="post" @vote="vote" /&gt;</code></pre>
          </div>

          <div class="bg-gray-50 p-6 rounded-xl">
            <h3 class="text-lg font-semibold mb-4">2. In Post Show Page</h3>
            <pre class="bg-gray-800 text-gray-100 p-4 rounded-lg text-sm overflow-x-auto"><code>&lt;!-- Already integrated with small size --&gt;
&lt;SocialShare 
  :post="post" 
  :showLabels="true"
  size="sm"
/&gt;</code></pre>
          </div>

          <div class="bg-gray-50 p-6 rounded-lg">
            <h3 class="text-lg font-semibold mb-4">3. Custom Implementation</h3>
            <pre class="bg-gray-800 text-gray-100 p-4 rounded-lg text-sm overflow-x-auto"><code>&lt;!-- For direct Facebook & X buttons --&gt;
&lt;SocialButtons 
  :post="post" 
  :platforms="['facebook', 'twitter']"
  :showLabels="false"
  size="md"
/&gt;

&lt;!-- For dropdown with all options --&gt;
&lt;SocialShare 
  :post="post" 
  :showLabels="true"
  size="lg"
/&gt;</code></pre>
          </div>
        </div>
      </div>

      <!-- Features -->
      <div class="bg-white rounded-2xl p-8 shadow-lg">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Features</h2>
        <div class="grid md:grid-cols-2 gap-8">
          <div>
            <h3 class="text-lg font-semibold mb-4 text-blue-600">SocialShare (Dropdown)</h3>
            <ul class="space-y-2 text-gray-600">
              <li>✅ Native mobile sharing (Web Share API)</li>
              <li>✅ Facebook, X (Twitter), LinkedIn, Reddit</li>
              <li>✅ Copy link to clipboard</li>
              <li>✅ Elegant dropdown interface</li>
              <li>✅ Dark mode support</li>
              <li>✅ Responsive design</li>
            </ul>
          </div>
          <div>
            <h3 class="text-lg font-semibold mb-4 text-green-600">SocialButtons (Inline)</h3>
            <ul class="space-y-2 text-gray-600">
              <li>✅ Direct platform buttons</li>
              <li>✅ Customizable platform selection</li>
              <li>✅ Brand-colored buttons</li>
              <li>✅ Multiple sizes (sm, md, lg)</li>
              <li>✅ With or without labels</li>
              <li>✅ Popup sharing windows</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import SocialShare from '@/Components/SocialShare.vue'
import SocialButtons from '@/Components/SocialButtons.vue'

// Example post data
const examplePost = {
  id: 1,
  title: "Amazing new features in our social platform!",
  content: "Check out these incredible new sharing capabilities that make it easier than ever to connect with your friends and communities.",
  images: [],
  user: {
    id: 1,
    name: "Demo User"
  }
}
</script>