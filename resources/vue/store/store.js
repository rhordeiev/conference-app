import {createStore} from 'vuex'
import VuexPersister from 'vuex-persister'
import {api} from "@/api";

const vuexPersister = new VuexPersister()

export default createStore({
    state() {
        return {
            user: null,
            conferences: [],
            reports: [],
            favoritesCount: 0,
        }
    },
    getters: {},
    mutations: {
        setUser(state, userInfo) {
            state.user = userInfo;
            state.user.country = state.user.country_name;
            delete state.user.country_name;
            api.setToken(userInfo.token);
        },
        unsetUser(state) {
            state.user = null;
            api.removeToken();
        },
        resetConferences(state, payload) {
            if (!_.isArray(payload.conferences)) {
                state.conferences = _.toArray(payload.conferences);
            } else {
                state.conferences = payload.conferences;
            }
            if (payload.filter) {
                state.conferences = state.conferences.filter(conference => {
                    const conferenceEndDatetime = new Date(`${conference.date}T${conference.end_time}`);
                    return Date.now() < conferenceEndDatetime;
                });
            }
        },
        joinConference(state, clickedConferenceId) {
            let updatedConference = {};
            state.conferences.forEach((conference, index) => {
                if (conference.id === parseInt(clickedConferenceId)) {
                    conference.joined = true;
                    updatedConference.index = index;
                    updatedConference.info = conference;
                }
            });
            state.conferences.splice(updatedConference.index, 1, updatedConference.info);
            state.user.join_count++;
        },
        cancelConference(state, clickedConferenceId) {
            let updatedConference = {};
            state.conferences.forEach((conference, index) => {
                if (conference.id === clickedConferenceId) {
                    conference.joined = false;
                    updatedConference.index = index;
                    updatedConference.info = conference;
                }
            });
            state.conferences.splice(updatedConference.index, 1, updatedConference.info);
        },
        newConference(state, conference) {
            state.conferences.push(conference);
        },
        deleteConference(state, conferenceId) {
            const foundIndex = state.conferences.findIndex((conference) => conference.id === parseInt(conferenceId));
            state.conferences.splice(foundIndex, 1);
        },
        editConference(state, editedConference) {
            const foundIndex = state.conferences.findIndex((conference) => conference.id === editedConference.id);
            state.conferences.splice(foundIndex, 1, editedConference);
        },
        newReport(state, report) {
            report.creator_id = state.user.id;
            state.reports.push(report);
        },
        deleteReport(state, payload) {
            const foundIndex = state.reports.findIndex(
                (report) => parseInt(report.conference_id) === payload.conferenceId &&
                    parseInt(report.creator_id) === payload.creatorId
            );
            state.reports.splice(foundIndex, 1);
        },
        resetReports(state, payload) {
            if (!_.isArray(payload.reports)) {
                state.reports = _.toArray(payload.reports);
            } else {
                state.reports = payload.reports;
            }
            if (payload.filter) {
                state.reports = state.reports.filter(report => {
                    const reportEndDatetime = new Date(`${report.date}T${report.end_time}`);
                    return Date.now() < reportEndDatetime;
                });
            }
        },
        editReport(state, editedReport) {
            const foundIndex = state.conferences.findIndex((report) => report.id === editedReport.id);
            state.conferences.splice(foundIndex, 1, editedReport);
        },
        addToFavorites(state, clickedReportId) {
            let updatedReport = {};
            state.reports.forEach((report, index) => {
                if (report.id === parseInt(clickedReportId)) {
                    report.favorite = true;
                    updatedReport.index = index;
                    updatedReport.info = report;
                }
            });
            state.reports.splice(updatedReport.index, 1, updatedReport.info);
            state.favoritesCount++;
        },
        removeFromFavorites(state, clickedReportId) {
            let updatedReport = {};
            state.reports.forEach((report, index) => {
                if (report.id === parseInt(clickedReportId)) {
                    report.favorite = false;
                    updatedReport.index = index;
                    updatedReport.info = report;
                }
            });
            state.reports.splice(updatedReport.index, 1, updatedReport.info);
            state.favoritesCount--;
        },
        setFavoritesCount(state, count) {
            state.favoritesCount = count;
        },
        joinReport(state, payload) {
            let updatedReport = {};
            state.reports.forEach((report, index) => {
                if (report.id === parseInt(payload.id)) {
                    report.joined = true;
                    if (report.meeting_id) {
                        report.join_url = payload.meeting.join_url;
                    }
                    updatedReport.index = index;
                    updatedReport.info = report;
                }
            });
            state.reports.splice(updatedReport.index, 1, updatedReport.info);
        },
        cancelReport(state, clickedReportId) {
            if (state.user?.type === 'listener') {
                let updatedReport = {};
                state.reports.forEach((report, index) => {
                    if (report.id === parseInt(clickedReportId)) {
                        report.joined = false;
                        if (report.meeting_id) {
                            delete report.join_url;
                        }
                        updatedReport.index = index;
                        updatedReport.info = report;
                    }
                });
                state.reports.splice(updatedReport.index, 1, updatedReport.info);
            } else if (state.user?.type === 'member') {
                const foundIndex = state.reports.findIndex((report) => report.id === parseInt(clickedReportId));
                state.reports.splice(foundIndex, 1);
            }
        },
        subscribeToPlan(state, planId) {
            state.user.plan_id = planId;
        },
        cancelSubscriptionToPlan(state) {
            state.user.plan_id = null;
        }
    },
    actions: {
        loginUser({commit}, payload) {
            return new Promise((resolve, reject) => {
                api.user.loginUser(payload.userInfo)
                    .then((result) => {
                        commit('setUser', result);
                        resolve();
                    })
                    .catch(() => {
                        reject();
                    });
            })
        },
        logoutUser({commit}) {
            return new Promise((resolve) => {
                api.user.logoutUser()
                    .then(() => {
                        commit('unsetUser');
                        resolve()
                    });
            })
        },
        editUser({commit}, payload) {
            return new Promise((resolve, reject) => {
                api.user.editUser(payload.userInfo)
                    .then(() => {
                        commit('setUser', payload.userInfo);
                        resolve();
                    })
                    .catch(() => {
                        reject();
                    });
            })
        },
        resetConferences({commit}, payload) {
            return new Promise((resolve) => {
                api.conference.getConferences(payload?.data).then((result) => {
                    commit('resetConferences', {
                        conferences: result,
                        filter: payload?.active
                    });
                    resolve();
                });
            })
        },
        resetConferencesByCategory({commit}, payload) {
            return new Promise((resolve) => {
                api.category.getCategoryConferences(payload.id).then((result) => {
                    commit('resetConferences', result);
                    resolve();
                });
            })
        },
        resetReportsByCategory({commit}, payload) {
            return new Promise((resolve) => {
                api.category.getCategoryReports(payload.id).then((result) => {
                    commit('resetReports', {
                        reports: result,
                        filter: false
                    });
                    resolve();
                });
            })
        },
        resetReportsByFavorites({commit}) {
            return new Promise((resolve) => {
                api.report.getFavoritesByUser().then((result) => {
                    commit('resetReports', {
                        reports: result,
                        filter: false
                    });
                    resolve();
                });
            })
        },
        joinConference({commit}, payload) {
            return new Promise((resolve, reject) => {
                api.conference.joinConference(payload.id)
                    .then(() => {
                        commit('joinConference', payload.id);
                        resolve();
                    })
                    .catch((error) => {
                        reject(error);
                    });
            })
        },
        cancelConference({commit}, payload) {
            return new Promise((resolve) => {
                api.conference.cancelConference(payload.conferenceId, payload.userId).then(() => {
                    if (payload?.withoutCommit) {
                        resolve();
                    }
                    commit('cancelConference', payload.conferenceId);
                    resolve();
                });
            })
        },
        newConference({commit}, payload) {
            return new Promise((resolve) => {
                api.conference.newConference(payload.conferenceInfo).then(() => {
                    commit('newConference', payload.conferenceInfo);
                    resolve();
                });
            })
        },
        deleteConference({commit}, payload) {
            return new Promise((resolve) => {
                api.conference.deleteConference(payload.id).then(() => {
                    commit('deleteConference', payload.id);
                    resolve();
                });
            })
        },
        editConference({commit}, payload) {
            return new Promise((resolve) => {
                api.conference.editConference(payload.conferenceInfo).then(() => {
                    commit('editConference', payload.conferenceInfo);
                    resolve();
                });
            })
        },
        newReport({commit}, payload) {
            return new Promise((resolve) => {
                api.report.newReport(payload.reportInfo).then(() => {
                    commit('newReport', payload.reportInfo);
                    resolve();
                });
            })
        },
        deleteReport({commit}, payload) {
            return new Promise((resolve) => {
                api.report.deleteReport(payload.conferenceId, payload.creatorId).then(() => {
                    commit('deleteReport', payload);
                    resolve();
                });
            })
        },
        resetReports({commit}, payload) {
            return new Promise((resolve) => {
                api.report.getReports(payload?.data).then((result) => {
                    commit('resetReports', {
                        reports: result,
                        filter: payload?.active
                    });
                    resolve();
                });
            })
        },
        editReport({commit}, payload) {
            return new Promise((resolve) => {
                api.report.editReport(payload.reportInfo).then(() => {
                    commit('editConference', payload.reportInfo);
                    resolve();
                });
            })
        },
        addToFavorites({commit}, payload) {
            return new Promise((resolve) => {
                api.report.addToFavorites(payload.id).then(() => {
                    commit('addToFavorites', payload.id);
                    resolve();
                });
            })
        },
        removeFromFavorites({commit}, payload) {
            return new Promise((resolve) => {
                api.report.removeFromFavorites(payload.id).then(() => {
                    commit('removeFromFavorites', payload.id);
                    resolve();
                });
            })
        },
        setFavoritesCount({commit}) {
            return new Promise((resolve) => {
                api.report.getFavoritesCount().then((result) => {
                    commit('setFavoritesCount', result);
                    resolve();
                });
            })
        },
        joinReport({commit}, payload) {
            return new Promise((resolve) => {
                api.report.joinReport(payload.id).then((result) => {
                    commit('joinReport', {
                        id: payload.id,
                        meeting: result
                    });
                    resolve(result);
                });
            })
        },
        cancelReport({commit}, payload) {
            return new Promise((resolve) => {
                api.report.cancelReport(payload.id).then(() => {
                    commit('cancelReport', payload.id);
                    resolve();
                });
            })
        },
        subscribeToPlan({commit}, payload) {
            return new Promise((resolve) => {
                api.plan.subscribe(payload).then(() => {
                    commit('subscribeToPlan', payload.planId);
                    resolve();
                });
            })
        },
        cancelSubscriptionToPlan({commit}) {
            return new Promise((resolve) => {
                api.plan.cancel().then(() => {
                    commit('cancelSubscriptionToPlan');
                    resolve();
                });
            })
        }
    },
    plugins: [vuexPersister.persist]
})
