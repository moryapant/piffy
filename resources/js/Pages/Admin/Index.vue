<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router, usePage } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import debounce from 'lodash/debounce';

// Helper methods for activity formatting
const formatActivityType = (type) => {
  if (!type) return 'Page Visit';
  
  const types = {
    'page_view': 'Page Visit',
    'post_vote': 'Post Vote',
    'comment': 'Comment',
    'reply': 'Reply Comment'
  };
  
  return types[type] || type.replace('_', ' ');
};

const getVoteType = (dataString) => {
  try {
    const data = typeof dataString === 'string' ? JSON.parse(dataString) : dataString;
    if (data.vote_type === 1) return '👍 Upvote';
    if (data.vote_type === -1) return '👎 Downvote';
    return 'Vote: ' + data.vote_type;
  } catch (e) {
    return 'Vote';
  }
};

const parseActivityData = (dataString) => {
  try {
    const data = typeof dataString === 'string' ? JSON.parse(dataString) : dataString;
    if (data.parent_id) {
      return `Reply to comment #${data.parent_id}`;
    }
    return `On post #${data.post_id}`;
  } catch (e) {
    return '';
  }
};

const showEditUserModal = ref(false);
const currentUser = ref(null);

const userForm = useForm({
  name: '',
  email: '',
  is_admin: false,
  password: '',
});

const openEditUserModal = (user) => {
  currentUser.value = user;
  userForm.name = user.name;
  userForm.email = user.email;
  userForm.is_admin = user.is_admin;
  userForm.password = '';
  showEditUserModal.value = true;
};

const closeEditUserModal = () => {
  showEditUserModal.value = false;
  currentUser.value = null;
  userForm.reset();
};

const updateUser = () => {
  userForm.patch(route('admin.users.update', currentUser.value.id), {
    preserveScroll: true,
    onSuccess: () => closeEditUserModal(),
  });
};

const deleteUser = (userId) => {
  if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
    useForm().delete(route('admin.users.delete', userId));
  }
};

const page = usePage();
const canManageUsers = computed(() => page.props.auth.user.is_admin);
const activeTab = ref('users');
const search = ref('');
const sortBy = ref('created_at');
const sortOrder = ref('desc');
const perPage = ref(10);
const selectedItems = ref([]);

// Debounced search function
const debouncedSearch = debounce(() => {
  router.get(
    route('admin.index'),
    { search: search.value, sort_by: sortBy.value, sort_order: sortOrder.value, per_page: perPage.value },
    { preserveState: true, preserveScroll: true }
  );
}, 300);

// Watch for changes in search input
watch(search, () => {
  debouncedSearch();
});

// Sort function
const sort = (column) => {
  if (sortBy.value === column) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortOrder.value = 'asc';
  }
  router.get(
    route('admin.index'),
    { search: search.value, sort_by: sortBy.value, sort_order: sortOrder.value, per_page: perPage.value },
    { preserveState: true, preserveScroll: true }
  );
};

// Bulk delete function
const bulkDelete = () => {
  if (!selectedItems.value.length) return;
  
  if (confirm(`Are you sure you want to delete ${selectedItems.value.length} items?`)) {
    router.delete(route('admin.bulk-delete'), {
      data: { 
        ids: selectedItems.value,
        type: activeTab.value
      },
      onSuccess: () => {
        selectedItems.value = [];
      },
    });
  }
};

// Export function
const exportData = () => {
  const params = new URLSearchParams({
    search: search.value,
    sort_by: sortBy.value,
    sort_order: sortOrder.value,
  }).toString();
  
  window.open(`${route('admin.export')}?${params}`, '_blank');
};

const toggleUserRole = (user) => {
  if (!canManageUsers.value) {
    alert('Only administrators can change user roles');
    return;
  }

  if (user.id === page.props.auth.user.id) {
    alert('You cannot change your own admin status');
    return;
  }

  router.patch(
    route('admin.users.update', user.id),
    { is_admin: !user.is_admin },
    {
      preserveScroll: true,
      onSuccess: () => {
        user.is_admin = !user.is_admin;
      },
      onError: (errors) => {
        alert(errors.error || 'An error occurred while updating the user role');
      },
    }
  );
};

