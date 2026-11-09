<script setup>
import { ref, computed, watch } from 'vue'
import {
  consultarCotacao,
  consultarFeriados,
} from '../services/informacoes'

const props = defineProps({
  codigoPais: {
    type: String,
    default: '',
  },
})

const moedas = [
  { codigo: 'BRL', nome: 'Real brasileiro' },
  { codigo: 'USD', nome: 'Dólar americano' },
  { codigo: 'EUR', nome: 'Euro' },
  { codigo: 'GBP', nome: 'Libra esterlina' },
]

const moedaOrigem = ref('BRL')
const moedaDestino = ref('USD')
const valor = ref(100)

const cotacao = ref(null)
const carregandoCotacao = ref(false)
const erroCotacao = ref('')

const ano = ref(new Date().getFullYear())
const feriados = ref([])
const carregandoFeriados = ref(false)
const erroFeriados = ref('')
const feriadosConsultados = ref(false)

const ehBrasil = computed(
  () => props.codigoPais?.toUpperCase() === 'BR'
)

const valorConvertido = computed(() => {
  if (!cotacao.value) return null

  return Number(valor.value) * cotacao.value.rate
})

function formatarMoeda(numero, moeda) {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: moeda,
  }).format(numero)
}

function formatarData(data) {
  return new Date(`${data}T12:00:00`).toLocaleDateString('pt-BR')
}

async function converter() {
  erroCotacao.value = ''
  cotacao.value = null

  if (!Number.isFinite(Number(valor.value)) || Number(valor.value) < 0) {
    erroCotacao.value = 'Informe um valor válido.'
    return
  }

  carregandoCotacao.value = true

  try {
    cotacao.value = await consultarCotacao(
      moedaOrigem.value,
      moedaDestino.value
    )
  } catch (erro) {
    erroCotacao.value = erro.message
  } finally {
    carregandoCotacao.value = false
  }
}

async function carregarFeriados() {
  if (!ehBrasil.value) return

  carregandoFeriados.value = true
  erroFeriados.value = ''
  feriados.value = []
  feriadosConsultados.value = false

  try {
    const resultado = await consultarFeriados(Number(ano.value))

    feriados.value = resultado.sort(
      (a, b) => a.date.localeCompare(b.date)
    )

    feriadosConsultados.value = true
  } catch (erro) {
    erroFeriados.value = erro.message
  } finally {
    carregandoFeriados.value = false
  }
}

watch(
  () => props.codigoPais,
  () => {
    feriados.value = []
    erroFeriados.value = ''
    feriadosConsultados.value = false

    if (ehBrasil.value) {
      carregarFeriados()
    }
  },
  { immediate: true }
)
</script>

