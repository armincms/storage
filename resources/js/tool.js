Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'storage',
      path: '/storage',
      component: require('./components/Tool'),
    },
  ])

  Vue.component('storage-uplaoder', require('./components/Uploader.vue'))
})