const props = defineProps({
  posts: {
    type: Object,
    required: true,
  },
  comments: {
    type: Object,
    required: true,
  },
  users: {
    type: Object,
    required: true,
  },
  communities: {
    type: Object,
    required: true,
  },
  visits: {
    type: Object,
    required: true,
  },
  visitsCount: {
    type: Number,
    required: false,
  },
  activityStats: {
    type: Object,
    required: false,
    default: () => ({
      page_views: 0,
      votes: 0,
      comments: 0,
      replies: 0
    })
  },
});

const showEditModal = ref(false);
const showEditCommunityModal = ref(false);
const currentComment = ref(null);
const currentCommunity = ref(null);

const communityForm = useForm({
  name: '',
  description: '',
});

const openEditCommunityModal = (community) => {
  currentCommunity.value = community;
  communityForm.name = community.name;
  communityForm.description = community.description;
  showEditCommunityModal.value = true;
};

const closeEditCommunityModal = () => {
  showEditCommunityModal.value = false;
  currentCommunity.value = null;
  communityForm.reset();
};

const updateCommunity = () => {
  communityForm.patch(route('admin.communities.update', currentCommunity.value.id), {
    preserveScroll: true,
    onSuccess: () => closeEditCommunityModal(),
  });
};

const deleteCommunity = (communityId) => {
  if (confirm('Are you sure you want to delete this community? This action cannot be undone.')) {
    router.delete(route('admin.communities.delete', communityId), {
      preserveScroll: true,
    });
  }
};

const editForm = useForm({
  content: "",
});

const openEditModal = (comment) => {
  currentComment.value = comment;
  editForm.content = comment.content;
  showEditModal.value = true;
};

const closeEditModal = () => {
  showEditModal.value = false;
  currentComment.value = null;
  editForm.reset();
};

const updateComment = () => {
  editForm.patch(route("admin.comments.update", currentComment.value.id), {
    preserveScroll: true,
    onSuccess: () => closeEditModal(),
  });
};

const deletePost = (postId) => {
  if (confirm("Are you sure you want to delete this post?")) {
    useForm().delete(route("admin.posts.delete", postId));
  }
};

const deleteComment = (commentId) => {
  if (confirm("Are you sure you want to delete this comment?")) {
    useForm().delete(route("admin.comments.delete", commentId));
  }
};
</script>

