<template>
  <Navbar :auth="$page.props.auth" title="Painel de Administração" @logout="logout" />

  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Lista de Usuários</h1>

      <div class="flex gap-2">
      <button @click="goToCreateUser" class="btn-new-user bg-emerald-600 hover:bg-emerald-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
            clip-rule="evenodd" />
        </svg>
        Novo Usuário
      </button>
      <button @click="goToInactiveUsers" class="btn-inactive-users">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
        </svg>
        Usuários Inativos
      </button>
    </div>
  </div>



    <!-- Filtros -->
    <div class="filter-container">
      <div class="flex flex-wrap items-center gap-4">
        <div class="flex-1 min-w-[200px]">
          <label class="filter-label">Ordenar por</label>
          <select v-model="filters.sort">
            <option value="name">Nome</option>
            <option value="created_at">Data de Criação</option>
          </select>
        </div>

        <div class="flex-1 min-w-[200px]">
          <label class="filter-label">Direção</label>
          <select v-model="filters.direction">
            <option value="asc">Crescente</option>
            <option value="desc">Decrescente</option>
          </select>
        </div>

        <div class="self-end">
          <button @click="applyFilters" class="btn-apply-filters bg-sky-600 hover:bg-sky-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                clip-rule="evenodd" />
            </svg>
            Aplicar Filtros
          </button>
        </div>
      </div>
    </div>

    <!-- Tabela de Usuários -->
    <table class="users-table">
      <thead>
        <tr>
          <th>Foto</th>
          <th>Nome</th>
          <th>Email</th>
          <th>Criado em</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>
            <img :src="user.photo || 'https://via.placeholder.com/40'" alt="Foto" class="user-photo" />
          </td>
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>{{ formatDate(user.created_at) }}</td>
          <td class="text-center">
            <button @click="openModal(user)" class="text-blue-500 hover:text-blue-700">🔍</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal -->
    <div v-if="selectedUser" class="modal-overlay">
      <div class="modal-content">
        <button @click="selectedUser = null" class="modal-close-btn">✖️</button>
        <h2 class="modal-title">Perfil do Usuário</h2>

        <div class="modal-body">
          <div class="photo-upload" @click="triggerPhotoUpload">
            <img :src="selectedUser.photo || 'https://via.placeholder.com/100'" class="user-photo-large" />
            <div class="photo-overlay">
              <svg class="photo-icon" fill="currentColor" viewBox="0 0 24 24">
                <path
                  d="M12 5c1.657 0 3 1.343 3 3 0 .234-.026.462-.074.682l2.852 2.852a1 1 0 01-1.415 1.414L13.5 10.5a3.01 3.01 0 01-.682.074 3 3 0 113-3z" />
              </svg>
            </div>
            <input type="file" ref="fileInput" class="file-input" @change="handlePhotoChange" />
          </div>

          <label class="input-label">
            Nome:
            <input v-model="selectedUser.name" class="text-input" />
          </label>

          <label class="input-label">
            Email:
            <input v-model="selectedUser.email" class="text-input" />
          </label>

          <label class="input-label">
            Criado em:
            <input :value="formatDate(selectedUser.created_at)" class="text-input" disabled />
          </label>

          <label class="checkbox-label">
            <input type="checkbox" v-model="selectedUser.is_admin" />
            Administrador
          </label>

          <div class="modal-actions">
            <button @click="updateUser" class="btn-save bg-emerald-600 hover:bg-emerald-700">Salvar</button>
            <button @click="deleteUser" class="btn-delete bg-rose-600 hover:bg-rose-700">Inativar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Navbar from '@/Components/Navbar.vue'
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const page = usePage()
const filters = ref({ ...page.props.filters })
const selectedUser = ref(null)
const users = computed(() => page.props.users)
const fileInput = ref(null)
const newPhoto = ref(null)

const applyFilters = () => {
  router.get(route('admin.users.index'), filters.value, {
    preserveScroll: true,
    only: ['users'],
    preserveState: false,
  })
}

const goToInactiveUsers = async () => {
  try {
    await router.visit(route('admin.users.inactive'))
  } catch (error) {
    console.error('Navigation error:', error)
    Swal.fire({
      title: 'Erro',
      text: 'Não foi possível acessar a lista de usuários inativos',
      icon: 'error'
    })
  }
}

const goToCreateUser = () => {
  router.visit(route('admin.users.create'))
}

const openModal = (user) => {
  selectedUser.value = {
    ...user,
    is_admin: !!user.is_admin,
  }
}

