import Vue from 'vue'
import VueRouter from 'vue-router'
// import Comment from '../page/public/Comment.vue'
// import Admin from '../page/admin/Admin.vue'
// import AdminLogin from '../page/admin/Login.vue'
// import AdminSite from '../page/admin/components/Site.vue'
// import AdminPath from '../page/admin/components/Path.vue'
// import AdminComment from '../page/admin/components/Comment.vue'

Vue.use(VueRouter)

const routes = [
  {
    path: '/public/:site/:path',
    name: 'comment',
    component: () => import(/* webpackChunkName: "group-public" */'../page/public/Comment.vue')
  },
  {
    path: '/admin/login',
    component: () => import(/* webpackChunkName: "group-admin" */'../page/admin/Login.vue')
  },
  {
    path: '/admin',
    component: () => import(/* webpackChunkName: "group-admin" */'../page/admin/Admin.vue'),
    children: [
      {
        path: '',
        redirect: 'login'
      },
      {
        path: 'site',
        component: () => import(/* webpackChunkName: "group-admin" */'../page/admin/components/Site.vue')
      },
      {
        path: 'path',
        component: () => import(/* webpackChunkName: "group-admin" */'../page/admin/components/Path.vue')
      },
      {
        path: 'comment',
        component: () => import(/* webpackChunkName: "group-admin" */'../page/admin/components/Comment.vue')
      }
    ]
  },
  {
    path: '*',
    beforeEnter() {location.href = '/404.html'}
  }
]

const router = new VueRouter({
  mode: 'hash',
  base: process.env.BASE_URL,
  routes
})

export default router
