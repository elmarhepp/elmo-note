<template>
  <div class="editor-wrapper">
    <div class="editor-toolbar">
      <button
        v-for="action in toolbarActions"
        :key="action.label"
        @click="action.command()"
        :class="{ active: action.isActive?.() }"
        :title="action.label"
        class="toolbar-btn"
      >
        <span v-html="action.icon"></span>
      </button>
    </div>
    <editor-content :editor="editor" class="editor-content" />
  </div>
</template>

<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import { watch } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit,
    Placeholder.configure({ placeholder: 'Beginne zu schreiben...' }),
  ],
  onUpdate({ editor }) {
    emit('update:modelValue', editor.getHTML())
  },
})

watch(() => props.modelValue, (val) => {
  if (editor.value && editor.value.getHTML() !== val) {
    editor.value.commands.setContent(val || '', false)
  }
})

const toolbarActions = [
  {
    label: 'Bold',
    icon: '<b>B</b>',
    command: () => editor.value?.chain().focus().toggleBold().run(),
    isActive: () => editor.value?.isActive('bold'),
  },
  {
    label: 'Italic',
    icon: '<i>I</i>',
    command: () => editor.value?.chain().focus().toggleItalic().run(),
    isActive: () => editor.value?.isActive('italic'),
  },
  {
    label: 'Strike',
    icon: '<s>S</s>',
    command: () => editor.value?.chain().focus().toggleStrike().run(),
    isActive: () => editor.value?.isActive('strike'),
  },
  {
    label: 'H1',
    icon: 'H1',
    command: () => editor.value?.chain().focus().toggleHeading({ level: 1 }).run(),
    isActive: () => editor.value?.isActive('heading', { level: 1 }),
  },
  {
    label: 'H2',
    icon: 'H2',
    command: () => editor.value?.chain().focus().toggleHeading({ level: 2 }).run(),
    isActive: () => editor.value?.isActive('heading', { level: 2 }),
  },
  {
    label: 'Liste',
    icon: '&#8226;&#8212;',
    command: () => editor.value?.chain().focus().toggleBulletList().run(),
    isActive: () => editor.value?.isActive('bulletList'),
  },
  {
    label: 'Nummerierte Liste',
    icon: '1.',
    command: () => editor.value?.chain().focus().toggleOrderedList().run(),
    isActive: () => editor.value?.isActive('orderedList'),
  },
  {
    label: 'Code',
    icon: '&lt;/&gt;',
    command: () => editor.value?.chain().focus().toggleCode().run(),
    isActive: () => editor.value?.isActive('code'),
  },
  {
    label: 'Zitat',
    icon: '&#x201C;',
    command: () => editor.value?.chain().focus().toggleBlockquote().run(),
    isActive: () => editor.value?.isActive('blockquote'),
  },
]
</script>

<style scoped>
.editor-wrapper {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.editor-toolbar {
  display: flex;
  gap: 0.25rem;
  padding: 0.5rem 1rem;
  border-bottom: 1px solid var(--color-border);
  background: var(--color-white);
  flex-wrap: wrap;
}

.toolbar-btn {
  padding: 0.3rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  color: var(--color-text-muted);
  transition: background 0.1s, color 0.1s;
  min-width: 28px;
  text-align: center;
}

.toolbar-btn:hover {
  background: var(--color-border);
  color: var(--color-text);
}

.toolbar-btn.active {
  background: rgba(74,158,107,0.12);
  color: var(--color-accent);
}

.editor-content {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem 2rem;
}
</style>

<style>
.tiptap {
  min-height: 100%;
  outline: none;
  font-size: 0.95rem;
  line-height: 1.7;
  color: var(--color-text);
}

.tiptap p {
  margin-bottom: 0.75em;
}

.tiptap h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.5em; }
.tiptap h2 { font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5em; }

.tiptap ul, .tiptap ol {
  padding-left: 1.5em;
  margin-bottom: 0.75em;
}

.tiptap blockquote {
  border-left: 3px solid var(--color-accent);
  padding-left: 1em;
  color: var(--color-text-muted);
  margin: 0.75em 0;
}

.tiptap code {
  background: rgba(0,0,0,0.06);
  padding: 0.1em 0.35em;
  border-radius: 3px;
  font-family: var(--font-mono);
  font-size: 0.875em;
}

.tiptap pre {
  background: #1a1a2e;
  color: #e8e8e8;
  padding: 1em;
  border-radius: 6px;
  margin: 0.75em 0;
  overflow-x: auto;
}

.tiptap pre code {
  background: none;
  padding: 0;
  font-size: 0.875em;
}

.tiptap p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  float: left;
  color: var(--color-text-muted);
  pointer-events: none;
  height: 0;
}
</style>
