<template >
  <!--  <div :id="divId" style="height: 50px" v-html="newContent" ></div >-->
  <div>
    <div :id="divId" style="min-height: 30px" v-html="value" >{{ value }}</div >
  </div>
  <!--  <div >-->
  <!--    Some content {{ media }} {{ value.length }} {{ value }}-->
  <!--  </div >-->
</template >

<script lang="ts" >
import Vue from "vue";
import {Prop} from "vue-property-decorator";
import Component from "vue-class-component";

declare var tinymce;
declare var wp;
@Component
export default class InputEditor extends Vue {

  public divId: string      = '';
  public newContent: string = '';
  // public newValue: string   = '';

  /********** Props **********/
         // @Prop({default: []}) readonly selectedIds: Array<number>;
  @Prop({default: false}) readonly media: boolean;
  @Prop({default: '', required: true}) readonly value: string;
  @Prop({default: false, required: false}) readonly withC: boolean;

  public created2() {
    setTimeout(() => {
      const original = this.value;
      console.log('value = ' + this.value.length, {original})
    }, 2000);
  }

  public created() {
    setTimeout(() => {
      const original = this.value;
      console.log('value = ' + this.value, {original})
      let content: string = this.value;
      content             = content.replace('/(?:\\r\\n|\\r|\\n)/g', '');
      content             = content.replace(/>n</g, '><');
      // console.log({content, original});
      this.newContent     = content;
      this.$emit('input', this.newContent);
      this.divId = 'wp-editor-' + Math.random().toString(36).substr(3, 10);
      setTimeout(() => {
        this.initEditor();
      }, 1000);
    }, 1000);

  }

  public beforeUnmount() {
    // wp.editor.remove(this.divId);
    // console.log('beforeUnmount');
  }

  private initEditor() {
    const dis = this;
    wp.editor.initialize(dis.divId, {
      mediaButtons: dis.media,
      quicktags   : false,
      tinymce     : {
        wpautop               : true,
        inline                : true,
        menubar               : false,
        plugins               : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
        toolbar1              : 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',
        toolbar2              : 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help',
        powerpaste_word_import: 'clean',
        powerpaste_html_import: 'clean',
        init_instance_callback: (editor) => {
          // editor.on('Change', function (e) {
          editor.on('blur', function (e) {
            let content    = tinymce.get(dis.divId).getContent()
            // content.replaceAll('\\n', '');
            const original = content;
            // let content: string = this.value;
            content        = content.replaceAll('\\n', '');
            content        = content.replace('/(?:\\r\\n|\\r|\\n)/g', '');
            content        = content.replace(/>n</g, '><');
            content        = content.replace(/>\n</g, '><');
            // console.log({original, content});
            dis.newContent = content;
            dis.$emit('input', dis.newContent);
          });
          editor.on('focusout', function (e) {
            let content    = tinymce.get(dis.divId).getContent()
            // content.replaceAll('\\n', '');
            const original = content;
            // let content: string = this.value;
            content        = content.replaceAll('\\n', '');
            content        = content.replace('/(?:\\r\\n|\\r|\\n)/g', '');
            content        = content.replace(/>n</g, '><');
            content        = content.replace(/>\n</g, '><');
            // console.log({original, content});
            dis.newContent = content;
            dis.$emit('input', dis.newContent);
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

}
</script >

<style lang="scss" scoped >

</style >