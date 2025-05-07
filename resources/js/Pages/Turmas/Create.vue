<template>
    <Navbar :auth="$page.props.auth" />
    <div class="p-6 max-w-3xl mx-auto">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold">Criar Nova Turma</h1>
          <p class="text-gray-600">Curso: {{ curso.nome }}</p>
        </div>
        <Link :href="route('cursos.turmas.index', curso.id)"
          class="flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg transition-colors shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
            clip-rule="evenodd" />
        </svg>
        Voltar
        </Link>
      </div>
  
      <div class="bg-white rounded-lg shadow-md p-6">
        <form @submit.prevent="submitForm">
          <div class="grid grid-cols-1 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Turma *</label>
              <input v-model="form.nome" type="text" required
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
              <p v-if="errors.nome" class="mt-1 text-sm text-red-600">{{ errors.nome }}</p>
            </div>
  
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Semestre *</label>
              <div class="flex gap-2">
                <select v-model="anoSelecionado" required
                  class="w-1/2 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <option v-for="year in anosDisponiveis" :key="year" :value="year">{{ year }}</option>
                </select>
                <select v-model="periodoSelecionado" required
                  class="w-1/2 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <option value="1">1º Semestre</option>
                  <option value="2">2º Semestre</option>
                </select>
              </div>
              <p class="mt-1 text-xs text-gray-500">Formato: YYYY.1 ou YYYY.2</p>
              <p v-if="errors.semestre" class="mt-1 text-sm text-red-600">{{ errors.semestre }}</p>
            </div>
  
            <div class="flex justify-end gap-3 pt-4">
              <Link :href="route('cursos.turmas.index', curso.id)"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                Cancelar
              </Link>
              <button type="submit"
                class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md transition-colors"
                :disabled="processing">
                <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                  fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                <span>{{ processing ? 'Salvando...' : 'Criar Turma' }}</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script setup>
  import Navbar from '@/Components/Navbar.vue'
  import { Link, router, useForm } from '@inertiajs/vue3'
  import { ref, computed } from 'vue'
  import Swal from 'sweetalert2'
  
  const props = defineProps({
    curso: Object,
    errors: Object
  })
  
  // Gerar anos de 2020 até 5 anos no futuro
  const anosDisponiveis = Array.from({ length: 10 }, (_, i) => new Date().getFullYear() - 2 + i)
  const anoSelecionado = ref(new Date().getFullYear())
  const periodoSelecionado = ref('1')
  
  const semestreCompleto = computed(() => {
    return `${anoSelecionado.value}.${periodoSelecionado.value}`
  })
  
  const form = useForm({
    nome: '',
    semestre: semestreCompleto,
    curso_id: props.curso.id
  })
  
  const processing = ref(false)
  
  const submitForm = () => {
  processing.value = true
  
  form.post(route('cursos.turmas.store', { curso: props.curso.id }), {
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: 'Sucesso!',
        text: 'Turma criada com sucesso.',
        timer: 2000,
        showConfirmButton: false
      }).then(() => {
        router.visit(route('cursos.turmas.index', props.curso.id))
      })
    },
    onError: () => {
      Swal.fire({
        icon: 'error',
        title: 'Erro',
        text: 'Ocorreu um erro ao criar a turma. Verifique os dados e tente novamente.',
      })
    },
    onFinish: () => {
      processing.value = false
    }
  })
}
  </script>
  
  <style>
  .animate-spin {
    animation: spin 1s linear infinite;
  }
  
  @keyframes spin {
    from {
      transform: rotate(0deg);
    }
    to {
      transform: rotate(360deg);
    }
  }
  </style>