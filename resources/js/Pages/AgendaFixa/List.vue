<template>
  <Navbar :auth="$page.props.auth" />
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center my-6">
      <div>
        <h1 class="text-2xl font-bold">Agenda da Turma: {{ turma.nome }}</h1>
        <p class="text-gray-600">Semestre: {{ formatSemestre(turma.semestre) }}</p>
      </div>
      
      <Link v-if="canEdit" :href="route('cursos.turmas.agendas.create', { curso: turma.curso_id, turma: turma.id })"
        class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Nova Aula
      </Link>
    </div>

    <!-- Botão para ativar/desativar o drag-and-drop -->
    <div v-if="canEdit" class="flex justify-end mb-4">
      <button @click="toggleDragAndDrop" class="p-2 rounded-full bg-blue-500 text-white hover:bg-blue-600 transition">
        {{ dragAndDropEnabled ? 'Desativar Troca' : 'Ativar Troca' }}
      </button>
    </div>

    <!-- Modal de confirmação de movimento -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-80">
        <h2 class="text-xl font-semibold mb-4">Você tem certeza que deseja mover esta aula?</h2>
        <div class="flex justify-between">
          <button @click="cancelMove" class="p-2 bg-gray-300 rounded text-black">Cancelar</button>
          <button @click="confirmMove" class="p-2 bg-blue-500 text-white rounded">Confirmar</button>
        </div>
      </div>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-80">
        <h2 class="text-xl font-semibold mb-4">Confirmar exclusão</h2>
        <p class="mb-4">Tem certeza que deseja remover esta aula?</p>
        <div class="flex justify-between">
          <button @click="showDeleteModal = false" class="p-2 bg-gray-300 rounded text-black">Cancelar</button>
          <button @click="deleteAula" :disabled="deleting"
            class="p-2 bg-red-500 text-white rounded flex items-center justify-center">
            <span v-if="deleting">Excluindo...</span>
            <span v-else>Excluir</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Versão Mobile (Apenas em telas pequenas) -->
    <div class="md:hidden">
      <!-- Controles Mobile -->
      <div class="flex justify-between items-center mb-4">
        <button @click="previousDay" class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <h2 class="text-xl font-semibold capitalize">{{ formatDay(currentDayIndex) }}</h2>

        <button @click="nextDay" class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>

    <div v-for="turno in ['manhã', 'tarde']" :key="turno" class="mb-10">
      <h2 class="text-xl font-semibold mb-4 capitalize">{{ turno }}</h2>

      <!-- Versão Desktop (tabela completa) -->
      <div class="hidden md:block">
        <div class="overflow-x-auto">
          <table class="table-auto w-full border border-gray-300">
            <thead>
              <tr class="bg-gray-100">
                <th v-for="(dia, index) in diasSemana" :key="index" class="border p-2 text-center">
                  {{ dia }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="bloco in (turno === 'manhã' ? blocosManha : blocosTarde)" :key="bloco">
                <td v-for="(dia, index) in diasSemana" :key="index" class="border p-2 text-center"
                  style="width: 14.28%; height: 120px;"
                  :draggable="!!(dragAndDropEnabled && canDrag(index, bloco, turno))"
                  @dragstart="onDragStart($event, index, bloco, turno)" @dragover="onDragOver($event)"
                  @drop="onDrop($event, index, bloco, turno)">
                  <div v-if="agendaMap[turno] && agendaMap[turno][index + 1] && agendaMap[turno][index + 1][bloco]"
                    class="flex justify-center items-center h-full relative group">
                    <!-- Botão de deletar (apenas para admin) -->
                    <div v-if="canEdit"
                      class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                      <button @click.stop="confirmDelete(index + 1, bloco, turno)"
                        class="p-1 bg-red-500 text-white rounded-full hover:bg-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                          stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>

                    <div :class="{
                      'bg-white border-gray-200': agendaMap[turno][index + 1][bloco].original,
                      'aula-trocada': !agendaMap[turno][index + 1][bloco].original
                    }" class="p-2 border rounded-lg shadow-md w-full h-full flex flex-col justify-center">
                      <img v-if="agendaMap[turno][index + 1][bloco].user_photo"
                        :src="agendaMap[turno][index + 1][bloco].user_photo" alt="Foto do Professor"
                        class="w-12 h-12 object-cover mx-auto mb-1 rounded-full" />
                      <p class="font-semibold text-sm text-center">
                        {{ agendaMap[turno][index + 1][bloco].disciplina_nome }}
                      </p>
                      <p v-if="!agendaMap[turno][index + 1][bloco].original" class="text-xs text-green-600 mt-1">
                        Troca Temporária
                      </p>
                    </div>
                  </div>
                  <div v-else class="text-gray-400 italic h-full flex items-center justify-center">Sem aula</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Versão Mobile (carrossel por dia) -->
      <div class="md:hidden">
        <div class="bg-white rounded-lg shadow-md p-4 mb-4">
          <h3 class="font-semibold mb-3 text-lg">{{ formatDay(currentDayIndex) }}</h3>

          <div v-for="bloco in (turno === 'manhã' ? blocosManha : blocosTarde)" :key="bloco"
            class="mb-4 border-b pb-4 last:border-b-0">
            <div
              v-if="agendaMap[turno] && agendaMap[turno][currentDayIndex + 1] && agendaMap[turno][currentDayIndex + 1][bloco]">
              <div class="flex items-center space-x-3 relative">
                <!-- Botão de deletar para mobile (apenas para admin) -->
                <button v-if="canEdit"
                  @click="confirmDelete(currentDayIndex + 1, bloco, turno)"
                  class="absolute top-0 right-0 p-1 bg-red-500 text-white rounded-full hover:bg-red-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>

                <!-- Imagem do Professor - Versão Corrigida -->
                <div class="relative">
                  <img v-if="agendaMap[turno][currentDayIndex + 1][bloco].user_photo"
                    :src="agendaMap[turno][currentDayIndex + 1][bloco].user_photo"
                    :alt="agendaMap[turno][currentDayIndex + 1][bloco].professor || 'Professor'"
                    class="w-10 h-10 object-cover rounded-full border-2 border-gray-200 shadow-sm"
                    @error="handleImageError" />
                  <div v-if="!agendaMap[turno][currentDayIndex + 1][bloco].original"
                    class="absolute -top-1 -right-1 bg-green-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                    <span class="text-[10px]">T</span>
                  </div>
                </div>

                <div>
                  <p class="font-semibold">{{ agendaMap[turno][currentDayIndex + 1][bloco].disciplina_nome }}</p>
                  <p class="text-sm text-gray-500">
                    {{ agendaMap[turno][currentDayIndex + 1][bloco].professor || 'Professor não definido' }}
                  </p>
                </div>
              </div>
            </div>
            <div v-else class="text-gray-400 italic py-2">Sem aula</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Navbar from '@/Components/Navbar.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  turma: Object,
  agendas: Array,
  trocasAtivas: Array,
  canEdit: Boolean
});

