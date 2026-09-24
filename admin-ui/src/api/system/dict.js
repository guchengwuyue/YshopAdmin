import request from '@/utils/request'

export function listDict(params) {
  return request({ url: '/api/system/dict/list', method: 'get', params })
}
export function getDict(id) {
  return request({ url: '/api/system/dict/get/' + id, method: 'get' })
}
export function addDict(data) {
  return request({ url: '/api/system/dict/add', method: 'post', data })
}
export function updateDict(data) {
  return request({ url: '/api/system/dict/edit', method: 'post', data })
}
export function removeDict(ids) {
  return request({ url: '/api/system/dict/remove', method: 'post', data: { ids } })
}
export function listDictData(params) {
  return request({ url: '/api/system/dict/data/list', method: 'get', params })
}
export function getDictData(id) {
  return request({ url: '/api/system/dict/data/get/' + id, method: 'get' })
}
export function addDictData(data) {
  return request({ url: '/api/system/dict/data/add', method: 'post', data })
}
export function updateDictData(data) {
  return request({ url: '/api/system/dict/data/edit', method: 'post', data })
}
export function removeDictData(ids) {
  return request({ url: '/api/system/dict/data/remove', method: 'post', data: { ids } })
}
