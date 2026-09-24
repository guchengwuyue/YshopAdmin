import request from '@/utils/request'

export function listGen(params) {
  return request({ url: '/api/tool/gen/list', method: 'get', params })
}
export function listDbTable(params) {
  return request({ url: '/api/tool/gen/db/list', method: 'get', params })
}
export function getGen(id) {
  return request({ url: '/api/tool/gen/get/' + id, method: 'get' })
}
export function importTable(tables) {
  return request({ url: '/api/tool/gen/importTable', method: 'post', data: { tables } })
}
export function updateGen(data) {
  return request({ url: '/api/tool/gen/edit', method: 'post', data })
}
export function removeGen(ids) {
  return request({ url: '/api/tool/gen/remove', method: 'post', data: { ids } })
}
export function previewGen(id) {
  return request({ url: '/api/tool/gen/preview/' + id, method: 'get' })
}
export function downloadGen(id) {
  return request({ url: '/api/tool/gen/download/' + id, method: 'get', responseType: 'blob' })
}

export function listFile(params) {
  return request({ url: '/api/tool/file/list', method: 'get', params })
}
export function uploadFile(file, groupId = 0) {
  const data = new FormData()
  data.append('file', file)
  if (groupId) data.append('groupId', String(groupId))
  return request({ url: '/api/tool/file/upload', method: 'post', data })
}
export function removeFile(ids) {
  return request({ url: '/api/tool/file/remove', method: 'post', data: { ids } })
}
export function moveFile(ids, groupId) {
  return request({ url: '/api/tool/file/move', method: 'post', data: { ids, groupId } })
}
export function treeFileGroup() {
  return request({ url: '/api/tool/fileGroup/tree', method: 'get' })
}
export function addFileGroup(data) {
  return request({ url: '/api/tool/fileGroup/add', method: 'post', data })
}
export function updateFileGroup(data) {
  return request({ url: '/api/tool/fileGroup/edit', method: 'post', data })
}
export function removeFileGroup(id) {
  return request({ url: '/api/tool/fileGroup/remove', method: 'post', data: { id } })
}
export function listFileConfig(params) {
  return request({ url: '/api/tool/fileConfig/list', method: 'get', params })
}
export function getFileConfig(id) {
  return request({ url: '/api/tool/fileConfig/get/' + id, method: 'get' })
}
export function addFileConfig(data) {
  return request({ url: '/api/tool/fileConfig/add', method: 'post', data })
}
export function updateFileConfig(data) {
  return request({ url: '/api/tool/fileConfig/edit', method: 'post', data })
}
export function removeFileConfig(ids) {
  return request({ url: '/api/tool/fileConfig/remove', method: 'post', data: { ids } })
}
export function masterFileConfig(id) {
  return request({ url: '/api/tool/fileConfig/master', method: 'post', data: { id } })
}
export function testFileConfig(id) {
  return request({ url: '/api/tool/fileConfig/test', method: 'post', data: { id } })
}

export function listAddonDev(params) {
  return request({ url: '/api/tool/addondev/list', method: 'get', params })
}
export function getAddonDev(name) {
  return request({ url: '/api/tool/addondev/get/' + name, method: 'get' })
}
export function addAddonDev(data) {
  return request({ url: '/api/tool/addondev/add', method: 'post', data })
}
export function updateAddonDev(data) {
  return request({ url: '/api/tool/addondev/edit', method: 'post', data })
}
export function packageAddonDev(data) {
  return request({ url: '/api/tool/addondev/package', method: 'post', data })
}
export function listAddonMenu(params) {
  return request({ url: '/api/tool/addondev/menu', method: 'get', params })
}
export function getAddonMenu(id, name) {
  return request({ url: '/api/tool/addondev/menu/get/' + id, method: 'get', params: { name } })
}
export function addonMenuTree(name, excludeId) {
  return request({ url: '/api/tool/addondev/menu/tree', method: 'get', params: { name, excludeId } })
}
export function addAddonMenu(data) {
  return request({ url: '/api/tool/addondev/menu/add', method: 'post', data })
}
export function updateAddonMenu(data) {
  return request({ url: '/api/tool/addondev/menu/edit', method: 'post', data })
}
export function removeAddonMenu(name, id) {
  return request({ url: '/api/tool/addondev/menu/remove', method: 'post', data: { name, id } })
}
export function syncAddonMenu(name) {
  return request({ url: '/api/tool/addondev/sync', method: 'post', data: { name } })
}