// Configuração básica
const diasSemana = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
const blocosManha = [1, 2, 3, 4];
const blocosTarde = [5, 6, 7, 8];
const currentDayIndex = ref(getCurrentDayIndex());
const dragAndDropEnabled = ref(false);
const showModal = ref(false);
const showDeleteModal = ref(false);
const deleting = ref(false);
const draggedItem = ref(null);
const itemToMove = ref(null);
const aulaToDelete = ref(null);
const pollingInterval = ref(null);
const ultimaVerificacao = ref(new Date());
const localAgendaMap = ref({});

// Formatar semestre
const formatSemestre = (semestre) => {
  const [ano, periodo] = semestre.split('.');
  return `${ano} - ${periodo === '1' ? '1º Semestre' : '2º Semestre'}`;
};

// Obter dia atual
function getCurrentDayIndex() {
  const today = new Date().getDay();
  return today >= 1 && today <= 6 ? today - 1 : 0;
}

// Navegação mobile
const previousDay = () => {
  currentDayIndex.value = currentDayIndex.value === 0 ? 5 : currentDayIndex.value - 1;
};

const nextDay = () => {
  currentDayIndex.value = currentDayIndex.value === 5 ? 0 : currentDayIndex.value + 1;
};

// Formatar dia para exibição
const formatDay = (index) => {
  return diasSemana[index] || 'Segunda';
};

// Drag and Drop
const toggleDragAndDrop = () => {
  dragAndDropEnabled.value = !dragAndDropEnabled.value;
};

const canDrag = (index, bloco, turno) => {
  if (!props.canEdit) return false;
  const item = localAgendaMap.value[turno][index + 1] && localAgendaMap.value[turno][index + 1][bloco];
  return item && item.user_id === props.auth?.user?.id;
};

