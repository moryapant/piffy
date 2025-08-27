<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm, usePage, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
  mustVerifyEmail: {
    type: Boolean,
  },
  status: {
    type: String,
  },
});

const page = usePage();
const avatarInput = ref(null);

// Create computed properties for reactive user data
const user = computed(() => page.props.auth.user);
const avatarUrl = computed(() => {
  return user.value.avatar
    ? user.value.avatar.startsWith("http")
      ? user.value.avatar
      : `/storage/${user.value.avatar}`
    : "https://www.gravatar.com/avatar/?d=mp";
});

const form = useForm({
  name: user.value.name,
  email: user.value.email,
});

const avatarForm = useForm({
  avatar: null,
});

const updateAvatar = (e) => {
  if (e.target.files.length > 0) {
    avatarForm.avatar = e.target.files[0];
    avatarForm.post(route("profile.avatar.update"), {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        e.target.value = null;
        router.reload({ only: ["auth.user"] });
      },
    });
  }
};
</script>

<template>
  <section>
    <!-- Avatar Section -->
    <div class="mb-6">
      <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-start">
        <div class="relative group">
          <img
            :src="avatarUrl"
            :alt="user.name"
            class="object-cover border-4 border-white rounded-full shadow-lg w-20 h-20 sm:w-24 sm:h-24 aspect-square"
            style="object-position: center"
          />
          <button
            type="button"
            @click="() => avatarInput.click()"
            class="absolute inset-0 flex items-center justify-center transition-opacity duration-200 rounded-full opacity-0 bg-black/50 group-hover:opacity-100 focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
          >
            <svg
              class="w-5 h-5 text-white sm:w-6 sm:h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
              />
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
              />
            </svg>
          </button>
        </div>
        <div class="text-center sm:text-left">
          <h3 class="text-sm font-medium text-gray-900 sm:text-base">Profile Picture</h3>
          <p class="text-xs text-gray-500 sm:text-sm">
            Click the image to change your avatar
          </p>
          <p class="mt-1 text-xs text-gray-400">
            JPG, PNG, WEBP up to 2MB
          </p>
        </div>
      </div>
      <input
        ref="avatarInput"
        type="file"
        accept=".jpg,.jpeg,.png,.webp"
        class="hidden"
        @change="updateAvatar"
      />
    </div>

    <form
      @submit.prevent="form.patch(route('profile.update'))"
      class="space-y-6"
    >
      <div class="grid gap-6 sm:grid-cols-2">
        <div>
          <InputLabel for="name" value="Name" />
          <TextInput
            id="name"
            type="text"
            class="block w-full mt-1"
            v-model="form.name"
            required
            autofocus
            autocomplete="name"
          />
          <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div>
          <InputLabel for="email" value="Email" />
          <TextInput
            id="email"
            type="email"
            class="block w-full mt-1"
            v-model="form.email"
            required
            autocomplete="username"
          />
          <InputError class="mt-2" :message="form.errors.email" />
        </div>
      </div>

      <div v-if="mustVerifyEmail && user.email_verified_at === null" class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <div class="flex items-start">
          <svg class="flex-shrink-0 w-5 h-5 mt-0.5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <div class="ml-3">
            <p class="text-sm font-medium text-yellow-800">
              Your email address is unverified.
            </p>
            <div class="mt-2">
              <Link
                :href="route('verification.send')"
                method="post"
                as="button"
                class="inline-flex items-center px-3 py-2 text-xs font-semibold text-yellow-800 bg-yellow-100 border border-yellow-300 rounded-md hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2"
              >
                Re-send verification email
              </Link>
            </div>
          </div>
        </div>

        <div
          v-show="status === 'verification-link-sent'"
          class="mt-3 p-3 bg-green-50 border border-green-200 rounded-md"
        >
          <div class="flex items-center">
            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <p class="ml-2 text-sm font-medium text-green-800">
              A new verification link has been sent to your email address.
            </p>
          </div>
        </div>
      </div>

      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <PrimaryButton :disabled="form.processing" class="w-full sm:w-auto">
          <svg v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Save Changes
        </PrimaryButton>

        <Transition
          enter-active-class="transition ease-in-out"
          enter-from-class="opacity-0"
          leave-active-class="transition ease-in-out"
          leave-to-class="opacity-0"
        >
          <div v-if="form.recentlySuccessful" class="flex items-center text-sm text-green-600">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Profile updated successfully.
          </div>
        </Transition>
      </div>
    </form>
  </section>
</template>
