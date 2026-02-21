<template>
  <div class="note-list">
    <div class="note-list-header">
      <h2 class="note-list-title">{{ title }}</h2>
      <button class="btn-new-note" @click="newNote" title="Neue Notiz">+</button>
    </div>

    <div class="note-items">
      <div
        v-for="note in notes"
        :key="note.id"
        class="note-item"
        :class="{ active: activeNoteId === note.id }"
        @click="$emit('select', note.id)"
      >
        <div class="note-item-title">{{ note.title || 'Ohne Titel' }}</div>
        <div class="note-item-meta">
          <span class="note-item-date">{{ formatDate(note.updated_at) }}</span>
          <span v-if="note.notebook" class="note-item-notebook">{{ note.notebook.name }}</span>
        </div>
        <div v-if="note.tags?.length" class="note-item-tags">
          <span v-for="tag in note.tags" :key="tag.id" class="tag-chip">{{ tag.name }}</span>
        </div>
      </div>

      <div v-if="notes.length === 0" class="empty-state">
        <p>Keine Notizen vorhanden.</p>
        <button @click="newNote" class="btn-create-first">Erste Notiz erstellen</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useNotesStore } from '@/stores/notes'

const props = defineProps({
  notes: { type: Array, default: () => [] },
  activeNoteId: { type: Number, default: null },
  title: { type: String, default: 'Alle Notizen' },
})

const emit = defineEmits(['select', 'created'])
const store = useNotesStore()

async function newNote() {
  const note = await store.createNote()
  emit('created', note.id)
}

function formatDate(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleDateString('de-DE', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>

<style scoped>
.note-list {
  width: 260px;
  min-width: 260px;
  background: var(--color-list-bg);
  border-right: 1px solid var(--color-border);
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.note-list-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1rem 0.75rem;
  border-bottom: 1px solid var(--color-border);
}

.note-list-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--color-text);
}

.btn-new-note {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: var(--color-accent);
  color: white;
  font-size: 1.2rem;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
}

.btn-new-note:hover {
  background: var(--color-accent-hover);
}

.note-items {
  flex: 1;
  overflow-y: auto;
  padding: 0.5rem 0;
}

.note-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
  border-bottom: 1px solid var(--color-border);
  transition: background 0.1s;
}

.note-item:hover {
  background: rgba(0,0,0,0.03);
}

.note-item.active {
  background: rgba(74,158,107,0.08);
  border-left: 3px solid var(--color-accent);
}

.note-item-title {
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--color-text);
  margin-bottom: 0.25rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.note-item-meta {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.note-item-notebook {
  background: rgba(0,0,0,0.06);
  padding: 0.1rem 0.4rem;
  border-radius: 3px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 100px;
}

.note-item-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
  margin-top: 0.35rem;
}

.tag-chip {
  font-size: 0.7rem;
  padding: 0.1rem 0.4rem;
  border-radius: 3px;
  background: rgba(74,158,107,0.12);
  color: var(--color-accent);
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  text-align: center;
  color: var(--color-text-muted);
  font-size: 0.85rem;
  gap: 1rem;
}

.btn-create-first {
  padding: 0.5rem 1rem;
  background: var(--color-accent);
  color: white;
  border-radius: 6px;
  font-size: 0.85rem;
  transition: background 0.15s;
}

.btn-create-first:hover {
  background: var(--color-accent-hover);
}
</style>
