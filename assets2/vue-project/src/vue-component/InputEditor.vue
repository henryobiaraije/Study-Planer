<template>
  <div style="background-color: #fff;padding: 2px;min-width: 20px;">
<!--    <div :id="divId" style="min-height: 30px" v-html="value"></div>-->
    <!--    <div :id="divId" style="min-height: 30px" v-html="value">{{ value }}</div>-->
    <ckeditor :editor="editor" v-model="editorData" :config="editorConfig" :data="value"></ckeditor>
  </div>
</template>

<script lang="ts">
import {defineComponent} from "vue";
import useDecks from "@/composables/useDecks";
import useTagSearch from "@/composables/useTagSearch";
import useBasicCard from "@/composables/useBasicCard";
import {spClientData} from "@/functions";
// import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import InlineEditor from '@ckeditor/ckeditor5-build-inline';

declare var tinymce;
declare var wp;
export default defineComponent({
  name: 'InputEditor',
  emits: ['update:modelValue'],
  components: {},
  props: {
    media: {
      type: Boolean,
      default: false,
    },
    value: {
      type: String,
      required: true,
      default: ''
    },
    withC: {
      type: Boolean,
      default: false,
      required: false,
    }
  },
  data() {
    return {
      // pageTitle: 'All Cards',
      // activeUrl: 'admin.php?page=study-planner-pro-deck-cards',
      // trashUrl: 'admin.php?page=study-planner-pro-deck-cards&status=trash',
      showMain: false,
      divId: '',
      newContent: '',
      editor: InlineEditor,
      editorData: '',
      editorConfig: {
        // The configuration of the editor.
      }
    }
  },
  setup: (props, ctx) => {
    return {}
  },
  computed: {},
  // created() {
  //   // Random wait from 1 to 4 seconds.
  //   const random1 = Math.floor(Math.random() * 4) + 1;
  //   const random2 = Math.floor(Math.random() * 4) + 1;
  //   console.log('created ', {random1, random2})
  //   setTimeout(() => {
  //     const original = this.value;
  //     // console.log('value = ' + this.value, {original})
  //     let content: string = this.value;
  //     content = content.replace('/(?:\\r\\n|\\r|\\n)/g', '');
  //     content = content.replace(/>n</g, '><');
  //     // console.log({content, original});
  //     this.newContent = content;
  //     // this.$emit('input', this.newContent);
  //     this.$emit('update:modelValue', this.newContent);
  //
  //     this.divId = 'wp-editor-' + Math.random().toString(36).substr(3, 10);
  //     console.log('value = ' + this.value, {original, content, divId: this.divId})
  //     setTimeout(() => {
  //       this.initEditor();
  //     },  4000);
  //   },  1000);
  // },
  created(){
    this.editorData = this.value;
  },
  methods: {
    initEditor() {
      const dis = this;
      wp.editor.initialize(dis.divId, {
        mediaButtons: dis.media,
        quicktags: false,
        tinymce: {
          wpautop: true,
          inline: true,
          menubar: false,
          plugins: 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
          toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',
          toolbar2: 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help',
          powerpaste_word_import: 'clean',
          powerpaste_html_import: 'clean',
          init_instance_callback: (editor) => {
            // editor.on('Change', function (e) {
            editor.on('blur', function (e) {
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
            editor.on('focusout', function (e) {
              let content = tinymce.get(dis.divId).getContent()
              // content.replaceAll('\\n', '');
              const original = content;
              // let content: string = this.value;
              content = content.replaceAll('\\n', '');
              content = content.replace('/(?:\\r\\n|\\r|\\n)/g', '');
              content = content.replace(/>n</g, '><');
              content = content.replace(/>\n</g, '><');
              console.log({original, content});
              dis.newContent = content;
              dis.$emit('update:modelValue', dis.newContent);
            });
            editor.on('init', function () {
              // editor.execCommand("fontName",false,"Arial");
              editor.execCommand("fontSize", false, "16");
            });
            // editor.on('blur', function(e) {
            //   alert('blur');
            // });

          },
          // setup                 : function (editor) {
          //   const cs = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
          //   cs.forEach((c) => {
          //     editor.ui.registry.addButton('customInsertButton', {
          //       text    : 'c' + c,
          //       onAction: function (_) {
          //         editor.insertContent(`&nbsp;<strong>{{c${c}:answer}}</strong>&nbsp;`);
          //       }
          //     });
          //   });
          // }
        },
      });
    }
  },
  beforeUnmount() {
    // wp.editor.remove(this.divId);
    // console.log('beforeUnmount');
  },
  watch: {
    editorData: function (val) {
      console.log('editorData', val);
      // this.$emit('input', val);
      this.$emit('update:modelValue', val);
    },
  },
});
</script>

<style lang="scss" scoped>

</style>