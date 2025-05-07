<template>
    <div>
      <AdminNavbar />
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        <slot />
      </div>
    </div>
  </template>
  
  <script setup>
  import AdminNavbar from '@/Components/AdminNavbar.vue';
  </script>