const onDragStart = (event, index, bloco, turno) => {
  if (!dragAndDropEnabled.value || !canDrag(index, bloco, turno)) {
    event.preventDefault();
    return;
  }
  draggedItem.value = {
    index,
    bloco,
    turno,
    item: localAgendaMap.value[turno][index + 1][bloco]
  };
};

const onDragOver = (event) => {
  event.preventDefault();
};

const onDrop = (event, index, bloco, turno) => {
  if (draggedItem.value) {
    event.preventDefault();
    itemToMove.value = {
      index,
      bloco,
      turno,
      item: localAgendaMap.value[turno][index + 1][bloco]
    };
    showModal.value = true;
  }
};

// Modal de movimento
const cancelMove = () => {
  showModal.value = false;
  draggedItem.value = null;
  itemToMove.value = null;
};

const confirmMove = async () => {
  if (!props.canEdit) return;

  const agendaOriginal = props.agendas.find(agenda =>
    agenda.dia_semana === draggedItem.value.index + 1 &&
    agenda.bloco === draggedItem.value.bloco &&
    agenda.turno === draggedItem.value.turno
  );

  const agendaDesejada = props.agendas.find(agenda =>
    agenda.dia_semana === itemToMove.value.index + 1 &&
    agenda.bloco === itemToMove.value.bloco &&
    agenda.turno === itemToMove.value.turno
  );

  if (!agendaOriginal || !agendaDesejada) {
    Swal.fire({
      title: 'Erro',
      text: 'Não foi possível encontrar as agendas para troca.',
      icon: 'error'
    });
    return;
  }

  try {
    await router.post(route('troca-agendas.store'), {
      agenda_original_id: agendaOriginal.id,
      agenda_desejada_id: agendaDesejada.id,
      disciplina_original_id: agendaOriginal.disciplina_id,
      disciplina_desejada_id: agendaDesejada.disciplina_id,
      turma_id: props.turma.id
    });

    showModal.value = false;
    draggedItem.value = null;
    itemToMove.value = null;

    Swal.fire({
      title: 'Sucesso!',
      text: 'Solicitação de troca enviada com sucesso.',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false
    });

    // Recarregar os dados
    router.reload({ only: ['agendas', 'trocasAtivas'] });
  } catch (error) {
    console.error('Erro ao enviar solicitação:', error);
    Swal.fire({
      title: 'Erro',
      text: 'Ocorreu um erro ao processar a troca.',
      icon: 'error'
    });
  }
};

// Exclusão de aula
const confirmDelete = (dia, bloco, turno) => {
  if (!props.canEdit) return;
  aulaToDelete.value = { dia, bloco, turno };
  showDeleteModal.value = true;
};

const deleteAula = async () => {
  if (!aulaToDelete.value || !props.canEdit) return;

  deleting.value = true;
  const { dia, bloco, turno } = aulaToDelete.value;
  const agenda = props.agendas.find(a =>
    a.dia_semana === dia &&
    a.bloco === bloco &&
    a.turno === turno
  );

  if (!agenda) {
    console.error('Aula não encontrada');
    deleting.value = false;
    Swal.fire({
      title: 'Erro',
      text: 'Aula não encontrada.',
      icon: 'error'
    });
    return;
  }

  try {
    await router.delete(route('cursos.turmas.agendas.destroy', {
      curso: props.turma.curso_id,
      turma: props.turma.id,
      agenda: agenda.id
    }));

    showDeleteModal.value = false;
    Swal.fire({
      title: 'Sucesso',
      text: 'Aula removida com sucesso.',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false
    });

    // Recarregar os dados
    router.reload({ only: ['agendas', 'trocasAtivas'] });
  } catch (error) {
    console.error('Erro ao excluir aula:', error);
    Swal.fire({
      title: 'Erro',
      text: 'Ocorreu um erro ao excluir a aula.',
      icon: 'error'
    });
  } finally {
    deleting.value = false;
  }
};

