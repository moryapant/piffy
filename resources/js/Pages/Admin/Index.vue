<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router, usePage } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import Modal from "@/Components/Modal.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue";
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

// Confirmation modal states
const showDeleteConfirm = ref(false);
const deleteConfirmData = ref({
  type: '',
  id: null,
  title: '',
  message: ''
});

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
  const user = props.users.data.find(u => u.id === userId);
  const reasons = getUserDataReason(user);
  deleteConfirmData.value = {
    type: 'user',
    id: userId,
    title: 'Delete User',
    message: hasUserData(user)
      ? `Delete user "${user?.name}"? Their posts/comments/votes will be removed via cascades, visits & activities anonymized, and any communities reassigned to you. ${reasons ? 'Associated: ' + reasons + '.' : ''}`
      : `Are you sure you want to delete the user "${user?.name}"? This action cannot be undone.`
  };
  showDeleteConfirm.value = true;
};

const page = usePage();
const canManageUsers = computed(() => page.props.auth.user.is_admin);
const activeTab = ref(page.props.filters?.tab || 'dashboard');
// Comment-specific filters
const commentType = ref(page.props.filters?.comment_type || ''); // '', 'top', 'replies'
const showDeletedComments = ref(!!page.props.filters?.show_deleted);
const search = ref('');
const sortBy = ref('created_at');
const sortOrder = ref('desc');
const perPage = ref(10);
const selectedItems = ref([]);

// Debounced search function
const buildQuery = (extra = {}) => ({
  tab: activeTab.value,
  search: search.value,
  sort_by: sortBy.value,
  sort_order: sortOrder.value,
  per_page: perPage.value,
  comment_type: commentType.value || undefined,
  show_deleted: showDeletedComments.value ? 1 : undefined,
  ...extra,
});

const debouncedSearch = debounce(() => {
  router.get(
    route('admin.index'),
    buildQuery(),
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
    buildQuery(),
    { preserveState: true, preserveScroll: true }
  );
};

// Bulk delete function
const bulkDelete = () => {
  if (!selectedItems.value.length) return;
  
  deleteConfirmData.value = {
    type: 'bulk',
    id: selectedItems.value,
    title: 'Delete Selected Items',
    message: `Are you sure you want to delete ${selectedItems.value.length} selected ${activeTab.value}? This action cannot be undone.`
  };
  showDeleteConfirm.value = true;
};

// Export function
const exportData = () => {
  const params = new URLSearchParams(buildQuery());
  window.open(`${route('admin.export')}?${params.toString()}`, '_blank');
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
  posts: { type: Object, required: true },
  comments: { type: Object, required: true },
  users: { type: Object, required: true },
  communities: { type: Object, required: true },
  visits: { type: Object, required: true },
  visitsCount: { type: Number, required: false },
  activityStats: {
    type: Object,
    required: false,
    default: () => ({ page_views: 0, votes: 0, comments: 0, replies: 0 })
  }
});

// Watch filters specific to comments tab
watch([commentType, showDeletedComments], () => {
  if (activeTab.value === 'comments') {
    debouncedSearch();
  }
});

