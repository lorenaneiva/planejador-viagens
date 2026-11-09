<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { login } from '../services/auth'

const router = useRouter()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

async function submit() {
  error.value = ''
  loading.value = true

  try {
    await login(email.value, password.value)
    await router.push('/dashboard')
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="auth-page">
    <section class="auth-card">
      <div class="brand">Planejador de Viagens</div>

      <h1>Entrar na sua conta</h1>
      <p class="muted">Organize suas próximas viagens em um só lugar.</p>

      <form @submit.prevent="submit">
        <label for="email">E-mail</label>
        <input
          id="email"
          v-model="email"
          type="email"
          placeholder="seu@email.com"
          autocomplete="email"
          required
        />

        <label for="password">Senha</label>
        <input
          id="password"
          v-model="password"
          type="password"
          placeholder="Sua senha"
          autocomplete="current-password"
          required
        />

        <p v-if="error" class="error" role="alert">{{ error }}</p>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>

      <p class="auth-footer">
        Ainda não tem conta?
        <RouterLink to="/cadastro">Criar conta</RouterLink>
      </p>
    </section>
  </main>
</template>
