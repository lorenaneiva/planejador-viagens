import { createRouter, createWebHistory } from 'vue-router'
import { getToken } from '../services/auth'

import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import DashboardView from '../views/DashboardView.vue'
import ViagensReactView from '../views/ViagensReactView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      redirect: '/dashboard',
    },
    {
      path: '/login',
      component: LoginView,
      meta: { guest: true },
    },
    {
      path: '/cadastro',
      component: RegisterView,
      meta: { guest: true },
    },
    {
      path: '/dashboard',
      component: DashboardView,
      meta: { requiresAuth: true },
    },
    {
      path: '/viagens',
      component: ViagensReactView,
      meta: { requiresAuth: true },
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
})

router.beforeEach((to) => {
  const authenticated = Boolean(getToken())

  if (to.meta.requiresAuth && !authenticated) {
    return '/login'
  }

  if (to.meta.guest && authenticated) {
    return '/dashboard'
  }
})

export default router
