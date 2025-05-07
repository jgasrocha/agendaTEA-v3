<template>
  <div class="p-6 max-w-xl mx-auto bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Criar Nova Disciplina</h1>

    <form @submit.prevent="submit">
      <div class="mb-4">
        <label class="block font-semibold mb-1" for="nome">Nome da Disciplina</label>
        <input
          v-model="form.nome"
          type="text"
          id="nome"
          class="w-full border-gray-300 rounded px-3 py-2"
        />
        <span v-if="form.errors.nome" class="text-red-500 text-sm">{{ form.errors.nome }}</span>
      </div>

      <!-- Campo de seleção múltipla de cursos -->
      <div class="mb-4">
        <label class="block font-semibold mb-1">Cursos Associados</label>
        <div class="space-y-2">
          <div v-for="curso in cursos" :key="curso.id" class="flex items-center">
            <input
              type="checkbox"
              :id="`curso-${curso.id}`"
              :value="curso.id"
              v-model="form.cursos_ids"
              class="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
            >
            <label :for="`curso-${curso.id}`" class="ml-2 text-sm text-gray-700">
              {{ curso.nome }}
            </label>
          </div>
        </div>
        <span v-if="form.errors.cursos_ids" class="text-red-500 text-sm">{{ form.errors.cursos_ids }}</span>
      </div>

      <div class="mb-6">
        <label class="block font-semibold mb-1" for="foto">Foto da Disciplina (opcional)</label>
        <input
          type="file"
          id="foto"
          @change="handleFileChange"
          class="w-full border-gray-300 rounded px-3 py-2"
        />
        <span v-if="form.errors.foto" class="text-red-500 text-sm">{{ form.errors.foto }}</span>
      </div>

      <div class="flex justify-end">
        <button
          type="submit"
          class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition"
          :disabled="form.processing"
        >
          <span v-if="form.processing">Salvando...</span>
          <span v-else>Criar Disciplina</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { defineProps } from 'vue'

const props = defineProps({
  users: Array,
  cursos: Array
})

const form = useForm({
  nome: '',
  cursos_ids: [], // Alterado para array de IDs
  foto: null,
})

const handleFileChange = (event) => {
  form.foto = event.target.files[0]
}

const submit = () => {
  form.post(route('admin.disciplinas.store'), {
    forceFormData: true,
    preserveScroll: true,
  })
}
</script>