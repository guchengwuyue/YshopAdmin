import request from '@/utils/request'

export function payWechat() {
  return request.get('/api/pay/wechat')
}

export function payAlipay() {
  return request.get('/api/pay/alipay')
}

export function payQuery(data) {
  return request.post('/api/pay/query', data)
}
