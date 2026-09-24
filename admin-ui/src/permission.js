import router from './router'
import { getToken } from './utils/auth'
import { useUserStore } from './store/user'
import { usePermissionStore } from './store/permission'

router.beforeEach(async (to) => {
  const token = getToken()
  if (!token) {
    if (to.path === '/login') {
      return true
    }
    return '/login'
  }
  if (to.path === '/login') {
    return '/'
  }
  const userStore = useUserStore()
  const permissionStore = usePermissionStore()
  if (userStore.inited && permissionStore.added) {
    return true
  }
  try {
    if (!userStore.inited) {
      await userStore.fetchInfo()
    }
    if (!permissionStore.added) {
      await permissionStore.generate(router)
    }
    return { ...to, replace: true }
  } catch (e) {
    userStore.reset()
    return '/login'
  }
})
