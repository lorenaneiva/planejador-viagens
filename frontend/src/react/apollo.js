import {
  ApolloClient,
  InMemoryCache,
  HttpLink,
  ApolloLink,
} from '@apollo/client'

import { setContext } from '@apollo/client/link/context'

const httpLink = new HttpLink({
  uri: `${import.meta.env.VITE_API_URL}/graphql`,
})

const authLink = setContext((_, { headers }) => {
  const token = sessionStorage.getItem('token')

  return {
    headers: {
      ...headers,
      Authorization: token ? `Bearer ${token}` : '',
    },
  }
})

export const reactApolloClient = new ApolloClient({
  link: ApolloLink.from([authLink, httpLink]),
  cache: new InMemoryCache(),
})
