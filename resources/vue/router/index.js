import {createRouter, createWebHistory} from 'vue-router'
import store from '../store/store'
import ConferencesView from '@/views/conference/ConferencesView.vue'
import LoginView from "@/views/auth/LoginView.vue";
import RegisterView from "@/views/auth/RegisterView.vue";
import NewConferenceView from "@/views/conference/NewConferenceView.vue";
import ConferenceView from '@/views/conference/ConferenceView.vue'
import EditConferenceView from "@/views/conference/EditConferenceView.vue";
import ReportsView from "@/views/report/ReportsView.vue";
import ReportView from "@/views/report/ReportView.vue";
import EditReportView from "@/views/report/EditReportView.vue";
import CategoriesView from "@/views/categories/CategoriesView.vue";
import CategoryView from "@/views/categories/CategoryView.vue";
import FavoritesView from "@/views/report/FavoritesView.vue";
import EditProfileView from "@/views/user/EditProfileView.vue";
import MeetingsView from "@/views/meeting/MeetingsView.vue";
import PlansView from "@/views/plan/PlansView.vue";

function loggedInUsersRedirect(to, from) {
    const isLoggedIn = !!store.state.user;
    if (isLoggedIn) {
        return {path: from.path}
    }
}

function newConferenceUsersRedirect(to, from) {
    const userType = store.state?.user?.type;
    if (userType !== 'announcer' && userType !== 'admin') {
        return {path: from.path}
    }
}

function unauthenticatedUsersRedirect() {
    const isLoggedIn = !!store.state.user;
    if (!isLoggedIn) {
        return {name: 'Register'}
    }
}

function editConferenceUsersRedirect(to, from) {
    const currentConference = store.state.conferences.filter((conference) => conference.id === parseInt(to.params.id))[0];
    if ((store.state?.user?.type !== 'announcer' ||
            (store.state?.user?.type === 'announcer' && !currentConference?.creator))
        && store.state?.user?.type !== 'admin') {
        return {path: from.path}
    }
}

function editReportUsersRedirect(to, from) {
    const currentReport = store.state.reports.filter((report) => report.id === parseInt(to.params.id))[0];
    if (!store.state?.user || store.state?.user?.type !== 'member' ||
        (store.state?.user?.type === 'member' && currentReport.creator_id !== store.state?.user?.id)) {
        return {path: from.path}
    }
}

function adminOnlyUsersRedirect(to, from) {
    if (!store.state?.user || store.state?.user?.type !== 'admin') {
        return {path: from.path}
    }
}

function memberOnlyUsersRedirect(to, from) {
    if (!store.state?.user || store.state?.user?.type !== 'member') {
        return {path: from.path}
    }
}

const router = createRouter({
    history: createWebHistory(), routes: [
        {
            path: '/', name: 'Home', component: ConferencesView
        },
        {
            path: '/login', name: 'Login', component: LoginView, beforeEnter: loggedInUsersRedirect
        },
        {
            path: '/register', name: 'Register', component: RegisterView, beforeEnter: loggedInUsersRedirect
        },
        {
            path: '/conference', children: [
                {
                    path: 'new',
                    name: 'NewConference',
                    component: NewConferenceView,
                    beforeEnter: newConferenceUsersRedirect
                },
                {
                    path: ':id',
                    name: 'Conference',
                    component: ConferenceView,
                    beforeEnter: unauthenticatedUsersRedirect
                },
                {
                    path: 'edit/:id',
                    name: 'EditConference',
                    component: EditConferenceView,
                    beforeEnter: editConferenceUsersRedirect
                }]
        },
        {
            path: '/reports', name: 'Reports', component: ReportsView
        },
        {
            path: '/report', children: [
                {
                    path: ':id', name: 'Report', component: ReportView, beforeEnter: unauthenticatedUsersRedirect
                },
                {
                    path: 'edit/:id',
                    name: 'EditReport',
                    component: EditReportView,
                    beforeEnter: editReportUsersRedirect
                }]
        },
        {
            path: '/categories', name: 'Categories', component: CategoriesView, beforeEnter: adminOnlyUsersRedirect
        },
        {
            path: '/category', children: [
                {
                    path: ':id', name: 'Category', component: CategoryView
                },
            ]
        },
        {
            path: '/favorites', name: 'Favorites', component: FavoritesView, beforeEnter: unauthenticatedUsersRedirect
        },
        {
            path: '/profile/edit',
            name: 'EditProfile',
            component: EditProfileView,
            beforeEnter: unauthenticatedUsersRedirect
        },
        {
            path: '/meetings', name: 'Meetings', component: MeetingsView, beforeEnter: adminOnlyUsersRedirect
        },
        {
            path: '/plans', name: 'Plans', component: PlansView, beforeEnter: memberOnlyUsersRedirect
        },
    ]
});

export default router
