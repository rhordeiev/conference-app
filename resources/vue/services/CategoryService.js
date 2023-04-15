export default class {

    async getCategories() {
        return new Promise((resolve) => {
            axios.get(`/api/categories`)
                .then((result) => {
                    resolve(result.data);
                });
        })
    }

    async getCategoryById(id) {
        return new Promise((resolve) => {
            axios.get(`/api/categories/${id}`).then((result) => {
                resolve(result.data);
            });
        })
    }

    async newCategory(categoryInfo) {
        return new Promise((resolve) => {
            axios({
                method: 'post',
                url: '/api/categories',
                data: {
                    ...categoryInfo
                }
            }).then((result) => {
                resolve(result.data);
            });
        })
    }

    async deleteCategory(id) {
        return new Promise((resolve) => {
            axios({
                method: 'delete',
                url: '/api/categories',
                data: {
                    id
                }
            }).then(() => {
                resolve()
            });
        })
    }
    async editCategory(categoryInfo) {
        return new Promise((resolve) => {
            axios.post('/api/categories', {
                _method: 'PUT',
                ...categoryInfo
            }).then(() => {
                resolve();
            });
        })
    }

    async attachConference(info) {
        return new Promise((resolve) => {
            axios({
                method: 'post',
                url: '/api/categories/attachConference',
                data: {
                    ...info
                }
            }).then(() => {
                resolve();
            });
        })
    }

    async detachConference(info) {
        return new Promise((resolve) => {
            axios({
                method: 'delete',
                url: '/api/categories/detachConference',
                data: {
                    ...info
                }
            }).then(() => {
                resolve();
            });
        })
    }

    async getChildCategories(parentId) {
        return new Promise((resolve) => {
            axios.get(`/api/categories/children/${parentId}`).then((result) => {
                resolve(result.data);
            });
        })
    }

    async attachReport(info) {
        return new Promise((resolve) => {
            axios({
                method: 'post',
                url: '/api/categories/attachReport',
                data: {
                    ...info
                }
            }).then(() => {
                resolve();
            });
        })
    }

    async detachReport(info) {
        return new Promise((resolve) => {
            axios({
                method: 'delete',
                url: '/api/categories/detachReport',
                data: {
                    ...info
                }
            }).then(() => {
                resolve();
            });
        })
    }

    async detachReports(info) {
        return new Promise((resolve) => {
            axios({
                method: 'delete',
                url: '/api/categories/detachReports',
                data: {
                    ...info
                }
            }).then(() => {
                resolve();
            });
        })
    }

    async getCategoryConferences(id) {
        return new Promise((resolve) => {
            axios.get(`/api/categories/${id}/conferences`)
                .then((result) => {
                    resolve(result.data);
                });
        })
    }

    async getCategoryReports(id) {
        return new Promise((resolve) => {
            axios.get(`/api/categories/${id}/reports`).then((result) => {
                resolve(result.data);
            });
        })
    }
}
