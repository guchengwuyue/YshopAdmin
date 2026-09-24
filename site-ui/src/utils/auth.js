const KEY = 'yshop_member_token'

export function getToken() {
  return localStorage.getItem(KEY) || ''
}

export function setToken(token) {
  localStorage.setItem(KEY, token || '')
}

export function removeToken() {
  localStorage.removeItem(KEY)
}
