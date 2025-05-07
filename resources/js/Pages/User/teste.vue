<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Bem-vindo, usuário!</h1>
    <p class="mb-6">Essa é a área do usuário comum.</p>

    <button
      @click="logout"
      class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition"
    >
      Sair
    </button>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

const logout = () => {
  router.post(route('logout'))
}
</script>
