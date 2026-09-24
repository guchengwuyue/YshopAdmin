import request from '@/utils/request'

export function listConfig(params) {
  return request({ url: '/api/system/config/list', method: 'get', params })
}
export function getConfig(id) {
  return request({ url: '/api/system/config/get/' + id, method: 'get' })
}
export function addConfig(data) {
  return request({ url: '/api/system/config/add', method: 'post', data })
}
export function updateConfig(data) {
  return request({ url: '/api/system/config/edit', method: 'post', data })
}
export function removeConfig(ids) {
  return request({ url: '/api/system/config/remove', method: 'post', data: { ids } })
}
