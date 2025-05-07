<template>
  <Navbar :auth="$page.props.auth" />
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Lista de Disciplinas</h1>
      <Link :href="route('admin.disciplinas.create')"
        class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition-colors shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
            clip-rule="evenodd" />
        </svg>
        Nova Disciplina
      </Link>
    </div>

    <!-- Filtro por curso -->
    <div class="mb-6">
      <label for="cursoFilter" class="block text-sm font-medium text-gray-700 mb-2">Filtrar por Curso:</label>
      <select 
        id="cursoFilter" 
        v-model="cursoFiltro"
        class="w-full md:w-1/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option value="">Todos os Cursos</option>
        <option v-for="curso in cursos" :key="curso.id" :value="curso.id">{{ curso.nome }}</option>
      </select>
    </div>

    <!-- Lista organizada por curso -->
    <div class="space-y-8">
      <div v-for="curso in cursosFiltrados" :key="curso.id" class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-3 border-b flex justify-between items-center">
          <h2 class="text-lg font-semibold text-gray-800">{{ curso.nome }}</h2>
          <span class="text-sm text-gray-500">
            {{ curso.disciplinas.length }} {{ curso.disciplinas.length === 1 ? 'disciplina' : 'disciplinas' }}
          </span>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
          <div 
            v-for="disciplina in curso.disciplinas" 
            :key="disciplina.id"
            class="bg-white rounded-lg shadow-sm p-4 border hover:shadow-md transition-shadow cursor-pointer disciplina-card"
            @click="abrirModal(disciplina)"
          >
            <div class="mb-3">
              <img v-if="disciplina.foto" :src="`/storage/${disciplina.foto}`" alt="Foto da Disciplina"
                class="w-full h-32 object-cover rounded-lg" />
              <div v-else class="w-full h-32 bg-gray-100 flex items-center justify-center text-gray-500 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>

            <h3 class="text-md font-semibold mb-1">{{ disciplina.nome }}</h3>
            <p class="text-xs text-gray-500 mt-2">
              Criado em: {{ formatDate(disciplina.created_at) }}
            </p>
          </div>
          
          <div v-if="curso.disciplinas.length === 0" class="col-span-full text-center py-6 text-gray-500">
            Nenhuma disciplina cadastrada para este curso
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Edição -->
    <div v-if="disciplinaSelecionada" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg p-6 w-full max-w-lg relative shadow-xl">
        <button @click="fecharModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <h2 class="text-xl font-bold mb-4 text-gray-800">Detalhes da Disciplina</h2>

        <div class="flex flex-col gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
            <input v-model="disciplinaSelecionada.nome"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Imagem atual</label>
            <img
              :src="previewImagem || (disciplinaSelecionada.foto ? `/storage/${disciplinaSelecionada.foto}` : 'https://via.placeholder.com/200x120?text=Sem+Imagem')"
              alt="Imagem" class="w-full h-32 object-cover rounded-lg border border-gray-200" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Alterar imagem</label>
            <input type="file" @change="selecionarImagem" accept="image/*" class="w-full text-sm text-gray-500
                file:mr-4 file:py-2 file:px-4
                file:rounded-md file:border-0
                file:text-sm file:font-semibold
                file:bg-blue-50 file:text-blue-700
                hover:file:bg-blue-100" />
          </div>

          <div class="flex justify-between mt-4">
            <button @click="salvarEdicao"
              class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md transition-colors"
              :disabled="salvando">
              <span v-if="salvando">Salvando...</span>
              <span v-else>Salvar</span>
            </button>
            <button @click="deletarDisciplina"
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
import Navbar from '@/Components/Navbar.vue'
import { Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  cursos: Array,
  current_curso_id: Number
})
const currentCursoId = ref(props.current_curso_id || null);
const cursoFiltro = ref('')
const disciplinaSelecionada = ref(null)
const novaImagem = ref(null)
const previewImagem = ref(null)
const salvando = ref(false)

// Filtra os cursos com base na seleção
const cursosFiltrados = computed(() => {
  if (!cursoFiltro.value) return props.cursos
  
  return props.cursos.filter(curso => curso.id == cursoFiltro.value)
})

// Formata a data para exibição
const formatDate = (dateString) => {
  const options = { day: '2-digit', month: '2-digit', year: 'numeric' }
  return new Date(dateString).toLocaleDateString('pt-BR', options)
}

// Abre o modal para edição da disciplina
const abrirModal = (disciplina) => {
  disciplinaSelecionada.value = disciplina
  previewImagem.value = null
}

// Fecha o modal
const fecharModal = () => {
  disciplinaSelecionada.value = null
  previewImagem.value = null
  novaImagem.value = null
}

// Salva a edição da disciplina
const salvarEdicao = async () => {
  salvando.value = true
  try {
    // Submeter alterações
    await router.post(route('admin.disciplinas.update', disciplinaSelecionada.value.id), {
      disciplina: disciplinaSelecionada.value,
      imagem: novaImagem.value
    })
    Swal.fire('Sucesso!', 'Disciplina atualizada com sucesso!', 'success')
    fecharModal()
  } catch (error) {
    Swal.fire('Erro!', 'Ocorreu um erro ao salvar.', 'error')
  } finally {
    salvando.value = false
  }
}

const deletarDisciplina = async () => {
  const resultado = await Swal.fire({
    title: 'Tem certeza?',
    text: "Verifique se esta disciplina está vinculada a mais de um curso. Ao excluí-la, ela será removida de todos os cursos nos quais está associada. Essa ação não pode ser desfeita.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, deletar!',
    cancelButtonText: 'Cancelar'
  })
  
  if (resultado.isConfirmed) {
    await router.delete(route('admin.disciplinas.destroy', disciplinaSelecionada.value.id))
    Swal.fire('Deletado!', 'Disciplina removida com sucesso.', 'success')
    fecharModal()
  }
}

// Seleciona uma nova imagem
const selecionarImagem = (event) => {
  const arquivo = event.target.files[0]
  if (arquivo) {
    previewImagem.value = URL.createObjectURL(arquivo)
    novaImagem.value = arquivo
  }
}
</script>

<style scoped>
.disciplina-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.disciplina-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
</style>
