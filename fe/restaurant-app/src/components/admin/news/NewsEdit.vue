<template>
  <form @submit.prevent="handleSubmit">
    <div class="row">
      <div class="col-md-12 mb-3">
        <label for="title" class="form-label">Tiêu đề</label>
        <input type="text" class="form-control" v-model="form.title" required />
      </div>
    </div>
    <div class="row">
      <div class="col-md-12 mb-3">
        <label for="summary" class="form-label">Tóm tắt</label>
        <textarea class="form-control" v-model="form.summary" rows="5"></textarea>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12 mb-3">
        <label for="content" class="form-label">Nội dung</label>
        <div class="quill-editor-container">
          <QuillEditor
            v-model:content="form.content"
            contentType="html"
            :options="editorOptions"
            class="quill-editor"
          />
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12 mb-3">
        <label for="thumbnail" class="form-label">Thumbnail</label>
        <div>
          <!-- Hiển thị ảnh hiện tại nếu có URL -->
          <img v-if="form.thumbnail && typeof form.thumbnail === 'string'" :src="form.thumbnail" alt="Current Thumbnail" style="max-width: 200px; max-height: 200px; margin-bottom: 10px;" />
          <!-- Input file để chọn file mới -->
          <input type="file" class="form-control form-control-lg" ref="thumbnailInput" accept="image/*" @change="handleFileChange" />
          <!-- Hiển thị tên file đã chọn -->
          <p v-if="selectedFileName" class="mt-2">File đã chọn: {{ selectedFileName }}</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12 mb-3">
        <label for="category_id" class="form-label">Danh mục</label>
        <select v-model="form.category_id" class="form-control">
          <option value="">Chọn danh mục</option>
          <option v-for="category in categories" :key="category.category_id" :value="category.category_id">
            {{ category.name }}
          </option>
        </select>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12 mb-3">
        <label for="status" class="form-label">Trạng thái</label>
        <select v-model="form.status" class="form-control">
          <option value="1">Hoạt động</option>
          <option value="0">Không hoạt động</option>
        </select>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12 mb-3">
        <label for="tags" class="form-label">Tags</label>
        <input type="text" class="form-control" v-model="form.tags" />
      </div>
    </div>
    <div class="row">
      <div class="col-md-12 mb-3">
        <label class="form-check-label">
          <input type="checkbox" v-model="form.is_pinned" class="form-check-input" />
          Ghim bài viết
        </label>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" @click="$emit('close')">Đóng</button>
      <button type="submit" class="btn btn-primary">Cập nhật</button>
    </div>
  </form>
</template>

<script setup>
import { ref, watch } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';

const props = defineProps({
  news: Object,
  categories: Array,
});

const emit = defineEmits(['save', 'close']);

const form = ref({ ...props.news });
const thumbnailInput = ref(null);
const selectedFileName = ref('');

const editorOptions = {
  theme: 'snow',
  modules: {
    toolbar: [
      ['bold', 'italic', 'underline', 'strike'],
      ['blockquote', 'code-block'],
      [{ 'header': 1 }, { 'header': 2 }],
      [{ 'list': 'ordered' }, { 'list': 'bullet' }],
      [{ 'script': 'sub' }, { 'script': 'super' }],
      [{ 'indent': '-1' }, { 'indent': '+1' }],
      [{ 'direction': 'rtl' }],
      [{ 'size': ['small', false, 'large', 'huge'] }],
      [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
      [{ 'color': [] }, { 'background': [] }],
      [{ 'font': [] }],
      [{ 'align': [] }],
      ['clean'],
      ['link', 'image', 'video'],
    ],
  },
};

const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.value.thumbnail = file; // Store the file object
    selectedFileName.value = file.name; // Update the file name to display
  } else {
    selectedFileName.value = ''; // Clear file name if no file is selected
  }
};

watch(
  () => props.news,
  (newNews) => {
    form.value = { ...newNews };
    if (typeof form.value.thumbnail === 'string') {
      selectedFileName.value = ''; // Clear file name if thumbnail is a URL
    }
  },
  { deep: true }
);

const handleSubmit = () => {
  emit('save', { ...form.value });
};
</script>

<style scoped>
.quill-editor-container {
  position: relative;
  height: 250px; /* Fixed height as requested */
  overflow-y: auto;
  margin-bottom: 1rem; /* Ensure spacing below the editor */
}

.quill-editor {
  height: 100%;
  box-sizing: border-box;
  border: 1px solid #ced4da;
  border-radius: 0.25rem;
}

.quill-editor :deep(.ql-container) {
  height: calc(100% - 40px); /* Subtract toolbar height (approx. 40px) */
  overflow-y: auto;
}

.quill-editor :deep(.ql-editor) {
  height: 100%;
  overflow-y: auto;
}

/* Ensure the toolbar doesn't cause overflow */
.quill-editor :deep(.ql-toolbar) {
  position: sticky;
  top: 0;
  z-index: 1;
  background: #fff;
}

/* Ensure consistent input height */
.form-control-lg {
  min-height: 48px; /* Increase height for better visibility */
  font-size: 1rem;
  line-height: 1.5;
  padding: 0.5rem 1rem;
}

.form-control-lg::placeholder {
  font-size: 1rem;
}

/* Adjust select and textarea for consistency */
select.form-control-lg,
textarea.form-control-lg {
  min-height: 48px; /* Match input height for select */
  font-size: 1rem;
  padding: 0.5rem 1rem;
}

textarea.form-control-lg {
  min-height: 120px; /* Ensure textarea has enough height */
}
</style>