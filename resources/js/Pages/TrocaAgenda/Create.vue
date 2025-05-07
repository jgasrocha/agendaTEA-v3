<script setup>
import { useForm } from '@inertiajs/vue3'
import { defineProps } from 'vue'

const props = defineProps({
  agendas: Array,
  errors: Object
})

// Setup do formulário
const form = useForm({
  agenda_original_id: '',
  agenda_desejada_id: ''
})

function submit() {
  form.post('/troca-agendas')
}
</script>

<template>
  <div class="p-6 max-w-md mx-auto">
    <h1 class="text-2xl font-bold mb-4">Solicitar Troca de Agenda</h1>

    <form @submit.prevent="submit" class="space-y-4">

      <!-- Agenda Original -->
      <div>
        <label for="agenda_original_id" class="block text-sm font-medium text-gray-700">Agenda Original</label>
        <select v-model="form.agenda_original_id" id="agenda_original_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
          <option value="">Selecione</option>
          <option v-for="agenda in props.agendas" :key="agenda.id" :value="agenda.id">
            {{ agenda.id }}
          </option>
        </select>
        <div v-if="form.errors.agenda_original_id" class="text-red-500 text-sm">{{ form.errors.agenda_original_id }}</div>
      </div>

      <!-- Agenda Desejada -->
      <div>
        <label for="agenda_desejada_id" class="block text-sm font-medium text-gray-700">Agenda Desejada</label>
        <select v-model="form.agenda_desejada_id" id="agenda_desejada_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
          <option value="">Selecione</option>
          <option v-for="agenda in props.agendas" :key="agenda.id" :value="agenda.id">
            {{ agenda.id }}
          </option>
        </select>
        <div v-if="form.errors.agenda_desejada_id" class="text-red-500 text-sm">{{ form.errors.agenda_desejada_id }}</div>
      </div>

      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Enviar Solicitação
      </button>
    </form>
  </div>
</template>
