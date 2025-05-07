<template>
  <Navbar :auth="$page.props.auth" />
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Lista de Cursos</h1>
      <Link 
      v-if="$page.props.auth.user?.is_admin"
      :href="route('cursos.create')"
        class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition-colors shadow-md">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd"
          d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
          clip-rule="evenodd" />
      </svg>
      Novo Curso
      </Link>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="curso in cursos" :key="curso.id"
        class="bg-white rounded-lg shadow-md p-4 cursor-pointer hover:shadow-lg transition-all duration-200 border border-gray-100"
        @click="abrirModal(curso)">
        <div class="mb-4">
          <img v-if="curso.imagem" :src="`/storage/${curso.imagem}`" alt="Imagem do Curso"
            class="w-full h-40 object-cover rounded-lg" />
          <div v-else class="w-full h-40 bg-gray-100 flex items-center justify-center text-gray-500 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                d="M8 7v4m0 4v4m4-8v4m0 4v4m4-8v4m0 4v4" />
            </svg>
          </div>
        </div>

        <h2 class="text-lg font-semibold mb-2">{{ curso.nome }}</h2>
        <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ curso.descricao }}</p>
        <div class="flex justify-between items-center mt-2">
          <span class="inline-block bg-sky-100 text-blue-800 text-xs px-2 py-1 rounded-full">
            {{ curso.turmas_count }} {{ curso.turmas_count === 1 ? 'turma' : 'turmas' }}
          </span>
          <Link :href="route('cursos.turmas.index', curso.id)"
            class="text-sky-600 hover:text-sky-700 text-sm flex items-center gap-1"
            @click.stop>
            Ver turmas
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </Link>
        </div>
      </div>
    </div>

    <!-- Modal de Edição -->
    <div v-if="cursoSelecionado"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg p-6 w-full max-w-lg relative shadow-xl">
        <button @click="fecharModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <h2 class="text-xl font-bold mb-4 text-gray-800">Detalhes do Curso</h2>

        <div class="flex flex-col gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
            <input v-model="cursoSelecionado.nome"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea v-model="cursoSelecionado.descricao"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 h-24"></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Imagem atual</label>
            <img
              :src="previewImagem || (cursoSelecionado.imagem ? `/storage/${cursoSelecionado.imagem}` : 'https://via.placeholder.com/200x120?text=Sem+Imagem')"
              alt="Imagem" class="w-full h-32 object-cover rounded-lg border border-gray-200" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Alterar imagem</label>
            <input type="file" @change="selecionarImagem" accept="image/*" class="w-full text-sm text-gray-500
                file:mr-4 file:py-2 file:px-4
                file:rounded-md file:border-0
                file:text-sm file:font-semibold
                file:bg-sky-50 file:text-sky-700
                hover:file:bg-sky-100" />
          </div>

          <div class="flex justify-between mt-4">
            <button @click="salvarEdicao"
              class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md transition-colors"
              :disabled="salvando">
              <span v-if="salvando">Salvando...</span>
              <span v-else>Salvar</span>
            </button>
            <button @click="deletarCurso"
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
import { defineProps, ref } from 'vue'
import Swal from 'sweetalert2'

defineProps({
  cursos: Array,
  auth: Object
})

const cursoSelecionado = ref(null)
const novaImagem = ref(null)
const previewImagem = ref(null)
const salvando = ref(false)

const abrirModal = (curso) => {
  cursoSelecionado.value = { ...curso }
  novaImagem.value = null
  previewImagem.value = null
}

const fecharModal = () => {
  cursoSelecionado.value = null
  novaImagem.value = null
  previewImagem.value = null
}

const selecionarImagem = (e) => {
  const file = e.target.files[0]
  if (file) {
    novaImagem.value = file
    previewImagem.value = URL.createObjectURL(file)
  }
}

const salvarEdicao = async () => {
  try {
    salvando.value = true

    const form = new FormData()
    form.append('nome', cursoSelecionado.value.nome)
    form.append('descricao', cursoSelecionado.value.descricao)

    if (novaImagem.value) {
      form.append('imagem', novaImagem.value)
    }
    form.append('_method', 'PUT')

    await router.post(route('cursos.update', cursoSelecionado.value.id), form, {
      preserveScroll: true,
    })

    await Swal.fire({
      icon: 'success',
      title: 'Sucesso!',
      text: 'Curso atualizado com sucesso.',
      timer: 2000,
      showConfirmButton: false
    })

    fecharModal()
  } catch (error) {
    await Swal.fire({
      icon: 'error',
      title: 'Erro',
      text: 'Ocorreu um erro ao atualizar o curso.',
    })
  } finally {
    salvando.value = false
  }
}

const deletarCurso = async () => {
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
      await router.delete(route('cursos.destroy', cursoSelecionado.value.id))

      await Swal.fire({
        icon: 'success',
        title: 'Excluído!',
        text: 'O curso foi removido com sucesso.',
        timer: 2000,
        showConfirmButton: false
      })

      fecharModal()
    } catch (error) {
      await Swal.fire({
        icon: 'error',
        title: 'Erro',
        text: 'Ocorreu um erro ao excluir o curso.',
      })
    }
  }
}
</script>

<style>
/* Estilos para selects personalizados */
select {
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.5rem center;
  background-size: 1.5em;
}

/* Transições suaves */
button,
.hover\:shadow-lg,
.cursor-pointer {
  transition: all 0.2s ease;
}

button:active {
  transform: scale(0.98);
}

/* Efeito hover nos cards de curso */
.bg-white:hover {
  transform: translateY(-2px);
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-box-orient: vertical;  
  overflow: hidden;
}
</style>