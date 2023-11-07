<template>
  <label class="switch">
    <input type="checkbox" v-model="modelValue" @input="handleChange">
    <span class="slider" :class="{ 'round': isRound }"></span>
  </label>
</template>

<script>
import {defineComponent, ref} from 'vue';

export default defineComponent({
  props: {
    modelValue: Boolean, // v-model value
    isRound: Boolean,
  },
  setup(props, {emit}) {
    const handleChange = () => {
      emit('update:modelValue', !props.modelValue); // For v-model
      emit('change', props.modelValue); // For the change event
    };

    return {
      handleChange,
    };
  },
});
</script>

<style scoped>
/* Add your provided CSS styles here */
/* ... */
/* The switch - the box around the slider */
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 25px;
}

/* Hide default HTML checkbox */
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 4px;
  bottom: 3px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: var(--sp-color-default);
}

input:focus + .slider {
  box-shadow: 0 0 1px var(--sp-color-default);
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>





