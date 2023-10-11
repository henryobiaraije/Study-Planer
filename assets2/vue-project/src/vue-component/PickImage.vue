<template>
  <div class="bg-image">
    <img :src="imageUrl" alt=""
         class="bg-gray-300"
         @click="selectImage"
         style="width: 150px; height: 150px;">
    <span v-if="bgImage.ajaxLoad.value.sending" class="loading">
      <i class="fa fa-spin fa-spinner">

      </i>
    </span>
  </div>
</template>

<script lang="ts">
import {defineComponent} from "vue";
import useBgImage from "@/composables/useBgImage";

export default defineComponent({
  name: 'PickImage',
  emits: ['update:modelValue'],
  props: {
    value: {type: Number, default: 0, required: true},
    defaultImage: {type: Number, default: 0, required: true,},
    buttonText: {type: String, default: 'Use Image'},
    headerText: {type: String, default: 'Pick Image',},
  },
  setup: (props, ctx) => {
    return {
      bgImage: useBgImage(),
    }
  },
  computed: {
    imageUrl() {
      return this.bgImage.loadedImageUrl.value;
    },
    imageId() {
      let imageId = this.value;
      if (imageId < 1) {
        imageId = this.defaultImage;
      }
      return imageId;
    }
  },
  methods: {
    initImage(imageId: number) {
      //@ts-ignore
      this.bgImage.xhrLoadImage(imageId);
    },
    selectImage() {
      //@ts-ignore
      this.bgImage.pickImage().then((res) => {
        this.$emit('update:modelValue', res.id);
        this.initImage(res.id);
        // console.log('resol', res);
      });
    }
  },
  created() {
    let imageId = this.value;
    if (imageId < 1) {
      imageId = this.defaultImage;
    }
    this.initImage(imageId);
  }
});

// watch(props.value, (to, from) => {
//   console.log('watch change', {to, from});
// });

</script>

<style lang="scss" scoped>
$width: 150px;
$height: 150px;
.bg-image {
  width: $width;
  height: $height;
  display: -moz-inline-block;
  display: inline-block;
  position: relative;
  cursor: pointer;
  border-radius: 5px;
  overflow: hidden;

  &:hover {
    opacity: 0.8;
  }

  img {
    width: $width;
    height: $height;
    border: 0;
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
</style>