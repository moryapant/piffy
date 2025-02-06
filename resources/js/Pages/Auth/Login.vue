<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import GoogleSignInButton from "@/Components/GoogleSignInButton.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
  canResetPassword: {
    type: Boolean,
  },
  status: {
    type: String,
  },
});

const form = useForm({
  email: "",
  password: "",
  remember: false,
});

const submit = () => {
  form.post(route("login"), {
    onFinish: () => form.reset("password"),
  });
};

const handleGoogleLogin = () => {
  window.location.href = route("auth.google");
};
</script>

<template>
  <GuestLayout>
    <Head title="Log in" />

    <div class="max-w-md mx-auto bg-white rounded-xl shadow-lg overflow-hidden p-8">
      <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Welcome Back</h2>

      <div v-if="status" class="mb-6 p-4 bg-green-50 rounded-lg">
        <p class="text-sm font-medium text-green-600">{{ status }}</p>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <InputLabel for="email" value="Email" class="text-gray-700" />
          <TextInput
            id="email"
            type="email"
            class="block w-full mt-1 px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
            v-model="form.email"
            required
            autofocus
            autocomplete="username"
            placeholder="your@email.com"
          />
          <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <div>
          <div class="flex justify-between items-center">
            <InputLabel for="password" value="Password" class="text-gray-700" />
            <Link
              v-if="canResetPassword"
              :href="route('password.request')"
              class="text-sm text-indigo-600 hover:text-indigo-500 transition-colors"
            >
              Forgot password?
            </Link>
          </div>
          <TextInput
            id="password"
            type="password"
            class="block w-full mt-1 px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
            v-model="form.password"
            required
            autocomplete="current-password"
            placeholder="••••••••"
          />
          <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <div class="flex items-center">
          <label class="flex items-center">
            <Checkbox name="remember" v-model:checked="form.remember" class="text-indigo-600" />
            <span class="ml-2 text-sm text-gray-600">Remember me</span>
          </label>
        </div>

        <div>
          <PrimaryButton
            class="w-full justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
            :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Signing in...' : 'Sign in' }}
          </PrimaryButton>
        </div>
      </form>

      <div class="mt-6">
        <div class="relative">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">Or continue with</span>
          </div>
        </div>

        <div class="mt-6">
          <button
            @click="handleGoogleLogin"
            type="button"
            class="w-full flex justify-center items-center gap-3 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
          >
            <svg class="h-5 w-5" viewBox="0 0 24 24">
              <path
                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                fill="#4285F4"
              />
              <path
                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                fill="#34A853"
              />
              <path
                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                fill="#FBBC05"
              />
              <path
                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                fill="#EA4335"
              />
              <path d="M1 1h22v22H1z" fill="none" />
            </svg>
            Sign in with Google
          </button>
        </div>
      </div>

      <p class="mt-8 text-center text-sm text-gray-500">
        Don't have an account?
        <Link
          :href="route('register')"
          class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors"
        >
          Sign up
        </Link>
      </p>
    </div>
  </GuestLayout>
</template>
