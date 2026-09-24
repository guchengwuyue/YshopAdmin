/** Section / block: fade + rise when scrolled into view */
export const pageEnter = {
  initial: { opacity: 0, y: 28 },
  visibleOnce: {
    opacity: 1,
    y: 0,
    transition: { type: 'spring', stiffness: 220, damping: 24 },
  },
}

/**
 * List / card stagger: each item enters in sequence when in viewport.
 * @param {number} index 0-based index within the list
 * @param {number} [step=80] delay step in ms
 */
export function pageEnterStagger(index, step = 80) {
  const delay = Math.max(0, Number(index) || 0) * step
  return {
    initial: { opacity: 0, y: 24 },
    visibleOnce: {
      opacity: 1,
      y: 0,
      transition: {
        type: 'spring',
        stiffness: 220,
        damping: 24,
        delay,
      },
    },
  }
}
