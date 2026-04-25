/**
 * Toast Service Helper
 * Permet d'afficher des notifications élégantes sans importer de composant
 */
export const toast = {
  success(message, title = 'Succès') {
    this.show(message, 'success', title)
  },
  error(message, title = 'Erreur') {
    this.show(message, 'error', title)
  },
  info(message, title = 'Information') {
    this.show(message, 'info', title)
  },
  show(message, type = 'info', title = '', duration = 4000) {
    const event = new CustomEvent('app-toast', {
      detail: { message, type, title, duration }
    })
    window.dispatchEvent(event)
  }
}

export default toast
