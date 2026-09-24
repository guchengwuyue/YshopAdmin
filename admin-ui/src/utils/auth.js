const KEY = 'Admin-Token'

export function getToken() {
  return localStorage.getItem(KEY) || ''
}

export function setToken(token) {
  localStorage.setItem(KEY, token)
}

export function removeToken() {
  localStorage.removeItem(KEY)
}

export function loginHref() {
  const base = import.meta.env.BASE_URL || '/'
  return (base.endsWith('/') ? base : base + '/') + 'login'
}
