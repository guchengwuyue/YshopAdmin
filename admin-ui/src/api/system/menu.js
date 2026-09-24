import request from '@/utils/request'

export function listMenu(params) {
  return request({ url: '/api/system/menu/list', method: 'get', params })
}

export function menuTree(excludeId) {
  return request({ url: '/api/system/menu/tree', method: 'get', params: { excludeId } })
}

export function getMenu(id) {
  return request({ url: '/api/system/menu/get/' + id, method: 'get' })
}

export function addMenu(data) {
  return request({ url: '/api/system/menu/add', method: 'post', data })
}

export function updateMenu(data) {
  return request({ url: '/api/system/menu/edit', method: 'post', data })
}

export function removeMenu(id) {
  return request({ url: '/api/system/menu/remove', method: 'post', data: { id } })
}
