<template>
    <Navbar :auth="$page.props.auth" />
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold">Turma: {{ turma.nome }}</h1>
          <p class="text-gray-600">Curso: {{ curso.nome }}</p>
        </div>
        <Link :href="route('cursos.turmas.index', curso.id)"
          class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
            clip-rule="evenodd" />
        </svg>
        Voltar
        </Link>
      </div>
  
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-lg font-semibold mb-2">Informações da Turma</h3>
            <div class="space-y-2">
              <p><span class="font-medium">Nome:</span> {{ turma.nome }}</p>
              <p><span class="font-medium">Semestre:</span> {{ formatSemestre(turma.semestre) }}</p>
            </div>
          </div>
          
          <div>
            <h3 class="text-lg font-semibold mb-2">Ações</h3>
            <div class="flex gap-3">
              <Link :href="route('cursos.turmas.edit', { curso: curso.id, turma: turma.id })"
                class="flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md transition-colors">
                Editar
              </Link>
              <button @click="deletarTurma"
                class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition-colors">
                Excluir
              </button>
            </div>
          </div>
        </div>
      </div>
  
      <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold mb-4">Agendas da Turma</h3>
        
        <div v-if="turma.agendas.length > 0" class="space-y-4">
          <div v-for="agenda in turma.agendas" :key="agenda.id" class="border border-gray-200 rounded-lg p-4">
            <div class="flex justify-between items-start">
              <div>
                <h4 class="font-medium">{{ agenda.disciplina.nome }}</h4>
                <p class="text-sm text-gray-600">Professor: {{ agenda.user.name }}</p>
              </div>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Ativa
              </span>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          <p>Nenhuma agenda cadastrada para esta turma</p>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import Navbar from '@/Components/Navbar.vue'
  import { Link, router } from '@inertiajs/vue3'
  import { defineProps } from 'vue'
  import Swal from 'sweetalert2'
  
  defineProps({
    curso: Object,
    turma: Object
  })
  
  const formatSemestre = (semestre) => {
    const [ano, periodo] = semestre.split('.')
    return `${ano} - ${periodo === '1' ? '1º Semestre' : '2º Semestre'}`
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
          curso: curso.id, 
          turma: turma.id 
        }))
  
        await Swal.fire({
          icon: 'success',
          title: 'Excluído!',
          text: 'A turma foi removida com sucesso.',
        })
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