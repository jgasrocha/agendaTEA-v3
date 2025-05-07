<template>
    <Navbar :auth="$page.props.auth" title="Painel de Administração" @logout="logout" />

    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Usuários Inativos</h1>

            <button @click="goBack" class="btn-back">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                        clip-rule="evenodd" />
                </svg>
                Voltar
            </button>
        </div>

        <!-- Tabela de Usuários Inativos -->
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
                        <button @click="restoreUser(user.id)"
                            class="text-green-500 hover:text-green-700">Ativar</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import Navbar from '@/Components/Navbar.vue'
import { router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const props = defineProps({
    users: Array,
    filters: Object,
    auth: Object
})

const logout = () => {
    router.post(route('logout'))
}

const goBack = () => {
    router.visit(route('admin.users.index'))
}

const restoreUser = (userId) => {
    Swal.fire({
        title: 'Reativar usuário?',
        text: "O usuário será marcado como ativo novamente.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, reativar!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.put(route('admin.users.restore', { user: userId }), {}, {
                onSuccess: () => {
                    Swal.fire(
                        'Reativado!',
                        'O usuário foi reativado com sucesso.',
                        'success'
                    )
                }
            })
        }
    })
}

const formatDate = (dateString) => {
    const options = { day: '2-digit', month: '2-digit', year: 'numeric' }
    return new Date(dateString).toLocaleDateString('pt-BR', options)
}
</script>

<style>
/* Estilos similares ao UserList.vue */
.btn-back {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background-color: #3b82f6;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    font-weight: 500;
}

.btn-back:hover {
    background-color: #2563eb;
}

.btn-inactive-users {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background-color: #6b7280;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    margin-left: 1rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    font-weight: 500;
}

.btn-inactive-users:hover {
    background-color: #4b5563;
}
</style>