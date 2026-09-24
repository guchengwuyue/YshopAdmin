import { defineStore } from 'pinia'
import { getInfo, login as loginApi, logout as logoutApi } from '@/api/login'
import { getToken, loginHref, removeToken, setToken } from '@/utils/auth'

export const useUserStore = defineStore('user', {
  state: () => ({
    token: getToken(),
    user: null,
    roles: [],
    permissions: [],
    inited: false,
  }),
  getters: {
    name: (state) => state.user?.userName || state.user?.loginName || '',
  },
  actions: {
    async login(payload) {
      const res = await loginApi(payload)
      const token = res.data?.token
      if (!token) {
        throw new Error('签发 Token 失败')
      }
      setToken(token)
      this.token = token
    },
    async fetchInfo() {
      const res = await getInfo()
      this.user = res.data?.user || null
      this.roles = res.data?.roles || []
      this.permissions = res.data?.permissions || []
      this.inited = true
    },
    hasPerm(perm) {
      if (!perm) {
        return true
      }
      const list = this.permissions || []
      return list.includes('*:*:*') || list.includes(perm)
    },
    async logout() {
      try {
        await logoutApi()
      } catch (e) {
        // 本地态仍要清掉
      }
      this.reset()
      window.location.href = loginHref()
    },
    reset() {
      removeToken()
      this.token = ''
      this.user = null
      this.roles = []
      this.permissions = []
      this.inited = false
    },
  },
})
