import request from '@/utils/request'

export function fetchSite(params) {
  return request.get('/api/cms/site', { params })
}

export function fetchHome(params) {
  return request.get('/api/cms/home', { params })
}

export function fetchChannel(diyname, params) {
  return request.get(`/api/cms/channel/${encodeURIComponent(diyname)}`, { params })
}

export function fetchArchives(diyname, params) {
  return request.get(`/api/cms/archives/${encodeURIComponent(diyname)}`, { params })
}

export function fetchPage(diyname, params) {
  return request.get(`/api/cms/page/${encodeURIComponent(diyname)}`, { params })
}

export function fetchTag(name, params) {
  return request.get(`/api/cms/tag/${encodeURIComponent(name)}`, { params })
}

export function fetchSpecial(diyname, params) {
  return request.get(`/api/cms/special/${encodeURIComponent(diyname)}`, { params })
}

export function fetchSearch(params) {
  return request.get('/api/cms/search', { params })
}

export function fetchDiyform(diyname, params) {
  return request.get(`/api/cms/diyform/${encodeURIComponent(diyname)}`, { params })
}

export function fetchDiyformPost(diyname, params) {
  return request.get(`/api/cms/diyform/${encodeURIComponent(diyname)}/post`, { params })
}

export function submitDiyform(diyname, data) {
  return request.post(`/api/cms/diyform/${encodeURIComponent(diyname)}/post`, data)
}

export function postVote(data) {
  return request.post('/api/cms/vote', data)
}

export function fetchComments(params) {
  return request.get('/api/cms/comments', { params })
}

export function postComment(data) {
  return request.post('/api/cms/comment/post', data)
}

/** 图片验证码（char 四位字母数字，与留言板一致） */
export function captchaImageUrl(type = 'char') {
  return `/captcha/captchaImage?type=${encodeURIComponent(type)}&t=${Date.now()}`
}
