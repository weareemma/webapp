<template>
    <div class="bb-uploader">
        <label
            :class="{
                highlighted,
                'uploader-dropzone': type == 'dropzone',
                'uploader-button': type == 'button',
            }"
            ref="dropArea"
        >
            <form ref="fileForm">
                <input
                    type="file"
                    class="hidden"
                    ref="fileInput"
                    @input="handleInput"
                    :multiple="multiple"
                    :accept="mimeTypes.join(', ')"
                />
            </form>

            <!-- custom -->
            <template v-if="type == 'custom'">
                <slot
                    :highlighted="highlighted"
                    :progress="form.progress"
                    :uploadCompleted="uploadCompleted"
                    :previewFiles="previewFiles"
                />
            </template>

            <!-- button -->
            <template v-if="type == 'button'">
                <span v-if="form.progress">
                    <CloudUploadIcon class="w-5 h-5 mr-2 animate-pulse" />
                </span>
                <span>
                    {{ buttonText }}
                </span>
            </template>

            <!-- dropzone -->
            <template v-if="type == 'dropzone'">
                <div v-if="previewFiles.length" class="preview-zone">
                    <div
                        v-for="(f, i) in previewFiles"
                        :key="`${i}:${f.name}`"
                        class="preview-file"
                    >
                        <img
                            v-if="f.type.includes('image')"
                            class="image-preview"
                            :src="f.previewURL"
                        />
                        <DocumentIcon v-else class="document-preview" />
                        <div class="file-name">{{ f.name }}</div>
                    </div>
                </div>

                <bb-input-validation :form="form" />

                <progress
                    v-if="form.progress"
                    :value="form.progress.percentage"
                    class="mb-4"
                    max="100"
                >
                    {{ form.progress.percentage }}%
                </progress>

                <div class="drag-text" v-if="!highlighted">
                    <div class="text-center">{{ dragText }}</div>
                    <div
                        v-if="readableMimes != '*'"
                        class="text-center font-bold text-sm uppercase mt-4"
                    >
                        {{ readableMimes }}
                    </div>
                </div>

                <div class="drop-text" v-if="highlighted">
                    <CloudUploadIcon class="w-12 animate-pulse mx-auto mb-4" />
                    <div class="text-center">{{ dropText }}</div>
                </div>
            </template>
        </label>
    </div>
</template>

<script>
import { useForm } from "@inertiajs/inertia-vue3";
import {
    computed,
    defineComponent,
    onBeforeUnmount,
    onMounted,
    ref,
} from "vue";
import { CloudUploadIcon, DocumentIcon, TrashIcon } from "@heroicons/vue/solid";
import uniqid from "uniqid";

export default defineComponent({
    components: { CloudUploadIcon, DocumentIcon, TrashIcon },
    props: {
        submitRoute: { type: String, required: true },
        mediaCollection: { type: String, default: null },
        mimeTypes: { type: Array, default: ["*"] },
        maxSize: { type: [String, Number], default: null },
        replace: { type: Boolean, default: false },
        multiple: { type: Boolean, default: false },
        type: { type: String, default: "dropzone" },
        buttonText: {
            type: String,
            default: "Upload",
        },
        dragText: {
            type: String,
            default: "Drop your files here or click to select",
        },
        dropText: {
            type: String,
            default: "Release here to upload your files",
        },
    },
    emits: ["completed"],
    setup(props, { emit }) {
        const uid = uniqid();

        const dropArea = ref(null);
        const fileForm = ref(null);
        const highlighted = ref(false);
        const readableMimes = computed(() =>
            props.mimeTypes.join(", ").replace(/\./g, "")
        );
        const form = useForm({
            mediaCollection: props.mediaCollection,
            files: [],
        });
        const previewFiles = ref(form.files);
        const uploadCompleted = ref(false);

        onMounted(() => {
            ["dragenter", "dragover", "dragleave", "drop"].forEach((e) =>
                dropArea.value.addEventListener(e, preventDefaults, false)
            );
            ["dragenter", "dragover"].forEach((e) =>
                dropArea.value.addEventListener(e, highlight, false)
            );
            ["dragleave", "drop"].forEach((e) =>
                dropArea.value.addEventListener(e, unhighlight, false)
            );
            dropArea.value.addEventListener("drop", handleDrop, false);
        });

        onBeforeUnmount(() => {
            ["dragenter", "dragover", "dragleave", "drop"].forEach((e) =>
                dropArea.value.removeEventListener(e, preventDefaults)
            );
            ["dragenter", "dragover"].forEach((e) =>
                dropArea.value.removeEventListener(e, highlight)
            );
            ["dragleave", "drop"].forEach((e) =>
                dropArea.value.removeEventListener(e, unhighlight)
            );
            dropArea.value.removeEventListener("drop", handleDrop);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight(e) {
            highlighted.value = true;
        }

        function unhighlight(e) {
            highlighted.value = false;
        }

        function handleDrop(e) {
            let dt = e.dataTransfer;
            let files = dt.files;
            submit(files);
        }

        function handleInput(event) {
            submit(event.target.files);
        }

        function submit(files) {
            uploadCompleted.value = false;

            const formFiles = props.multiple
                ? [...form.files, ...files]
                : [...files];
            form.files = formFiles.map((f) => {
                f.previewURL = URL.createObjectURL(f);
                return f;
            });
            form.transform((data) => {
                const additionalData = {};
                if (props.mimeTypes)
                    additionalData.mimeTypes = props.mimeTypes
                        .join(",")
                        .replace(/\./g, "");
                if (props.maxSize) additionalData.maxSize = props.maxSize;
                if (props.replace) additionalData.replace = props.replace;
                return {
                    uid,
                    ...data,
                    ...additionalData,
                };
            }).post(props.submitRoute, {
                preserveScroll: true,
                onSuccess: (response) => {
                    uploadCompleted.value = true;
                    previewFiles.value = form.files;
                    form.reset();
                    fileForm.value.reset();
                    emit("completed", {
                        uid: uid,
                        files: previewFiles.value,
                        media: response.props.flash,
                    });
                },
            });
        }

        return {
            dropArea,
            fileForm,
            highlighted,
            handleInput,
            form,
            uploadCompleted,
            previewFiles,
            readableMimes,
        };
    },
});
</script>