// Calcular mapa de agenda
const calcularAgendaMap = (agendas) => {
  const map = { manhã: {}, tarde: {} };
  const hoje = new Date();
  hoje.setHours(0, 0, 0, 0);

  agendas.forEach(agenda => {
    const { dia_semana, bloco, turno, disciplina, user } = agenda;

    if (!map[turno][dia_semana]) map[turno][dia_semana] = {};

    map[turno][dia_semana][bloco] = {
      disciplina_nome: disciplina?.nome || 'Sem Disciplina',
      user_photo: user?.photo ? '/storage/' + user.photo : '',
      professor: user?.name || '',
      original: true,
      id: agenda.id,
      user_id: user?.id
    };
  });

  props.trocasAtivas?.forEach(troca => {
    const dataInicio = new Date(troca.data_inicio);
    dataInicio.setHours(0, 0, 0, 0);

    const dataFim = new Date(troca.data_fim);
    dataFim.setHours(23, 59, 59, 999);

    const estaAtiva = troca.status === 'aceita' &&
      dataInicio <= hoje &&
      hoje <= dataFim;

    if (estaAtiva && troca.agenda_original && troca.agenda_desejada) {
      aplicarTroca(map, troca.agenda_original, troca.agenda_desejada);
      aplicarTroca(map, troca.agenda_desejada, troca.agenda_original);
    }
  });

  return map;
};

const aplicarTroca = (map, origem, destino) => {
  if (origem.turno && origem.dia_semana && origem.bloco &&
    destino.disciplina && destino.user) {

    if (!map[origem.turno][origem.dia_semana]) {
      map[origem.turno][origem.dia_semana] = {};
    }

    map[origem.turno][origem.dia_semana][origem.bloco] = {
      disciplina_nome: destino.disciplina.nome,
      user_photo: destino.user.photo ? '/storage/' + destino.user.photo : '',
      professor: destino.user.name,
      original: false,
      id: origem.id,
      user_id: destino.user.id
    };
  }
};

// Atualizar agenda quando os dados mudam
watch(() => props.agendas, (newAgendas) => {
  localAgendaMap.value = calcularAgendaMap(newAgendas);
}, { deep: true, immediate: true });

// Verificação de trocas expiradas
const verificarTrocasExpiradas = () => {
  const agora = new Date();
  const trocasExpiradas = props.trocasAtivas?.filter(troca => {
    const dataFim = new Date(troca.data_fim);
    dataFim.setHours(23, 59, 59, 999);

    const ultimaVerificacao = new Date(ultimaVerificacao.value);
    const estavaAtiva = new Date(troca.data_inicio) <= ultimaVerificacao &&
      ultimaVerificacao <= dataFim;

    return estavaAtiva && agora > dataFim;
  });

  if (trocasExpiradas?.length > 0) {
    console.log('Trocas expiradas encontradas:', trocasExpiradas);
    router.reload({ only: ['agendas', 'trocasAtivas'] });
  }

  ultimaVerificacao.value = new Date();
};

// Configurar intervalo de verificação
onMounted(() => {
  pollingInterval.value = setInterval(verificarTrocasExpiradas, 60000); // Verifica a cada minuto
  
  // Solicitar permissão para notificações
  if ('Notification' in window) {
    Notification.requestPermission();
  }
});

// Limpar intervalo ao desmontar
onBeforeUnmount(() => {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value);
  }
});

// Tratamento de erro de imagem
const handleImageError = (event) => {
  event.target.src = '/images/default-profile.png';
};
</script>

<style scoped>
.table-auto {
  table-layout: fixed;
}

.border {
  border: 1px solid #e2e8f0;
}

.bg-white {
  background-color: white;
}

.shadow-md {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.rounded-lg {
  border-radius: 0.5rem;
}

.flex {
  display: flex;
}

.justify-center {
  justify-content: center;
}

.items-center {
  align-items: center;
}

.h-full {
  height: 100%;
}

.w-full {
  width: 100%;
}

.text-sm {
  font-size: 0.875rem;
}

.rounded-full {
  border-radius: 9999px;
}

.transition {
  transition: all 0.2s ease;
}

.hover\:bg-gray-200:hover {
  background-color: #edf2f7;
}

.fixed {
  z-index: 9999;
}

.absolute {
  z-index: 10;
}

.group:hover .group-hover\:opacity-100 {
  transition: opacity 0.2s ease;
}

.bg-red-500 {
  background-color: #ef4444;
}

.bg-red-500:hover {
  background-color: #dc2626;
}

.aula-trocada {
  background-color: #f0fdf4;
  border-left: 4px solid #10b981;
  position: relative;
}

.aula-trocada::after {
  content: "TROCA TEMPORÁRIA";
  position: absolute;
  top: 2px;
  right: 2px;
  background-color: #10b981;
  color: white;
  font-size: 0.6rem;
  padding: 2px 4px;
  border-radius: 4px;
}

@media (min-width: 768px) {
  .mobile-controls {
    display: none;
  }
}
</style>