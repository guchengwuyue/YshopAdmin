import request from '@/utils/request'

export function listAddon(params) {
  return request({ url: '/api/system/addon/list', method: 'get', params })
}
export function installAddon(name) {
  return request({ url: '/api/system/addon/install', method: 'post', data: { name } })
}
export function uninstallAddon(name) {
  return request({ url: '/api/system/addon/uninstall', method: 'post', data: { name } })
}
export function enableAddon(name) {
  return request({ url: '/api/system/addon/enable', method: 'post', data: { name } })
}
export function disableAddon(name) {
  return request({ url: '/api/system/addon/disable', method: 'post', data: { name } })
}
export function getAddonConfig(name) {
  return request({ url: '/api/system/addon/config/' + name, method: 'get' })
}
export function saveAddonConfig(data) {
  return request({ url: '/api/system/addon/config', method: 'post', data })
}
export function uploadAddon(file) {
  const data = new FormData()
  data.append('file', file)
  return request({ url: '/api/system/addon/upload', method: 'post', data })
}
