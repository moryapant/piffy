<script setup>
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import NotificationsDropdown from "@/Components/NotificationsDropdown.vue";
import ThemeToggle from "@/Components/ThemeToggle.vue";
import { Menu, MenuButton, MenuItems, MenuItem } from "@headlessui/vue";

const isMobileMenuOpen = ref(false);
</script>

<template>
  <nav class="fixed top-0 z-50 w-full bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <Link href="/" class="flex items-center flex-shrink-0 group">
          <ApplicationLogo class="w-10 h-10" />
          <div class="ml-3 flex flex-col">
            <span
              class="text-2xl font-bold bg-gradient-to-r from-pink-500 to-fuchsia-600 bg-clip-text text-transparent transition-all duration-300 ease-in-out group-hover:tracking-wide"
              >Fappify</span
            >
            <span
              class="text-sm font-medium tracking-wider uppercase bg-gradient-to-r from-rose-400 to-pink-600 bg-clip-text text-transparent group-hover:from-rose-500 group-hover:to-pink-700 transition-all duration-300 ease-in-out"
              >bollyfappers</span
            >
          </div>
        </Link>

        <!-- Desktop Navigation - Centered -->
        <div
          class="hidden md:flex md:items-center md:space-x-6 flex-grow justify-center"
        >
          <!-- Navigation Links -->
          <div class="flex items-center space-x-6">
            <Link
              href="/"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-pink-700 bg-pink-50 rounded-full hover:text-pink-800 hover:bg-pink-100 transition-all duration-300 group"
            >
              <svg
                class="w-4 h-4 mr-2 text-pink-500 group-hover:text-pink-600 transition-colors duration-300"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                />
              </svg>
              Home
            </Link>

            <Link
              :href="route('subfapps.index')"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-fuchsia-700 bg-fuchsia-50 rounded-full hover:text-fuchsia-800 hover:bg-fuchsia-100 transition-all duration-300 group"
            >
              <svg
                class="w-4 h-4 mr-2 text-fuchsia-500 group-hover:text-fuchsia-600 transition-colors duration-300"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                />
              </svg>
              Communities
            </Link>

            <a
              href="https://bollyfappers.com"
              target="_blank"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-fuchsia-700 bg-fuchsia-50 rounded-full hover:text-fuchsia-800 hover:bg-fuchsia-100 transition-all duration-300 group"
            >
              <svg
                class="w-4 h-4 mr-2 text-fuchsia-500 group-hover:text-fuchsia-600 transition-colors duration-300"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"
                />
              </svg>
              BollyFappers
            </a>

            <!-- <a
              href="https://bollyfappers.com"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-pink-500 to-fuchsia-500 rounded-full hover:from-pink-600 hover:to-fuchsia-600 transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 group"
            >
              <svg class="w-4 h-4 mr-2 text-white group-hover:text-fuchsia-100 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
              </svg>
              BollyFappers
            </a> -->
          </div>
        </div>

        <!-- Right Section: Auth Links/Avatar -->
        <div class="hidden md:flex md:items-center md:space-x-6 flex-shrink-0">
          <template v-if="!$page.props.auth.user">
            <Link
              :href="route('login')"
              class="inline-flex items-center px-5 py-2 text-sm font-medium text-white bg-gradient-to-r from-pink-500 to-fuchsia-500 rounded-full hover:from-pink-600 hover:to-fuchsia-600 transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500"
            >
              <svg
                class="w-4 h-4 mr-1.5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                />
              </svg>
              Log in
            </Link>
            <Link
              :href="route('register')"
              class="inline-flex items-center px-5 py-2 text-sm font-medium text-white bg-gradient-to-r from-fuchsia-500 to-purple-500 rounded-full hover:from-fuchsia-600 hover:to-purple-600 transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-fuchsia-500"
            >
              <svg
                class="w-4 h-4 mr-1.5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                />
              </svg>
              Sign Up
            </Link>
          </template>

          <template v-if="$page.props.auth.user">
            <Link
              :href="route('posts.create')"
              class="inline-flex items-center px-5 py-2 text-sm font-medium text-white bg-gradient-to-r from-pink-500 to-rose-500 rounded-full hover:from-pink-600 hover:to-rose-600 transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500"
            >
              <svg
                class="w-4 h-4 mr-1.5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 4v16m8-8H4"
                />
              </svg>
              New Post
            </Link>

            <Link
              :href="route('subfapps.create')"
              class="inline-flex items-center px-5 py-2 text-sm font-medium text-fuchsia-700 bg-fuchsia-50 border border-transparent rounded-full hover:bg-fuchsia-100 transition-all duration-300 transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-fuchsia-500 group"
            >
              <svg
                class="w-4 h-4 mr-1.5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 4v16m8-8H4"
                />
              </svg>
              New Community
            </Link>

            <!-- Notifications Dropdown -->
            <NotificationsDropdown />

            <!-- Theme Toggle -->
            <ThemeToggle />

            <Menu as="div" class="relative">
              <div>
                <MenuButton
                  class="flex items-center p-2 space-x-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                >
                  <img
                    :src="
                      $page.props.auth.user.avatar
                        ? $page.props.auth.user.avatar.startsWith('http')
                          ? $page.props.auth.user.avatar
                          : `/storage/${$page.props.auth.user.avatar}`
                        : 'https://www.gravatar.com/avatar/?d=mp'
                    "
                    :alt="$page.props.auth.user.name"
                    class="object-cover w-8 h-8 rounded-full border-2 border-gray-200"
                  />
                  <svg
                    class="w-4 h-4 text-gray-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </MenuButton>
              </div>
              <transition
                enter-active-class="transition duration-100 ease-out"
                enter-from-class="opacity-0 transform scale-95"
                enter-to-class="opacity-100 transform scale-100"
                leave-active-class="transition duration-75 ease-in"
                leave-from-class="opacity-100 transform scale-100"
                leave-to-class="opacity-0 transform scale-95"
              >
                <MenuItems
                  class="absolute right-0 mt-2 w-48 origin-top-right bg-white dark:bg-gray-800 rounded-md shadow-lg ring-1 ring-black dark:ring-gray-700 ring-opacity-5 focus:outline-none"
                >
                  <div class="py-1">
                    <MenuItem v-slot="{ active }">
                      <Link
                        :href="route('dashboard')"
                        :class="[
                          active ? 'bg-gray-100' : '',
                          'group flex items-center px-4 py-2 text-sm text-gray-700',
                        ]"
                      >
                        <svg
                          class="w-4 h-4 mr-3 text-gray-500 group-hover:text-gray-600"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                          />
                        </svg>
                        Dashboard
                      </Link>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                      <Link
                        :href="route('profile.edit')"
                        :class="[
                          active ? 'bg-gray-100' : '',
                          'group flex items-center px-4 py-2 text-sm text-gray-700',
                        ]"
                      >
                        <svg
                          class="w-4 h-4 mr-3 text-gray-500 group-hover:text-gray-600"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                          />
                        </svg>
                        Your Profile
                      </Link>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                      <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        :class="[
                          active ? 'bg-gray-100' : '',
                          'group flex w-full items-center px-4 py-2 text-sm text-red-600 hover:text-red-700',
                        ]"
                      >
                        <svg
                          class="w-4 h-4 mr-3 text-red-500 group-hover:text-red-600"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                          />
                        </svg>
                        Sign out
                      </Link>
                    </MenuItem>
                  </div>
                </MenuItems>
              </transition>
            </Menu>
          </template>
        </div>

        <!-- Mobile menu button -->
        <button
          @click="isMobileMenuOpen = !isMobileMenuOpen"
          class="inline-flex justify-center items-center p-2 text-gray-700 rounded-md md:hidden hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
        >
          <span class="sr-only">Open main menu</span>
          <svg
            class="block w-6 h-6"
            :class="{ hidden: isMobileMenuOpen, block: !isMobileMenuOpen }"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
          </svg>
          <svg
            class="w-6 h-6"
            :class="{ block: isMobileMenuOpen, hidden: !isMobileMenuOpen }"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <!-- Mobile menu -->
      <div
        :class="{
          block: isMobileMenuOpen,
          hidden: !isMobileMenuOpen,
        }"
        class="md:hidden"
      >
        <div class="px-2 pt-2 pb-3 space-y-1">
          <Link
            href="/"
            class="flex items-center px-3 py-2 text-base font-medium text-pink-700 rounded-md hover:text-pink-800 hover:bg-pink-50 transition-all duration-300"
          >
            <svg
              class="w-5 h-5 mr-3 text-pink-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
              />
            </svg>
            Home
          </Link>

          <Link
            :href="route('subfapps.index')"
            class="flex items-center px-3 py-2 text-base font-medium text-fuchsia-700 rounded-md hover:text-fuchsia-800 hover:bg-fuchsia-50 transition-all duration-300"
          >
            <svg
              class="w-5 h-5 mr-3 text-fuchsia-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
              />
            </svg>
            Communities
          </Link>

          <a
            href="https://bollyfappers.com"
            target="_blank"
            class="flex items-center px-3 py-2 text-base font-medium text-fuchsia-700 rounded-md hover:text-fuchsia-800 hover:bg-fuchsia-50 transition-all duration-300"
          >
            <svg
              class="w-5 h-5 mr-3 text-fuchsia-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"
              />
            </svg>
            BollyFappers
          </a>

          <!-- <a
            href="https://bollyfappers.com"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center px-3 py-2 text-base font-medium text-white bg-gradient-to-r from-pink-500 to-fuchsia-500 rounded-md hover:from-pink-600 hover:to-fuchsia-600 transition-all duration-300"
          >
            <svg class="w-5 h-5 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
            </svg>
            BollyFappers
          </a> -->

          <template v-if="$page.props.auth.user">
            <div class="px-3 py-2">
              <Link
                :href="route('posts.create')"
                class="flex items-center justify-center w-full px-4 py-2 text-base font-medium text-white bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full hover:from-blue-600 hover:to-indigo-700 transition-all duration-150 shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <svg
                  class="w-5 h-5 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4v16m8-8H4"
                  />
                </svg>
                New Post
              </Link>
            </div>

            <div class="px-3 py-2">
              <Link
                :href="route('subfapps.create')"
                class="flex items-center justify-center w-full px-4 py-2 text-base font-medium text-indigo-700 bg-indigo-50 border border-transparent rounded-full hover:bg-indigo-100 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <svg
                  class="w-5 h-5 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4v16m8-8H4"
                  />
                </svg>
                New Community
              </Link>
            </div>

            <div class="mt-3 pt-3 border-t border-gray-200">
              <Link
                :href="route('dashboard')"
                class="flex items-center px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-gray-900 hover:bg-gray-50 transition-colors duration-150"
              >
                <svg
                  class="w-5 h-5 mr-3 text-gray-500"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                  />
                </svg>
                Dashboard
              </Link>

              <Link
                :href="route('profile.edit')"
                class="flex items-center px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-gray-900 hover:bg-gray-50 transition-colors duration-150"
              >
                <svg
                  class="w-5 h-5 mr-3 text-gray-500"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                  />
                </svg>
                Profile
              </Link>

              <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="flex items-center w-full px-3 py-2 text-base font-medium text-red-600 rounded-md hover:text-red-700 hover:bg-gray-50 transition-colors duration-150"
              >
                <svg
                  class="w-5 h-5 mr-3 text-red-500"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                  />
                </svg>
                Sign out
              </Link>
            </div>
          </template>

          <template v-if="!$page.props.auth.user">
            <div class="px-3 py-2">
              <Link
                :href="route('login')"
                class="flex items-center justify-center w-full px-4 py-2 text-base font-medium text-white bg-gradient-to-r from-green-500 to-green-600 rounded-full hover:from-green-600 hover:to-green-700 transition-all duration-150 shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
              >
                <svg
                  class="w-5 h-5 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                  />
                </svg>
                Log in
              </Link>
            </div>

            <div class="px-3 py-2">
              <Link
                :href="route('register')"
                class="flex items-center justify-center w-full px-4 py-2 text-base font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-full hover:from-blue-600 hover:to-blue-700 transition-all duration-150 shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <svg
                  class="w-5 h-5 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                  />
                </svg>
                Sign Up
              </Link>
            </div>
          </template>
        </div>
      </div>
    </div>
  </nav>
  <!-- Spacer to prevent content from hiding under fixed navbar -->
  <div class="h-16"></div>
</template>

<style scoped>
</style>
