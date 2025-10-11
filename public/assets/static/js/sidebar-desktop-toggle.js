(function () {
  const STORAGE_KEY = 'sidebar.desktop.icon_only'
  const DESKTOP_BREAKPOINT = 1200

  const isDesktop = () => window.innerWidth >= DESKTOP_BREAKPOINT

  const getSidebar = () => document.getElementById('sidebar')

  const readStoredState = () => {
    try {
      return localStorage.getItem(STORAGE_KEY) === 'true'
    } catch (error) {
      return false
    }
  }

  const persistState = (value) => {
    try {
      localStorage.setItem(STORAGE_KEY, value ? 'true' : 'false')
    } catch (error) {
      // ignore storage failure silently
    }
  }

  const applyState = (collapsed) => {
    const sidebar = getSidebar()
    if (!sidebar) return

    sidebar.classList.toggle('sidebar-icon-only', collapsed)
    persistState(collapsed)
  }

  const handleToggle = (event) => {
    if (!isDesktop()) return

    const sidebar = getSidebar()
    if (!sidebar) return

    event.preventDefault()
    event.stopImmediatePropagation()

    const collapsed = !sidebar.classList.contains('sidebar-icon-only')
    applyState(collapsed)
  }

  const handleSidebarLinkClick = (event) => {
    if (!isDesktop()) return

    const sidebar = getSidebar()
    if (!sidebar || !sidebar.classList.contains('sidebar-icon-only')) return

    event.preventDefault()
    event.stopImmediatePropagation()

    applyState(false)
    const link = event.currentTarget
    requestAnimationFrame(() => {
      link.focus()
    })
  }

  const syncWithViewport = () => {
    const sidebar = getSidebar()
    if (!sidebar) return

    if (isDesktop()) {
      sidebar.classList.toggle('sidebar-icon-only', readStoredState())
    } else {
      sidebar.classList.remove('sidebar-icon-only')
      persistState(false)
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    syncWithViewport()

    const toggles = document.querySelectorAll('.burger-btn, .sidebar-hide')
    toggles.forEach((toggle) => {
      toggle.addEventListener('click', handleToggle, true)
    })

    const sidebarLinks = document.querySelectorAll('#sidebar .sidebar-link')
    sidebarLinks.forEach((link) => {
      link.addEventListener('click', handleSidebarLinkClick, true)
    })
  })

  window.addEventListener('resize', () => {
    syncWithViewport()
  })
})()
