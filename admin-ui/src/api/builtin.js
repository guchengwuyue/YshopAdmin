import request from '@/utils/request'

export function getPayConfig() {
  return request({ url: '/api/pay/config', method: 'get' })
}
export function savePayConfig(config) {
  return request({ url: '/api/pay/config', method: 'post', data: { config } })
}
export function uploadPayCert(file, certname) {
  const data = new FormData()
  data.append('file', file)
  if (certname) data.append('certname', certname)
  return request({ url: '/api/pay/config/upload', method: 'post', data })
}
export function payTest(data) {
  return request({ url: '/api/pay/test', method: 'post', data })
}

export function getWechatSetting() {
  return request({ url: '/api/wechat/setting', method: 'get' })
}
export function saveWechatSetting(config) {
  return request({ url: '/api/wechat/setting', method: 'post', data: { config } })
}
export function listWechatConfig(params) {
  return request({ url: '/api/wechat/config/list', method: 'get', params })
}
export function getWechatConfig(id) {
  return request({ url: '/api/wechat/config/get/' + id, method: 'get' })
}
export function addWechatConfig(data) {
  return request({ url: '/api/wechat/config/add', method: 'post', data })
}
export function updateWechatConfig(data) {
  return request({ url: '/api/wechat/config/edit', method: 'post', data })
}
export function removeWechatConfig(ids) {
  return request({ url: '/api/wechat/config/remove', method: 'post', data: { ids } })
}
export function getWechatMenu() {
  return request({ url: '/api/wechat/menu', method: 'get' })
}
export function saveWechatMenu(menu) {
  return request({ url: '/api/wechat/menu/save', method: 'post', data: { menu } })
}
export function pullWechatMenu() {
  return request({ url: '/api/wechat/menu/pull', method: 'post' })
}
export function syncWechatMenu() {
  return request({ url: '/api/wechat/menu/sync', method: 'post' })
}
export function listAutoreply(params) {
  return request({ url: '/api/wechat/autoreply/list', method: 'get', params })
}
export function getAutoreply(id) {
  return request({ url: '/api/wechat/autoreply/get/' + id, method: 'get' })
}
export function addAutoreply(data) {
  return request({ url: '/api/wechat/autoreply/add', method: 'post', data })
}
export function updateAutoreply(data) {
  return request({ url: '/api/wechat/autoreply/edit', method: 'post', data })
}
export function removeAutoreply(ids) {
  return request({ url: '/api/wechat/autoreply/remove', method: 'post', data: { ids } })
}
export function listResponse(params) {
  return request({ url: '/api/wechat/response/list', method: 'get', params })
}
export function getResponse(id) {
  return request({ url: '/api/wechat/response/get/' + id, method: 'get' })
}
export function addResponse(data) {
  return request({ url: '/api/wechat/response/add', method: 'post', data })
}
export function updateResponse(data) {
  return request({ url: '/api/wechat/response/edit', method: 'post', data })
}
export function removeResponse(ids) {
  return request({ url: '/api/wechat/response/remove', method: 'post', data: { ids } })
}
