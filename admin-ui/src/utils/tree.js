export function handleTree(list, id = 'id', parentId = 'parentId') {
  const nodes = (list || []).map((item) => ({ ...item, children: [] }))
  const map = new Map(nodes.map((node) => [String(node[id]), node]))
  const roots = []
  nodes.forEach((node) => {
    const pid = node[parentId]
    const parent = pid !== undefined && pid !== null && String(pid) !== '0' ? map.get(String(pid)) : null
    if (parent) {
      parent.children.push(node)
    } else {
      roots.push(node)
    }
  })
  const prune = (arr) => arr.map((node) => {
    if (!node.children.length) {
      const copy = { ...node }
      delete copy.children
      return copy
    }
    return { ...node, children: prune(node.children) }
  })
  return prune(roots)
}

export function ztreeToAnt(nodes) {
  const map = new Map()
  const list = []
  ;(nodes || []).forEach((node) => {
    const id = Number(node.id)
    map.set(id, { key: id, value: id, title: node.name || node.title || String(id), children: [] })
    list.push({
      id,
      pid: Number(node.pId ?? node.pid ?? 0),
      checked: !!node.checked,
    })
  })
  const roots = []
  list.forEach((item) => {
    const current = map.get(item.id)
    const parent = item.pid !== item.id ? map.get(item.pid) : null
    if (parent) {
      parent.children.push(current)
    } else {
      roots.push(current)
    }
  })
  const prune = (arr) => arr.map((node) => {
    if (!node.children.length) {
      const copy = { ...node }
      delete copy.children
      return copy
    }
    return { ...node, children: prune(node.children) }
  })
  return {
    tree: prune(roots),
    checked: list.filter((item) => item.checked).map((item) => item.id),
  }
}

export function num(value) {
  if (value === null || value === undefined || value === '') {
    return undefined
  }
  const n = Number(value)
  return Number.isNaN(n) ? undefined : n
}

export function nums(list) {
  return (list || []).map((item) => Number(item)).filter((item) => !Number.isNaN(item))
}

export function isOn(status) {
  return String(status) === '0'
}
