<template>
  <nav class="bg-slate-800 text-white px-6 py-3 flex justify-between items-center mb-6">
    <div class="flex gap-4 items-center"> <a href="/">
      <img src="/llogo_nova.png" alt="Logo"
     class="w-20 max-h-16 object-contain mr-2 hover:opacity-80 cursor-pointer scale-150" />
      </a>
      <template v-if="$page.props.auth?.user">
        <button v-if="$page.props.auth.user.is_admin" @click="goToUsers" class="hover:underline">Usuários</button>
        <button @click="goToCursos" class="hover:underline">Cursos</button>
        <button v-if="$page.props.auth.user.is_admin" @click="goToDisciplinas"
          class="hover:underline">Disciplinas</button>
        <button v-if="$page.props.route?.params?.curso" @click="goToCreateAula" class="hover:underline">
          Cadastrar Aula
        </button>

        <div v-if="$page.props.auth.user.is_admin" class="relative">
          <button @click="goToAdminTrocas" class="hover:underline">Solicitações de Troca</button>
          <span v-if="$page.props.pendingRequests > 0"
            class="absolute -top-2 -right-4 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center notification-badge">
            {{ $page.props.pendingRequests }}
          </span>
        </div>

        <button v-if="!$page.props.auth.user.is_admin" @click="goToMinhasTrocas" class="hover:underline">
          Minhas Solicitações
        </button>
      </template>
    </div>

    <a v-if="!$page.props.auth?.user" :href="route('login')" class="bg-sky-600 px-4 py-2 rounded hover:bg-sky-700">
      Entrar
    </a>

    <button v-else @click="logout" class="bg-rose-600 px-4 py-2 rounded hover:bg-rose-700">Sair</button>
  </nav>
</template>

<script setup>
import { router, usePage } from '@inertiajs/vue3'

const goToAgenda = () => {
  router.visit(route('agenda-fixa.index'))
}

const goToCursos = () => {
  if (usePage().props.auth?.user?.is_admin) {
    router.visit(route('admin.cursos.index'))
  } else {
    router.visit(route('cursos.index'))
  }
}

const goToUsers = () => {
  router.visit(route('admin.users.index'))
}

const goToDisciplinas = () => {
  router.visit(route('admin.disciplinas.index'))
}


const goToCreateAula = () => {
  const cursoId = router.page.props.route.params.curso
  const turmaId = router.page.props.route.params.turma

  if (cursoId && turmaId) {
    router.visit(route('cursos.turmas.agendas.create', {
      curso: cursoId,
      turma: turmaId
    }))
  } else {
    // Se não estiver no contexto de uma turma, redireciona para seleção
    router.visit(route('cursos.index'))
  }
}

const goToAdminTrocas = () => {
  router.visit(route('admin.trocas-agenda.index'))
}

const goToMinhasTrocas = () => {
  router.visit(route('troca-agendas.index'))
}

const logout = async () => {
  try {
    await router.post(route('logout'));
    router.visit(route('login'));
  } catch (error) {
    console.error('Erro ao fazer logout:', error);
  }
}
</script>

<style>
.notification-badge {
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% {
    transform: scale(1);
  }

  50% {
    transform: scale(1.1);
  }

  100% {
    transform: scale(1);
  }
}
</style>