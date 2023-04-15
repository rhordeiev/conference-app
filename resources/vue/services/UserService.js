export default class {

    registerUser(userInfo) {
        return new Promise((resolve, reject) => {
            axios({
                method: 'post',
                url: '/api/users/register',
                data: userInfo
            }).then(() => {
                resolve();
            }).catch((error) => {
                const errors = error.response.data.errors;
                if (errors.email) {
                    reject();
                }
            });
        })
    }

    loginUser(userInfo) {
        return new Promise((resolve, reject) => {
            axios({
                method: 'post',
                url: '/api/users/login',
                data: userInfo
            }).then((result) => {
                resolve(result.data);
            }).catch(() => {
                reject();
            });
        });
    }

    logoutUser() {
        return new Promise((resolve, reject) => {
            axios({
                method: 'delete',
                url: '/api/users/logout',
            }).then(() => {
                resolve();
            }).catch(() => {
                reject();
            });
        });
    }

    hasToken() {
        return new Promise((resolve, reject) => {
            axios.get('/api/hasToken')
                .then(() => {
                    resolve();
                })
                .catch(() => {
                    reject();
                });
        })
    }

    editUser(userInfo) {
        return new Promise((resolve, reject) => {
            axios.post('/api/users', {
                _method: 'PUT',
                ...userInfo
            })
                .then(() => {
                    resolve();
                })
                .catch(() => {
                    reject();
                });
        })
    }
}
