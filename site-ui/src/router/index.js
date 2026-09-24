import { createRouter, createWebHistory } from 'vue-router'
import SiteLayout from '@/layouts/SiteLayout.vue'
import { getToken } from '@/utils/auth'

const router = createRouter({
  // Always mount at domain root. Vite `base` (/site/ in prod) only prefixes built assets.
  history: createWebHistory('/'),
  routes: [
    {
      path: '/',
      component: SiteLayout,
      children: [
        { path: '', name: 'Home', component: () => import('@/views/Home.vue'), meta: { title: '首页' } },
        { path: 'c/:diyname', name: 'Channel', component: () => import('@/views/Channel.vue'), meta: { title: '栏目' } },
        { path: 'a/:diyname', name: 'Archives', component: () => import('@/views/Archives.vue'), meta: { title: '文档' } },
        { path: 'p/:diyname', name: 'Page', component: () => import('@/views/Page.vue'), meta: { title: '单页' } },
        { path: 't/:name', name: 'Tag', component: () => import('@/views/Tag.vue'), meta: { title: '标签' } },
        { path: 'special/:diyname', name: 'Special', component: () => import('@/views/Special.vue'), meta: { title: '专题' } },
        { path: 'd/:diyname/post', name: 'DiyformPost', component: () => import('@/views/DiyformPost.vue'), meta: { title: '填写表单' } },
        { path: 'd/:diyname', name: 'Diyform', component: () => import('@/views/Diyform.vue'), meta: { title: '表单' } },
        { path: 's', name: 'Search', component: () => import('@/views/Search.vue'), meta: { title: '搜索' } },
        { path: 'member/login', name: 'MemberLogin', component: () => import('@/views/member/Login.vue'), meta: { title: '登录', guest: true } },
        { path: 'member/register', name: 'MemberRegister', component: () => import('@/views/member/Register.vue'), meta: { title: '注册', guest: true } },
        { path: 'member', name: 'MemberHome', component: () => import('@/views/member/Home.vue'), meta: { title: '会员中心', auth: true } },
        { path: 'member/profile', name: 'MemberProfile', component: () => import('@/views/member/Profile.vue'), meta: { title: '个人资料', auth: true } },
        { path: 'member/changepwd', name: 'MemberChangepwd', component: () => import('@/views/member/Changepwd.vue'), meta: { title: '修改密码', auth: true } },
        { path: 'member/comments', name: 'MemberComments', component: () => import('@/views/member/Comments.vue'), meta: { title: '我发表的评论', auth: true } },
        { path: 'pay/wechat', name: 'PayWechat', component: () => import('@/views/pay/Wechat.vue'), meta: { title: '微信支付' } },
        { path: 'pay/alipay', name: 'PayAlipay', component: () => import('@/views/pay/Alipay.vue'), meta: { title: '支付宝支付' } },
        { path: 'pay/result', name: 'PayResult', component: () => import('@/views/pay/Result.vue'), meta: { title: '支付结果' } },
        { path: ':pathMatch(.*)*', name: 'NotFound', component: () => import('@/views/NotFound.vue'), meta: { title: '页面不存在' } },
      ],
    },
  ],
  scrollBehavior() {
    return { top: 0 }
  },
})

router.beforeEach((to) => {
  if (to.meta?.auth && !getToken()) {
    return { path: '/member/login', query: { url: to.fullPath } }
  }
  if (to.meta?.guest && getToken() && (to.name === 'MemberLogin' || to.name === 'MemberRegister')) {
    return { path: '/member' }
  }
  return true
})

export default router
