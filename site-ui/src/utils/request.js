import axios from 'axios'
import { getToken, removeToken } from './auth'

const service = axios.create({
  baseURL: '/',
  timeout: 20000,
  withCredentials: true,
})

service.interceptors.request.use((config) => {
  const token = getToken()
  if (token) {
    config.headers.Authorization = 'Bearer ' + token
  }
  config.headers.Accept = 'application/json'
  return config
})

service.interceptors.response.use(
  (res) => {
    const data = res.data || {}
    if (data.code === 200) {
      return data
    }
    if (data.code === 401) {
      removeToken()
      return Promise.reject(new Error(data.msg || '请先登录'))
    }
    return Promise.reject(new Error(data.msg || '请求失败'))
  },
  (err) => {
    const data = err.response?.data
    if (data?.code === 401) {
      removeToken()
    }
    return Promise.reject(new Error(data?.msg || err.message || '网络错误'))
  }
)

export default service
