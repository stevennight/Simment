import Vue from 'vue'
import VueRouter from 'vue-router'
import Comment from '../page/public/Comment.vue'
import Admin from '../page/admin/Admin.vue'
import AdminLogin from '../page/admin/Login.vue'
import AdminSite from '../page/admin/components/Site.vue'
import AdminPath from '../page/admin/components/Path.vue'
import AdminComment from '../page/admin/components/Comment.vue'

Vue.use(VueRouter)

const routes = [
  {
    path: '/public/:site/:path',
    name: 'comment',
    component: Comment
  },
  {
    path: '/admin/login',
    component: AdminLogin
  },
  {
    path: '/admin',
    component: Admin,
    children: [
      {
        path: '',
        redirect: 'login'
      },
      {
        path: 'site',
        component: AdminSite
      },
      {
        path: 'path',
        component: AdminPath
      },
      {
        path: 'comment',
        component: AdminComment
      }
    ]
  },
]

const router = new VueRouter({
  mode: 'hash',
  base: process.env.BASE_URL,
  routes
})

export default router
