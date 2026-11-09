import {
  ApolloClient,
  InMemoryCache,
  createHttpLink,
  from,
} from '@apollo/client/core'

import { setContext } from '@apollo/client/link/context'

const httpLink = createHttpLink({
  uri: `${import.meta.env.VITE_API_URL}/graphql`,
})

const authLink = setContext((_, { headers }) => {
  const token = sessionStorage.getItem('token')

  return {
    headers: {
      ...headers,
      authorization: token ? `Bearer ${token}` : '',
    },
  }
})

export const apolloClient = new ApolloClient({
  link: from([authLink, httpLink]),
  cache: new InMemoryCache(),
})
