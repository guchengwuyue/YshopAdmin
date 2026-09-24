import request from '@/utils/request'

export function login(data) {
  return request.post('/api/member/login', data)
}

export function register(data) {
  return request.post('/api/member/register', data)
}

export function logout() {
  return request.post('/api/member/logout')
}

export function getInfo() {
  return request.get('/api/member/getInfo')
}

export function updateProfile(data) {
  return request.post('/api/member/profile', data)
}

export function changePwd(data) {
  return request.post('/api/member/changepwd', data)
}
