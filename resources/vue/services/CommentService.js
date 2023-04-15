export default class {

    async getComments(reportId, offset, limit) {
        return new Promise((resolve) => {
            axios.get(`/api/comments/${reportId}?offset=${offset}&limit=${limit}`)
                .then((result) => {
                    resolve(result.data);
                });
        })
    }

    async newComment(commentInfo) {
        return new Promise((resolve) => {
            axios({
                method: 'post',
                url: '/api/comments',
                data: {
                    ...commentInfo
                }
            }).then((result) => {
                resolve(result.data);
            });
        })
    }

    async editComment(commentInfo) {
        return new Promise((resolve) => {
            axios.post('/api/comments', {
                _method: 'PUT',
                ...commentInfo
            }).then(() => {
                resolve();
            });
        })
    }
}
