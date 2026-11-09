import { gql } from '@apollo/client'

export const MINHAS_VIAGENS = gql`
  query MinhasViagens {
    minhasViagens {
      id
      titulo
      descricao
      data_inicio
      data_fim
      status
    }
  }
`

export const CRIAR_VIAGEM = gql`
  mutation CriarViagem(
    $titulo: String!
    $descricao: String
    $data_inicio: Date!
    $data_fim: Date!
    $status: String!
  ) {
    criarViagem(
      titulo: $titulo
      descricao: $descricao
      data_inicio: $data_inicio
      data_fim: $data_fim
      status: $status
    ) {
      id
      titulo
    }
  }
`

export const ATUALIZAR_VIAGEM = gql`
  mutation AtualizarViagem(
    $id: ID!
    $titulo: String
    $descricao: String
    $data_inicio: Date
    $data_fim: Date
    $status: String
  ) {
    atualizarViagem(
      id: $id
      titulo: $titulo
      descricao: $descricao
      data_inicio: $data_inicio
      data_fim: $data_fim
      status: $status
    ) {
      id
      titulo
    }
  }
`

export const EXCLUIR_VIAGEM = gql`
  mutation ExcluirViagem($id: ID!) {
    excluirViagem(id: $id)
  }
`
