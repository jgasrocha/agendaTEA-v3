<template>
  <Navbar :auth="$page.props.auth" />
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Lista de Turmas</h1>
      <Link :href="route('cursos.turmas.create', curso.id)"
        class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition-colors shadow-md">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd"
          d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
          clip-rule="evenodd" />
      </svg>
      Nova Turma
      </Link>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="turma in turmas" :key="turma.id"
        class="bg-white rounded-lg shadow-md p-4 cursor-pointer hover:shadow-lg transition-all duration-200 border border-gray-100"
        @click="verTurma(turma)">
        <h2 class="text-lg font-semibold mb-2">{{ turma.nome }}</h2>
        
        <div class="flex items-center gap-2 mb-3">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-800">
            {{ formatSemestre(turma.semestre) }}
          </span>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800">
            {{ turma.agendas_count }} {{ turma.agendas_count === 1 ? 'agenda' : 'agendas' }}
          </span>
        </div>

        <div class="flex justify-end">
          <button @click.stop="abrirModal(turma)"
            class="text-sky-600 hover:text-sky-800 text-sm flex items-center gap-1">
            Editar
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Edição -->
    <div v-if="turmaSelecionada"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg p-6 w-full max-w-lg relative shadow-xl">
        <button @click="fecharModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <h2 class="text-xl font-bold mb-4 text-gray-800">Editar Turma</h2>

        <div class="flex flex-col gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
            <input v-model="turmaSelecionada.nome" required
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
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
          </div>

          <div class="flex justify-between mt-4">
            <button @click="salvarEdicao"
              class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md transition-colors"
              :disabled="salvando">
              <span v-if="salvando">Salvando...</span>
              <span v-else>Salvar</span>
            </button>
            <button @click="deletarTurma"
              class="flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-md transition-colors">
              Excluir
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, ref, computed, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import Navbar from '@/Components/Navbar.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  turmas: Array,
  curso: Object
})

const turmaSelecionada = ref(null)
const salvando = ref(false)
const anoSelecionado = ref(new Date().getFullYear())
const periodoSelecionado = ref('1')

// Gerar anos de 2020 até 5 anos no futuro
const anosDisponiveis = Array.from({ length: 10 }, (_, i) => new Date().getFullYear() - 2 + i)

const formatSemestre = (semestre) => {
  const [ano, periodo] = semestre.split('.')
  return `${ano} - ${periodo === '1' ? '1º Semestre' : '2º Semestre'}`
}

// Redireciona para a página de agenda da turma
const verTurma = (turma) => {
  const {props} = usePage()
  router.visit(route('cursos.turmas.agenda.index', { 
    curso: props.curso.id, 
    turma: turma.id 
  }))
}

// Abre modal de edição
const abrirModal = (turma) => {
  turmaSelecionada.value = { ...turma }
  const [ano, periodo] = turma.semestre.split('.')
  anoSelecionado.value = ano
  periodoSelecionado.value = periodo
}

const fecharModal = () => {
  turmaSelecionada.value = null
}

const semestreCompleto = computed(() => {
  return `${anoSelecionado.value}.${periodoSelecionado.value}`
})

watch(semestreCompleto, (newVal) => {
  if (turmaSelecionada.value) {
    turmaSelecionada.value.semestre = newVal
  }
})

const salvarEdicao = async () => {
  try {
    salvando.value = true

    const form = {
      nome: turmaSelecionada.value.nome,
      semestre: turmaSelecionada.value.semestre,
      _method: 'PUT'
    }

    await router.post(route('cursos.turmas.update', { 
      curso: props.curso.id, 
      turma: turmaSelecionada.value.id 
    }), form)

    await Swal.fire({
      icon: 'success',
      title: 'Sucesso!',
      text: 'Turma atualizada com sucesso.',
      timer: 2000,
      showConfirmButton: false
    })

    fecharModal()
  } catch (error) {
    await Swal.fire({
      icon: 'error',
      title: 'Erro',
      text: 'Ocorreu um erro ao atualizar a turma.',
    })
  } finally {
    salvando.value = false
  }
}

const deletarTurma = async () => {
  const result = await Swal.fire({
    title: 'Tem certeza?',
    text: "Você não poderá reverter esta ação!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sim, excluir!',
    cancelButtonText: 'Cancelar'
  })

  if (result.isConfirmed) {
    try {
      await router.delete(route('cursos.turmas.destroy', { 
        curso: props.curso.id, 
        turma: turmaSelecionada.value.id 
      }))

      await Swal.fire({
        icon: 'success',
        title: 'Excluído!',
        text: 'A turma foi removida com sucesso.',
        timer: 2000,
        showConfirmButton: false
      })

      fecharModal()
    } catch (error) {
      await Swal.fire({
        icon: 'error',
        title: 'Erro',
        text: 'Ocorreu um erro ao excluir a turma.',
      })
    }
  }
}
</script>