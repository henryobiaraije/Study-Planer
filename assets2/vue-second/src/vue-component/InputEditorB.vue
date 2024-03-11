<template>
  <div :id="divId" style="height: 50px" v-html="newContent"></div>
</template>

<script lang="ts">

import {defineComponent} from "vue";

declare var tinymce;
declare var wp;

export default defineComponent({
  emits: ['update:modelValue'],
  name: 'AdminEditorB',
  components: {},
  props: {
    media: {
      type: Boolean,
      default: false,
    },
    value: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      divId: '',
      newContent: '',
    }
  },
  setup: (props, ctx) => {
    const url = new URL(window.location.href);
    const searchParams = new URLSearchParams(url.search);
    const status = searchParams.get('status');
    const action = searchParams.get('action');
    const cardGroupId = Number(searchParams.get('card-group'));

    return {}
  },
  created() {
    const original = this.value;
    let content: string = this.value;
    content = content.replace('/(?:\\r\\n|\\r|\\n)/g', '');
    content = content.replace(/>n</g, '><');
    console.log({content, original});
    this.newContent = content;
    this.$emit('update:modelValue', this.newContent);
    this.divId = 'wp-editor-' + Math.random().toString(36).substr(3, 10);
    setTimeout(() => {
      this.initEditor();
    }, 1000);
  },
  methods: {
    beforeUnmount() {
      wp.editor.remove(this.divId);
      console.log('beforeUnmount');
    },
    initEditor() {
      const dis = this;
      wp.editor.initialize(dis.divId, {
        mediaButtons: dis.media,
        tinymce: {
          wpautop: true,
          plugins: 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
          toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',
          toolbar2: 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help',
          init_instance_callback: function (editor) {
            editor.on('Change', function (e) {
              let content = tinymce.get(dis.divId).getContent()
              // content.replaceAll('\\n', '');
              const original = content;
              // let content: string = this.value;
              content = content.replaceAll('\\n', '');
              content = content.replace('/(?:\\r\\n|\\r|\\n)/g', '');
              content = content.replace(/>n</g, '><');
              content = content.replace(/>\n</g, '><');
              // console.log({original, content});
              dis.newContent = content;
              dis.$emit('update:modelValue', dis.newContent);
            });
          }
        },
        quicktags: true,
      });
    }
  }
});


</script>

<style lang="scss" scoped>

</style>