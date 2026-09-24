import { defineStore } from 'pinia'
import { getInfo, login as apiLogin, logout as apiLogout, register as apiRegister } from '@/api/member'
import { getToken, removeToken, setToken } from '@/utils/auth'

export const useUserStore = defineStore('user', {
  state: () => ({
    token: getToken(),
    member: null,
  }),
  getters: {
    isLogin: (s) => !!s.token,
    displayName: (s) => s.member?.nickname || s.member?.username || '',
  },
  actions: {
    async login(form) {
      const res = await apiLogin(form)
      const data = res.data || {}
      setToken(data.token || '')
      this.token = data.token || ''
      this.member = data.member || null
      if (!this.member) {
        await this.fetchInfo()
      }
      return data
    },
    async register(form) {
      const res = await apiRegister(form)
      const data = res.data || {}
      setToken(data.token || '')
      this.token = data.token || ''
      this.member = data.member || null
      return data
    },
    async fetchInfo() {
      if (!this.token) {
        this.member = null
        return null
      }
      try {
        const res = await getInfo()
        this.member = res.data || null
        return this.member
      } catch (e) {
        removeToken()
        this.token = ''
        this.member = null
        return null
      }
    },
    async logout() {
      try {
        if (this.token) await apiLogout()
      } catch (e) {
        /* ignore */
      }
      removeToken()
      this.token = ''
      this.member = null
    },
  },
})
