import axios from 'axios'
import { message } from 'ant-design-vue'
import { getToken, loginHref, removeToken } from './auth'

const service = axios.create({
  baseURL: '/yshopadmin',
  timeout: 20000,
})

service.interceptors.request.use((config) => {
  const token = getToken()
  if (token) {
    config.headers.Authorization = 'Bearer ' + token
  }
  config.headers.Accept = 'application/json'
  return config
})

function fail(msg) {
  message.error(msg || '????')
  return Promise.reject(new Error(msg || '????'))
}

service.interceptors.response.use(
  (res) => {
    if (res.config.responseType === 'blob' || res.data instanceof Blob) {
      return res
    }
    const data = res.data || {}
    if (data.code === 200) {
      return data
    }
    if (data.code === 401) {
      removeToken()
      if (!location.pathname.endsWith('/login')) {
        location.href = loginHref()
      }
      return fail(data.msg || '?????????')
    }
    return fail(data.msg || '????')
  },
  (err) => {
    const data = err.response?.data
    if (data?.code === 401) {
      removeToken()
      if (!location.pathname.endsWith('/login')) {
        location.href = loginHref()
      }
    }
    return fail(data?.msg || err.message || '????')
  }
)

export default service