<template>
  <div class="informacoes-extras">
    <section class="painel">
      <div class="titulo-painel">
        <span class="etiqueta">FRANKFURTER API</span>
        <h3>Conversor de moedas</h3>
        <p>Consulte a cotação entre diferentes moedas.</p>
      </div>

      <form class="formulario" @submit.prevent="converter">
        <div class="campo">
          <label for="valor-moeda">Valor</label>

          <input
            id="valor-moeda"
            v-model.number="valor"
            type="number"
            min="0"
            step="0.01"
            required
          />
        </div>

        <div class="campo">
          <label for="moeda-origem">De</label>

          <select id="moeda-origem" v-model="moedaOrigem">
            <option
              v-for="moeda in moedas"
              :key="moeda.codigo"
              :value="moeda.codigo"
            >
              {{ moeda.codigo }} — {{ moeda.nome }}
            </option>
          </select>
        </div>

        <div class="campo">
          <label for="moeda-destino">Para</label>

          <select id="moeda-destino" v-model="moedaDestino">
            <option
              v-for="moeda in moedas"
              :key="moeda.codigo"
              :value="moeda.codigo"
            >
              {{ moeda.codigo }} — {{ moeda.nome }}
            </option>
          </select>
        </div>

        <button type="submit" :disabled="carregandoCotacao">
          {{ carregandoCotacao ? 'Consultando...' : 'Converter' }}
        </button>
      </form>

      <p v-if="erroCotacao" class="erro" role="alert">
        {{ erroCotacao }}
      </p>

      <div v-if="cotacao" class="resultado-cambio">
        <span>Resultado da conversão</span>

        <strong>
          {{ formatarMoeda(valorConvertido, moedaDestino) }}
        </strong>

        <p>
          1 {{ moedaOrigem }} =
          {{ cotacao.rate.toLocaleString('pt-BR', {
            maximumFractionDigits: 6
          }) }}
          {{ moedaDestino }}
        </p>

        <small>
          Cotação de referência: {{ formatarData(cotacao.date) }}
        </small>
      </div>
    </section>

    <section class="painel">
      <div class="titulo-painel">
        <span class="etiqueta">BRASILAPI</span>
        <h3>Feriados brasileiros</h3>
      </div>

      <template v-if="ehBrasil">
        <p>Consulte os feriados nacionais do ano desejado.</p>

        <form class="formulario" @submit.prevent="carregarFeriados">
          <div class="campo">
            <label for="ano-feriados">Ano</label>

            <input
              id="ano-feriados"
              v-model.number="ano"
              type="number"
              min="1900"
              max="2199"
              required
            />
          </div>

          <button type="submit" :disabled="carregandoFeriados">
            {{ carregandoFeriados ? 'Consultando...' : 'Consultar feriados' }}
          </button>
        </form>

        <p v-if="erroFeriados" class="erro" role="alert">
          {{ erroFeriados }}
        </p>

        <p v-if="carregandoFeriados">
          Carregando feriados...
        </p>

        <div v-if="feriados.length" class="lista-feriados">
          <div
            v-for="feriado in feriados"
            :key="`${feriado.date}-${feriado.name}`"
            class="feriado"
          >
            <strong>{{ formatarData(feriado.date) }}</strong>
            <span>{{ feriado.name }}</span>
          </div>
        </div>

        <p v-else-if="feriadosConsultados">
          Nenhum feriado encontrado.
        </p>
      </template>

      <div v-else class="aviso">
        A BrasilAPI fornece feriados brasileiros.
        Não há consulta de feriados estrangeiros nesta integração.
      </div>
    </section>
  </div>
</template>

<style scoped>
.informacoes-extras {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 20px;
  margin-top: 30px;
}

.painel {
  padding: 25px;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  background: #fff;
}

.titulo-painel h3 {
  margin: 10px 0;
  font-size: 21px;
}

.titulo-painel p,
.painel > p {
  color: #64748b;
}

.etiqueta {
  color: #2563eb;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 1px;
}

.formulario {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-top: 20px;
}

.campo {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.campo label {
  font-size: 13px;
  font-weight: 700;
}

.campo input,
.campo select {
  width: 100%;
  padding: 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  background: white;
  color: #172b4d;
}

.formulario button {
  padding: 13px;
  border: none;
  border-radius: 8px;
  background: #2563eb;
  color: white;
  font-weight: 700;
  cursor: pointer;
}

.formulario button:disabled {
  opacity: .6;
  cursor: not-allowed;
}

.resultado-cambio {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 20px;
  margin-top: 20px;
  background: #eff6ff;
  border-radius: 10px;
}

.resultado-cambio strong {
  color: #1d4ed8;
  font-size: 30px;
}

.resultado-cambio p {
  margin: 0;
}

.resultado-cambio small {
  color: #64748b;
}

.lista-feriados {
  margin-top: 20px;
  max-height: 360px;
  overflow-y: auto;
}

.feriado {
  display: flex;
  justify-content: space-between;
  gap: 15px;
  padding: 13px 0;
  border-bottom: 1px solid #e2e8f0;
  font-size: 13px;
}

.feriado span {
  text-align: right;
}

.aviso {
  margin-top: 20px;
  padding: 18px;
  background: #f8fafc;
  color: #64748b;
  border-radius: 9px;
  line-height: 1.6;
}

.erro {
  color: #dc2626;
  font-size: 13px;
}

@media (max-width: 800px) {
  .informacoes-extras {
    grid-template-columns: 1fr;
  }
}
</style>
