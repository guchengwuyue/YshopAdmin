import request from '@/utils/request'

export function login(data) {
  return request({ url: '/api/login', method: 'post', data })
}

export function logout() {
  return request({ url: '/api/logout', method: 'post' })
}

export function getInfo() {
  return request({ url: '/api/getInfo', method: 'get' })
}

export function getRouters() {
  return request({ url: '/api/getRouters', method: 'get' })
}

/** 图形验证码地址（math / char），带时间戳防缓存 */
export function getCaptchaImageUrl(type = 'math') {
  return `/yshopadmin/captcha/captchaImage?type=${encodeURIComponent(type)}&t=${Date.now()}`
}
