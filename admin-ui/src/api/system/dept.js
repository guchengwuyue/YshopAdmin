import request from '@/utils/request'

export function listDept(params) {
  return request({ url: '/api/system/dept/list', method: 'get', params })
}

export function deptTree() {
  return request({ url: '/api/system/dept/tree', method: 'get' })
}

export function getDept(id) {
  return request({ url: '/api/system/dept/get/' + id, method: 'get' })
}

export function addDept(data) {
  return request({ url: '/api/system/dept/add', method: 'post', data })
}

export function updateDept(data) {
  return request({ url: '/api/system/dept/edit', method: 'post', data })
}

export function removeDept(id) {
  return request({ url: '/api/system/dept/remove', method: 'post', data: { id } })
}
