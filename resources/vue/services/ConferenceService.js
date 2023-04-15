export default class {

    async getConferences(filterData) {
        let config = {
            params: filterData || null
        };
        return new Promise((resolve) => {
            axios.get('/api/conferences', config).then((result) => {
                resolve(result.data);
            });
        })
    }

    async getConference(id, breadcrumbs) {
        return new Promise((resolve) => {
            axios.get(`/api/conferences/${id}`).then((result) => {
                resolve(breadcrumbs ? result.data : result.data.conference);
            });
        })
    }

    async deleteConference(id) {
        return new Promise((resolve) => {
            axios({
                method: 'delete',
                url: '/api/conferences',
                data: {
                    id
                }
            }).then(() => {
                resolve()
            });
        })
    }

    async joinConference(id) {
        return new Promise((resolve, reject) => {
            axios({
                method: 'post',
                url: '/api/conferences/join',
                data: {
                    id
                }
            }).then(() => {
                resolve();
            }).catch((error) => {
                console.log(error);
                reject(error.response.data);
            });
        })
    }

    async cancelConference(conferenceId, userId) {
        return new Promise((resolve) => {
            axios({
                method: 'delete',
                url: '/api/conferences/cancel',
                data: {
                    conferenceId,
                    userId
                }
            }).then(() => {
                resolve();
            });
        })
    }

    async newConference(conferenceInfo) {
        return new Promise((resolve) => {
            axios({
                method: 'post',
                url: '/api/conferences',
                data: {
                    ...conferenceInfo
                }
            }).then(() => {
                resolve();
            });
        })
    }

    async editConference(conferenceInfo) {
        return new Promise((resolve) => {
            axios.post('/api/conferences', {
                _method: 'PUT',
                ...conferenceInfo
            }).then(() => {
                resolve();
            });
        })
    }

    async getCategory(id) {
        return new Promise((resolve) => {
            axios.get(`/api/conferences/${id}/category`).then((result) => {
                resolve(result.data);
            });
        })
    }

    async search(searchData) {
        let config = {
            params: searchData
        };
        return new Promise((resolve) => {
            axios.get('/api/conferences/search', config).then((result) => {
                resolve(result.data);
            });
        })
    }

    async exportConferences(filterData) {
        let config = {
            params: filterData || null
        };
        return new Promise((resolve) => {
            axios.get('/api/conferences/export', config).then((result) => {
                resolve(result.data);
            });
        })
    }

    async exportReports(id) {
        return new Promise((resolve) => {
            axios.get(`/api/conferences/${id}/exportReports`).then((result) => {
                resolve(result.data);
            });
        })
    }
    async exportMembers(id) {
        return new Promise((resolve) => {
            axios.get(`/api/conferences/${id}/exportMembers`).then((result) => {
                resolve(result.data);
            });
        })
    }
}
