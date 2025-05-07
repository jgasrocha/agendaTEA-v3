<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
      <button
        @click="goToLogin"
        class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700 transition"
      >
        Ir para Login
      </button>
    </div>
  </template>
  
  <script setup>
  import { router } from '@inertiajs/vue3'
  
  const goToLogin = () => {
    router.visit('/login')
  }
  </script>
  