const updateUser = async () => {
  try {
    const formData = new FormData()
    formData.append('_method', 'PUT') // Necessário para simular PUT com FormData
    formData.append('name', selectedUser.value.name)
    formData.append('email', selectedUser.value.email)
    formData.append('is_admin', selectedUser.value.is_admin ? '1' : '0') // Enviar como string

    if (newPhoto.value) {
      formData.append('photo', newPhoto.value)
    }

    // Sempre usar a mesma rota de atualização
    await router.post(
      route('admin.users.update', { user: selectedUser.value.id }),
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        },
        onSuccess: () => {
          Swal.fire({
            title: 'Sucesso!',
            text: 'Usuário atualizado com sucesso.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
          selectedUser.value = null;
          newPhoto.value = null;
          applyFilters();
        },
        onError: (errors) => {
          Swal.fire({
            title: 'Erro!',
            text: Object.values(errors).join('\n'),
            icon: 'error'
          });
        }
      }
    );
  } catch (error) {
    console.error('Erro ao atualizar usuário:', error)
  }
}

const deleteUser = async () => {
  const result = await Swal.fire({
    title: 'Inativar usuário?',
    text: "O usuário será marcado como inativo e não aparecerá mais nesta lista.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Sim, inativar!',
    cancelButtonText: 'Cancelar'
  });

  if (result.isConfirmed) {
    try {
      await router.delete(route('admin.users.destroy', selectedUser.value.id), {
        onSuccess: () => {
          Swal.fire({
            title: 'Inativado!',
            text: 'O usuário foi inativado com sucesso.',
            icon: 'success',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false
          });

          // Força a recarga completa da página para garantir
          window.location.reload();
        },
        onError: () => {
          Swal.fire({
            title: 'Erro!',
            text: 'Ocorreu um erro ao inativar o usuário.',
            icon: 'error'
          });
        }
      });
    } catch (error) {
      console.error('Erro ao inativar usuário:', error);
      Swal.fire({
        title: 'Erro!',
        text: 'Ocorreu um erro ao inativar o usuário.',
        icon: 'error'
      });
    }
  }
};

const logout = () => {
  router.post(route('logout'))
}

const formatDate = (dateString) => {
  const options = { day: '2-digit', month: '2-digit', year: 'numeric' }
  return new Date(dateString).toLocaleDateString('pt-BR', options)
}

const triggerPhotoUpload = () => {
  fileInput.value?.click()
}

const handlePhotoChange = (event) => {
  const file = event.target.files[0]
  if (!file || !selectedUser.value) return

  newPhoto.value = file
  const reader = new FileReader()
  reader.onload = (e) => {
    selectedUser.value.photo = e.target.result
  }
  reader.readAsDataURL(file)
}
</script>


<style>
/* Estilos gerais */
.btn-new-user {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: all 0.2s;
  border: none;
  cursor: pointer;
  font-weight: 500;
}

.btn-new-user:hover {
  transform: scale(1.05);
}

.filter-container {
  background-color: white;
  padding: 1rem;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  margin-bottom: 1.5rem;
}

.filter-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.25rem;
}

select {
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.5rem center;
  background-size: 1.5em;
  padding: 0.5rem 2.5rem 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  width: 100%;
  font-size: 1rem;
  line-height: 1.5;
  color: #374151;
  background-color: white;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
}

.btn-apply-filters {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  transition: background-color 0.2s;
  border: none;
  cursor: pointer;
  font-weight: 500;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.users-table th {
  background-color: #f9fafb;
  color: #374151;
  font-weight: 600;
  padding: 0.75rem;
  text-align: left;
  border: 1px solid #e5e7eb;
}

.users-table td {
  color: #4b5563;
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
}

.users-table tr:hover td {
  background-color: #f9fafb;
}

.user-photo {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}

.modal-content {
  background-color: white;
  border-radius: 0.5rem;
  padding: 1.5rem;
  width: 100%;
  max-width: 32rem;
  position: relative;
}

.modal-close-btn {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: #6b7280;
}

.modal-close-btn:hover {
  color: #374151;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: #111827;
}

.modal-body {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.photo-upload {
  position: relative;
  width: 6rem;
  height: 6rem;
  margin: 0 auto;
  cursor: pointer;
}

.user-photo-large {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

.photo-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.4);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
}

.photo-upload:hover .photo-overlay {
  opacity: 1;
}

.photo-icon {
  width: 1.5rem;
  height: 1.5rem;
  color: white;
}

.file-input {
  display: none;
}

.input-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.25rem;
}

.text-input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 1rem;
}

.text-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
}

.modal-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 1rem;
}

.btn-save {
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  border: none;
  cursor: pointer;
  font-weight: 500;
}

.btn-delete {
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  border: none;
  cursor: pointer;
  font-weight: 500;
}

.btn-inactive-users {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background-color: #40485a; /* Azul diferente do verde do botão novo */
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: all 0.2s;
  border: none;
  cursor: pointer;
  font-weight: 500;
  margin-left: 0.5rem; /* Espaço entre os botões */
}

.btn-inactive-users:hover {
  background-color: #383e4b; /* Tom mais escuro para o hover */
  transform: scale(1.05);
}

button {
  transition: all 0.2s ease;
}

button:active {
  transform: scale(0.98);
}
</style>