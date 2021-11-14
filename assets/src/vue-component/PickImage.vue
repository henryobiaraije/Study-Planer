<template >
  <div class="bg-image" >
    <img :src="imageUrl" alt=""
         class="bg-gray-300"
         @click="selectImage"
         style="width: 150px; height: 150px;" >
    <span v-if="bgImage.ajaxLoad.value.sending" class="loading" >
      <i class="fa fa-spin fa-spinner" >

      </i >
    </span >
  </div >
</template >

<script lang="ts" >
import Vue from "vue";
import {Prop, Watch} from "vue-property-decorator";
import Component from "vue-class-component";
import useBgImage from "../composables/useBgImage";
import watch from "@vue/composition-api";


declare var tinymce;
declare var wp;
@Component({
  setup() {
    return {
      bgImage: useBgImage(),
    }
  }
})
export default class PickImage extends Vue {

  // bgImage = setup(() => useBgImage());
  // counter = setup(() => useCounter());

  /********** Props **********/
  @Prop({default: 0, required: true}) readonly value: number; // the image id v-model
  @Prop({default: 0, required: true}) readonly defaultImage: number;
  @Prop({default: 'Use Image'}) readonly buttonText: string;
  @Prop({default: 'Pick Image'}) readonly headerText: string;

  public get imageUrl() {
    //@ts-ignore
    return this.bgImage.loadedImageUrl.value;
  }

  public created() {
    let imageId = this.value;
    if (imageId < 1) {
      imageId = this.defaultImage;
    }
    this.initImage(imageId);
  }

  private initImage(imageId: number) {
    //@ts-ignore
    this.bgImage.xhrLoadImage(imageId);
  }

  private selectImage() {
    //@ts-ignore
    this.bgImage.pickImage().then((res) => {
      this.$emit('input', res.id);
      this.initImage(res.id);
      // console.log('resol', res);
    });
  }

  @Watch('value')
  idChange(to, from) {
    console.log('watch change', {to, from});
  }

}
</script >

<style lang="scss" scoped >
$width: 150px;
$height: 150px;
.bg-image {
  width: $width;
  height: $height;
  display: -moz-inline-block;
  display: inline-block;
  position: relative;
  cursor: pointer;
  &:hover {
    opacity: 0.8;
  }
  img {
    width: $width;
    height: $height;
  }
  span.loading {
    position: absolute;
    width: 100%;
    top: 40%;
    text-align: center;
    i.fa {
      font-size: 30px;
      color: #fff;
      background: #0000006e;
      border-radius: 50%;
    }
  }
}
</style >