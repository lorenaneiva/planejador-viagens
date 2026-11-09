import { getToken } from './auth'

const API_URL = import.meta.env.VITE_API_URL

export async function pesquisarCidades(nome) {
  const params = new URLSearchParams({ q: nome.trim() })

  const response = await fetch(`${API_URL}/api/cidades?${params}`, {
    headers: {
      Accept: 'application/json',
      Authorization: `Bearer ${getToken()}`,
    },
  })

  const result = await response.json()

  if (!response.ok) {
    throw new Error(result.message || 'Erro ao pesquisar cidades.')
  }

  return result
}

export async function consultarClima(latitude, longitude) {
  const params = new URLSearchParams({
    latitude: String(latitude),
    longitude: String(longitude),
    current: 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m',
    daily: 'temperature_2m_max,temperature_2m_min,weather_code',
    timezone: 'auto',
    forecast_days: '5',
  })

  const response = await fetch(
    `https://api.open-meteo.com/v1/forecast?${params}`
  )

  if (!response.ok) {
    throw new Error('Não foi possível consultar a previsão do tempo.')
  }

  return response.json()
}
