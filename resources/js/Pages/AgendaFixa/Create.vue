<template>
  <div class="max-w-xl mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6 text-center">Cadastrar Nova Aula Fixa</h1>
    
    <form @submit.prevent="submitForm" class="bg-white shadow-md rounded-lg p-6">
      <!-- Disciplina -->
      <div class="mb-6">
        <label for="disciplina_id" class="block text-gray-700 font-medium mb-2">Disciplina</label>
        <select 
          v-model="form.disciplina_id" 
          id="disciplina_id" 
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          required
          @change="updateProfessorSugerido"
        >
          <option value="" disabled selected>Selecione uma disciplina</option>
          <option 
            v-for="disciplina in disciplinas" 
            :key="disciplina.id" 
            :value="disciplina.id"
            class="py-1"
          >
            {{ disciplina.nome }}
          </option>
        </select>
        <span v-if="form.errors.disciplina_id" class="text-red-500 text-sm mt-1 block">{{ form.errors.disciplina_id }}</span>
      </div>

      <!-- Professor -->
      <div class="mb-6">
        <label for="user_id" class="block text-gray-700 font-medium mb-2">Professor</label>
        <select 
          v-model="form.user_id" 
          id="user_id" 
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          required
          :disabled="!!professorSugerido"
        >
          <option value="" disabled selected>Selecione um professor</option>
          <option 
            v-for="user in users" 
            :key="user.id" 
            :value="user.id"
            class="py-1"
          >
            {{ user.name }}
          </option>
        </select>
        <div v-if="professorSugerido" class="mt-2 text-sm text-blue-600">
          Esta disciplina está associada ao professor {{ professorSugerido.name }} neste curso.
        </div>
        <span v-if="form.errors.user_id" class="text-red-500 text-sm mt-1 block">{{ form.errors.user_id }}</span>
      </div>

      <!-- Dia da Semana -->
      <div class="mb-6">
        <label class="block text-gray-700 font-medium mb-2">Dia da Semana</label>
        <div class="grid grid-cols-3 gap-2">
          <button
            v-for="(dia, index) in diasSemana"
            :key="index"
            type="button"
            @click="form.dia_semana = index + 1"
            :class="[
              'py-2 px-4 rounded-lg border transition',
              form.dia_semana === index + 1 
                ? 'bg-blue-500 text-white border-blue-500' 
                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
            ]"
          >
            {{ dia }}
          </button>
        </div>
        <span v-if="form.errors.dia_semana" class="text-red-500 text-sm mt-1 block">{{ form.errors.dia_semana }}</span>
      </div>

      <!-- Turno e Horário -->
      <div class="mb-6">
        <label class="block text-gray-700 font-medium mb-2">Turno e Horário</label>
        
        <div class="flex mb-4">
          <button
            type="button"
            @click="setTurno('manhã')"
            :class="[
              'flex-1 py-2 px-4 rounded-l-lg border transition',
              form.turno === 'manhã' 
                ? 'bg-blue-500 text-white border-blue-500' 
                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
            ]"
          >
            Manhã
          </button>
          <button
            type="button"
            @click="setTurno('tarde')"
            :class="[
              'flex-1 py-2 px-4 rounded-r-lg border transition',
              form.turno === 'tarde' 
                ? 'bg-blue-500 text-white border-blue-500' 
                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
            ]"
          >
            Tarde
          </button>
        </div>

        <div class="grid grid-cols-4 gap-2">
          <button
            v-for="horario in 4"
            :key="horario"
            type="button"
            @click="form.bloco = form.turno === 'manhã' ? horario : horario + 4"
            :class="[
              'py-2 px-4 rounded-lg border transition',
              form.bloco === (form.turno === 'manhã' ? horario : horario + 4)
                ? 'bg-blue-500 text-white border-blue-500' 
                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
            ]"
          >
            {{ horario }}º Horário
            <span class="block text-xs opacity-80">
              {{ getHoraIntervalo(horario) }}
            </span>
          </button>
        </div>
        <span v-if="form.errors.bloco" class="text-red-500 text-sm mt-1 block">{{ form.errors.bloco }}</span>
        <span v-if="form.errors.turno" class="text-red-500 text-sm mt-1 block">{{ form.errors.turno }}</span>
      </div>

      <!-- Botões -->
      <div class="flex justify-between items-center mt-8">
        <a 
          :href="route('cursos.turmas.agenda.index', { curso: curso.id, turma: turma.id })" 
          class="text-blue-600 hover:text-blue-800 font-medium flex items-center"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
          </svg>
          Voltar
        </a>
        <button 
          type="submit" 
          class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition flex items-center"
          :disabled="isSubmitting"
        >
          <span v-if="isSubmitting" class="inline-block mr-2">
            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </span>
          {{ isSubmitting ? 'Salvando...' : 'Cadastrar Aula' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  props: {
    disciplinas: Array,
    users: Array,
    turma: Object,
    curso: Object,
    professoresAssociados: Object,
  },
  data() {
    return {
      form: {
        disciplina_id: '',
        user_id: '',
        dia_semana: null,
        bloco: null,
        turno: 'manhã',
        turma_id: this.turma.id,
        curso_id: this.curso.id,
        errors: {},
      },
      diasSemana: ['Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'],
      isSubmitting: false,
      professorSugerido: null,
      horarios: {
        manhã: [
          { inicio: '08:00', fim: '09:00' },
          { inicio: '09:00', fim: '10:00' },
          { inicio: '10:10', fim: '11:10' },
          { inicio: '11:10', fim: '12:00' }
        ],
        tarde: [
          { inicio: '13:10', fim: '14:10' },
          { inicio: '14:10', fim: '15:10' },
          { inicio: '15:20', fim: '16:20' },
          { inicio: '16:20', fim: '17:00' }
        ]
      }
    };
  },
  methods: {
    setTurno(turno) {
      this.form.turno = turno;
      this.form.bloco = null;
    },
    
    getHoraIntervalo(horario) {
      const intervalos = this.horarios[this.form.turno];
      const intervalo = intervalos[horario - 1];
      return intervalo ? `${intervalo.inicio} - ${intervalo.fim}` : '';
    },
    
    updateProfessorSugerido() {
      if (!this.form.disciplina_id) {
        this.professorSugerido = null;
        return;
      }

      const professorId = this.professoresAssociados[this.form.disciplina_id];
      if (professorId) {
        this.professorSugerido = this.users.find(user => user.id === professorId);
        this.form.user_id = professorId;
      } else {
        this.professorSugerido = null;
        this.form.user_id = '';
      }
    },
    
    async submitForm() {
      this.form.errors = {};
      
      if (!this.form.dia_semana) {
        this.form.errors.dia_semana = 'Selecione um dia da semana';
        return;
      }
      
      if (!this.form.bloco) {
        this.form.errors.bloco = 'Selecione um horário';
        return;
      }

      if (!this.form.disciplina_id) {
        this.form.errors.disciplina_id = 'Selecione uma disciplina';
        return;
      }

      if (!this.form.user_id) {
        this.form.errors.user_id = 'Selecione um professor';
        return;
      }

      this.isSubmitting = true;

      try {
        const url = route('cursos.turmas.agendas.store', {
          curso: this.curso.id,
          turma: this.turma.id
        });

        await this.$inertia.post(url, {
          user_id: this.form.user_id,
          disciplina_id: this.form.disciplina_id,
          dia_semana: this.form.dia_semana,
          bloco: this.form.bloco,
          turno: this.form.turno,
          turma_id: this.form.turma_id,
          curso_id: this.form.curso_id
        }, {
          onSuccess: () => {
            this.$inertia.visit(route('cursos.turmas.agenda.index', { 
              curso: this.curso.id, 
              turma: this.turma.id 
            }), {
              onFinish: () => {
                if (this.$toast) {
                  this.$toast.success('Aula cadastrada com sucesso!');
                }
              }
            });
          },
          onError: (errors) => {
            this.form.errors = errors;
            
            if (errors.disciplina_id) {
              if (this.$toast) {
                this.$toast.error(errors.disciplina_id);
              }
            } else if (this.$toast && Object.keys(errors).length > 0) {
              this.$toast.error('Verifique os erros no formulário');
            }
          },
          onFinish: () => {
            this.isSubmitting = false;
          }
        });
      } catch (error) {
        console.error('Erro ao enviar formulário:', error);
        this.isSubmitting = false;
        if (this.$toast) {
          this.$toast.error('Ocorreu um erro ao cadastrar a aula');
        }
      }
    }
  }
};
</script>

<style scoped>
button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>