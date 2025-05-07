<template>
  <Navbar :auth="$page.props.auth"/>
  <div class="p-6 max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Minhas Solicitações de Troca</h1>

    <div v-if="trocas.length === 0" class="text-gray-500">Nenhuma troca solicitada ainda.</div>

    <div class="grid gap-4">
      <div
        v-for="troca in trocas"
        :key="troca.id"
        class="p-4 border rounded shadow bg-white"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
          <div class="border-r pr-4">
            <h3 class="font-bold text-lg mb-2">Aula Original</h3>
            <p><strong>Disciplina:</strong> {{ getDisciplinaNome(troca.agenda_original) }}</p>
            <p><strong>Dia:</strong> {{ getDiaSemana(troca.agenda_original?.dia_semana) }}</p>
            <p><strong>Horário:</strong> Bloco {{ troca.agenda_original?.bloco }} ({{ troca.agenda_original?.turno }})</p>
          </div>
          
          <div>
            <h3 class="font-bold text-lg mb-2">Aula Desejada</h3>
            <p><strong>Disciplina:</strong> {{ getDisciplinaNome(troca.agenda_desejada) }}</p>
            <p><strong>Dia:</strong> {{ getDiaSemana(troca.agenda_desejada?.dia_semana) }}</p>
            <p><strong>Horário:</strong> Bloco {{ troca.agenda_desejada?.bloco }} ({{ troca.agenda_desejada?.turno }})</p>
          </div>
        </div>

        <div class="mt-3 pt-3 border-t">
          <strong>Status:</strong>
          <span
            :class="{
              'text-yellow-600': troca.status === 'pendente',
              'text-green-600': troca.status === 'aceita',
              'text-red-600': troca.status === 'rejeitada',
            }"
            class="font-medium ml-2"
          >
            {{ formatStatus(troca.status) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Navbar from '@/Components/Navbar.vue';

const diasSemana = {
  1: 'Segunda-feira',
  2: 'Terça-feira',
  3: 'Quarta-feira',
  4: 'Quinta-feira',
  5: 'Sexta-feira',
  6: 'Sábado'
};

const formatStatus = (status) => {
  const statusMap = {
    'pendente': 'Pendente',
    'aceita': 'Aprovada',
    'rejeitada': 'Rejeitada',
    'expirada': 'Expirada'
  };
  return statusMap[status] || status;
};

const getDiaSemana = (diaNum) => {
  return diasSemana[diaNum] || 'N/A';
};

const getDisciplinaNome = (agenda) => {
  return agenda?.disciplina?.nome || 'N/A';
};

defineProps({
  trocas: Array,
});
</script>