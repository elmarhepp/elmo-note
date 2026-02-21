<template>
  <div class="app-layout">
    <Sidebar @search="handleSearch" @clearSearch="clearSearch" />

    <NoteList
      :notes="displayNotes"
      :active-note-id="activeNoteId"
      :title="listTitle"
      @select="selectNote"
      @created="selectNote"
    />

    <main class="editor-pane">
      <template v-if="activeNote">
        <div class="editor-header">
          <input
            class="note-title-input"
            v-model="noteTitle"
            placeholder="Titel..."
            @blur="saveTitle"
            @keydown.enter="$event.target.blur()"
          />
          <div class="note-meta">
            <select v-model="noteNotebookId" @change="saveNotebook" class="meta-select">
              <option :value="null">Kein Notizbuch</option>
              <option v-for="nb in notebooks" :key="nb.id" :value="nb.id">{{ nb.name }}</option>
            </select>
            <div class="tag-selector">
              <span
                v-for="tag in allTags"
                :key="tag.id"
                class="tag-option"
                :class="{ selected: noteTagIds.includes(tag.id) }"
                @click="toggleTag(tag.id)"
              >
                #{{ tag.name }}
              </span>
            </div>
          </div>
        </div>

        <TiptapEditor v-model="noteContent" @update:modelValue="scheduleAutoSave" />

        <div class="save-status">{{ saveStatus }}</div>
      </template>

      <div v-else class="empty-editor">
        <p>Wähle eine Notiz aus oder erstelle eine neue.</p>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useNotesStore } from '@/stores/notes'
import Sidebar from '@/components/Sidebar.vue'
import NoteList from '@/components/NoteList.vue'
import TiptapEditor from '@/components/TiptapEditor.vue'

const store = useNotesStore()

const activeNoteId = ref(null)
const noteTitle = ref('')
const noteContent = ref('')
const noteNotebookId = ref(null)
const noteTagIds = ref([])
const saveStatus = ref('')
const searchResults = ref(null)
let autoSaveTimer = null

const activeNote = computed(() => store.activeNote)
const notebooks = computed(() => store.notebooks)
const allTags = computed(() => store.tags)
const displayNotes = computed(() => searchResults.value ?? store.notes)
const listTitle = computed(() => {
  if (searchResults.value !== null) return `Suchergebnisse (${searchResults.value.length})`
  if (store.activeNotebookId) return notebooks.value.find(n => n.id === store.activeNotebookId)?.name ?? 'Notizbuch'
  if (store.activeTagId) return '#' + (allTags.value.find(t => t.id === store.activeTagId)?.name ?? 'Tag')
  return 'Alle Notizen'
})

onMounted(async () => {
  await Promise.all([store.fetchNotebooks(), store.fetchTags(), store.fetchNotes()])
})

watch(activeNote, (note) => {
  if (note) {
    noteTitle.value = note.title
    noteContent.value = note.content || ''
    noteNotebookId.value = note.notebook_id
    noteTagIds.value = note.tags?.map(t => t.id) ?? []
  }
})

async function selectNote(id) {
  activeNoteId.value = id
  await store.fetchNote(id)
}

function scheduleAutoSave() {
  saveStatus.value = 'Änderungen...'
  clearTimeout(autoSaveTimer)
  autoSaveTimer = setTimeout(saveContent, 1200)
}

async function saveContent() {
  if (!activeNote.value) return
  await store.updateNote(activeNote.value.id, {
    content: noteContent.value,
  })
  saveStatus.value = 'Gespeichert'
  setTimeout(() => saveStatus.value = '', 2000)
}

async function saveTitle() {
  if (!activeNote.value || noteTitle.value === activeNote.value.title) return
  await store.updateNote(activeNote.value.id, { title: noteTitle.value })
}

async function saveNotebook() {
  if (!activeNote.value) return
  await store.updateNote(activeNote.value.id, { notebook_id: noteNotebookId.value })
  await store.fetchNotebooks()
}

async function toggleTag(tagId) {
  if (!activeNote.value) return
  if (noteTagIds.value.includes(tagId)) {
    noteTagIds.value = noteTagIds.value.filter(id => id !== tagId)
  } else {
    noteTagIds.value.push(tagId)
  }
  await store.updateNote(activeNote.value.id, { tag_ids: noteTagIds.value })
  await store.fetchTags()
}

async function handleSearch(query) {
  const results = await store.search(query)
  searchResults.value = results
}

function clearSearch() {
  searchResults.value = null
}
</script>

<style scoped>
.app-layout {
  display: flex;
  height: 100vh;
  width: 100%;
  overflow: hidden;
}

.editor-pane {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: var(--color-white);
  overflow: hidden;
  position: relative;
}

.editor-header {
  padding: 1rem 2rem 0.75rem;
  border-bottom: 1px solid var(--color-border);
}

.note-title-input {
  width: 100%;
  font-size: 1.4rem;
  font-weight: 700;
  border: none;
  outline: none;
  color: var(--color-text);
  background: transparent;
  margin-bottom: 0.5rem;
}

.note-meta {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.meta-select {
  font-size: 0.8rem;
  padding: 0.2rem 0.5rem;
  border: 1px solid var(--color-border);
  border-radius: 4px;
  outline: none;
  color: var(--color-text-muted);
  background: transparent;
}

.tag-selector {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
}

.tag-option {
  font-size: 0.75rem;
  padding: 0.15rem 0.5rem;
  border-radius: 3px;
  border: 1px solid var(--color-border);
  color: var(--color-text-muted);
  cursor: pointer;
  transition: all 0.1s;
}

.tag-option:hover {
  border-color: var(--color-accent);
  color: var(--color-accent);
}

.tag-option.selected {
  background: rgba(74,158,107,0.12);
  border-color: var(--color-accent);
  color: var(--color-accent);
}

.save-status {
  position: absolute;
  bottom: 1rem;
  right: 1.5rem;
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.empty-editor {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-muted);
  font-size: 0.9rem;
}
</style>
