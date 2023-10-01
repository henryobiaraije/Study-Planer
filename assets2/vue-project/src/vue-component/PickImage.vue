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

<script setup lang="ts">

const bgImage = useBgImage();
// @Component({
//   setup() {
//     return {
//       bgImage: useBgImage(),
//     }
//   }
// })

// bgImage = setup(() => useBgImage());
// counter = setup(() => useCounter());

const props = defineProps({
  value: {type: Number, default: 0, required: true},
  defaultImage: {type: Number, default: 0, required: true,},
  buttonText: {type: String, default: 'Use Image'},
  headerText: {type: String, default: 'Pick Image',},
})
import useBgImage from "@/composables/useBgImage";
import {computed, watch} from "vue";

const emit = defineEmits<{
  (e: 'input', id: number): void
}>();


const imageUrl = computed(() => bgImage.loadedImageUrl.value);

const imageId = computed(() => {
  let imageId = props.value;
  if (imageId < 1) {
    imageId = props.defaultImage;
  }
  return imageId;
});

function initImage(imageId: number) {
  //@ts-ignore
  bgImage.xhrLoadImage(imageId);
}

function selectImage() {
  //@ts-ignore
  bgImage.pickImage().then((res) => {
    emit('input', res.id);
    initImage(res.id);
    // console.log('resol', res);
  });
}


watch(props.value, (to, from) => {
  console.log('watch change', {to, from});
});

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