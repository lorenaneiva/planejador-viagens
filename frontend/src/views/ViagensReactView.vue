<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { RouterLink } from 'vue-router'

import React from 'react'
import { createRoot } from 'react-dom/client'
import { ApolloProvider } from '@apollo/client'

import Viagens from '../react/Viagens.jsx'
import '../react/viagens.css'
import { reactApolloClient } from '../react/apollo'

const container = ref(null)

let reactRoot = null

onMounted(() => {
  reactRoot = createRoot(container.value)

  reactRoot.render(
    React.createElement(
      ApolloProvider,
      { client: reactApolloClient },
      React.createElement(Viagens)
    )
  )
})

onBeforeUnmount(() => {
  if (reactRoot) {
    reactRoot.unmount()
    reactRoot = null
  }
})
</script>

<template>
  <div class="react-page">
    <header class="react-header">
      <RouterLink to="/dashboard" class="voltar">
        ← Explorar destinos
      </RouterLink>

      <span class="identificacao">Planejador de Viagens</span>
    </header>

    <div ref="container"></div>
  </div>
</template>

<style scoped>
.react-page {
  min-height: 100vh;
  background: #f5f7fb;
}

.react-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  padding: 20px max(24px, calc((100vw - 1100px) / 2));
  background: white;
  border-bottom: 1px solid #e2e8f0;
}

.voltar {
  color: #2563eb;
  font-weight: 700;
  text-decoration: none;
}

.identificacao {
  color: #64748b;
  font-weight: 600;
}

@media (max-width: 600px) {
  .react-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
