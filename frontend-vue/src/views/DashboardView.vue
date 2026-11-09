<script setup>
import { ref, onMounted } from 'vue'
import PesquisaCidade from '../components/PesquisaCidade.vue'
import { useRouter } from 'vue-router'
import { gql } from '@apollo/client/core'
import { apolloClient } from '../plugins/apollo'
import { getToken, logout } from '../services/auth'

const router = useRouter()

const user = ref(null)
const loading = ref(true)
const error = ref('')
const loggingOut = ref(false)

const ME_QUERY = gql`
  query Me {
    me {
      id
      name
      email
    }
  }
`

async function loadUser() {
  loading.value = true
  error.value = ''

  try {
    const response = await apolloClient.query({
      query: ME_QUERY,
      fetchPolicy: 'network-only',
    })

    if (!response.data?.me) {
      throw new Error('Não foi possível identificar o usuário.')
    }

    user.value = response.data.me
  } catch (err) {
    error.value = err.message || 'Erro ao consultar o usuário.'

    if (
      !getToken() ||
      /unauthenticated|unauthenticated\.|não autenticado|unauthorized/i.test(error.value)
    ) {
      sessionStorage.removeItem('token')
      await apolloClient.clearStore()
      await router.replace('/login')
    }
  } finally {
    loading.value = false
  }
}

async function sair() {
  loggingOut.value = true
  error.value = ''

  try {
    await logout()
  } catch {
    // O token local é removido mesmo se o servidor estiver indisponível.
  } finally {
    loggingOut.value = false
    await router.replace('/login')
  }
}

onMounted(loadUser)
</script>

<template>
  <div class="dashboard">
    <header class="topbar">
      <div class="brand">Planejador de Viagens</div>

      <button
        class="logout-button"
        :disabled="loggingOut"
        @click="sair"
      >
        {{ loggingOut ? 'Saindo...' : 'Sair' }}
      </button>
    </header>

    <main class="dashboard-content">
      <p v-if="loading">Carregando seus dados...</p>

      <div v-else-if="error" class="card">
        <p class="error">{{ error }}</p>
        <button @click="loadUser">Tentar novamente</button>
      </div>

      <template v-else>
        <div class="welcome">
          <h1>Olá, {{ user.name.split(' ')[0].charAt(0).toUpperCase() + user.name.split(' ')[0].slice(1) }}!</h1>

          <p>
            Descubra seu próximo destino e planeje sua viagem.
          </p>
        </div>

        <PesquisaCidade />
      </template>
    </main>
  </div>
</template>
