<template>
    <div>
        <!-- Mensagens de feedback -->
        <div v-if="successMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
            <p>{{ successMessage }}</p>
        </div>
        <div v-if="errorMessage" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
            <p>{{ errorMessage }}</p>
        </div>
        <Navbar :auth="$page.props.auth" />
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-bold">Solicitações de Troca de Agenda</h1>
                </div>
            </div>

            <div class="mt-8 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Professor
                                    </th>
                                    <th scope="col"
                                        class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Detalhes da Troca
                                    </th>
                                    <th scope="col"
                                        class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="troca in trocasList" :key="troca.id">
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <img v-if="troca.user?.photo" :src="'/storage/' + troca.user.photo"
                                                class="h-10 w-10 rounded-full mr-3" />
                                            <span>{{ troca.user?.name || 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="border-r pr-4">
                                                <h3 class="font-bold text-sm mb-1">Aula Original</h3>
                                                <p><strong>Disciplina:</strong> {{
                                                    troca.agenda_original?.disciplina?.nome
                                                    || 'N/A' }}</p>
                                                <p><strong>Dia:</strong> {{
                                                    getDiaSemana(troca.agenda_original?.dia_semana)
                                                    }}</p>
                                                <p><strong>Horário:</strong> {{
                                                    formatHorario(troca.agenda_original?.bloco,
                                                        troca.agenda_original?.turno) }}</p>
                                                <p><strong>Professor:</strong> {{ troca.agenda_original?.user?.name ||
                                                    'N/A'
                                                    }}</p>
                                            </div>

                                            <div>
                                                <h3 class="font-bold text-sm mb-1">Aula Desejada</h3>
                                                <p><strong>Disciplina:</strong> {{
                                                    troca.agenda_desejada?.disciplina?.nome
                                                    || 'N/A' }}</p>
                                                <p><strong>Dia:</strong> {{
                                                    getDiaSemana(troca.agenda_desejada?.dia_semana)
                                                    }}</p>
                                                <p><strong>Horário:</strong> {{
                                                    formatHorario(troca.agenda_desejada?.bloco,
                                                        troca.agenda_desejada?.turno) }}</p>
                                                <p><strong>Professor:</strong> {{ troca.agenda_desejada?.user?.name ||
                                                    'N/A'
                                                    }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <span :class="{
                                            'bg-yellow-100 text-yellow-800': troca.status === 'pendente',
                                            'bg-green-100 text-green-800': troca.status === 'aceita',
                                            'bg-red-100 text-red-800': troca.status === 'rejeitada'
                                        }" class="px-2 py-1 rounded-full text-xs font-medium">
                                            {{ formatStatus(troca.status) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <button v-if="troca.status === 'pendente'"
                                            @click="aprovarTroca(troca.id, troca.agenda_desejada?.bloco)"
                                            class="mr-2 text-green-600 hover:text-green-900"
                                            :disabled="processingApproval === troca.id">
                                            <span v-if="processingApproval === troca.id">Aprovando...</span>
                                            <span v-else>Aprovar</span>
                                        </button>
                                        <button v-if="troca.status === 'pendente'" @click="rejeitarTroca(troca.id)"
                                            class="text-red-600 hover:text-red-900"
                                            :disabled="processingRejection === troca.id">
                                            <span v-if="processingRejection === troca.id">Rejeitando...</span>
                                            <span v-else>Rejeitar</span>
                                        </button>
                                        <span v-if="troca.status !== 'pendente'" class="text-gray-400">
                                            {{ formatStatus(troca.status) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Navbar from '@/Components/Navbar.vue';
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { watch } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    trocas: {
        type: Array,
        default: () => [],
    },
    pendingRequests: Number,
});

const successMessage = ref('');
const errorMessage = ref('');
const processingApproval = ref(null);
const processingRejection = ref(null);

const trocasList = ref([...props.trocas]);

watch(() => props.trocas, (newTrocas) => {
    trocasList.value = [...newTrocas];
}, { deep: true });

onMounted(() => {
    if (!Array.isArray(props.trocas)) {
        console.error('Prop "trocas" deve ser um array');
    }
});

const diasSemana = {
    1: 'Segunda-feira',
    2: 'Terça-feira',
    3: 'Quarta-feira',
    4: 'Quinta-feira',
    5: 'Sexta-feira',
    6: 'Sábado'
};

const blocosHorarios = {
    1: '1º Horário (08:00 - 09:00)',
    2: '2º Horário (09:00 - 10:10)',
    3: '3º Horário (10:10 - 11:10)',
    4: '4º Horário (11:10 - 12:00)',
    5: '5º Horário (13:10 - 14:10)',
    6: '6º Horário (14:10 - 15:10)',
    7: '7º Horário (15:30 - 16:20)',
    8: '8º Horário (16:20 - 17:00)'
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

const formatHorario = (bloco, turno) => {
    if (!bloco || !turno) return 'N/A';
    const horario = blocosHorarios[bloco] || `Bloco ${bloco}`;
    return `${horario} (${turno})`;
};

const aprovarTroca = async (trocaId) => {
    try {
        processingApproval.value = trocaId;

        await Swal.fire({
            title: 'Confirmar aprovação',
            text: "Deseja realmente aprovar esta solicitação de troca?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, aprovar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                router.put(`/admin/trocas-agenda/${trocaId}/aprovar`, {}, {
                    preserveScroll: true,
                    onSuccess: () => {
                        Swal.fire(
                            'Aprovado!',
                            'A troca foi aprovada com sucesso.',
                            'success'
                        );
                        
                        window.dispatchEvent(new Event('troca-aprovada'));
                        router.reload({
                            only: ['trocas', 'agendas', 'trocasAtivas'],
                            onFinish: () => {
                                processingApproval.value = null;
                            }
                        });
                    },
                    onError: (errors) => {
                        Swal.fire(
                            'Erro!',
                            errors.message || 'Erro ao aprovar troca',
                            'error'
                        );
                        processingApproval.value = null;
                    }
                });
            } else {
                processingApproval.value = null;
            }
        });
    } catch (error) {
        Swal.fire(
            'Erro!',
            'Erro ao processar a solicitação',
            'error'
        );
        processingApproval.value = null;
    }
};

const rejeitarTroca = async (trocaId) => {
    processingRejection.value = trocaId;

    await Swal.fire({
        title: 'Confirmar rejeição',
        text: "Deseja realmente rejeitar esta solicitação de troca?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, rejeitar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.put(`/admin/trocas-agenda/${trocaId}/rejeitar`, {}, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire(
                        'Rejeitada!',
                        'A troca foi rejeitada com sucesso.',
                        'success'
                    );
                    
                    window.dispatchEvent(new CustomEvent('troca-rejeitada'));
                    setTimeout(() => {
                        router.reload({ only: ['trocas', 'agendas', 'trocasAtivas'] });
                    }, 500);
                },
                onError: (errors) => {
                    Swal.fire(
                        'Erro!',
                        errors.message || 'Erro ao rejeitar troca',
                        'error'
                    );
                },
                onFinish: () => {
                    processingRejection.value = null;
                }
            });
        } else {
            processingRejection.value = null;
        }
    });
};
</script>