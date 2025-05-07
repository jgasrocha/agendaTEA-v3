<template>
  <div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Criar Novo Usuário</h1>

    <form @submit.prevent="submit">
      <div class="mb-4">
        <label class="block mb-1">Nome</label>
        <input type="text" v-model="form.name" class="w-full border px-3 py-2 rounded" required />
      </div>

      <div class="mb-4">
        <label class="block mb-1">Email</label>
        <input type="email" v-model="form.email" class="w-full border px-3 py-2 rounded" required />
      </div>

      <div class="mb-4">
        <label class="block mb-1">Senha (mínimo 6 caracteres)</label>
        <input type="password" v-model="form.password" @input="validatePassword" class="w-full border px-3 py-2 rounded"
          required />
        <p v-if="passwordError && form.password.length > 0" class="text-rose-500 text-sm mt-1">{{ passwordError }}</p>
      </div>

      <div class="mb-4">
        <label class="block mb-1">Confirme a Senha</label>
        <input type="password" v-model="form.password_confirmation" @input="validatePasswordConfirmation"
          class="w-full border px-3 py-2 rounded" required />
        <p v-if="passwordConfirmationError && form.password_confirmation.length > 0" class="text-rose-500 text-sm mt-1">
          {{ passwordConfirmationError }}</p>
      </div>

      <div class="mb-4">
        <label class="block mb-1">Foto</label>
        <input type="file" @change="handlePhoto" />
      </div>

      <div v-if="successMessage" class="text-emerald-600 mb-4">{{ successMessage }}</div>
      <div v-if="errorMessage" class="text-rose-600 mb-4">{{ errorMessage }}</div>

      <button type="submit" :disabled="isFormInvalid"
        :class="{ 'bg-sky-400': isFormInvalid, 'bg-sky-600': !isFormInvalid }" class="text-white px-4 py-2 rounded">
        Criar Usuário
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  photo: null,
})

const passwordError = ref('')
const passwordConfirmationError = ref('')
const successMessage = ref('')
const errorMessage = ref('')

const isFormInvalid = computed(() => {
  return passwordError.value !== '' ||
    passwordConfirmationError.value !== '' ||
    form.value.password.length < 6 ||
    form.value.password !== form.value.password_confirmation
})

const validatePassword = () => {
  if (form.value.password.length > 0) {
    if (form.value.password.length < 6) {
      passwordError.value = 'A senha deve ter pelo menos 6 caracteres'
    } else {
      passwordError.value = ''
    }
    validatePasswordConfirmation()
  } else {
    passwordError.value = ''
  }
}

const validatePasswordConfirmation = () => {
  if (form.value.password_confirmation.length > 0) {
    if (form.value.password_confirmation !== form.value.password) {
      passwordConfirmationError.value = 'As senhas não coincidem'
    } else {
      passwordConfirmationError.value = ''
    }
  } else {
    passwordConfirmationError.value = ''
  }
}

const handlePhoto = (event) => {
  form.value.photo = event.target.files[0]
}

const submit = async () => {
  validatePassword()
  validatePasswordConfirmation()

  if (isFormInvalid.value) {
    errorMessage.value = 'Corrija os erros no formulário antes de enviar'
    return
  }

  const formData = new FormData()
  // Adicione campos explicitamente para garantir a ordem correta
  formData.append('name', form.value.name)
  formData.append('email', form.value.email)
  formData.append('password', form.value.password)
  formData.append('password_confirmation', form.value.password_confirmation)
  if (form.value.photo) {
    formData.append('photo', form.value.photo)
  }

  try {
    const response = await axios.post('/admin/users', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
    })

    // Adicione feedback visual antes do redirecionamento
    successMessage.value = 'Usuário criado com sucesso!'
    setTimeout(() => {
      router.visit('/admin/users')
    }, 1500)

  } catch (error) {
    if (error.response && error.response.data.errors) {
      // Mostra todos os erros de validação
      const errors = Object.values(error.response.data.errors).flat()
      errorMessage.value = errors.join('\n')
    } else {
      errorMessage.value = 'Ocorreu um erro ao criar o usuário'
    }
    console.error('Detalhes do erro:', error.response?.data)
  }
}
</script>