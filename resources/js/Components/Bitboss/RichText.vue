<template>
    <div class="bb-rich-text">
        <div class="rich-text-buttons" v-if="editor">
            <!-- bold - italic - underline -->
            <div class="buttons-group">
                <button
                    title="Bold"
                    @click="editor.chain().focus().toggleBold().run()"
                    :class="{ 'is-active': editor.isActive('bold') }"
                >
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 16 16"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <text
                            x="50%"
                            y="50%"
                            text-anchor="middle"
                            dy=".5ex"
                            font-family="Segoe UI, Frutiger, Dejavu Sans, Helvetica Neue, Arial, sans-serif"
                            font-size="15"
                            font-weight="bold"
                        >
                            B
                        </text>
                    </svg>
                </button>
                <button
                    title="Italic"
                    @click="editor.chain().focus().toggleItalic().run()"
                    :class="{ 'is-active': editor.isActive('italic') }"
                >
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 16 16"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <text
                            x="50%"
                            y="50%"
                            text-anchor="middle"
                            dy=".5ex"
                            font-family="Segoe UI, Frutiger, Dejavu Sans, Helvetica Neue, Arial, sans-serif"
                            font-weight="bold"
                            font-size="15"
                            font-style="italic"
                        >
                            i
                        </text>
                    </svg>
                </button>
                <button
                    title="Underline"
                    @click="editor.chain().focus().toggleUnderline().run()"
                    :class="{ 'is-active': editor.isActive('underline') }"
                >
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 16 16"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <text
                            x="50%"
                            y="50%"
                            text-anchor="middle"
                            dy=".5ex"
                            font-family="Segoe UI, Frutiger, Dejavu Sans, Helvetica Neue, Arial, sans-serif"
                            font-weight="bold"
                            font-size="15"
                            text-decoration="underline"
                        >
                            U
                        </text>
                    </svg>
                </button>
            </div>

            <!-- copy - cut - paste -->
            <!-- <div class="buttons-group">
                <button title="Copy" @click="copy()">
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 16 16"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M6.404 5.002a.5.5 0 0 0-.406.5v10a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5V8.596a.492.492 0 0 0 0-.094.662.662 0 0 0 0-.063v-.063l-.031-.063v-.031a.557.557 0 0 0-.094-.094l-.031-.031-2.875-2.844a.498.498 0 0 0-.125-.156.5.5 0 0 0-.344-.156h-5a.59.59 0 0 0-.094.001c-.239.046.031-.003 0 0zm.594 1h4v2.5a.5.5 0 0 0 .5.5h2.5v6h-7v-9zm5 .687l1.313 1.313h-1.313V6.689zM1.406.002a.517.517 0 0 0-.406.5v10c0 .262.238.5.5.5H7V6l3-.063V3.596a.492.492 0 0 0 0-.094.331.331 0 0 0 0-.063v-.063c-.009-.021-.02-.041-.031-.062v-.031a.597.597 0 0 0-.094-.094l-.031-.031L6.969.314a.484.484 0 0 0-.125-.156A.506.506 0 0 0 6.5.002h-5a.492.492 0 0 0-.094 0c-.229.044.032-.003 0 0zm.594 1h4v2.5c0 .262.238.5.5.5H9v1.029L7 5 6 6v4l-4 .002v-9zm5 .687l1.313 1.313H7V1.689z"
                        />
                    </svg>
                </button>
                <button title="Cut" @click="cut()">
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 16 16"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M3 .5c0 2.936 3.774 7.73 3.938 7.938l-1.813 2.844A2.46 2.46 0 0 0 4 11c-1.375 0-2.5 1.125-2.5 2.5S2.625 16 4 16s2.5-1.125 2.5-2.5c0-.444-.138-.856-.344-1.22L8 9.845l1.844 2.438A2.473 2.473 0 0 0 9.5 13.5c0 1.375 1.125 2.5 2.5 2.5s2.5-1.125 2.5-2.5S13.375 11 12 11a2.46 2.46 0 0 0-1.125.28L9.062 8.439C9.226 8.232 13 3.437 13 .5h-1L8 6.78 4 .5H3zM4 12c.834 0 1.5.666 1.5 1.5S4.834 15 4 15s-1.5-.666-1.5-1.5S3.166 12 4 12zm8 0c.834 0 1.5.666 1.5 1.5S12.834 15 12 15s-1.5-.666-1.5-1.5.666-1.5 1.5-1.5z"
                        />
                    </svg>
                </button>
                <button title="Paste" @click="paste()">
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 16 16"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M4.406 0A.5.5 0 0 0 4 .5V1H1.5a.5.5 0 0 0-.5.5v10a.5.5 0 0 0 .5.5H6v2.5a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5V7.594a.492.492 0 0 0 0-.094.436.436 0 0 0 0-.125.916.916 0 0 0-.031-.063v-.031a.749.749 0 0 0-.063-.063.749.749 0 0 0-.063-.063l-2.875-2.844a.498.498 0 0 0-.125-.156A.498.498 0 0 0 11.5 4H10V1.5a.5.5 0 0 0-.5-.5H7V.5a.5.5 0 0 0-.5-.5h-2a.492.492 0 0 0-.094 0c-.239.045.032-.003 0 0zM2 2h1v.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5V2h1v2H6.5a.64.64 0 0 0-.062 0 .493.493 0 0 0-.094.031.474.474 0 0 0-.125.063l-.031.031-.031.031a.916.916 0 0 0-.063.031.47.47 0 0 0-.031.094l-.031.031A.506.506 0 0 0 6 4.5V11H2V2zm5 3h4v2.5a.5.5 0 0 0 .5.5H14v6H7v-2.406a.492.492 0 0 0 0-.094V5zm5 .688L13.313 7H12V5.688zM4.406 0A.5.5 0 0 0 4 .5V1H1.5a.5.5 0 0 0-.5.5v10a.5.5 0 0 0 .5.5h5a.5.5 0 0 0 .5-.5V5h2.5a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5H7V.5a.5.5 0 0 0-.5-.5h-2a.492.492 0 0 0-.094 0c-.239.045.032-.003 0 0zM2 2h1v.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5V2h1v2H6.5a.5.5 0 0 0-.5.5V11H2V2zm4.406 2A.5.5 0 0 0 6 4.5v10a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5V7.594a.492.492 0 0 0 0-.094.331.331 0 0 0 0-.063v-.063a.916.916 0 0 0-.031-.063V7.28a.523.523 0 0 0-.094-.094l-.031-.031-2.875-2.844a.498.498 0 0 0-.125-.156A.503.503 0 0 0 11.5 4h-5a.492.492 0 0 0-.094 0c-.239.045.032-.003 0 0zM7 5h4v2.5a.5.5 0 0 0 .5.5H14v6H7V5zm5 .688L13.313 7H12V5.688zM8 12h5v1H8v-1zm0-2h5v1H8v-1zm0-2h5v1H8V8zm0-2h3v1H8V6z"
                        />
                    </svg>
                </button>
            </div> -->

            <button title="Test" @click="test(editor.chain().focus())">
                test
            </button>

            <!-- <button
                    @click="editor.chain().focus().toggleStrike().run()"
                    :class="{ 'is-active': editor.isActive('strike') }"
                >
                    strike
                </button>
                <button
                    @click="editor.chain().focus().toggleCode().run()"
                    :class="{ 'is-active': editor.isActive('code') }"
                >
                    code
                </button>
                <button @click="editor.chain().focus().unsetAllMarks().run()">
                    clear marks
                </button>
                <button @click="editor.chain().focus().clearNodes().run()">
                    clear nodes
                </button>
                <button
                    @click="editor.chain().focus().setParagraph().run()"
                    :class="{ 'is-active': editor.isActive('paragraph') }"
                >
                    paragraph
                </button>
                <button
                    @click="
                        editor.chain().focus().toggleHeading({ level: 1 }).run()
                    "
                    :class="{
                        'is-active': editor.isActive('heading', { level: 1 }),
                    }"
                >
                    h1
                </button>
                <button
                    @click="
                        editor.chain().focus().toggleHeading({ level: 2 }).run()
                    "
                    :class="{
                        'is-active': editor.isActive('heading', { level: 2 }),
                    }"
                >
                    h2
                </button>
                <button
                    @click="
                        editor.chain().focus().toggleHeading({ level: 3 }).run()
                    "
                    :class="{
                        'is-active': editor.isActive('heading', { level: 3 }),
                    }"
                >
                    h3
                </button>
                <button
                    @click="
                        editor.chain().focus().toggleHeading({ level: 4 }).run()
                    "
                    :class="{
                        'is-active': editor.isActive('heading', { level: 4 }),
                    }"
                >
                    h4
                </button>
                <button
                    @click="
                        editor.chain().focus().toggleHeading({ level: 5 }).run()
                    "
                    :class="{
                        'is-active': editor.isActive('heading', { level: 5 }),
                    }"
                >
                    h5
                </button>
                <button
                    @click="
                        editor.chain().focus().toggleHeading({ level: 6 }).run()
                    "
                    :class="{
                        'is-active': editor.isActive('heading', { level: 6 }),
                    }"
                >
                    h6
                </button>
                <button
                    @click="editor.chain().focus().toggleBulletList().run()"
                    :class="{ 'is-active': editor.isActive('bulletList') }"
                >
                    bullet list
                </button>
                <button
                    @click="editor.chain().focus().toggleOrderedList().run()"
                    :class="{ 'is-active': editor.isActive('orderedList') }"
                >
                    ordered list
                </button>
                <button
                    @click="editor.chain().focus().toggleCodeBlock().run()"
                    :class="{ 'is-active': editor.isActive('codeBlock') }"
                >
                    code block
                </button>
                <button
                    @click="editor.chain().focus().toggleBlockquote().run()"
                    :class="{ 'is-active': editor.isActive('blockquote') }"
                >
                    blockquote
                </button>
                <button
                    @click="editor.chain().focus().setHorizontalRule().run()"
                >
                    horizontal rule
                </button>
                <button @click="editor.chain().focus().setHardBreak().run()">
                    hard break
                </button>
                <button @click="editor.chain().focus().undo().run()">
                    undo
                </button>
                <button @click="editor.chain().focus().redo().run()">
                    redo
                </button> -->
        </div>
        <div class="rich-text-content">
            <editor-content :editor="editor" />
        </div>
    </div>
</template>

<script>
import { useEditor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import Underline from "@tiptap/extension-underline";
import Mention from "@tiptap/extension-mention";

export default {
    components: {
        EditorContent,
    },

    props: {
        modelValue: {
            type: String,
            default: "",
        },
        mentionSuggestion: {
            type: Object,
            default: {},
        },
    },
    emits: ["update:modelValue"],

    setup(props, { emit }) {
        const editor = useEditor({
            content: props.modelValue,
            extensions: [
                StarterKit,
                Underline,
                Mention.configure({
                    HTMLAttributes: {
                        class: "mention",
                    },
                    renderLabel({ options, node }) {
                        return `${options.suggestion.char}${
                            node.attrs.label ?? node.attrs.id
                        }`;
                    },
                    suggestion: props.mentionSuggestion,
                }),
            ],
            onUpdate: () => emit("update:modelValue", editor.value.getHTML()),
        });

        function test(focus) {
            console.log(focus);
        }

        function copy() {
            document.execCommand("copy");
        }

        function cut() {
            document.execCommand("cut");
        }

        function paste() {
            document.execCommand("paste");
        }

        return { editor, copy, cut, paste, test };
    },
};
</script>
