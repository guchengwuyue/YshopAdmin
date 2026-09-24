import request from '@/utils/request'

export function listOperlog(params) {
  return request({ url: '/api/monitor/operlog/list', method: 'get', params })
}
export function getOperlog(id) {
  return request({ url: '/api/monitor/operlog/get/' + id, method: 'get' })
}
export function removeOperlog(ids) {
  return request({ url: '/api/monitor/operlog/remove', method: 'post', data: { ids } })
}
export function cleanOperlog() {
  return request({ url: '/api/monitor/operlog/clean', method: 'post' })
}
export function listLogininfor(params) {
  return request({ url: '/api/monitor/logininfor/list', method: 'get', params })
}
export function removeLogininfor(ids) {
  return request({ url: '/api/monitor/logininfor/remove', method: 'post', data: { ids } })
}
export function cleanLogininfor() {
  return request({ url: '/api/monitor/logininfor/clean', method: 'post' })
}
export function unlockLogininfor(loginName) {
  return request({ url: '/api/monitor/logininfor/unlock', method: 'post', data: { loginName } })
}
export function listOnline(params) {
  return request({ url: '/api/monitor/online/list', method: 'get', params })
}
export function forceLogout(ids) {
  return request({ url: '/api/monitor/online/batchForceLogout', method: 'post', data: { ids } })
}
export function getServer() {
  return request({ url: '/api/monitor/server', method: 'get' })
}
export function cacheNames() {
  return request({ url: '/api/monitor/cache/names', method: 'get' })
}
export function cacheKeys(cacheName) {
  return request({ url: '/api/monitor/cache/keys', method: 'get', params: { cacheName } })
}
export function cacheValue(cacheName, cacheKey) {
  return request({ url: '/api/monitor/cache/value', method: 'get', params: { cacheName, cacheKey } })
}
export function clearCacheName(cacheName) {
  return request({ url: '/api/monitor/cache/clearName', method: 'post', data: { cacheName } })
}
export function clearCacheKey(cacheName, cacheKey) {
  return request({ url: '/api/monitor/cache/clearKey', method: 'post', data: { cacheName, cacheKey } })
}
export function clearAllCache() {
  return request({ url: '/api/monitor/cache/clearAll', method: 'post' })
}
export function listJob(params) {
  return request({ url: '/api/monitor/job/list', method: 'get', params })
}
export function getJob(id) {
  return request({ url: '/api/monitor/job/get/' + id, method: 'get' })
}
export function addJob(data) {
  return request({ url: '/api/monitor/job/add', method: 'post', data })
}
export function updateJob(data) {
  return request({ url: '/api/monitor/job/edit', method: 'post', data })
}
export function removeJob(ids) {
  return request({ url: '/api/monitor/job/remove', method: 'post', data: { ids } })
}
export function changeJobStatus(jobId, status) {
  return request({ url: '/api/monitor/job/changeStatus', method: 'post', data: { jobId, status } })
}
export function runJob(jobId) {
  return request({ url: '/api/monitor/job/run', method: 'post', data: { jobId } })
}
export function listJobLog(params) {
  return request({ url: '/api/monitor/jobLog/list', method: 'get', params })
}
export function cleanJobLog() {
  return request({ url: '/api/monitor/jobLog/clean', method: 'post' })
}
