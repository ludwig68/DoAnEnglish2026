import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/user/HomeView.vue'
import LoginView from '../views/user/LoginView.vue'
import RegisterView from '../views/user/RegisterView.vue'
import CoursesView from '../views/user/CoursesView.vue'
import AboutView from '../views/user/AboutView.vue'
import CourseDetailView from '../views/user/CourseDetailView.vue'
import UserLayout from '../views/user/UserLayout.vue'
import UserDashboard from '../views/user/UserDashboard.vue'
import UserAssignments from '../views/user/UserAssignments.vue'
import UserLeaveRequest from '../views/user/UserLeaveRequest.vue'
import UserMakeUpClass from '../views/user/UserMakeUpClass.vue'
import UserSupport from '../views/user/UserSupport.vue'
import SupportView from '../views/user/SupportView.vue'
import ContactView from '../views/user/ContactView.vue'
import TeacherLayout from '../views/teacher/TeacherLayout.vue'
import TeacherDashboard from '../views/teacher/TeacherDashboard.vue'
import TeacherSchedule from '../views/teacher/TeacherSchedule.vue'
import TeacherClasses from '../views/teacher/TeacherClasses.vue'
import TeacherClassDetail from '../views/teacher/TeacherClassDetail.vue'
import TeacherAssignments from '../views/teacher/TeacherAssignments.vue'
import AdminLayout from '../views/admin/AdminLayout.vue'
import AdminOverview from '../views/admin/AdminOverview.vue'
import UserManagerView from '../views/admin/UserManagerView.vue'
import CourseManager from '../views/admin/CourseManager.vue'
import CategoryManager from '../views/admin/CategoryManager.vue'
import LearningPathManager from '../views/admin/LearningPathManager.vue'
import ConsultationManager from '../views/admin/ConsultationManager.vue'
import ContactManager from '../views/admin/ContactManager.vue'
import WebsiteContentManager from '../views/admin/WebsiteContentManager.vue'
import ScheduleManager from '../views/admin/ScheduleManager.vue'
import ClassManager from '../views/admin/ClassManager.vue'
import LessonDetail from '../views/admin/LessonDetail.vue'
import EnrollmentManager from '../views/admin/EnrollmentManager.vue'
import QuizBuilder from '../views/admin/QuizBuilder.vue'
import { clearAuthSession, getCurrentUser } from '../utils/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'home', component: HomeView },
    { path: '/login', name: 'login', component: LoginView },
    { path: '/register', name: 'register', component: RegisterView },
    {
      path: '/user',
      component: UserLayout,
      children: [
        {
          path: 'dashboard',
          name: 'user-dashboard',
          component: UserDashboard
        },
        {
          path: 'assignments',
          name: 'user-assignments',
          component: UserAssignments
        },
        {
          path: 'support',
          name: 'user-support',
          component: UserSupport
        },
        {
          path: 'leave-request',
          name: 'user-leave-request',
          component: UserLeaveRequest
        },
        {
          path: 'makeup-class',
          name: 'user-makeup-class',
          component: UserMakeUpClass
        }
      ]
    },
    { path: '/courses', name: 'courses', component: CoursesView },
    { path: '/about', name: 'about', component: AboutView },
    { path: '/support', name: 'support', component: SupportView },
    { path: '/contact', name: 'contact', component: ContactView },
    {
      path: '/teacher',
      component: TeacherLayout,
      children: [
        {
          path: 'dashboard',
          name: 'teacher-dashboard',
          component: TeacherDashboard
        },
        {
          path: 'schedule',
          name: 'teacher-schedule',
          component: TeacherSchedule
        },
        {
          path: 'classes',
          name: 'teacher-classes',
          component: TeacherClasses
        },
        {
          path: 'classes/:id',
          name: 'teacher-class-detail',
          component: TeacherClassDetail
        },
        {
          path: 'assignments',
          name: 'teacher-assignments',
          component: TeacherAssignments
        }
      ]
    },
    {
      path: '/admin',
      component: AdminLayout,
      children: [
        {
          path: '',
          name: 'admin-overview',
          component: AdminOverview
        },
        {
          path: 'users',
          name: 'admin-users',
          component: UserManagerView
        },
        {
          path: 'courses',
          name: 'admin-courses',
          component: CourseManager
        },
        {
          path: 'categories',
          name: 'admin-categories',
          component: CategoryManager
        },
        {
          path: 'learning-paths',
          name: 'admin-learning-paths',
          component: LearningPathManager
        },
        {
          path: 'consultations',
          name: 'admin-consultations',
          component: ConsultationManager
        },
        {
          path: 'contacts',
          name: 'admin-contacts',
          component: ContactManager
        },
        {
          path: 'content',
          name: 'admin-content',
          component: WebsiteContentManager
        }
          ,{
            path: 'schedules',
            name: 'admin-schedules',
            component: ScheduleManager

          },
          {
            path: 'classes',
            name: 'admin-classes',
            component: ClassManager
          },
          {
            path: 'quizzes',
            name: 'admin-quizzes',
            component: QuizBuilder
          },
          {
            path: 'schedules/:id', 
            name: 'admin-lesson-detail',
            component: LessonDetail
          },
          {
            path: 'enrollments',
            name: 'admin-enrollments',
            component: EnrollmentManager
          }


      ]
    },
    { path: '/course/:id', name: 'course-detail', component: CourseDetailView }
  ]
})

router.beforeEach((to, from, next) => {
  const user = getCurrentUser()

  if (!user) {
    clearAuthSession()
  }

  if (to.path.startsWith('/admin')) {
    if (!user) {
      return next('/login')
    }
    if (user.role !== 'admin') {
      return next('/')
    }
  }

  if (to.path.startsWith('/teacher')) {
    if (!user) {
      return next('/login')
    }
    if (user.role !== 'instructor') {
      return next('/')
    }
  }

  if (to.path.startsWith('/user/dashboard')) {
    if (!user) {
      return next('/login')
    }
    if (user.role === 'admin') {
      return next('/admin')
    }
    if (user.role === 'instructor') {
      return next('/teacher/dashboard')
    }
  }

  if ((to.path === '/login' || to.path === '/register') && user) {
    if (user.role === 'admin') return next('/admin')
    if (user.role === 'instructor') return next('/teacher/dashboard')
    return next('/user/dashboard')
  }

  next()
})

export default router
