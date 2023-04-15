export default class {

    async getReports(filterData) {
        let config = {
            params: filterData || null
        };
        return new Promise((resolve) => {
            axios.get('/api/reports', config).then((result) => {
                resolve(result.data);
            });
        })
    }

    async getReport(id, breadcrumbs) {
        return new Promise((resolve) => {
            axios.get(`/api/reports/${id}`).then((result) => {
                resolve(breadcrumbs ? result.data : result.data.report);
            });
        })
    }

    async getReportsByConference(conferenceId) {
        return new Promise((resolve) => {
            axios.get(`/api/reports/conference/${conferenceId}`).then((result) => {
                resolve(result.data);
            });
        })
    }

    async deleteReport(conferenceId, creatorId) {
        return new Promise((resolve) => {
            axios({
                method: 'delete',
                url: '/api/reports',
                data: {
                    conferenceId,
                    creatorId
                }
            }).then(() => {
                resolve()
            });
        })
    }

    async deletePresentation(conferenceId, creatorId) {
        return new Promise((resolve) => {
            axios({
                method: 'delete',
                url: '/api/presentations',
                data: {
                    conferenceId,
                    creatorId
                }
            }).then(() => {
                resolve()
            });
        })
    }

    async uploadPresentation(presentation) {
        const config = {
            headers: {
                'Content-Type': 'multipart/form-data'
            },
        }
        const presentationFormData = new FormData();
        presentationFormData.append('presentation', presentation);
        return new Promise((resolve) => {
            axios.post('/api/presentations', presentationFormData, config).then((result) => {
                resolve(result.data);
            });
        })
    }

    async newReport(reportInfo) {
        return new Promise((resolve) => {
            axios({
                method: 'post',
                url: '/api/reports',
                data: {
                    ...reportInfo
                }
            }).then(() => {
                resolve();
            });
        })
    }

    async editReport(reportInfo) {
        return new Promise((resolve) => {
            axios.post('/api/reports', {
                _method: 'PUT',
                ...reportInfo
            }).then(() => {
                resolve();
            });
        })
    }

    async getCategory(id) {
        return new Promise((resolve) => {
            axios.get(`/api/reports/${id}/category`).then((result) => {
                resolve(result.data);
            });
        })
    }

    async addToFavorites(id) {
        return new Promise((resolve) => {
            axios({
                method: 'post',
                url: '/api/reports/favorites',
                data: {
                    id
                }
            }).then(() => {
                resolve();
            });
        })
    }

    async removeFromFavorites(id) {
        return new Promise((resolve) => {
            axios({
                method: 'delete',
                url: '/api/reports/favorites',
                data: {
                    id
                }
            }).then(() => {
                resolve();
            });
        })
    }

    async getFavoritesCount() {
        return new Promise((resolve) => {
            axios.get(`/api/reports/favorites/count`).then((result) => {
                resolve(result.data);
            });
        })
    }

    async getFavoritesByUser() {
        return new Promise((resolve) => {
            axios.get(`/api/reports/favorites`).then((result) => {
                resolve(result.data);
            });
        })
    }

    async search(searchData) {
        let config = {
            params: searchData
        };
        return new Promise((resolve) => {
            axios.get('/api/reports/search', config).then((result) => {
                resolve(result.data);
            });
        })
    }

    async exportComments(id) {
        return new Promise((resolve) => {
            axios.get(`/api/reports/${id}/exportComments`).then((result) => {
                resolve(result.data);
            });
        })
    }

    async joinReport(id) {
        return new Promise((resolve) => {
            axios({
                method: 'post',
                url: '/api/reports/join',
                data: {
                    id
                }
            }).then((result) => {
                resolve(result.data);
            });
        })
    }

    async cancelReport(id) {
        return new Promise((resolve) => {
            axios({
                method: 'delete',
                url: '/api/reports/cancel',
                data: {
                    id
                }
            }).then(() => {
                resolve();
            });
        })
    }
}
