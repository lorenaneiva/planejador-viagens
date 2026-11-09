import { useState } from 'react'
import { useQuery, useMutation } from '@apollo/client'

import {
  MINHAS_VIAGENS,
  CRIAR_VIAGEM,
  ATUALIZAR_VIAGEM,
  EXCLUIR_VIAGEM,
} from './graphql/viagens'

const formularioInicial = {
  titulo: '',
  descricao: '',
  data_inicio: '',
  data_fim: '',
  status: 'planejada',
}

export default function Viagens() {
  const [form, setForm] = useState(formularioInicial)
  const [editando, setEditando] = useState(null)
  const [mostrarForm, setMostrarForm] = useState(false)
  const [erro, setErro] = useState('')
  const [salvando, setSalvando] = useState(false)

  const { data, loading, error, refetch } = useQuery(
    MINHAS_VIAGENS,
    { fetchPolicy: 'network-only' }
  )

  const [criarViagem] = useMutation(CRIAR_VIAGEM)
  const [atualizarViagem] = useMutation(ATUALIZAR_VIAGEM)
  const [excluirViagem] = useMutation(EXCLUIR_VIAGEM)

  const viagens = data?.minhasViagens ?? []

  function alterarCampo(event) {
    setForm({
      ...form,
      [event.target.name]: event.target.value,
    })
  }

  function novaViagem() {
    setEditando(null)
    setForm(formularioInicial)
    setErro('')
    setMostrarForm(true)
  }

  function editar(viagem) {
    setEditando(viagem.id)

    setForm({
      titulo: viagem.titulo,
      descricao: viagem.descricao || '',
      data_inicio: viagem.data_inicio,
      data_fim: viagem.data_fim,
      status: viagem.status,
    })

    setErro('')
    setMostrarForm(true)
  }

  function cancelar() {
    setMostrarForm(false)
    setEditando(null)
    setForm(formularioInicial)
    setErro('')
  }

  async function salvar(event) {
    event.preventDefault()
    setErro('')

    if (form.data_fim < form.data_inicio) {
      setErro('A data final não pode ser anterior à inicial.')
      return
    }

    setSalvando(true)

    try {
      if (editando) {
        await atualizarViagem({
          variables: {
            id: editando,
            ...form,
          },
        })
      } else {
        await criarViagem({
          variables: form,
        })
      }

      await refetch()
      cancelar()
    } catch (err) {
      setErro(err.message)
    } finally {
      setSalvando(false)
    }
  }

  async function excluir(id) {
    if (!window.confirm('Deseja excluir esta viagem?')) {
      return
    }

    setErro('')

    try {
      await excluirViagem({
        variables: { id },
      })

      await refetch()
    } catch (err) {
      setErro(err.message)
    }
  }

  function formatarData(data) {
    if (!data) return '-'

    return new Date(`${data}T12:00:00`)
      .toLocaleDateString('pt-BR')
  }

  return (
    <main className="page-container">
      <div className="page-heading">
        <div>
          <span className="eyebrow">ORGANIZAÇÃO</span>
          <h1>Minhas viagens</h1>
          <p>Planeje, organize e acompanhe seus roteiros.</p>
        </div>

        <button
          className="btn-primary"
          onClick={novaViagem}
        >
          + Nova viagem
        </button>
      </div>

      {mostrarForm && (
        <section className="card">
          <h2>
            {editando ? 'Editar viagem' : 'Cadastrar viagem'}
          </h2>

          <form onSubmit={salvar} className="form-viagem">
            <label>
              Título
              <input
                name="titulo"
                value={form.titulo}
                onChange={alterarCampo}
                required
                maxLength="200"
              />
            </label>

            <label>
              Descrição
              <textarea
                name="descricao"
                value={form.descricao}
                onChange={alterarCampo}
                rows="3"
              />
            </label>

            <div className="form-grid">
              <label>
                Data inicial
                <input
                  type="date"
                  name="data_inicio"
                  value={form.data_inicio}
                  onChange={alterarCampo}
                  required
                />
              </label>

              <label>
                Data final
                <input
                  type="date"
                  name="data_fim"
                  min={form.data_inicio || undefined}
                  value={form.data_fim}
                  onChange={alterarCampo}
                  required
                />
              </label>
            </div>

            <label>
              Status
              <select
                name="status"
                value={form.status}
                onChange={alterarCampo}
              >
                <option value="planejada">Planejada</option>
                <option value="em_andamento">Em andamento</option>
                <option value="concluida">Concluída</option>
                <option value="cancelada">Cancelada</option>
              </select>
            </label>

            {erro && (
              <p className="error" role="alert">{erro}</p>
            )}

            <div className="form-actions">
              <button
                type="button"
                className="btn-secondary"
                onClick={cancelar}
              >
                Cancelar
              </button>

              <button
                type="submit"
                className="btn-primary"
                disabled={salvando}
              >
                {salvando ? 'Salvando...' : 'Salvar viagem'}
              </button>
            </div>
          </form>
        </section>
      )}

      {!mostrarForm && erro && (
        <p className="error" role="alert">{erro}</p>
      )}

      {loading && <p>Carregando viagens...</p>}

      {error && (
        <p className="error" role="alert">
          Erro ao carregar viagens: {error.message}
        </p>
      )}

      {!loading && !error && viagens.length === 0 && (
        <div className="card empty-state">
          <h2>Nenhuma viagem cadastrada</h2>
          <p>Comece organizando seu próximo destino.</p>

          <button
            className="btn-primary"
            onClick={novaViagem}
          >
            Criar primeira viagem
          </button>
        </div>
      )}

      <div className="viagens-grid">
        {viagens.map((viagem) => (
          <article className="card viagem-card" key={viagem.id}>
            <div className="viagem-header">
              <h2>{viagem.titulo}</h2>

              <span className="status">
                {viagem.status.replaceAll('_', ' ')}
              </span>
            </div>

            <p>
              {viagem.descricao || 'Sem descrição.'}
            </p>

            <div className="viagem-datas">
              <span>
                Início: {formatarData(viagem.data_inicio)}
              </span>

              <span>
                Fim: {formatarData(viagem.data_fim)}
              </span>
            </div>

            <div className="card-actions">
              <button
                className="btn-secondary"
                onClick={() => editar(viagem)}
              >
                Editar
              </button>

              <button
                className="btn-danger"
                onClick={() => excluir(viagem.id)}
              >
                Excluir
              </button>
            </div>
          </article>
        ))}
      </div>
    </main>
  )
}
