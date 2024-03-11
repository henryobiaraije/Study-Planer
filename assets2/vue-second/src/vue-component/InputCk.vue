<!--<template >-->
<!--  &lt;!&ndash;  <div :id="divId" style="" v-html="newContent" ></div >&ndash;&gt;-->
<!--&lt;!&ndash;  <ckeditor :editor="editor" @input="inputTyped" v-model="newContent" :config="editorConfig" ></ckeditor >&ndash;&gt;-->
<!--  <ckeditor :editor="editor" @input="inputTyped" v-model="newContent" :config="editorConfig" ></ckeditor >-->
<!--</template >-->

<!--<script lang="ts" >-->
<!--import Vue from "vue";-->
<!--import {Prop} from "vue-property-decorator";-->
<!--import Component from "vue-class-component";-->
<!--import InlineEditor from '@ckeditor/ckeditor5-build-inline';-->
<!--import CKEditor from '@ckeditor/ckeditor5-vue2';-->

<!--declare var tinymce;-->
<!--declare var wp;-->
<!--@Component({-->
<!--  name      : 'InputCk',-->
<!--  // components: {-->
<!--  //   ckeditor: CKEditor.component-->
<!--  // }-->
<!--})-->
<!--export default class InputCk extends Vue {-->

<!--  public divId: string      = '';-->
<!--  public newContent: string = '';-->
<!--  public editorConfig       = {-->
<!--    // The configuration of the editor.-->
<!--  };-->
<!--  public editor             = InlineEditor;-->
<!--  // public newValue: string   = '';-->

<!--  /********** Props **********/-->
<!--         // @Prop({default: []}) readonly selectedIds: Array<number>;-->
<!--  @Prop({default: false}) readonly media: boolean;-->
<!--  @Prop({default: ''}) readonly value: string;-->

<!--  public created() {-->
<!--    // wp.editor.remove(this.divId);-->
<!--    // this.divId = 'wp-editor-' + Math.random().toString(36).substr(3, 10)-->
<!--    //              + Math.random().toString(36).substr(3, 10)-->
<!--    //              + new Date().getTime() + new Date().getMilliseconds();-->
<!--    //-->
<!--    // const elemId = '#' + this.divId;-->
<!--    // setTimeout(() => {-->
<!--    //   const elem = document.querySelector(elemId);-->
<!--    //   InlineEditor-->
<!--    //       .create(elem)-->
<!--    //       .catch(error => {-->
<!--    //         console.error('ckeditor error', {elem, elemId, error});-->
<!--    //       });-->
<!--    // }, 400);-->
<!--  }-->

<!--  public inputTyped() {-->
<!--    console.log('Typed in now')-->
<!--    // this.$emit('input', this.newContent);-->
<!--  }-->

<!--  public beforeUnmount() {-->

<!--    // console.log('beforeUnmount');-->
<!--  }-->

<!--  private initEditor() {-->
<!--    const dis = this;-->
<!--    wp.editor.initialize(dis.divId, {-->
<!--      mediaButtons: dis.media,-->
<!--      quicktags   : false,-->
<!--      tinymce     : {-->
<!--        wpautop: true,-->
<!--        // inline                : true,-->
<!--        menubar               : false,-->
<!--        plugins               : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',-->
<!--        toolbar1              : 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',-->
<!--        toolbar2              : 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help',-->
<!--        powerpaste_word_import: 'clean',-->
<!--        powerpaste_html_import: 'clean',-->
<!--        init_instance_callback: function (editor) {-->
<!--          editor.on('Change', function (e) {-->
<!--            let content    = tinymce.get(dis.divId).getContent()-->
<!--            // content.replaceAll('\\n', '');-->
<!--            const original = content;-->
<!--            // let content: string = this.value;-->
<!--            content        = content.replaceAll('\\n', '');-->
<!--            content        = content.replace('/(?:\\r\\n|\\r|\\n)/g', '');-->
<!--            content        = content.replace(/>n</g, '><');-->
<!--            content        = content.replace(/>\n</g, '><');-->
<!--            // console.log({original, content});-->
<!--            dis.newContent = content;-->
<!--            dis.$emit('input', dis.newContent);-->
<!--          });-->
<!--        }-->
<!--      },-->
<!--    });-->
<!--  }-->

<!--}-->
<!--</script >-->

<!--<style lang="scss" scoped >-->

<!--</style >-->