import request from '@/utils/request'

export function listUser(params) {
  return request({ url: '/api/system/user/list', method: 'get', params })
}

export function addForm() {
  return request({ url: '/api/system/user/add', method: 'get' })
}

export function editForm(id) {
  return request({ url: '/api/system/user/edit/' + id, method: 'get' })
}

export function addUser(data) {
  return request({ url: '/api/system/user/add', method: 'post', data })
}

export function updateUser(data) {
  return request({ url: '/api/system/user/edit', method: 'post', data })
}

export function removeUser(ids) {
  return request({ url: '/api/system/user/remove', method: 'post', data: { ids } })
}

export function resetUserPwd(data) {
  return request({ url: '/api/system/user/resetPwd', method: 'post', data })
}

export function changeUserStatus(data) {
  return request({ url: '/api/system/user/changeStatus', method: 'post', data })
}

export function userDeptTree() {
  return request({ url: '/api/system/user/deptTree', method: 'get' })
}
