import request from '@/utils/request'

export function listPost(params) {
  return request({ url: '/api/system/post/list', method: 'get', params })
}

export function getPost(id) {
  return request({ url: '/api/system/post/get/' + id, method: 'get' })
}

export function addPost(data) {
  return request({ url: '/api/system/post/add', method: 'post', data })
}

export function updatePost(data) {
  return request({ url: '/api/system/post/edit', method: 'post', data })
}

export function removePost(ids) {
  return request({ url: '/api/system/post/remove', method: 'post', data: { ids } })
}
