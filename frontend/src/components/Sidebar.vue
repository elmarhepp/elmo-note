<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <span class="sidebar-logo">elmo-note</span>
      <button class="btn-logout" @click="handleLogout" title="Abmelden">&#x2192;</button>
    </div>

    <div class="sidebar-search">
      <input
        v-model="searchQuery"
        placeholder="Suchen..."
        @keydown.enter="emitSearch"
        @input="onSearchInput"
      />
    </div>

    <nav class="sidebar-nav">
      <button
        class="nav-item"
        :class="{ active: !activeNotebookId && !activeTagId && !isSearching }"
        @click="selectAll"
      >
        <span class="nav-icon">&#128196;</span> Alle Notizen
      </button>
    </nav>

    <div class="sidebar-section">
      <div class="section-header">
        <span>Notizbücher</span>
        <button class="btn-add" @click="showNewNotebook = true">+</button>
      </div>
      <div v-if="showNewNotebook" class="new-item-form">
        <input
          v-model="newNotebookName"
          placeholder="Name..."
          @keydown.enter="addNotebook"
          @keydown.esc="showNewNotebook = false"
          autofocus
        />
      </div>
      <button
        v-for="nb in notebooks"
        :key="nb.id"
        class="nav-item"
        :class="{ active: activeNotebookId === nb.id }"
        @click="selectNotebook(nb.id)"
      >
        <span class="nav-icon">&#128193;</span>
        <span class="nav-label">{{ nb.name }}</span>
        <span class="nav-count">{{ nb.notes_count }}</span>
      </button>
    </div>

    <div class="sidebar-section">
      <div class="section-header">
        <span>Tags</span>
        <button class="btn-add" @click="showNewTag = true">+</button>
      </div>
      <div v-if="showNewTag" class="new-item-form">
        <input
          v-model="newTagName"
          placeholder="Tag-Name..."
          @keydown.enter="addTag"
          @keydown.esc="showNewTag = false"
          autofocus
        />
      </div>
      <button
        v-for="tag in tags"
        :key="tag.id"
        class="nav-item"
        :class="{ active: activeTagId === tag.id }"
        @click="selectTag(tag.id)"
      >
        <span class="nav-icon">#</span>
        <span class="nav-label">{{ tag.name }}</span>
        <span class="nav-count">{{ tag.notes_count }}</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotesStore } from '@/stores/notes'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const store = useNotesStore()
const router = useRouter()

const searchQuery = ref('')
const showNewNotebook = ref(false)
const newNotebookName = ref('')
const showNewTag = ref(false)
const newTagName = ref('')
const isSearching = ref(false)

const notebooks = computed(() => store.notebooks)
const tags = computed(() => store.tags)
const activeNotebookId = computed(() => store.activeNotebookId)
const activeTagId = computed(() => store.activeTagId)

const emit = defineEmits(['search', 'clearSearch'])

function selectAll() {
  store.activeNotebookId = null
  store.activeTagId = null
  isSearching.value = false
  searchQuery.value = ''
  store.fetchNotes()
  emit('clearSearch')
}

function selectNotebook(id) {
  store.activeNotebookId = id
  store.activeTagId = null
  isSearching.value = false
  searchQuery.value = ''
  store.fetchNotes({ notebook_id: id })
}

function selectTag(id) {
  store.activeTagId = id
  store.activeNotebookId = null
  isSearching.value = false
  searchQuery.value = ''
  store.fetchNotes({ tag_id: id })
}

async function addNotebook() {
  if (!newNotebookName.value.trim()) return
  await store.createNotebook(newNotebookName.value.trim())
  newNotebookName.value = ''
  showNewNotebook.value = false
}

async function addTag() {
  if (!newTagName.value.trim()) return
  await store.createTag(newTagName.value.trim())
  newTagName.value = ''
  showNewTag.value = false
}

function emitSearch() {
  if (searchQuery.value.trim().length >= 2) {
    isSearching.value = true
    emit('search', searchQuery.value.trim())
  }
}

function onSearchInput() {
  if (!searchQuery.value) {
    isSearching.value = false
    emit('clearSearch')
  }
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.sidebar {
  width: 220px;
  min-width: 220px;
  background: var(--color-sidebar);
  color: var(--color-sidebar-text);
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow-y: auto;
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1rem 0.75rem;
}

.sidebar-logo {
  font-weight: 700;
  font-size: 1rem;
  color: var(--color-accent);
  letter-spacing: 0.02em;
}

.btn-logout {
  color: var(--color-sidebar-text);
  opacity: 0.5;
  font-size: 1.1rem;
  padding: 0.2rem 0.4rem;
  border-radius: 4px;
  transition: opacity 0.15s;
}

.btn-logout:hover {
  opacity: 1;
}

.sidebar-search input {
  width: calc(100% - 2rem);
  margin: 0 1rem 0.75rem;
  padding: 0.45rem 0.7rem;
  border-radius: 6px;
  border: none;
  background: rgba(255,255,255,0.1);
  color: var(--color-sidebar-text);
  font-size: 0.85rem;
  outline: none;
}

.sidebar-search input::placeholder {
  color: rgba(255,255,255,0.4);
}

.sidebar-nav {
  padding: 0 0.5rem 0.5rem;
}

.sidebar-section {
  padding: 0.5rem 0.5rem;
  border-top: 1px solid rgba(255,255,255,0.08);
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.3rem 0.5rem 0.5rem;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: rgba(255,255,255,0.4);
}

.btn-add {
  color: rgba(255,255,255,0.5);
  font-size: 1rem;
  line-height: 1;
  padding: 0 0.2rem;
  transition: color 0.15s;
}

.btn-add:hover {
  color: var(--color-accent);
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.4rem 0.6rem;
  border-radius: 6px;
  font-size: 0.875rem;
  color: var(--color-sidebar-text);
  text-align: left;
  transition: background 0.1s;
  opacity: 0.8;
}

.nav-item:hover {
  background: var(--color-sidebar-hover);
  opacity: 1;
}

.nav-item.active {
  background: rgba(74,158,107,0.2);
  color: var(--color-accent);
  opacity: 1;
}

.nav-icon {
  font-size: 0.9rem;
  flex-shrink: 0;
}

.nav-label {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.nav-count {
  font-size: 0.75rem;
  opacity: 0.5;
  flex-shrink: 0;
}

.new-item-form input {
  width: 100%;
  padding: 0.4rem 0.6rem;
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 6px;
  color: white;
  font-size: 0.85rem;
  outline: none;
  margin-bottom: 0.25rem;
}
</style>
