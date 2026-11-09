<script setup>
import { useRouter } from 'vue-router'
import MapaCidade from './MapaCidade.vue'
import InformacoesExtras from './InformacoesExtras.vue'
import { ref } from 'vue'
import { pesquisarCidades, consultarClima } from '../services/viagens'


const router = useRouter()

function planejarViagem(data = null) {
  const cidade = cidadeSelecionada.value

  if (!cidade) return

  const query = {
    cidade: cidade.nome,
    pais: cidade.pais || '',
    codigo_pais: cidade.codigo_pais || '',
    latitude: String(cidade.latitude),
    longitude: String(cidade.longitude),
    osm_id: cidade.osm_id || '',
    osm_type: cidade.osm_type || '',
  }

  if (data) {
    query.data = data
  }

  router.push({
    path: '/viagens',
    query,
  })
}

const busca = ref('')
const cidades = ref([])
const cidadeSelecionada = ref(null)
const clima = ref(null)

const pesquisando = ref(false)
const carregandoClima = ref(false)
const erro = ref('')

const descricoes = {
  0: 'Céu limpo',
  1: 'Predominantemente limpo',
  2: 'Parcialmente nublado',
  3: 'Nublado',
  45: 'Nevoeiro',
  48: 'Nevoeiro',
  51: 'Garoa',
  53: 'Garoa',
  55: 'Garoa intensa',
  61: 'Chuva fraca',
  63: 'Chuva moderada',
  65: 'Chuva forte',
  71: 'Neve fraca',
  73: 'Neve moderada',
  75: 'Neve forte',
  80: 'Pancadas de chuva',
  81: 'Pancadas de chuva',
  82: 'Pancadas fortes',
  95: 'Tempestade',
  96: 'Tempestade com granizo',
  99: 'Tempestade com granizo',
}

function descricaoTempo(codigo) {
  return descricoes[codigo] || 'Condição não identificada'
}

function formatarData(data) {
  return new Date(`${data}T12:00:00`).toLocaleDateString('pt-BR', {
    weekday: 'short',
    day: '2-digit',
    month: '2-digit',
  })
}

async function pesquisar() {
  if (busca.value.trim().length < 3) {
    erro.value = 'Digite pelo menos 3 caracteres.'
    return
  }

  pesquisando.value = true
  erro.value = ''
  cidades.value = []
  cidadeSelecionada.value = null
  clima.value = null

  try {
    cidades.value = await pesquisarCidades(busca.value)

    if (cidades.value.length === 0) {
      erro.value = 'Nenhuma localização encontrada.'
    }
  } catch (e) {
    erro.value = e.message
  } finally {
    pesquisando.value = false
  }
}

async function selecionarCidade(cidade) {
  cidadeSelecionada.value = cidade
  cidades.value = []
  clima.value = null
  erro.value = ''
  carregandoClima.value = true

  try {
    clima.value = await consultarClima(
      cidade.latitude,
      cidade.longitude
    )
  } catch (e) {
    erro.value = e.message
  } finally {
    carregandoClima.value = false
  }
}
</script>

