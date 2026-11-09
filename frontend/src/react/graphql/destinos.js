import { gql } from '@apollo/client'

export const CONSULTAR_DESTINOS = gql`
  query ConsultarDestinosPlanejamento {
    destinos {
      id
      nome
      pais
      codigo_pais
      osm_id
      osm_type
    }
  }
`

export const CRIAR_DESTINO = gql`
  mutation CriarDestinoPlanejamento(
    $nome: String!
    $estado: String
    $pais: String!
    $codigo_pais: String
    $latitude: Float
    $longitude: Float
    $osm_id: String
    $osm_type: String
  ) {
    criarDestino(
      nome: $nome
      estado: $estado
      pais: $pais
      codigo_pais: $codigo_pais
      latitude: $latitude
      longitude: $longitude
      osm_id: $osm_id
      osm_type: $osm_type
    ) {
      id
      nome
    }
  }
`

export const ADICIONAR_DESTINO_VIAGEM = gql`
  mutation AdicionarDestinoPlanejado(
    $viagem_id: ID!
    $destino_id: ID!
    $ordem: Int!
    $data_chegada: Date
    $data_saida: Date
  ) {
    adicionarDestinoViagem(
      viagem_id: $viagem_id
      destino_id: $destino_id
      ordem: $ordem
      data_chegada: $data_chegada
      data_saida: $data_saida
    ) {
      id
      destino {
        id
        nome
      }
    }
  }
`
