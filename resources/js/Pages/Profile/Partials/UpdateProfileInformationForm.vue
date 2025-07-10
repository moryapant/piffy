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
    <header>
      <h2 class="text-lg font-medium text-gray-900">Profile Information</h2>

      <p class="mt-1 text-sm text-gray-600">
        Update your account's profile information and email address.
      </p>
    </header>

    <!-- Avatar Section -->
    <div class="mt-6">
      <div class="flex items-center gap-4">
        <div class="relative group">
          <img
            :src="avatarUrl"
            :alt="user.name"
            class="object-cover w-24 h-24 border-4 border-white rounded-full shadow-lg aspect-square"
            style="object-position: center"
          />
          <button
            type="button"
            @click="() => avatarInput.click()"
            class="absolute inset-0 flex items-center justify-center transition-opacity duration-200 rounded-full opacity-0 bg-black/50 group-hover:opacity-100"
          >
            <svg
              class="w-6 h-6 text-white"
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
        <div>
          <h3 class="text-sm font-medium text-gray-900">Profile Picture</h3>
          <p class="text-xs text-gray-500">
            Click the image to change your avatar
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
      class="mt-6 space-y-6"
    >
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

      <div v-if="mustVerifyEmail && user.email_verified_at === null">
        <p class="mt-2 text-sm text-gray-800">
          Your email address is unverified.
          <Link
            :href="route('verification.send')"
            method="post"
            as="button"
            class="text-sm text-gray-600 underline rounded-md hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
          >
            Click here to re-send the verification email.
          </Link>
        </p>

        <div
          v-show="status === 'verification-link-sent'"
          class="mt-2 text-sm font-medium text-green-600"
        >
          A new verification link has been sent to your email address.
        </div>
      </div>

      <div class="flex items-center gap-4">
        <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

        <Transition
          enter-active-class="transition ease-in-out"
          enter-from-class="opacity-0"
          leave-active-class="transition ease-in-out"
          leave-to-class="opacity-0"
        >
          <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">
            Saved.
          </p>
        </Transition>
      </div>
    </form>
  </section>
</template>
