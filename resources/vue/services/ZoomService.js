export default class {

    getAllMeetings() {
        return new Promise((resolve, reject) => {
            axios.get('/api/zoom/meetings')
                .then((result) => {
                    resolve(result.data);
                })
                .catch(() => {
                    reject();
                });
        })
    }
}
