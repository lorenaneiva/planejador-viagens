<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { register } from '../services/auth'

const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const confirmation = ref('')
const loading = ref(false)
const error = ref('')

async function submit() {
  error.value = ''

  if (password.value !== confirmation.value) {
    error.value = 'As senhas não coincidem.'
    return
  }

  loading.value = true

  try {
    await register(
      name.value,
      email.value,
      password.value,
      confirmation.value
    )

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

      <h1>Criar conta</h1>
      <p class="muted">Comece a planejar seus próximos destinos.</p>

      <form @submit.prevent="submit">
        <label for="name">Nome</label>
        <input
          id="name"
          v-model="name"
          type="text"
          autocomplete="name"
          required
        />

        <label for="email">E-mail</label>
        <input
          id="email"
          v-model="email"
          type="email"
          autocomplete="email"
          required
        />

        <label for="password">Senha</label>
        <input
          id="password"
          v-model="password"
          type="password"
          minlength="8"
          autocomplete="new-password"
          required
        />

        <label for="confirmation">Confirmar senha</label>
        <input
          id="confirmation"
          v-model="confirmation"
          type="password"
          minlength="8"
          autocomplete="new-password"
          required
        />

        <p v-if="error" class="error" role="alert">{{ error }}</p>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Cadastrando...' : 'Criar conta' }}
        </button>
      </form>

      <p class="auth-footer">
        Já possui conta?
        <RouterLink to="/login">Entrar</RouterLink>
      </p>
    </section>
  </main>
</template>
