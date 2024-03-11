<template>
  <div style="background-color: #fff;padding: 2px;min-width: 20px; " class="">
    <!--    <div :id="divId" style="min-height: 30px" v-html="value"></div>-->
    <!--    <div :id="divId" style="min-height: 30px" v-html="value">{{ value }}</div>-->
    <!--    <ckeditor :editor="editor" v-model="editorData" :config="editorConfig" :data="value"></ckeditor>-->
    <!--    <QuillEditor theme="snow" :toolbar="quilToolbar" ref="myQuillEditor"-->
    <!--                 @editorChange="updateContent"-->
    <!--    ></QuillEditor>-->
    <div ref="myQuillEditor"/>
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
import {Delta, Quill, QuillEditor} from "@vueup/vue-quill";

// import { Essentials } from '@ckeditor/ckeditor5-essentials';
// import { Bold, Italic } from '@ckeditor/ckeditor5-basic-styles';
// import { Link } from '@ckeditor/ckeditor5-link';
// import { Paragraph } from '@ckeditor/ckeditor5-paragraph';

declare var tinymce;
declare var wp;
export default defineComponent({
  name: 'InputEditor',
  emits: ['update:modelValue'],
  components: {QuillEditor},
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
      showToolbar:false,
      showMain: false,
      divId: '',
      newContent: '',
      editor: InlineEditor,
      editorData: '',
      editorConfig: {},
      quilToolbar: [
        ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
        ['blockquote', 'code-block'],

        [{'header': 1}, {'header': 2}],               // custom button values
        [{'list': 'ordered'}, {'list': 'bullet'}],
        [{'script': 'sub'}, {'script': 'super'}],      // superscript/subscript
        [{'indent': '-1'}, {'indent': '+1'}],          // outdent/indent
        [{'direction': 'rtl'}],                         // text direction

        [{'size': ['small', false, 'large', 'huge']}],  // custom dropdown
        [{'header': [1, 2, 3, 4, 5, 6, false]}],

        [{'color': []}, {'background': [
            '#000000', '#e60000', '#ff9900', '#ffff00', '#008a00', '#0066cc', '#9933ff', '#ffffff',
            '#facccc', '#ffebcc', '#ffffcc', '#cce8cc', '#cce0f5', '#ebd6ff', '#bbbbbb', '#f06666',
            '#ffc266', '#ffff66', '#66b966', '#66a3e0', '#c285ff', '#888888', '#a10000', '#b26b00',
            '#b2b200', '#006100', '#0047b2', '#6b24b2', '#444444', '#5c0000', '#663d00', '#666600',
            '#003700', '#002966', '#3d1466', 'custom-color'
          ]}],          // dropdown with defaults from theme
        [{'font': []}],
        [{'align': []}],

        ['clean'],                                         // remove formatting button

        ['link', 'image', 'video']                         // link and image, video
      ],
      quilEditor: null as Quill | null,
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
  created() {
    this.editorData = this.value;
    // Set quil content on create.
    // this.$refs.myQuillEditor.quill.setContents(this.value);

  },
  mounted() {
    this.quilEditor = new Quill(this.$refs.myQuillEditor, {
      modules: {
        toolbar: this.quilToolbar
      },
      //theme: 'bubble',
      theme: 'snow',
      formats: ['bold', 'underline', 'header', 'italic', 'strike', 'blockquote', 'code-block', 'list', 'bullet', 'script', 'indent', 'direction', 'size', 'color', 'background', 'font', 'align', 'clean', 'link', 'image', 'video'],
      placeholder: "Type something in here!"
    });

    if (null !== this.quilEditor) {
      this.quilEditor.root.innerHTML = this.value;

      this.quilEditor.on('text-change', () => this.updateQuil());

      // Show toolbar on focus.
      // this.quilEditor.on('focus', () => {
      //   this.showToolbar = true;});
    }
  },
  methods: {
    updateContent(name: 'text-change', delta: Delta, oldContents: Delta, source) {
      // this.editorData = content;
      console.log({name, delta, oldContents, source});
      this.$emit('update:modelValue', this.editorData);
    },
    updateQuil() {
      // this.$emit('input', this.editor.getText() ? this.editor.root.innerHTML : '');
      const content = this.quilEditor.getText() ? this.quilEditor.root.innerHTML : '';
      // get html content.
      // const content = this.quilEditor.root.innerHTML;
      console.log({content});
      this.$emit('update:modelValue', content);
    },
    initQuilEditor() {

    },
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