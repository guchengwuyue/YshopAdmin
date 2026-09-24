import request from '@/utils/request'

export function listRole(params) {
  return request({ url: '/api/system/role/list', method: 'get', params })
}

export function getRole(id) {
  return request({ url: '/api/system/role/get/' + id, method: 'get' })
}

export function addRole(data) {
  return request({ url: '/api/system/role/add', method: 'post', data })
}

export function updateRole(data) {
  return request({ url: '/api/system/role/edit', method: 'post', data })
}

export function removeRole(ids) {
  return request({ url: '/api/system/role/remove', method: 'post', data: { ids } })
}

export function changeRoleStatus(data) {
  return request({ url: '/api/system/role/changeStatus', method: 'post', data })
}

export function authDataScope(data) {
  return request({ url: '/api/system/role/authDataScope', method: 'post', data })
}

export function roleMenuTree(roleId) {
  return request({ url: '/api/system/role/menuTree', method: 'get', params: { roleId } })
}

export function roleDeptTree(roleId) {
  return request({ url: '/api/system/role/deptTree', method: 'get', params: { roleId } })
}
