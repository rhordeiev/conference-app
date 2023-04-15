export function getWaitingStatus(date, startTime, endTime) {
    const statuses = {
        waiting: {
            color: 'warning',
            status: 'Waiting'
        },
        started: {
            color: 'error',
            status: 'Started'
        },
        ended: {
            color: 'grey',
            status: 'Ended'
        }
    }
    const reportStartDatetime = new Date(`${date}T${startTime}`);
    const reportEndDatetime = new Date(`${date}T${endTime}`);

    if (Date.now() < reportStartDatetime) {
        return statuses.waiting;
    } else if (Date.now() >= reportStartDatetime && Date.now() <= reportEndDatetime) {
        return statuses.started;
    } else {
        return statuses.ended;
    }
}
