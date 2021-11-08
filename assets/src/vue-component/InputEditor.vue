<template >
  <div :id="divId" style="height: 50px" v-html="newContent" ></div >
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
  @Prop({default: false}) readonly value: string;

  public created() {
    const original      = this.value;
    let content: string = this.value;
    content             = content.replace('/(?:\\r\\n|\\r|\\n)/g', '');
    content             = content.replace(/>n</g, '><');
    // console.log({content, original});
    this.newContent = content;
    this.$emit('input', this.newContent);
    this.divId = 'wp-editor-' + Math.random().toString(36).substr(3, 10);
    setTimeout(() => {
      this.initEditor();
    }, 1000);
  }

  public beforeUnmount() {
    wp.editor.remove(this.divId);
    console.log('beforeUnmount');
  }

  private initEditor() {
    const dis = this;
    wp.editor.initialize(dis.divId, {
      mediaButtons: dis.media,
      tinymce     : {
        wpautop               : true,
        plugins               : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
        toolbar1              : 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',
        toolbar2              : 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help',
        init_instance_callback: function (editor) {
          editor.on('Change', function (e) {
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
        }
      },
      quicktags   : true,
      // setup:function(ed) {
      //   ed.on('change', function(e) {
      //     console.log('the event object ', e);
      //     console.log('the editor object ', ed);
      //     console.log('the content ', ed.getContent());
      //     const content = wp.editor.getContent(tetarea_id);
      //     console.log('the content ', ed.getContent(),content);
      //   });
      // }
    });
  }

}
</script >

<style lang="scss" scoped >

</style >