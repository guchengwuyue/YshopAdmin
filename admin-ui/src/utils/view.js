const localModules = import.meta.glob('../views/**/*.vue')
const addonModules = import.meta.glob('../../../addons/*/web/admin/**/*.vue')

export function loadView(component) {
  const localKey = `../views/${component}.vue`
  if (localModules[localKey]) {
    return localModules[localKey]
  }
  const name = String(component || '')
  const slash = name.indexOf('/')
  if (slash > 0) {
    const addon = name.slice(0, slash)
    const rest = name.slice(slash + 1)
    const addonKey = `../../../addons/${addon}/web/admin/${rest}.vue`
    if (addonModules[addonKey]) {
      return addonModules[addonKey]
    }
  }
  return () => import('../views/placeholder/index.vue')
}

export function collectViews(nodes, acc = []) {
  ;(nodes || []).forEach((node) => {
    if (node.children?.length) {
      collectViews(node.children, acc)
      return
    }
    if (node.meta?.link) {
      return
    }
    if (!node.component || ['Layout', 'ParentView', 'InnerLink'].includes(node.component)) {
      return
    }
    acc.push(node)
  })
  return acc
}

export function findNode(nodes, path) {
  for (const node of nodes || []) {
    if (node.path === path) {
      return node
    }
    const child = findNode(node.children, path)
    if (child) {
      return child
    }
  }
  return null
}

export function findParents(nodes, path, parents = []) {
  for (const node of nodes || []) {
    if (node.path === path) {
      return parents
    }
    if (node.children?.length) {
      const found = findParents(node.children, path, [...parents, node.path])
      if (found) {
        return found
      }
    }
  }
  return null
}
