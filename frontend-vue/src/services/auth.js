import { apolloClient } from '../plugins/apollo'

const API_URL = import.meta.env.VITE_API_URL

async function request(path, data, token = null) {
  let response

  try {
    response = await fetch(`${API_URL}${path}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
      body: JSON.stringify(data),
    })
  } catch {
    throw new Error('Não foi possível conectar ao servidor Laravel.')
  }

  const result = await response.json()

  if (!response.ok) {
    const validationMessage = result.errors
      ? Object.values(result.errors).flat().join(' ')
      : null

    throw new Error(validationMessage || result.message || 'Erro na requisição.')
  }

  return result
}

export function getToken() {
  return sessionStorage.getItem('token')
}

export async function login(email, password) {
  const result = await request('/api/login', { email, password })

  await apolloClient.clearStore()
  sessionStorage.setItem('token', result.token)

  return result.user
}

export async function register(name, email, password, password_confirmation) {
  const result = await request('/api/register', {
    name,
    email,
    password,
    password_confirmation,
  })

  await apolloClient.clearStore()
  sessionStorage.setItem('token', result.token)

  return result.user
}

export async function logout() {
  const token = getToken()

  try {
    if (token) {
      await request('/api/logout', {}, token)
    }
  } finally {
    sessionStorage.removeItem('token')
    await apolloClient.clearStore()
  }
}