<template>
  <Head title="Admin Panel" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Admin Panel
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Search and Actions Bar -->
        <div class="mb-4 flex items-center justify-between gap-4">
          <div class="flex-1 max-w-sm">
            <TextInput
              v-model="search"
              type="search"
              placeholder="Search..."
              class="w-full"
            />
          </div>
          
          <div class="flex items-center gap-2">
            <select
              v-model="perPage"
              class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              @change="debouncedSearch()"
            >
              <option value="10">10 per page</option>
              <option value="25">25 per page</option>
              <option value="50">50 per page</option>
              <option value="100">100 per page</option>
            </select>

            <PrimaryButton
              @click="exportData"
              class="whitespace-nowrap"
            >
              Export Data
            </PrimaryButton>

            <DangerButton
              v-if="selectedItems.length"
              @click="bulkDelete"
              class="whitespace-nowrap"
            >
              Delete Selected ({{ selectedItems.length }})
            </DangerButton>
          </div>
        </div>
        <!-- Tabs -->
        <div class="mb-4 border-b border-gray-200">
          <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button
              :class="[
                'px-3 py-2 font-medium text-sm rounded-md',
                activeTab === 'communities'
                  ? 'bg-gray-100 text-gray-700'
                  : 'text-gray-500 hover:text-gray-700',
              ]"
              @click="activeTab = 'communities'"
            >
              Communities
            </button>
            <button
              @click="activeTab = 'users'"
              :class="[
                activeTab === 'users'
                  ? 'border-indigo-500 text-indigo-600'
                  : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium'
              ]"
            >
              Users
            </button>
            <button
              @click="activeTab = 'posts'"
              :class="[
                activeTab === 'posts'
                  ? 'border-indigo-500 text-indigo-600'
                  : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium'
              ]"
            >
              Posts
            </button>
            <button
              @click="activeTab = 'comments'"
              :class="[
                activeTab === 'comments'
                  ? 'border-indigo-500 text-indigo-600'
                  : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium'
              ]"
            >
              Comments
            </button>
            <button
              @click="activeTab = 'visits'"
              :class="[
                activeTab === 'visits'
                  ? 'border-indigo-500 text-indigo-600'
                  : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium'
              ]"
            >
              Visitors
            </button>

          </nav>
        </div>

        <!-- Users Section -->
        <div v-show="activeTab === 'users'" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Users</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Name
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Email
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Role
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Stats
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="user in users.data" :key="user.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ user.name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ user.email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <button
                        @click="toggleUserRole(user)"
                        :disabled="user.id === page.props.auth.user.id || !canManageUsers"
                        :class="[
                          'px-3 py-1 text-xs font-semibold rounded-full transition-all duration-200 flex items-center gap-1',
                          user.is_admin 
                            ? 'bg-red-100 text-red-800 hover:bg-red-200' 
                            : 'bg-green-100 text-green-800 hover:bg-green-200',
                          (user.id === $page.props.auth.user.id || !canManageUsers) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                        ]"
                      >
                        <span>{{ user.is_admin ? 'Admin' : 'User' }}</span>
                        <svg v-if="user.is_admin" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-500">
                        {{ user.posts_count }} posts · {{ user.comments_count }} comments
                      </div>
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                      <div class="flex justify-end space-x-2">
                        <PrimaryButton @click="openEditUserModal(user)">Edit</PrimaryButton>
                        <DangerButton 
                          @click="deleteUser(user.id)"
                          :disabled="user.id === $page.props.auth.user.id"
                        >
                          Delete
                        </DangerButton>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <!-- Posts Section -->
        <div v-show="activeTab === 'posts'" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Posts</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                    >
                      Title
                    </th>
                    <th
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                    >
                      Author
                    </th>
                    <th
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                    >
                      Community
                    </th>
                    <th
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                    >
                      Stats
                    </th>
                    <th
                      class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase"
                    >
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="post in posts.data" :key="post.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ post.title }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        {{ post.user.name }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        {{ post.subfapp?.name || 'No Community' }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-500">
                        {{ post.comments_count }} comments ·
                        {{ post.votes_count }} votes
                      </div>
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                      <DangerButton @click="deletePost(post.id)"
                        >Delete</DangerButton
                      >
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>


        <!-- Comments Section -->
        <!-- Communities Table -->
        <div v-show="activeTab === 'communities'" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Communities</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="w-4 px-6 py-3">
                      <input
                        type="checkbox"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :checked="selectedItems.length === communities.data.length"
                        @change="e => {
                          selectedItems = e.target.checked ? communities.data.map(c => c.id) : [];
                        }"
                      >
                    </th>
                    <th
                      v-for="column in ['Name', 'Description', 'Posts', 'Subscribers', 'Created At', 'Actions']"
                      :key="column"
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                      @click="sort(column.toLowerCase())"
                    >
                      {{ column }}
                      <span v-if="sortBy === column.toLowerCase()" class="ml-1">
                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                      </span>
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="community in communities.data" :key="community.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <input
                        type="checkbox"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :checked="selectedItems.includes(community.id)"
                        @change="e => {
                          selectedItems = e.target.checked
                            ? [...selectedItems, community.id]
                            : selectedItems.filter(id => id !== community.id);
                        }"
                      >
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">
                        {{ community.name }}
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm text-gray-900">
                        {{ community.description }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ community.posts_count }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ community.subscribers_count }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ new Date(community.created_at).toLocaleDateString() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <button
                        @click="openEditCommunityModal(community)"
                        class="text-indigo-600 hover:text-indigo-900 mr-2"
                      >
                        Edit
                      </button>
                      <button
                        @click="deleteCommunity(community.id)"
                        class="text-red-600 hover:text-red-900"
                      >
                        Delete
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Comments Table -->
        <div v-show="activeTab === 'comments'" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Comments</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                    >
                      Content
                    </th>
                    <th
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                    >
                      Author
                    </th>
                    <th
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                    >
                      Post
                    </th>
                    <th
                      class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase"
                    >
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="comment in comments.data" :key="comment.id">
                    <td class="px-6 py-4">
                      <div class="text-sm text-gray-900">
                        {{ comment.content }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        {{ comment.user.name }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        {{ comment.post?.title || "[Deleted Post]" }}
                      </div>
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                      <div class="flex justify-end space-x-2">
                        <PrimaryButton @click="openEditModal(comment)"
                          >Edit</PrimaryButton
                        >
                        <DangerButton @click="deleteComment(comment.id)"
                          >Delete</DangerButton
                        >
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Visitors Table -->
        <div v-show="activeTab === 'visits'" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="mb-4 text-lg font-medium text-gray-900">User Activity Tracking</h3>
            
            <!-- Activity Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
              <div class="bg-blue-50 rounded p-4 text-center">
                <h4 class="text-lg font-semibold text-blue-800">Page Views</h4>
                <p class="text-2xl font-bold text-blue-900">{{ activityStats?.page_views || 0 }}</p>
              </div>
              <div class="bg-green-50 rounded p-4 text-center">
                <h4 class="text-lg font-semibold text-green-800">Votes</h4>
                <p class="text-2xl font-bold text-green-900">{{ activityStats?.votes || 0 }}</p>
              </div>
              <div class="bg-purple-50 rounded p-4 text-center">
                <h4 class="text-lg font-semibold text-purple-800">Comments</h4>
                <p class="text-2xl font-bold text-purple-900">{{ activityStats?.comments || 0 }}</p>
              </div>
              <div class="bg-orange-50 rounded p-4 text-center">
                <h4 class="text-lg font-semibold text-orange-800">Replies</h4>
                <p class="text-2xl font-bold text-orange-900">{{ activityStats?.replies || 0 }}</p>
              </div>
            </div>
            
            <!-- Activity Stats Debug -->
            <div v-if="!activityStats || Object.values(activityStats).every(v => v === 0)" class="mb-4 p-4 bg-yellow-50 rounded">
              <p class="text-yellow-800">
                <strong>Note:</strong> Activity statistics will appear here once users start interacting with the site.
              </p>
              <p class="text-sm text-yellow-700 mt-2">
                Try visiting different pages, voting on posts, or adding comments to see the stats update.
              </p>
            </div>
            
            <!-- Debug information -->
            <div v-if="visitsCount !== undefined" class="mb-4 p-4 bg-blue-50 rounded">
              <p class="text-blue-800">Debug: Raw visit count in database: {{ visitsCount }}</p>
            </div>
            
            <div v-if="visits.data.length === 0" class="py-4 text-center text-gray-500">
              No visit records found. Try visiting some pages or check the test route.
            </div>
            
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                    >
                      Activity
                    </th>
                    <th
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                    >
                      Details
                    </th>
                    <th
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                    >
                      User
                    </th>
                    <th
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                    >
                      IP Address
                    </th>
                    <th
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                    >
                      Time
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="visit in visits.data" :key="visit.id">
                    <td class="px-6 py-4">
                      <div class="text-sm font-medium" :class="{
                        'text-blue-600': visit.activity_type === 'page_view',
                        'text-green-600': visit.activity_type === 'post_vote',
                        'text-purple-600': visit.activity_type === 'comment',
                        'text-orange-600': visit.activity_type === 'reply',
                        'text-gray-900': !visit.activity_type
                      }">
                        {{ formatActivityType(visit.activity_type) }}
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm text-gray-900">{{ visit.page_title || 'Unknown Page' }}</div>
                      <div class="text-xs text-gray-500 max-w-md truncate" :title="visit.page_visited">
                        {{ visit.page_visited }}
                      </div>
                      <div v-if="visit.activity_data" class="text-xs text-gray-500 mt-1">
                        <span v-if="visit.activity_type === 'post_vote'">
                          {{ getVoteType(visit.activity_data) }}
                        </span>
                        <span v-else-if="visit.activity_type === 'comment' || visit.activity_type === 'reply'">
                          {{ parseActivityData(visit.activity_data) }}
                        </span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        {{ visit.user?.name || 'Anonymous' }}
                      </div>
                      <div v-if="visit.user" class="text-xs text-gray-500">
                        {{ visit.user.email }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ visit.ip_address || 'Unknown' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        {{ new Date(visit.visited_at).toLocaleString() }}
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              
              <!-- Pagination -->
              <div class="mt-6 flex justify-between">
                <div class="text-sm text-gray-700">
                  Showing {{ visits.from }} to {{ visits.to }} of {{ visits.total }} visits
                </div>
                <div class="flex space-x-1">
                  <button
                    v-for="(link, i) in visits.links"
                    :key="i"
                    :disabled="!link.url || link.active"
                    @click="link.url && $inertia.visit(link.url)"
                    class="px-4 py-2 text-sm rounded"
                    :class="{
                      'bg-indigo-50 text-indigo-600': link.active,
                      'text-gray-700 hover:bg-gray-50': !link.active && link.url,
                      'text-gray-400': !link.url
                    }"
                    v-html="link.label"
                  ></button>
                </div>
              </div>
            </div>
          </div>
        </div>


      </div>
    </div>

    <!-- Edit Comment Modal -->
    <Modal :show="showEditModal" @close="closeEditModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">Edit Comment</h2>
        <div class="mt-6">
          <form @submit.prevent="updateComment">
            <div>
              <TextInput
                v-model="editForm.content"
                type="text"
                class="block w-full mt-1"
                required
              />
              <InputError :message="editForm.errors.content" class="mt-2" />
            </div>

            <div class="flex justify-end mt-6 space-x-2">
              <SecondaryButton @click="closeEditModal">Cancel</SecondaryButton>
              <PrimaryButton :disabled="editForm.processing"
                >Save</PrimaryButton
              >
            </div>
          </form>
        </div>
      </div>
    </Modal>
    <!-- Edit User Modal -->
    <Modal :show="showEditUserModal" @close="closeEditUserModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">Edit User</h2>
        <div class="mt-6 space-y-6">
          <div>
            <InputLabel for="name" value="Name" />
            <TextInput
              id="name"
              v-model="userForm.name"
              type="text"
              class="block w-full mt-1"
              required
            />
            <InputError :message="userForm.errors.name" class="mt-2" />
          </div>

          <div>
            <InputLabel for="email" value="Email" />
            <TextInput
              id="email"
              v-model="userForm.email"
              type="email"
              class="block w-full mt-1"
              required
            />
            <InputError :message="userForm.errors.email" class="mt-2" />
          </div>

          <div>
            <InputLabel for="password" value="Password (leave blank to keep current)" />
            <TextInput
              id="password"
              v-model="userForm.password"
              type="password"
              class="block w-full mt-1"
            />
            <InputError :message="userForm.errors.password" class="mt-2" />
          </div>

          <div class="flex items-center">
            <input
              type="checkbox"
              id="is_admin"
              v-model="userForm.is_admin"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <label for="is_admin" class="block ml-2 text-sm text-gray-900">
              Admin privileges
            </label>
          </div>
        </div>

        <div class="flex justify-end mt-6 space-x-3">
          <SecondaryButton @click="closeEditUserModal">Cancel</SecondaryButton>
          <PrimaryButton
            :class="{ 'opacity-25': userForm.processing }"
            :disabled="userForm.processing"
            @click="updateUser"
          >
            Save Changes
          </PrimaryButton>
        </div>
      </div>
    </Modal>

    <!-- Edit Community Modal -->
    <Modal :show="showEditCommunityModal" @close="closeEditCommunityModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
          Edit Community
        </h2>

        <div class="mt-6">
          <div class="mb-4">
            <InputLabel for="name" value="Name" />
            <TextInput
              id="name"
              type="text"
              class="mt-1 block w-full"
              v-model="communityForm.name"
            />
            <InputError :message="communityForm.errors.name" class="mt-2" />
          </div>

          <div class="mb-4">
            <InputLabel for="description" value="Description" />
            <textarea
              id="description"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              rows="3"
              v-model="communityForm.description"
            ></textarea>
            <InputError :message="communityForm.errors.description" class="mt-2" />
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <SecondaryButton @click="closeEditCommunityModal" class="mr-2">
            Cancel
          </SecondaryButton>
          <PrimaryButton
            :class="{ 'opacity-25': communityForm.processing }"
            :disabled="communityForm.processing"
            @click="updateCommunity"
          >
            Save Changes
          </PrimaryButton>
        </div>
      </div>
    </Modal>

    <!-- Edit Community Modal -->
    <Modal :show="showEditCommunityModal" @close="closeEditCommunityModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
          Edit Community
        </h2>

        <div class="mt-6">
          <div class="mb-4">
            <InputLabel for="name" value="Name" />
            <TextInput
              id="name"
              type="text"
              class="mt-1 block w-full"
              v-model="communityForm.name"
            />
            <InputError :message="communityForm.errors.name" class="mt-2" />
          </div>

          <div class="mb-4">
            <InputLabel for="description" value="Description" />
            <textarea
              id="description"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              rows="3"
              v-model="communityForm.description"
            ></textarea>
            <InputError :message="communityForm.errors.description" class="mt-2" />
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <SecondaryButton @click="closeEditCommunityModal" class="mr-2">
            Cancel
          </SecondaryButton>
          <PrimaryButton
            :class="{ 'opacity-25': communityForm.processing }"
            :disabled="communityForm.processing"
            @click="updateCommunity"
          >
            Save Changes
          </PrimaryButton>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>