// Reset selection when tab changes & push tab param
watch(activeTab, () => {
  selectedItems.value = [];
  router.get(route('admin.index'), buildQuery(), { preserveState: true, preserveScroll: true });
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
  const community = props.communities.data.find(c => c.id === communityId);
  deleteConfirmData.value = {
    type: 'community',
    id: communityId,
    title: 'Delete Community',
    message: `Are you sure you want to delete the community "f/${community?.name}"? This action cannot be undone.`
  };
  showDeleteConfirm.value = true;
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
  const post = props.posts.data.find(p => p.id === postId);
  deleteConfirmData.value = {
    type: 'post',
    id: postId,
    title: 'Delete Post',
    message: `Are you sure you want to delete the post "${post?.title}"? This action cannot be undone.`
  };
  showDeleteConfirm.value = true;
};

const deleteComment = (commentId) => {
  const comment = props.comments.data.find(c => c.id === commentId);
  const truncatedContent = comment?.content.substring(0, 50) + (comment?.content.length > 50 ? '...' : '');
  deleteConfirmData.value = {
    type: 'comment',
    id: commentId,
    title: 'Delete Comment',
    message: `Are you sure you want to delete the comment "${truncatedContent}"? This action cannot be undone.`
  };
  showDeleteConfirm.value = true;
};

// Confirm delete action
const confirmDelete = () => {
  const { type, id } = deleteConfirmData.value;
  
  switch (type) {
    case 'user':
      useForm().delete(route('admin.users.delete', id), { preserveScroll: true });
      break;
    case 'community':
      router.delete(route('admin.communities.delete', id), {
        preserveScroll: true,
      });
      break;
    case 'post':
      useForm().delete(route('admin.posts.delete', id));
      break;
    case 'comment':
      useForm().delete(route('admin.comments.delete', id));
      break;
    case 'bulk':
      router.delete(route('admin.bulk-delete'), {
        data: { 
          ids: id,
          type: activeTab.value
        },
        onSuccess: () => {
          selectedItems.value = [];
        },
      });
      break;
  }
  
  showDeleteConfirm.value = false;
};

// Cancel delete action
const cancelDelete = () => {
  showDeleteConfirm.value = false;
  deleteConfirmData.value = {
    type: '',
    id: null,
    title: '',
    message: ''
  };
};

// Check if user has associated data that would prevent deletion
const hasUserData = (user) => {
  const totalActivity = (user.posts_count || 0) + (user.comments_count || 0) + (user.votes_count || 0);
  return totalActivity > 0;
};

// Get detailed reason why user cannot be deleted
const getUserDataReason = (user) => {
  const reasons = [];
  if (user.posts_count > 0) reasons.push(`${user.posts_count} posts`);
  if (user.comments_count > 0) reasons.push(`${user.comments_count} comments`);
  if (user.votes_count > 0) reasons.push(`${user.votes_count} votes`);
  
  if (reasons.length === 0) return '';
  if (reasons.length === 1) return reasons[0];
  if (reasons.length === 2) return reasons.join(' and ');
  return reasons.slice(0, -1).join(', ') + ', and ' + reasons[reasons.length - 1];
};
</script>

<template>
  <Head title="Admin Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold leading-tight text-gray-900">
          🚀 Admin Dashboard
        </h2>
        <div class="text-sm text-gray-600">
          Welcome back, {{ $page.props.auth.user.name }}!
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Dashboard Overview -->
        <div v-show="activeTab === 'dashboard'" class="space-y-8">
          <!-- Stats Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-xl p-6 text-white">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8 text-blue-100" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-blue-100 truncate">Total Users</dt>
                    <dd class="text-3xl font-bold">{{ users.total || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
            
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-xl p-6 text-white">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8 text-green-100" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V8z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-green-100 truncate">Total Posts</dt>
                    <dd class="text-3xl font-bold">{{ posts.total || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
            
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-xl p-6 text-white">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8 text-purple-100" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-purple-100 truncate">Total Comments</dt>
                    <dd class="text-3xl font-bold">{{ comments.total || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
            
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl shadow-xl p-6 text-white">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8 text-orange-100" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V8z"/>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-orange-100 truncate">Communities</dt>
                    <dd class="text-3xl font-bold">{{ communities.total || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions and Recent Activity -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-xl shadow-xl p-6">
              <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <span>⚡</span>
                Recent Activity
              </h3>
              <div class="space-y-4">
                <div v-for="visit in visits.data.slice(0, 5)" :key="visit.id" class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                  <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                      <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                      </svg>
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                      {{ visit.user ? visit.user.name : 'Anonymous User' }}
                    </p>
                    <p class="text-sm text-gray-500 truncate">
                      {{ formatActivityType(visit.activity_type) }} - {{ visit.page_title || 'Unknown Page' }}
                    </p>
                  </div>
                  <div class="flex-shrink-0 text-xs text-gray-400">
                    {{ new Date(visit.visited_at).toLocaleDateString() }}
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-xl shadow-xl p-6">
              <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <span>📊</span>
                Activity Statistics
              </h3>
              <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                  <span class="text-sm font-medium text-blue-800">Page Views</span>
                  <span class="text-xl font-bold text-blue-600">{{ activityStats?.page_views || 0 }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                  <span class="text-sm font-medium text-green-800">Post Votes</span>
                  <span class="text-xl font-bold text-green-600">{{ activityStats?.votes || 0 }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                  <span class="text-sm font-medium text-purple-800">Comments</span>
                  <span class="text-xl font-bold text-purple-600">{{ activityStats?.comments || 0 }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-orange-50 rounded-lg">
                  <span class="text-sm font-medium text-orange-800">Replies</span>
                  <span class="text-xl font-bold text-orange-600">{{ activityStats?.replies || 0 }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Search and Actions Bar (shown on data tabs) -->
        <div v-show="activeTab !== 'dashboard'" class="mb-8">
          <div class="bg-white rounded-xl shadow-xl p-6">
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
              <div class="flex-1 max-w-md">
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <TextInput
                    v-model="search"
                    type="search"
                    placeholder="Search everything..."
                    class="pl-10 w-full"
                  />
                </div>
              </div>
              
              <div class="flex items-center gap-3">
                <select
                  v-model="perPage"
                  class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                  @change="debouncedSearch()"
                >
                  <option value="10">10 per page</option>
                  <option value="25">25 per page</option>
                  <option value="50">50 per page</option>
                  <option value="100">100 per page</option>
                </select>

                <button
                  @click="exportData"
                  class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl"
                >
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                  </svg>
                  Export
                </button>

                <button
                  v-if="selectedItems.length"
                  @click="bulkDelete"
                  class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl"
                >
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3l3-3a1 1 0 111.414 1.414L12.414 10l3 3a1 1 0 01-1.414 1.414L11 11.414V14a1 1 0 11-2 0v-2.586l-3 3a1 1 0 01-1.414-1.414L7.586 10l-3-3a1 1 0 111.414-1.414L9 8.586V6a1 1 0 011-1z" clip-rule="evenodd"/>
                  </svg>
                  Delete ({{ selectedItems.length }})
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="mb-8">
          <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            <nav class="flex space-x-1 p-2" aria-label="Tabs">
              <button
                v-for="tab in [
                  { id: 'dashboard', name: 'Dashboard', icon: '📊' },
                  { id: 'users', name: 'Users', icon: '👥' },
                  { id: 'posts', name: 'Posts', icon: '📄' },
                  { id: 'comments', name: 'Comments', icon: '💬' },
                  { id: 'communities', name: 'Communities', icon: '🏘️' },
                  { id: 'visits', name: 'Analytics', icon: '📈' }
                ]"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                  'flex items-center gap-3 px-6 py-4 rounded-xl font-semibold text-sm transition-all duration-300 transform hover:scale-105',
                  activeTab === tab.id
                    ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg'
                    : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50',
                ]"
              >
                <span class="text-lg">{{ tab.icon }}</span>
                <span>{{ tab.name }}</span>
              </button>
            </nav>
          </div>
        </div>

        <!-- Posts Section -->
        <div v-show="activeTab === 'posts'" class="bg-white rounded-xl shadow-xl overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <span>📄</span>
                Posts Management
              </h3>
              <div class="flex items-center gap-4">
                <div class="text-sm text-gray-600 bg-white px-3 py-1 rounded-full shadow-sm">
                  Total: {{ posts.total || 0 }} posts
                </div>
                <button
                  v-if="selectedItems.length && activeTab === 'posts'"
                  @click="bulkDelete"
                  class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-all duration-200"
                >
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3l3-3a1 1 0 111.414 1.414L12.414 10l3 3a1 1 0 01-1.414 1.414L11 11.414V14a1 1 0 11-2 0v-2.586l-3 3a1 1 0 01-1.414-1.414L7.586 10l-3-3a1 1 0 111.414-1.414L9 8.586V6a1 1 0 011-1z" clip-rule="evenodd"/>
                  </svg>
                  Delete ({{ selectedItems.length }})
                </button>
              </div>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                  <th class="w-4 px-6 py-4">
                    <input
                      type="checkbox"
                      class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :checked="props.posts.data && selectedItems.length === props.posts.data.length && props.posts.data.length > 0"
                      @change="e => {
                        if (props.posts.data) {
                          selectedItems = e.target.checked ? props.posts.data.map(p => p.id) : [];
                        }
                      }"
                    >
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:text-gray-800" @click="sort('title')">
                    <div class="flex items-center gap-2">
                      Post Details
                      <svg v-if="sortBy === 'title'" class="w-4 h-4" :class="sortOrder === 'asc' ? 'transform rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                      </svg>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Author & Community
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Engagement
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:text-gray-800" @click="sort('created_at')">
                    <div class="flex items-center gap-2">
                      Created
                      <svg v-if="sortBy === 'created_at'" class="w-4 h-4" :class="sortOrder === 'asc' ? 'transform rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                      </svg>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="post in props.posts.data" :key="post.id" class="hover:bg-gray-50 transition-colors duration-200">
                  <td class="px-6 py-4">
                    <input
                      type="checkbox"
                      class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :checked="selectedItems.includes(post.id)"
                      @change="e => {
                        selectedItems = e.target.checked
                          ? [...selectedItems, post.id]
                          : selectedItems.filter(id => id !== post.id);
                      }"
                    >
                  </td>
                  <td class="px-6 py-4">
                    <div class="space-y-2">
                      <div class="flex items-start space-x-3">
                        <div v-if="post.images && post.images.length > 0" class="flex-shrink-0">
                          <img 
                            :src="post.images[0].url" 
                            :alt="post.title"
                            class="w-16 h-16 rounded-lg object-cover shadow-sm"
                          >
                        </div>
                        <div v-else class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-lg shadow-sm">
                          {{ post.title.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1 min-w-0">
                          <div class="text-sm font-semibold text-gray-900 line-clamp-2">
                            {{ post.title }}
                          </div>
                          <div class="text-sm text-gray-500 mt-1 line-clamp-2" v-if="post.content">
                            {{ post.content.replace(/<[^>]*>/g, '').substring(0, 100) }}{{ post.content.length > 100 ? '...' : '' }}
                          </div>
                          <div class="flex items-center space-x-2 mt-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                              ID: {{ post.id }}
                            </span>
                            <span v-if="post.deleted_at" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3l3-3a1 1 0 111.414 1.414L12.414 10l3 3a1 1 0 01-1.414 1.414L11 11.414V14a1 1 0 11-2 0v-2.586l-3 3a1 1 0 01-1.414-1.414L7.586 10l-3-3a1 1 0 111.414-1.414L9 8.586V6a1 1 0 011-1z" clip-rule="evenodd"/>
                              </svg>
                              Deleted
                            </span>
                            <span v-if="post.is_pinned" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                              </svg>
                              Pinned
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="space-y-2">
                      <div class="flex items-center space-x-2">
                        <div class="flex-shrink-0 h-8 w-8">
                          <div v-if="post.user?.avatar_url" class="h-8 w-8 rounded-full overflow-hidden">
                            <img :src="post.user.avatar_url" :alt="post.user.name" class="h-full w-full object-cover">
                          </div>
                          <div v-else class="h-8 w-8 rounded-full bg-gradient-to-r from-green-500 to-green-600 flex items-center justify-center text-white font-semibold text-sm">
                            {{ post.user?.name?.charAt(0)?.toUpperCase() }}
                          </div>
                        </div>
                        <div>
                          <div class="text-sm font-medium text-gray-900">{{ post.user?.name || 'Unknown' }}</div>
                          <div class="text-xs text-gray-500">{{ post.user?.email || 'No email' }}</div>
                        </div>
                      </div>
                      <div v-if="post.subfapp" class="flex items-center space-x-2">
                        <div class="w-6 h-6 bg-purple-100 rounded flex items-center justify-center">
                          <svg class="w-3 h-3 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V8z"/>
                          </svg>
                        </div>
                        <div class="text-sm text-gray-600">f/{{ post.subfapp.name }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="space-y-3">
                      <div class="grid grid-cols-2 gap-2">
                        <div class="flex items-center space-x-2 text-sm">
                          <div class="w-6 h-6 bg-blue-100 rounded flex items-center justify-center">
                            <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                          </div>
                          <div>
                            <div class="font-medium text-gray-900">{{ post.votes_count || 0 }}</div>
                            <div class="text-xs text-gray-500">Votes</div>
                          </div>
                        </div>
                        <div class="flex items-center space-x-2 text-sm">
                          <div class="w-6 h-6 bg-purple-100 rounded flex items-center justify-center">
                            <svg class="w-3 h-3 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                          </div>
                          <div>
                            <div class="font-medium text-gray-900">{{ post.comments_count || 0 }}</div>
                            <div class="text-xs text-gray-500">Comments</div>
                          </div>
                        </div>
                      </div>
                      <div v-if="post.score" class="flex items-center space-x-2 text-sm">
                        <div class="w-6 h-6 bg-green-100 rounded flex items-center justify-center">
                          <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                          </svg>
                        </div>
                        <div>
                          <div class="font-medium text-gray-900">{{ Math.round(post.score * 100) / 100 }}</div>
                          <div class="text-xs text-gray-500">Score</div>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="space-y-1">
                      <div class="text-sm font-medium text-gray-900">
                        {{ new Date(post.created_at).toLocaleDateString() }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ new Date(post.created_at).toLocaleTimeString() }}
                      </div>
                      <div v-if="post.updated_at !== post.created_at" class="text-xs text-gray-400">
                        Updated: {{ new Date(post.updated_at).toLocaleDateString() }}
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end space-x-2">
                      <a
                        :href="route('posts.show', post.id)"
                        target="_blank"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 hover:shadow-md"
                      >
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                          <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                        </svg>
                        View
                      </a>
                      <button
                        @click="deletePost(post.id)"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 hover:shadow-md transform hover:scale-105"
                      >
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                          <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3l3-3a1 1 0 111.414 1.414L12.414 10l3 3a1 1 0 01-1.414 1.414L11 11.414V14a1 1 0 11-2 0v-2.586l-3 3a1 1 0 01-1.414-1.414L7.586 10l-3-3a1 1 0 111.414-1.414L9 8.586V6a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="props.posts.links && props.posts.links.length > 3" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
              <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                  Showing {{ props.posts.from || 0 }} to {{ props.posts.to || 0 }} of {{ props.posts.total || 0 }} posts
                </div>
                <div class="flex space-x-1">
                  <button
                    v-for="(link, i) in props.posts.links"
                    :key="i"
                    :disabled="!link.url || link.active"
                    @click="link.url && router.visit(link.url, { preserveState: true, preserveScroll: true })"
                    class="px-3 py-2 text-sm rounded-lg transition-all duration-200"
                    :class="{
                      'bg-indigo-600 text-white shadow-md': link.active,
                      'text-gray-700 hover:bg-gray-100 border border-gray-300': !link.active && link.url,
                      'text-gray-400 cursor-not-allowed': !link.url
                    }"
                    v-html="link.label"
                  ></button>
                </div>
              </div>
            </div>
          </div>
        </div>

  <!-- Comments Section -->
  <div v-show="activeTab === 'comments'" class="bg-white rounded-xl shadow-xl overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <span>💬</span>
                Comments Management
              </h3>
              <div class="flex items-center gap-4">
                <div class="text-sm text-gray-600 bg-white px-3 py-1 rounded-full shadow-sm">
                  Total: {{ comments.total || 0 }} comments
                </div>
                <div class="flex items-center gap-2">
                  <select v-model="commentType" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">All</option>
                    <option value="top">Top-level</option>
                    <option value="replies">Replies</option>
                  </select>
                  <label class="inline-flex items-center space-x-2 text-sm">
                    <input type="checkbox" v-model="showDeletedComments" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    <span>Show Deleted</span>
                  </label>
                </div>
                <button
                  v-if="selectedItems.length && activeTab === 'comments'"
                  @click="bulkDelete"
                  class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-all duration-200"
                >
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3l3-3a1 1 0 111.414 1.414L12.414 10l3 3a1 1 0 01-1.414 1.414L11 11.414V14a1 1 0 11-2 0v-2.586l-3 3a1 1 0 01-1.414-1.414L7.586 10l-3-3a1 1 0 111.414-1.414L9 8.586V6a1 1 0 011-1z" clip-rule="evenodd"/>
                  </svg>
                  Delete ({{ selectedItems.length }})
                </button>
              </div>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                  <th class="w-4 px-6 py-4">
                    <input
                      type="checkbox"
                      class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :checked="props.comments.data && selectedItems.length === props.comments.data.length && props.comments.data.length > 0"
                      @change="e => {
                        if (props.comments.data) {
                          selectedItems = e.target.checked ? props.comments.data.map(c => c.id) : [];
                        }
                      }"
                    >
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:text-gray-800" @click="sort('content')">
                    <div class="flex items-center gap-2">
                      Comment Content
                      <svg v-if="sortBy === 'content'" class="w-4 h-4" :class="sortOrder === 'asc' ? 'transform rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                      </svg>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:text-gray-800" @click="sort('upvotes')">
                    <div class="flex items-center gap-2">
                      Votes
                      <svg v-if="sortBy === 'upvotes'" class="w-4 h-4" :class="sortOrder === 'asc' ? 'transform rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                      </svg>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Author & Post
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:text-gray-800" @click="sort('created_at')">
                    <div class="flex items-center gap-2">
                      Created
                      <svg v-if="sortBy === 'created_at'" class="w-4 h-4" :class="sortOrder === 'asc' ? 'transform rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                      </svg>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="comment in props.comments.data" :key="comment.id" class="hover:bg-gray-50 transition-colors duration-200">
                  <td class="px-6 py-4">
                    <input
                      type="checkbox"
                      class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :checked="selectedItems.includes(comment.id)"
                      @change="e => {
                        selectedItems = e.target.checked
                          ? [...selectedItems, comment.id]
                          : selectedItems.filter(id => id !== comment.id);
                      }"
                    >
                  </td>
                  <td class="px-6 py-4">
                    <div class="space-y-2">
                      <div class="text-sm text-gray-900 line-clamp-3">
                        {{ comment.content }}
                      </div>
                      <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                          ID: {{ comment.id }}
                        </span>
                        <span v-if="comment.parent_id" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                          Reply to #{{ comment.parent_id }}
                        </span>
                        <span v-if="comment.deleted_at" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                          Deleted
                        </span>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-4 text-sm">
                      <div class="flex items-center gap-1 text-green-700">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                        <span class="font-semibold">{{ comment.upvotes }}</span>
                      </div>
                      <div class="flex items-center gap-1 text-red-700">
                        <svg class="w-4 h-4 rotate-180" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                        <span class="font-semibold">{{ comment.downvotes }}</span>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="space-y-2">
                      <div class="flex items-center space-x-2">
                        <div class="flex-shrink-0 h-8 w-8">
                          <div v-if="comment.user?.avatar_url" class="h-8 w-8 rounded-full overflow-hidden">
                            <img :src="comment.user.avatar_url" :alt="comment.user.name" class="h-full w-full object-cover">
                          </div>
                          <div v-else class="h-8 w-8 rounded-full bg-gradient-to-r from-purple-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">
                            {{ comment.user?.name?.charAt(0)?.toUpperCase() }}
                          </div>
                        </div>
                        <div>
                          <div class="text-sm font-medium text-gray-900">{{ comment.user?.name || 'Unknown' }}</div>
                          <div class="text-xs text-gray-500">{{ comment.user?.email || 'No email' }}</div>
                        </div>
                      </div>
                      <div v-if="comment.post" class="text-sm">
                        <div class="text-gray-600 line-clamp-2">
                          On: {{ comment.post.title }}
                        </div>
                        <div v-if="comment.post.subfapp" class="text-xs text-purple-600">
                          in f/{{ comment.post.subfapp.name }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="space-y-1">
                      <div class="text-sm font-medium text-gray-900">
                        {{ new Date(comment.created_at).toLocaleDateString() }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ new Date(comment.created_at).toLocaleTimeString() }}
                      </div>
                      <div v-if="comment.updated_at !== comment.created_at" class="text-xs text-gray-400">
                        Updated: {{ new Date(comment.updated_at).toLocaleDateString() }}
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end space-x-2">
                      <button
                        @click="openEditModal(comment)"
                        :disabled="comment.deleted_at"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 hover:shadow-md"
                        :class="comment.deleted_at ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-50'"
                      >
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                        </svg>
                        Edit
                      </button>
                      <button
                        v-if="!comment.deleted_at"
                        @click="deleteComment(comment.id)"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 hover:shadow-md transform hover:scale-105"
                      >
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                          <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3l3-3a1 1 0 111.414 1.414L12.414 10l3 3a1 1 0 01-1.414 1.414L11 11.414V14a1 1 0 11-2 0v-2.586l-3 3a1 1 0 01-1.414-1.414L7.586 10l-3-3a1 1 0 111.414-1.414L9 8.586V6a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        Delete
                      </button>
                      <button
                        v-else
                        @click="router.post(route('admin.comments.restore', comment.id), buildQuery(), { preserveScroll: true })"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 hover:shadow-md transform hover:scale-105"
                      >
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a1 1 0 011-1h5a4 4 0 010 8H9v2h1a4 4 0 010 8H5a1 1 0 110-2h5a2 2 0 100-4H9a1 1 0 01-1-1V9a1 1 0 011-1h1a2 2 0 100-4H5a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
                        Restore
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="props.comments.links && props.comments.links.length > 3" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
              <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                  Showing {{ props.comments.from || 0 }} to {{ props.comments.to || 0 }} of {{ props.comments.total || 0 }} comments
                </div>
                <div class="flex space-x-1">
                  <button
                    v-for="(link, i) in props.comments.links"
                    :key="i"
                    :disabled="!link.url || link.active"
                    @click="link.url && router.visit(link.url, { preserveState: true, preserveScroll: true })"
                    class="px-3 py-2 text-sm rounded-lg transition-all duration-200"
                    :class="{
                      'bg-indigo-600 text-white shadow-md': link.active,
                      'text-gray-700 hover:bg-gray-100 border border-gray-300': !link.active && link.url,
                      'text-gray-400 cursor-not-allowed': !link.url
                    }"
                    v-html="link.label"
                  ></button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Communities Section -->
        <div v-show="activeTab === 'communities'" class="bg-white rounded-xl shadow-xl overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <span>🏘️</span>
                Communities Management
              </h3>
              <div class="flex items-center gap-4">
                <div class="text-sm text-gray-600 bg-white px-3 py-1 rounded-full shadow-sm">
                  Total: {{ communities.total || 0 }} communities
                </div>
                <button
                  v-if="selectedItems.length && activeTab === 'communities'"
                  @click="bulkDelete"
                  class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-all duration-200"
                >
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3l3-3a1 1 0 111.414 1.414L12.414 10l3 3a1 1 0 01-1.414 1.414L11 11.414V14a1 1 0 11-2 0v-2.586l-3 3a1 1 0 01-1.414-1.414L7.586 10l-3-3a1 1 0 111.414-1.414L9 8.586V6a1 1 0 011-1z" clip-rule="evenodd"/>
                  </svg>
                  Delete ({{ selectedItems.length }})
                </button>
              </div>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                  <th class="w-4 px-6 py-4">
                    <input
                      type="checkbox"
                      class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :checked="props.communities.data && selectedItems.length === props.communities.data.length && props.communities.data.length > 0"
                      @change="e => {
                        if (props.communities.data) {
                          selectedItems = e.target.checked ? props.communities.data.map(c => c.id) : [];
                        }
                      }"
                    >
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:text-gray-800" @click="sort('name')">
                    <div class="flex items-center gap-2">
                      Community Details
                      <svg v-if="sortBy === 'name'" class="w-4 h-4" :class="sortOrder === 'asc' ? 'transform rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                      </svg>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Activity Stats
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:text-gray-800" @click="sort('created_at')">
                    <div class="flex items-center gap-2">
                      Created
                      <svg v-if="sortBy === 'created_at'" class="w-4 h-4" :class="sortOrder === 'asc' ? 'transform rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                      </svg>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="community in props.communities.data" :key="community.id" class="hover:bg-gray-50 transition-colors duration-200">
                  <td class="px-6 py-4">
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
                  <td class="px-6 py-4">
                    <div class="space-y-2">
                      <div class="flex items-center space-x-3">
                        <div v-if="community.cover_image" class="flex-shrink-0">
                          <img 
                            :src="community.cover_image" 
                            :alt="`f/${community.name}`"
                            class="w-12 h-12 rounded-lg object-cover shadow-sm"
                          >
                        </div>
                        <div v-else class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center text-white font-bold text-lg shadow-sm">
                          {{ community.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1 min-w-0">
                          <div class="text-lg font-bold text-gray-900">
                            f/{{ community.name }}
                          </div>
                          <div class="text-sm text-gray-600 line-clamp-2">
                            {{ community.description }}
                          </div>
                          <div class="flex items-center space-x-2 mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                              ID: {{ community.id }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="space-y-3">
                      <div class="grid grid-cols-2 gap-2">
                        <div class="flex items-center space-x-2 text-sm">
                          <div class="w-6 h-6 bg-green-100 rounded flex items-center justify-center">
                            <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V8z" clip-rule="evenodd"/>
                            </svg>
                          </div>
                          <div>
                            <div class="font-medium text-gray-900">{{ community.posts_count || 0 }}</div>
                            <div class="text-xs text-gray-500">Posts</div>
                          </div>
                        </div>
                        <div class="flex items-center space-x-2 text-sm">
                          <div class="w-6 h-6 bg-blue-100 rounded flex items-center justify-center">
                            <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                          </div>
                          <div>
                            <div class="font-medium text-gray-900">{{ community.subscribers_count || 0 }}</div>
                            <div class="text-xs text-gray-500">Members</div>
                          </div>
                        </div>
                      </div>
                      <div v-if="community.recent_posts_count !== undefined" class="flex items-center space-x-2 text-sm">
                        <div class="w-6 h-6 bg-purple-100 rounded flex items-center justify-center">
                          <svg class="w-3 h-3 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                          </svg>
                        </div>
                        <div>
                          <div class="font-medium text-gray-900">{{ community.recent_posts_count }}</div>
                          <div class="text-xs text-gray-500">This week</div>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="space-y-1">
                      <div class="text-sm font-medium text-gray-900">
                        {{ new Date(community.created_at).toLocaleDateString() }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ new Date(community.created_at).toLocaleTimeString() }}
                      </div>
                      <div v-if="community.updated_at !== community.created_at" class="text-xs text-gray-400">
                        Updated: {{ new Date(community.updated_at).toLocaleDateString() }}
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end space-x-2">
                      <a
                        :href="route('subfapps.show', community.name)"
                        target="_blank"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 hover:shadow-md"
                      >
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                          <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                        </svg>
                        View
                      </a>
                      <button
                        @click="openEditCommunityModal(community)"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 hover:shadow-md"
                      >
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                        </svg>
                        Edit
                      </button>
                      <button
                        @click="deleteCommunity(community.id)"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 hover:shadow-md transform hover:scale-105"
                      >
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                          <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3l3-3a1 1 0 111.414 1.414L12.414 10l3 3a1 1 0 01-1.414 1.414L11 11.414V14a1 1 0 11-2 0v-2.586l-3 3a1 1 0 01-1.414-1.414L7.586 10l-3-3a1 1 0 111.414-1.414L9 8.586V6a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="props.communities.links && props.communities.links.length > 3" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
              <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                  Showing {{ props.communities.from || 0 }} to {{ props.communities.to || 0 }} of {{ props.communities.total || 0 }} communities
                </div>
                <div class="flex space-x-1">
                  <button
                    v-for="(link, i) in props.communities.links"
                    :key="i"
                    :disabled="!link.url || link.active"
                    @click="link.url && router.visit(link.url, { preserveState: true, preserveScroll: true })"
                    class="px-3 py-2 text-sm rounded-lg transition-all duration-200"
                    :class="{
                      'bg-indigo-600 text-white shadow-md': link.active,
                      'text-gray-700 hover:bg-gray-100 border border-gray-300': !link.active && link.url,
                      'text-gray-400 cursor-not-allowed': !link.url
                    }"
                    v-html="link.label"
                  ></button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Analytics/Visits Section -->
        <div v-show="activeTab === 'visits'" class="bg-white rounded-xl shadow-xl overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <span>📈</span>
                Analytics & Visits
              </h3>
              <div class="text-sm text-gray-600 bg-white px-3 py-1 rounded-full shadow-sm">
                Total: {{ visitsCount || 0 }} visits
              </div>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Visitor Info
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Activity Details
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:text-gray-800" @click="sort('visited_at')">
                    <div class="flex items-center gap-2">
                      Visit Time
                      <svg v-if="sortBy === 'visited_at'" class="w-4 h-4" :class="sortOrder === 'asc' ? 'transform rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                      </svg>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Technical Info
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="visit in props.visits.data" :key="visit.id" class="hover:bg-gray-50 transition-colors duration-200">
                  <td class="px-6 py-4">
                    <div class="space-y-2">
                      <div v-if="visit.user" class="flex items-center space-x-2">
                        <div class="flex-shrink-0 h-8 w-8">
                          <div v-if="visit.user.avatar_url" class="h-8 w-8 rounded-full overflow-hidden">
                            <img :src="visit.user.avatar_url" :alt="visit.user.name" class="h-full w-full object-cover">
                          </div>
                          <div v-else class="h-8 w-8 rounded-full bg-gradient-to-r from-indigo-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-sm">
                            {{ visit.user.name.charAt(0).toUpperCase() }}
                          </div>
                        </div>
                        <div>
                          <div class="text-sm font-medium text-gray-900">{{ visit.user.name }}</div>
                          <div class="text-xs text-gray-500">{{ visit.user.email }}</div>
                        </div>
                      </div>
                      <div v-else class="flex items-center space-x-2">
                        <div class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center text-white font-semibold text-sm">
                          ?
                        </div>
                        <div>
                          <div class="text-sm font-medium text-gray-600">Anonymous User</div>
                          <div class="text-xs text-gray-500">Not logged in</div>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="space-y-2">
                      <div class="text-sm font-medium text-gray-900">
                        {{ formatActivityType(visit.activity_type) }}
                      </div>
                      <div class="text-sm text-gray-600 line-clamp-2">
                        {{ visit.page_title || 'Untitled Page' }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ visit.page_visited }}
                      </div>
                      <div v-if="visit.activity_data && visit.activity_type === 'post_vote'" class="text-xs text-indigo-600">
                        {{ getVoteType(visit.activity_data) }}
                      </div>
                      <div v-else-if="visit.activity_data && (visit.activity_type === 'comment' || visit.activity_type === 'reply')" class="text-xs text-purple-600">
                        {{ parseActivityData(visit.activity_data) }}
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="space-y-1">
                      <div class="text-sm font-medium text-gray-900">
                        {{ new Date(visit.visited_at).toLocaleDateString() }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ new Date(visit.visited_at).toLocaleTimeString() }}
                      </div>
                      <div class="text-xs text-gray-400">
                        {{ Math.floor((Date.now() - new Date(visit.visited_at)) / (1000 * 60 * 60 * 24)) }} days ago
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="space-y-1">
                      <div class="text-xs text-gray-600">
                        IP: {{ visit.ip_address || 'Unknown' }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ visit.user_agent ? visit.user_agent.substring(0, 50) + '...' : 'No user agent' }}
                      </div>
                      <div class="flex items-center space-x-1 mt-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                          ID: {{ visit.id }}
                        </span>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="props.visits.links && props.visits.links.length > 3" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
              <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                  Showing {{ props.visits.from || 0 }} to {{ props.visits.to || 0 }} of {{ props.visits.total || 0 }} visits
                </div>
                <div class="flex space-x-1">
                  <button
                    v-for="(link, i) in props.visits.links"
                    :key="i"
                    :disabled="!link.url || link.active"
                    @click="link.url && router.visit(link.url, { preserveState: true, preserveScroll: true })"
                    class="px-3 py-2 text-sm rounded-lg transition-all duration-200"
                    :class="{
                      'bg-indigo-600 text-white shadow-md': link.active,
                      'text-gray-700 hover:bg-gray-100 border border-gray-300': !link.active && link.url,
                      'text-gray-400 cursor-not-allowed': !link.url
                    }"
                    v-html="link.label"
                  ></button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Users Section -->
        <div v-show="activeTab === 'users'" class="bg-white rounded-xl shadow-xl overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <span>👥</span>
                Users Management
              </h3>
              <div class="flex items-center gap-4">
                <div class="text-sm text-gray-600 bg-white px-3 py-1 rounded-full shadow-sm">
                  Total: {{ users.total || 0 }} users
                </div>
                <button
                  v-if="selectedItems.length && activeTab === 'users'"
                  @click="bulkDelete"
                  class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-all duration-200"
                >
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3l3-3a1 1 0 111.414 1.414L12.414 10l3 3a1 1 0 01-1.414 1.414L11 11.414V14a1 1 0 11-2 0v-2.586l-3 3a1 1 0 01-1.414-1.414L7.586 10l-3-3a1 1 0 111.414-1.414L9 8.586V6a1 1 0 011-1z" clip-rule="evenodd"/>
                  </svg>
                  Delete ({{ selectedItems.length }})
                </button>
              </div>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                  <th class="w-4 px-6 py-4">
                    <input
                      type="checkbox"
                      class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :checked="props.users.data && selectedItems.length === props.users.data.length && props.users.data.length > 0"
                      @change="e => {
                        if (props.users.data) {
                          selectedItems = e.target.checked ? props.users.data.map(u => u.id) : [];
                        }
                      }"
                    >
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:text-gray-800" @click="sort('name')">
                    <div class="flex items-center gap-2">
                      User Profile
                      <svg v-if="sortBy === 'name'" class="w-4 h-4" :class="sortOrder === 'asc' ? 'transform rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                      </svg>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:text-gray-800" @click="sort('email')">
                    <div class="flex items-center gap-2">
                      Contact & Status
                      <svg v-if="sortBy === 'email'" class="w-4 h-4" :class="sortOrder === 'asc' ? 'transform rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                      </svg>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Role & Permissions
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Activity Stats
                  </th>
                  <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="user in props.users.data" :key="user.id" class="hover:bg-gray-50 transition-colors duration-200">
                  <td class="px-6 py-4">
                    <input
                      type="checkbox"
                      class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :checked="selectedItems.includes(user.id)"
                      @change="e => {
                        selectedItems = e.target.checked
                          ? [...selectedItems, user.id]
                          : selectedItems.filter(id => id !== user.id);
                      }"
                    >
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center space-x-4">
                      <div class="flex-shrink-0 h-12 w-12">
                        <div v-if="user.avatar_url" class="h-12 w-12 rounded-full overflow-hidden">
                          <img :src="user.avatar_url" :alt="user.name" class="h-full w-full object-cover">
                        </div>
                        <div v-else class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                          {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-900 truncate">
                          {{ user.name }}
                        </div>
                        <div class="text-sm text-gray-500">
                          ID: {{ user.id }}
                        </div>
                        <div v-if="user.id === $page.props.auth.user.id" class="mt-1">
                          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M10 2L3 7v11a2 2 0 002 2h4v-6h2v6h4a2 2 0 002-2V7l-7-5z"/>
                            </svg>
                            You
                          </span>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="space-y-1">
                      <div class="text-sm font-medium text-gray-900">{{ user.email }}</div>
                      <div class="flex items-center space-x-2">
                        <span v-if="user.email_verified_at" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                          </svg>
                          Verified
                        </span>
                        <span v-else class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                          <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                          </svg>
                          Unverified
                        </span>
                      </div>
                      <div class="text-xs text-gray-500">
                        Last login: {{ user.last_login_at ? new Date(user.last_login_at).toLocaleDateString() : 'Never' }}
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="space-y-2">
                      <button
                        @click="toggleUserRole(user)"
                        :disabled="user.id === $page.props.auth.user.id || !canManageUsers"
                        :class="[
                          'inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105',
                          user.is_admin 
                            ? 'bg-gradient-to-r from-red-500 to-red-600 text-white hover:from-red-600 hover:to-red-700' 
                            : 'bg-gradient-to-r from-green-500 to-green-600 text-white hover:from-green-600 hover:to-green-700',
                          (user.id === $page.props.auth.user.id || !canManageUsers) ? 'opacity-50 cursor-not-allowed transform-none' : 'cursor-pointer shadow-lg hover:shadow-xl'
                        ]"
                      >
                        <svg v-if="user.is_admin" class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                        </svg>
                        <svg v-else class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        {{ user.is_admin ? 'Administrator' : 'User' }}
                      </button>
                      <div v-if="user.is_admin" class="flex flex-wrap gap-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                          Full Access
                        </span>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="space-y-3">
                      <div class="grid grid-cols-2 gap-3">
                        <div class="flex items-center space-x-2 text-sm">
                          <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V8z" clip-rule="evenodd"/>
                            </svg>
                          </div>
                          <div>
                            <div class="font-semibold text-gray-900">{{ user.posts_count || 0 }}</div>
                            <div class="text-gray-500 text-xs">Posts</div>
                          </div>
                        </div>
                        <div class="flex items-center space-x-2 text-sm">
                          <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                          </div>
                          <div>
                            <div class="font-semibold text-gray-900">{{ user.comments_count || 0 }}</div>
                            <div class="text-gray-500 text-xs">Comments</div>
                          </div>
                        </div>
                      </div>
                      <div v-if="user.votes_count" class="flex items-center space-x-2 text-sm">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                          <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                          </svg>
                        </div>
                        <div>
                          <div class="font-semibold text-gray-900">{{ user.votes_count || 0 }}</div>
                          <div class="text-gray-500 text-xs">Votes</div>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end space-x-2">
                      <button
                        @click="openEditUserModal(user)"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 hover:shadow-md"
                      >
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                        </svg>
                        Edit
                      </button>
                      <div class="flex flex-col items-end space-y-1">
                        <button
                          @click="deleteUser(user.id)"
                          :disabled="user.id === $page.props.auth.user.id"
                          :class="[
                            'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-white transition-all duration-200',
                            user.id === $page.props.auth.user.id 
                              ? 'bg-gray-400 cursor-not-allowed' 
                              : 'bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 hover:shadow-md transform hover:scale-105'
                          ]"
                        >
                          <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3l3-3a1 1 0 111.414 1.414L12.414 10l3 3a1 1 0 01-1.414 1.414L11 11.414V14a1 1 0 11-2 0v-2.586l-3 3a1 1 0 01-1.414-1.414L7.586 10l-3-3a1 1 0 111.414-1.414L9 8.586V6a1 1 0 011-1z" clip-rule="evenodd"/>
                          </svg>
                          Delete
                        </button>
                        <div v-if="hasUserData(user)" class="text-xs text-yellow-600 text-right">
                          Will remove & anonymize: {{ getUserDataReason(user) }}
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="props.users.links && props.users.links.length > 3" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
              <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                  Showing {{ props.users.from || 0 }} to {{ props.users.to || 0 }} of {{ props.users.total || 0 }} users
                </div>
                <div class="flex space-x-1">
                  <button
                    v-for="(link, i) in props.users.links"
                    :key="i"
                    :disabled="!link.url || link.active"
                    @click="link.url && router.visit(link.url, { preserveState: true, preserveScroll: true })"
                    class="px-3 py-2 text-sm rounded-lg transition-all duration-200"
                    :class="{
                      'bg-indigo-600 text-white shadow-md': link.active,
                      'text-gray-700 hover:bg-gray-100 border border-gray-300': !link.active && link.url,
                      'text-gray-400 cursor-not-allowed': !link.url
                    }"
                    v-html="link.label"
                  ></button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Other sections would follow similar pattern... -->
      </div>
    </div>

    <!-- User Edit Modal -->
    <Modal :show="showEditUserModal" @close="closeEditUserModal">
      <div class="p-6">
        <div class="flex items-center gap-3 mb-6">
          <div class="flex-shrink-0 h-12 w-12">
            <div v-if="currentUser?.avatar_url" class="h-12 w-12 rounded-full overflow-hidden">
              <img :src="currentUser.avatar_url" :alt="currentUser.name" class="h-full w-full object-cover">
            </div>
            <div v-else class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
              {{ currentUser?.name?.charAt(0)?.toUpperCase() }}
            </div>
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-900">Edit User</h2>
            <p class="text-sm text-gray-600">Update user information and permissions</p>
          </div>
        </div>

        <form @submit.prevent="updateUser" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <InputLabel for="name" value="Full Name" />
              <TextInput
                id="name"
                v-model="userForm.name"
                type="text"
                class="mt-1 block w-full"
                required
                autocomplete="name"
              />
              <InputError :message="userForm.errors.name" class="mt-2" />
            </div>

            <div>
              <InputLabel for="email" value="Email Address" />
              <TextInput
                id="email"
                v-model="userForm.email"
                type="email"
                class="mt-1 block w-full"
                required
                autocomplete="email"
              />
              <InputError :message="userForm.errors.email" class="mt-2" />
            </div>
          </div>

          <div>
            <InputLabel for="password" value="New Password (optional)" />
            <TextInput
              id="password"
              v-model="userForm.password"
              type="password"
              class="mt-1 block w-full"
              placeholder="Leave blank to keep current password"
              autocomplete="new-password"
            />
            <InputError :message="userForm.errors.password" class="mt-2" />
            <p class="mt-1 text-sm text-gray-500">
              Leave empty if you don't want to change the password
            </p>
          </div>

          <!-- Role and Permissions -->
          <div class="space-y-4">
            <div class="border-t border-gray-200 pt-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Role & Permissions</h3>
              
              <div class="flex items-center space-x-3">
                <input
                  type="checkbox"
                  id="is_admin"
                  v-model="userForm.is_admin"
                  :disabled="currentUser?.id === $page.props.auth.user.id"
                  class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                />
                <div class="flex-1">
                  <label for="is_admin" class="text-sm font-medium text-gray-900">
                    Administrator Privileges
                  </label>
                  <p class="text-sm text-gray-600">
                    Grant full administrative access to the platform
                  </p>
                </div>
              </div>

              <div v-if="currentUser?.id === $page.props.auth.user.id" class="mt-3 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                  <span class="text-sm text-yellow-800">
                    You cannot modify your own admin status
                  </span>
                </div>
              </div>
            </div>

            <!-- User Statistics Display -->
            <div class="border-t border-gray-200 pt-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">User Activity</h3>
              <div class="grid grid-cols-3 gap-4">
                <div class="bg-green-50 rounded-lg p-4 text-center">
                  <div class="text-2xl font-bold text-green-600">{{ currentUser?.posts_count || 0 }}</div>
                  <div class="text-sm text-green-800">Posts</div>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 text-center">
                  <div class="text-2xl font-bold text-purple-600">{{ currentUser?.comments_count || 0 }}</div>
                  <div class="text-sm text-purple-800">Comments</div>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 text-center">
                  <div class="text-2xl font-bold text-blue-600">{{ currentUser?.votes_count || 0 }}</div>
                  <div class="text-sm text-blue-800">Votes</div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <SecondaryButton @click="closeEditUserModal" :disabled="userForm.processing">
              Cancel
            </SecondaryButton>
            <PrimaryButton
              type="submit"
              :class="{ 'opacity-25': userForm.processing }"
              :disabled="userForm.processing"
            >
              <svg v-if="userForm.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ userForm.processing ? 'Saving...' : 'Save Changes' }}
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Comment Edit Modal -->
    <Modal :show="showEditModal" @close="closeEditModal">
      <div class="p-6">
        <div class="flex items-center gap-3 mb-6">
          <div class="flex-shrink-0 h-12 w-12">
            <div v-if="currentComment?.user?.avatar_url" class="h-12 w-12 rounded-full overflow-hidden">
              <img :src="currentComment.user.avatar_url" :alt="currentComment.user.name" class="h-full w-full object-cover">
            </div>
            <div v-else class="h-12 w-12 rounded-full bg-gradient-to-r from-purple-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
              {{ currentComment?.user?.name?.charAt(0)?.toUpperCase() }}
            </div>
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-900">Edit Comment</h2>
            <p class="text-sm text-gray-600">Moderate and edit user comment</p>
          </div>
        </div>

        <form @submit.prevent="updateComment" class="space-y-6">
          <div>
            <InputLabel for="content" value="Comment Content" />
            <textarea
              id="content"
              v-model="editForm.content"
              rows="4"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              required
            ></textarea>
            <InputError :message="editForm.errors.content" class="mt-2" />
          </div>

          <!-- Comment Context -->
          <div v-if="currentComment?.post" class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Comment Context</h3>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-900">{{ currentComment.post.title }}</div>
              <div v-if="currentComment.post.subfapp" class="text-xs text-purple-600 mt-1">
                in f/{{ currentComment.post.subfapp.name }}
              </div>
              <div v-if="currentComment.parent_id" class="text-xs text-gray-500 mt-2">
                This is a reply to comment #{{ currentComment.parent_id }}
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <SecondaryButton @click="closeEditModal" :disabled="editForm.processing">
              Cancel
            </SecondaryButton>
            <PrimaryButton
              type="submit"
              :class="{ 'opacity-25': editForm.processing }"
              :disabled="editForm.processing"
            >
              <svg v-if="editForm.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Community Edit Modal -->
    <Modal :show="showEditCommunityModal" @close="closeEditCommunityModal">
      <div class="p-6">
        <div class="flex items-center gap-3 mb-6">
          <div class="flex-shrink-0 w-12 h-12">
            <div v-if="currentCommunity?.cover_image" class="w-12 h-12 rounded-lg overflow-hidden">
              <img :src="currentCommunity.cover_image" :alt="`f/${currentCommunity.name}`" class="h-full w-full object-cover">
            </div>
            <div v-else class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">
              {{ currentCommunity?.name?.charAt(0)?.toUpperCase() }}
            </div>
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-900">Edit Community</h2>
            <p class="text-sm text-gray-600">Update community information</p>
          </div>
        </div>

        <form @submit.prevent="updateCommunity" class="space-y-6">
          <div>
            <InputLabel for="community_name" value="Community Name" />
            <TextInput
              id="community_name"
              v-model="communityForm.name"
              type="text"
              class="mt-1 block w-full"
              required
              placeholder="e.g., technology"
            />
            <InputError :message="communityForm.errors.name" class="mt-2" />
            <p class="mt-1 text-sm text-gray-500">
              This will be displayed as f/{{ communityForm.name || 'communityname' }}
            </p>
          </div>

          <div>
            <InputLabel for="community_description" value="Description" />
            <textarea
              id="community_description"
              v-model="communityForm.description"
              rows="3"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              required
              placeholder="Describe what this community is about..."
            ></textarea>
            <InputError :message="communityForm.errors.description" class="mt-2" />
          </div>

          <!-- Community Statistics Display -->
          <div v-if="currentCommunity" class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Community Statistics</h3>
            <div class="grid grid-cols-3 gap-4">
              <div class="bg-green-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ currentCommunity.posts_count || 0 }}</div>
                <div class="text-sm text-green-800">Posts</div>
              </div>
              <div class="bg-blue-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ currentCommunity.subscribers_count || 0 }}</div>
                <div class="text-sm text-blue-800">Members</div>
              </div>
              <div class="bg-purple-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ currentCommunity.recent_posts_count || 0 }}</div>
                <div class="text-sm text-purple-800">This Week</div>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <SecondaryButton @click="closeEditCommunityModal" :disabled="communityForm.processing">
              Cancel
            </SecondaryButton>
            <PrimaryButton
              type="submit"
              :class="{ 'opacity-25': communityForm.processing }"
              :disabled="communityForm.processing"
            >
              <svg v-if="communityForm.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ communityForm.processing ? 'Saving...' : 'Save Changes' }}
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      :isOpen="showDeleteConfirm"
      :title="deleteConfirmData.title"
      :message="deleteConfirmData.message"
      confirmText="Delete"
      cancelText="Cancel"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />
  </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
  line-clamp: 2;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  line-clamp: 3;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>