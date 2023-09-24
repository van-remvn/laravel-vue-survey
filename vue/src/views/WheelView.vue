<template>
    <div class="text-gray-600 text-center py-16">
      <!-- type: canvas -->
      <FortuneWheel
        style="width: 500px; max-width: 100%;"
        :verify="canvasVerify"
        :canvas="canvasOptions"
        :prizes="prizesCanvas"
        @rotateStart="onCanvasRotateStart"
        @rotateEnd="onRotateEnd"
      />

      <div style="margin-top: 20px;">
        <!-- <button type="button" @click="wheelEl.value.startRotate()" style="background-color: blue; color: white; padding: 15px; border-radius: 5px; font-size: 20px;">Start</button> -->
        <p style="font-size: 14px; color: red;">{{ updatePointSuccess }}</p>
        <h1 style="font-size: 20px;">Current Points: <span style="font-weight: bold;">{{ currentPoint }}</span></h1>
      </div>
    </div>
  </template>
  
  <script setup>

  import { ref, computed, onMounted, watchEffect } from 'vue'
  import FortuneWheel from 'vue-fortune-wheel'
  import 'vue-fortune-wheel/style.css'

  import { useStore } from "vuex";
  import axiosClient from "../axios";

  const store = useStore();
  
  const prizeId = ref(0)
  
  const wheelEl = ref()
  const canvasVerify = ref(false) // Whether the turntable in canvas mode is enabled for verification
  const verifyDuration = 1
  const canvasOptions = {
    btnWidth: 140,
    borderColor: '#584b43',
    borderWidth: 6,
    lineHeight: 30,
    fontSize: 25,
  }
  
  const prizesCanvas = [
    {
      id: 1, //* The unique id of each prize, an integer greater than 0
      name: '0', // Prize name, display value when type is canvas (this parameter is not needed when type is image)
      value: 0, //* Prize value, return value after spinning
      bgColor: '#FF0000', // Background color (no need for this parameter when type is image)
      color: '#ffffff', // Font color (this parameter is not required when type is image)
      fontSize: 100,
      probability: 14 //* Probability, up to 4 decimal places (the sum of the probabilities of all prizes
    },
    {
      id: 2,
      name: '-100',
      value: -100,
      bgColor: '#00FF00',
      color: '#ffffff',
      probability: 14
    },
    {
      id: 3,
      name: '+200',
      value: 200,
      bgColor: '#0000FF',
      color: '#ffffff',
      probability: 14
    },
    {
      id: 4,
      name: '+500',
      value: 500,
      bgColor: '#FFCCFF',
      color: '#ffffff',
      probability: 15
    },
    {
      id: 5,
      name: '-200',
      value: -200,
      bgColor: '#00FFFF',
      color: '#ffffff',
      probability: 14
    },
    {
      id: 6,
      name: '-500',
      value: -500,
      bgColor: '#FF00FF',
      color: '#ffffff',
      probability: 15
    },
    {
      id: 7,
      name: '+100',
      value: 100,
      bgColor: '#C0C0C0',
      color: '#ffffff',
      probability: 14
    },
  ]
  
  const prizesImage = [
    {
      id: 1, //* The unique id of each prize, an integer greater than 0
      value: 'Blue\'s value', //* Prize value, return value after spinning
      weight: 1 // Weight, if useWeight is true, the probability is calculated by weight (weight must be an integer), so probability is invalid
    },
    {
      id: 2,
      value: 'Red\'s value',
      weight: 0
    },
    {
      id: 3,
      value: 'Yellow\'s value',
      weight: 0
    }
  ]
  
  const prizeRes = computed(() => {
    return prizesCanvas.find(item => item.id === prizeId.value) || prizesCanvas[0]
  })
  
  
  onMounted(() => {
    // wheelEl.value.startRotate() // Can start rotation
  })

  let currentPoint = ref(0);

  const updatePointSuccess = ref('')

  const user = computed(() => store.state.user.data);

  // Wait for user.value to be defined before accessing user.value.point
  watchEffect(() => {
    if (user.value) {
      currentPoint.value = user.value.point
      console.log('data: ' + user.value.point);
    }
  });
  
  // Simulate the request back-end interface
  function testRequest (verified, duration) { // verified: whether to pass the verification, duration: delay time
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve(verified)
      }, duration)
    })
  }
  
  function onCanvasRotateStart (rotate) {
    if (canvasVerify.value) {
      const verified = true // true: the test passed the verification, false: the test failed the verification
      testRequest(verified, verifyDuration * 1000).then((verifiedRes) => {
        if (verifiedRes) {
          console.log('Verification passed, start to rotate')
          rotate() // Call the callback, start spinning
          canvasVerify.value = false // Turn off verification mode
        } else {
          alert('Failed verification')
        }
      })
      return
    }
    console.log('onCanvasRotateStart')
  }
  
  function onImageRotateStart () {
    console.log('onImageRotateStart')
  }
  
  function onRotateEnd (prize) {
    // alert(prize.value)
    // console.log(prize.value);
    currentPoint.value += prize.value

    axiosClient.post('/update-point', { point: currentPoint.value, id: user.value.id })
     .then((response) => {
        console.log(response.data.success);
        if (response.data.success == true) {
            if (prize.value > 0) {
                updatePointSuccess.value = 'You get ' + prize.value + ' points'
            } else if (prize.value < 0) {
                updatePointSuccess.value = 'You will be minused ' + prize.value + ' points'
            } else {
                updatePointSuccess.value = 'You do not receive any points'
            }
        }
     })
    // console.log(currentPoint);
  }
  
  function onChangePrize (id) {
    prizeId.value = id
  }
  </script>