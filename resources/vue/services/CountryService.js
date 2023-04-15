export default class {
    async getCountries() {
        return new Promise((resolve) => {
            axios.get('/api/countries').then((result) => {
                resolve(result.data)
            });
        })
    }
}