<template>
  <section class="pesquisa">
    <div class="cabecalho">
      <div>
        <span class="etiqueta">DESCUBRA SEU PRÓXIMO DESTINO</span>
        <h2>Para onde vamos?</h2>
        <p>Pesquise uma cidade e consulte a previsão do tempo.</p>
      </div>
    </div>

    <form class="form-busca" @submit.prevent="pesquisar">
      <input
        v-model="busca"
        placeholder="Ex.: Belém, Lisboa, Paris..."
        aria-label="Nome da cidade"
        minlength="3"
        maxlength="100"
        required
      />

      <button type="submit" :disabled="pesquisando">
        {{ pesquisando ? 'Pesquisando...' : 'Pesquisar' }}
      </button>
    </form>

    <p class="credito">
      Dados de localização: © OpenStreetMap contributors.
    </p>

    <p v-if="erro" class="mensagem-erro" role="alert">
      {{ erro }}
    </p>

    <div v-if="cidades.length" class="resultados">
      <h3>Selecione uma localização</h3>

      <button
        v-for="cidade in cidades"
        :key="`${cidade.osm_type}-${cidade.osm_id}`"
        class="resultado"
        @click="selecionarCidade(cidade)"
      >
        <span>📍 {{ cidade.nome }}</span>
        <span>Selecionar →</span>
      </button>
    </div>

    <p v-if="carregandoClima">Consultando previsão do tempo...</p>

    <div v-if="cidadeSelecionada" class="destino">
      <h3>{{ cidadeSelecionada.nome }}</h3>

      <p>
        Coordenadas:
        {{ cidadeSelecionada.latitude.toFixed(4) }},
        {{ cidadeSelecionada.longitude.toFixed(4) }}
      </p>


      <button
        type="button"
        class="botao-planejar"
        @click="planejarViagem()"
      >
        Planejar viagem para esta cidade →
      </button>

      <div v-if="clima" class="clima">
        <div class="clima-atual">
          <span class="etiqueta">TEMPO ATUAL</span>

          <div class="temperatura">
            {{ Math.round(clima.current.temperature_2m) }}°C
          </div>

          <strong>
            {{ descricaoTempo(clima.current.weather_code) }}
          </strong>

          <p>
            Umidade: {{ clima.current.relative_humidity_2m }}%
            · Vento: {{ clima.current.wind_speed_10m }} km/h
          </p>
        </div>

        <h4>Previsão para os próximos 5 dias</h4>

        <div class="dias">
          <div
            v-for="(data, index) in clima.daily.time"
            :key="data"
            class="dia"
          >
            <strong>{{ formatarData(data) }}</strong>

            <p>
              {{ descricaoTempo(clima.daily.weather_code[index]) }}
            </p>

            <span>
              {{ Math.round(clima.daily.temperature_2m_min[index]) }}°
              /
              {{ Math.round(clima.daily.temperature_2m_max[index]) }}°
            </span>

            <button
              type="button"
              class="botao-data"
              @click="planejarViagem(data)"
            >
              Planejar nesta data
            </button>

          </div>
        </div>
      </div>
    </div>


    <MapaCidade
      v-if="cidadeSelecionada"
      :latitude="cidadeSelecionada.latitude"
      :longitude="cidadeSelecionada.longitude"
      :nome="cidadeSelecionada.nome"
    />

    <InformacoesExtras
      v-if="cidadeSelecionada"
      :codigo-pais="cidadeSelecionada.codigo_pais"
    />
  </section>
</template>

<style scoped>
.pesquisa {
  padding: 30px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  margin-bottom: 25px;
}

.cabecalho h2 {
  margin: 10px 0;
  font-size: 28px;
}

.cabecalho p {
  color: #64748b;
}

.etiqueta {
  color: #2563eb;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 1px;
}

.form-busca {
  display: flex;
  flex-direction: row;
  gap: 12px;
  margin-top: 25px;
}

.form-busca input {
  flex: 1;
}

.form-busca button {
  margin: 0;
  padding: 12px 24px;
  border: none;
  border-radius: 9px;
  color: white;
  background: #2563eb;
  font-weight: 700;
}

.credito {
  font-size: 12px;
  color: #64748b;
  margin-top: 12px;
}

.mensagem-erro {
  color: #dc2626;
  margin-top: 18px;
}

.resultados {
  margin-top: 25px;
}

.resultado {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 15px;
  width: 100%;
  padding: 16px;
  margin: 8px 0;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 9px;
  text-align: left;
  color: #172b4d;
}

.resultado:hover {
  background: #eff6ff;
}

.destino {
  margin-top: 28px;
}

.destino h3 {
  font-size: 22px;
}

.destino > p {
  color: #64748b;
}

.clima-atual {
  margin-top: 20px;
  padding: 25px;
  border-radius: 14px;
  background: linear-gradient(135deg, #dbeafe, #eff6ff);
}

.temperatura {
  font-size: 54px;
  font-weight: 800;
  margin: 12px 0;
}

.dias {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 10px;
}

.dia {
  padding: 15px;
  background: #f8fafc;
  border-radius: 10px;
  text-align: center;
}

.dia p {
  font-size: 12px;
  color: #64748b;
  min-height: 36px;
}

@media (max-width: 700px) {
  .form-busca {
    flex-direction: column;
  }

  .dias {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .pesquisa {
    padding: 20px;
  }
}

.botao-planejar {
  display: block;
  width: 100%;
  margin: 20px 0;
  padding: 15px;
  border: none;
  border-radius: 10px;
  background: #2563eb;
  color: white;
  font-weight: 700;
  cursor: pointer;
}

.botao-planejar:hover {
  background: #1d4ed8;
}

.botao-data {
  display: block;
  width: 100%;
  margin-top: 12px;
  padding: 9px 5px;
  border: none;
  border-radius: 7px;
  background: #dbeafe;
  color: #1d4ed8;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
}

.botao-data:hover {
  background: #bfdbfe;
}

</style>
