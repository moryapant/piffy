<template>
  <div class="overflow-hidden bg-white rounded-md border border-gray-300 rich-text-editor">
    <div class="flex overflow-x-auto items-center p-2 space-x-2 bg-gray-50 border-b border-gray-200 menu-bar">
      <button
        v-for="item in menuItems"
        :key="item.title"
        @click="item.action"
        :class="[
          'p-2 rounded hover:bg-gray-100 transition-colors',
          { 'bg-gray-100': item.isActive?.() }
        ]"
        type="button"
        :title="item.title"
      >
        <component :is="item.icon" class="w-5 h-5" />
      </button>
    </div>
    <editor-content
      :editor="editor"
      class="prose max-w-none p-4 min-h-[200px] focus:outline-none"
    />
  </div>
</template>

<script setup>
import { onBeforeUnmount, watch } from 'vue';
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import TextAlign from '@tiptap/extension-text-align';

import {
  BoldIcon,
  ItalicIcon,
  ListBulletIcon,
  QueueListIcon,
  CodeBracketIcon,
  ChatBubbleLeftRightIcon,
  MinusIcon,
  ArrowUturnLeftIcon,
  ArrowUturnRightIcon,
  LinkIcon,
  Bars3BottomLeftIcon,
  Bars2Icon,
  Bars3BottomRightIcon,
  HashtagIcon, // Used instead of HeadingIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue']);

const editor = new Editor({
  extensions: [
    StarterKit,
    Link.configure({
      openOnClick: false,
      HTMLAttributes: {
        class: 'text-blue-600 hover:text-blue-800 underline',
      },
    }),
    TextAlign.configure({
      types: ['heading', 'paragraph'],
    }),
  ],
  content: props.modelValue,
  editorProps: {
    attributes: {
      class: 'prose prose-sm sm:prose lg:prose-lg xl:prose-xl focus:outline-none',
    },
  },
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getHTML());
  },
});

// Watch for external changes to modelValue
watch(() => props.modelValue, (newContent) => {
  if (newContent !== editor.getHTML()) {
    editor.commands.setContent(newContent, false);
  }
});

// Clean up on unmount
onBeforeUnmount(() => {
  editor.destroy();
});

function addLink() {
  const url = window.prompt('Enter URL');
  if (url) {
    editor.chain().focus().setLink({ href: url }).run();
  } else if (url === '') {
    editor.chain().focus().unsetLink().run();
  }
}

const menuItems = [
  {
    icon: BoldIcon,
    title: 'Bold',
    action: () => editor.chain().focus().toggleBold().run(),
    isActive: () => editor.isActive('bold'),
  },
  {
    icon: ItalicIcon,
    title: 'Italic',
    action: () => editor.chain().focus().toggleItalic().run(),
    isActive: () => editor.isActive('italic'),
  },
  {
    icon: ListBulletIcon,
    title: 'Bullet List',
    action: () => editor.chain().focus().toggleBulletList().run(),
    isActive: () => editor.isActive('bulletList'),
  },
  {
    icon: QueueListIcon,
    title: 'Numbered List',
    action: () => editor.chain().focus().toggleOrderedList().run(),
    isActive: () => editor.isActive('orderedList'),
  },
  {
    icon: CodeBracketIcon,
    title: 'Code Block',
    action: () => editor.chain().focus().toggleCodeBlock().run(),
    isActive: () => editor.isActive('codeBlock'),
  },
  {
    icon: ChatBubbleLeftRightIcon,
    title: 'Quote',
    action: () => editor.chain().focus().toggleBlockquote().run(),
    isActive: () => editor.isActive('blockquote'),
  },
  {
    icon: MinusIcon,
    title: 'Horizontal Line',
    action: () => editor.chain().focus().setHorizontalRule().run(),
  },
  {
    icon: ArrowUturnLeftIcon,
    title: 'Undo',
    action: () => editor.chain().focus().undo().run(),
  },
  {
    icon: ArrowUturnRightIcon,
    title: 'Redo',
    action: () => editor.chain().focus().redo().run(),
  },
  {
    icon: LinkIcon,
    title: 'Add Link',
    action: addLink,
    isActive: () => editor.isActive('link'),
  },
  {
    icon: HashtagIcon,
    title: 'Heading 1',
    action: () => editor.chain().focus().toggleHeading({ level: 1 }).run(),
    isActive: () => editor.isActive('heading', { level: 1 }),
  },
  {
    icon: HashtagIcon,
    title: 'Heading 2',
    action: () => editor.chain().focus().toggleHeading({ level: 2 }).run(),
    isActive: () => editor.isActive('heading', { level: 2 }),
  },
  {
    icon: Bars3BottomLeftIcon,
    title: 'Align Left',
    action: () => editor.chain().focus().setTextAlign('left').run(),
    isActive: () => editor.isActive({ textAlign: 'left' }),
  },
  {
    icon: Bars2Icon,
    title: 'Align Center',
    action: () => editor.chain().focus().setTextAlign('center').run(),
    isActive: () => editor.isActive({ textAlign: 'center' }),
  },
  {
    icon: Bars3BottomRightIcon,
    title: 'Align Right',
    action: () => editor.chain().focus().setTextAlign('right').run(),
    isActive: () => editor.isActive({ textAlign: 'right' }),
  },
];
</script>

<style>
.rich-text-editor {
  @apply border border-gray-200 rounded-lg overflow-hidden bg-white;
}

.ProseMirror {
  @apply min-h-[200px] outline-none;
}

.ProseMirror p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  @apply text-gray-400 float-left h-0 pointer-events-none;
}
</style>
