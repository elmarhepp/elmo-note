import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api/axios'

export const useNotesStore = defineStore('notes', () => {
  const notes = ref([])
  const notebooks = ref([])
  const tags = ref([])
  const activeNote = ref(null)
  const activeNotebookId = ref(null)
  const activeTagId = ref(null)

  async function fetchNotebooks() {
    const { data } = await api.get('/notebooks')
    notebooks.value = data
  }

  async function fetchTags() {
    const { data } = await api.get('/tags')
    tags.value = data
  }

  async function fetchNotes(params = {}) {
    const { data } = await api.get('/notes', { params })
    notes.value = data
  }

  async function fetchNote(id) {
    const { data } = await api.get(`/notes/${id}`)
    activeNote.value = data
    return data
  }

  async function createNote() {
    const payload = { title: 'Neue Notiz', content: '' }
    if (activeNotebookId.value) payload.notebook_id = activeNotebookId.value
    const { data } = await api.post('/notes', payload)
    notes.value.unshift(data)
    activeNote.value = data
    return data
  }

  async function updateNote(id, payload) {
    const { data } = await api.put(`/notes/${id}`, payload)
    const index = notes.value.findIndex((n) => n.id === id)
    if (index !== -1) notes.value[index] = { ...notes.value[index], ...data }
    if (activeNote.value?.id === id) activeNote.value = data
    return data
  }

  async function deleteNote(id) {
    await api.delete(`/notes/${id}`)
    notes.value = notes.value.filter((n) => n.id !== id)
    if (activeNote.value?.id === id) activeNote.value = null
  }

  async function createNotebook(name) {
    const { data } = await api.post('/notebooks', { name })
    notebooks.value.push(data)
    return data
  }

  async function createTag(name) {
    const { data } = await api.post('/tags', { name })
    tags.value.push(data)
    return data
  }

  async function search(q) {
    const { data } = await api.get('/search', { params: { q } })
    return data
  }

  return {
    notes, notebooks, tags, activeNote, activeNotebookId, activeTagId,
    fetchNotebooks, fetchTags, fetchNotes, fetchNote,
    createNote, updateNote, deleteNote, createNotebook, createTag, search,
  }
})
