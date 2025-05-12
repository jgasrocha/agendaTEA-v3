<template>
  <Navbar :auth="$page.props.auth" />
  <div class="p-6 max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Criar Novo Curso</h1>
      <Link :href="route('admin.cursos.index')"
        class="flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors shadow-md">
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Curso *</label>
            <input v-model="form.nome" type="text" required
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <p v-if="errors.nome" class="mt-1 text-sm text-red-600">{{ errors.nome }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea v-model="form.descricao"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 h-32"></textarea>
            <p v-if="errors.descricao" class="mt-1 text-sm text-red-600">{{ errors.descricao }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Administrador do Curso (opcional)</label>
            <select v-model="form.admin_id"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option :value="null">Selecione um administrador</option>
              <option v-for="user in usuarios" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Imagem do Curso</label>
            <div class="flex items-center gap-4">
              <div class="w-32 h-32 border border-gray-300 rounded-lg overflow-hidden">
                <img v-if="previewImagem" :src="previewImagem" alt="Preview da imagem"
                  class="w-full h-full object-cover" />
                <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
              </div>
              <div class="flex-1">
                <input type="file" @change="selecionarImagem" accept="image/*" class="w-full text-sm text-gray-500
                      file:mr-4 file:py-2 file:px-4
                      file:rounded-md file:border-0
                      file:text-sm file:font-semibold
                      file:bg-blue-50 file:text-blue-700
                      hover:file:bg-blue-100" />
                <p class="mt-1 text-xs text-gray-500">Formatos suportados: JPG, PNG, GIF. Tamanho máximo: 2MB.</p>
                <p v-if="errors.imagem" class="mt-1 text-sm text-red-600">{{ errors.imagem }}</p>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <Link :href="route('admin.cursos.index')"
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
            Cancelar
            </Link>
            <button type="submit"
              class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors"
              :disabled="form.processing">
              <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              <span>{{ form.processing ? 'Salvando...' : 'Salvar Curso' }}</span>
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
import { ref } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  errors: Object,
  usuarios: Array
})

const form = useForm({
  nome: '',
  descricao: '',
  imagem: null,
  admin_id: null
})

const previewImagem = ref(null)

const selecionarImagem = (e) => {
  const file = e.target.files[0]
  if (file) {
    // Verifica o tamanho do arquivo (máximo 2MB)
    if (file.size > 2 * 1024 * 1024) {
      Swal.fire({
        icon: 'error',
        title: 'Arquivo muito grande',
        text: 'A imagem deve ter no máximo 2MB.',
      })
      e.target.value = '' // Limpa o input
      return
    }

    // Verifica o tipo do arquivo
    const tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif']
    if (!tiposPermitidos.includes(file.type)) {
      Swal.fire({
        icon: 'error',
        title: 'Formato não suportado',
        text: 'Por favor, selecione uma imagem no formato JPG, PNG ou GIF.',
      })
      e.target.value = '' // Limpa o input
      return
    }

    form.imagem = file
    previewImagem.value = URL.createObjectURL(file)
  }
}

const submitForm = () => {
  form.post(route('admin.cursos.store'), {
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: 'Sucesso!',
        text: 'Curso criado com sucesso.',
        timer: 2000,
        showConfirmButton: false
      }).then(() => {
        router.visit(route('admin.cursos.index'))
      })
    },
    onError: () => {
      Swal.fire({
        icon: 'error',
        title: 'Erro',
        text: 'Ocorreu um erro ao criar o curso. Verifique os dados e tente novamente.',
      })
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