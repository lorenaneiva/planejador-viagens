<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const props = defineProps({
  latitude: {
    type: Number,
    required: true,
  },
  longitude: {
    type: Number,
    required: true,
  },
  nome: {
    type: String,
    default: 'Destino selecionado',
  },
})

const elementoMapa = ref(null)

let mapa = null
let marcador = null

const icone = L.divIcon({
  className: 'marcador-personalizado',
  html: '<span class="pino-mapa"></span>',
  iconSize: [28, 36],
  iconAnchor: [14, 36],
})

function atualizarMapa() {
  if (!mapa) return

  const coordenadas = [props.latitude, props.longitude]

  mapa.setView(coordenadas, 12)

  if (marcador) {
    marcador.remove()
  }

  marcador = L.marker(coordenadas, {
    icon: icone,
  }).addTo(mapa)

  // bindPopup trata o texto recebido como conteúdo textual.
  // Não inserir HTML não confiável no popup.
  const popup = document.createElement('div')
  popup.textContent = props.nome

  marcador.bindPopup(popup).openPopup()
}

onMounted(async () => {
  await nextTick()

  mapa = L.map(elementoMapa.value, {
    zoomControl: true,
    scrollWheelZoom: false,
  })

  L.tileLayer(
    'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
    {
      maxZoom: 19,
      attribution:
        '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap contributors</a>',
    }
  ).addTo(mapa)

  atualizarMapa()

  // Garante a renderização correta quando o componente aparece.
  await nextTick()
  mapa.invalidateSize()
})

watch(
  () => [props.latitude, props.longitude, props.nome],
  async () => {
    await nextTick()
    atualizarMapa()
  }
)

onBeforeUnmount(() => {
  if (mapa) {
    mapa.remove()
    mapa = null
  }
})
</script>

<template>
  <section class="mapa-container">
    <div class="mapa-cabecalho">
      <div>
        <span class="etiqueta">OPENSTREETMAP</span>
        <h3>Localização no mapa</h3>
        <p>Explore a região do destino selecionado.</p>
      </div>
    </div>

    <div
      ref="elementoMapa"
      class="mapa"
      aria-label="Mapa interativo do destino selecionado"
    ></div>

    <div class="mapa-rodape">
      <span>
        {{ latitude.toFixed(5) }},
        {{ longitude.toFixed(5) }}
      </span>

      <a
        :href="`https://www.openstreetmap.org/?mlat=${latitude}&mlon=${longitude}#map=12/${latitude}/${longitude}`"
        target="_blank"
        rel="noopener noreferrer"
      >
        Abrir no OpenStreetMap ↗
      </a>
    </div>
  </section>
</template>

<style scoped>
.mapa-container {
  margin-top: 28px;
  overflow: hidden;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
}

.mapa-cabecalho {
  padding: 24px 24px 10px;
}

.mapa-cabecalho h3 {
  margin: 8px 0;
  font-size: 22px;
}

.mapa-cabecalho p {
  color: #64748b;
}

.etiqueta {
  color: #2563eb;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 1px;
}

.mapa {
  width: 100%;
  height: 390px;
  z-index: 0;
}

.mapa-rodape {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  padding: 15px 24px;
  font-size: 13px;
  color: #64748b;
}

.mapa-rodape a {
  color: #2563eb;
  font-weight: 700;
  text-decoration: none;
}

@media (max-width: 600px) {
  .mapa {
    height: 300px;
  }

  .mapa-rodape {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

<style>
.marcador-personalizado {
  background: transparent;
  border: none;
}

.pino-mapa {
  display: block;
  width: 24px;
  height: 24px;
  background: #2563eb;
  border: 4px solid white;
  border-radius: 50% 50% 50% 0;
  transform: rotate(-45deg);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
}
</style>
