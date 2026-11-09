async function obterJSON(url) {
  let resposta

  try {
    resposta = await fetch(url)
  } catch {
    throw new Error('Não foi possível conectar ao serviço externo.')
  }

  if (!resposta.ok) {
    throw new Error(`Serviço indisponível (HTTP ${resposta.status}).`)
  }

  return resposta.json()
}

// Frankfurter: cotação entre duas moedas.
export async function consultarCotacao(origem, destino) {
  if (origem === destino) {
    return {
      base: origem,
      quote: destino,
      rate: 1,
      date: new Date().toISOString().slice(0, 10),
    }
  }

  return obterJSON(
    `https://api.frankfurter.dev/v2/rate/${origem}/${destino}`
  )
}

// BrasilAPI: feriados brasileiros por ano.
export async function consultarFeriados(ano) {
  if (!Number.isInteger(ano) || ano < 1900 || ano > 2199) {
    throw new Error('Ano inválido.')
  }

  return obterJSON(
    `https://brasilapi.com.br/api/feriados/v1/${ano}`
  )
}
