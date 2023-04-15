export default class {

    getAllPlans() {
        return new Promise((resolve, reject) => {
            axios.get('/api/plans')
                .then((result) => {
                    resolve(result.data);
                })
                .catch(() => {
                    reject();
                });
        })
    }

    getPlan(planId) {
        return new Promise((resolve, reject) => {
            axios.get(`/api/plans/${planId}`)
                .then((result) => {
                    resolve(result.data);
                })
                .catch(() => {
                    reject();
                });
        })
    }

    createSetupIntent() {
        return new Promise((resolve, reject) => {
            axios.post('/api/plans/createSetupIntent')
                .then((result) => {
                    resolve(result.data);
                })
                .catch(() => {
                    reject();
                });
        })
    }

    subscribe(subscriptionInfo) {
        return new Promise((resolve, reject) => {
            axios.post('/api/plans/subscribe', subscriptionInfo)
                .then((result) => {
                    resolve(result.data);
                })
                .catch(() => {
                    reject();
                });
        })
    }

    cancel() {
        return new Promise((resolve, reject) => {
            axios.delete('/api/plans/cancel')
                .then((result) => {
                    resolve(result.data);
                })
                .catch(() => {
                    reject();
                });
        })
    }
}
