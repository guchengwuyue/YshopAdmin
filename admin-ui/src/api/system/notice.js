import request from '@/utils/request'

export function listNotice(params) {
  return request({ url: '/api/system/notice/list', method: 'get', params })
}

export function getNotice(id) {
  return request({ url: '/api/system/notice/get/' + id, method: 'get' })
}

export function addNotice(data) {
  return request({ url: '/api/system/notice/add', method: 'post', data })
}

export function updateNotice(data) {
  return request({ url: '/api/system/notice/edit', method: 'post', data })
}

export function removeNotice(ids) {
  return request({ url: '/api/system/notice/remove', method: 'post', data: { ids } })
}
