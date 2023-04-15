export function getTimeDifferenceFromDateString(dateString, member) {
    let date = new Date(dateString);
    if(member) {
        date = date - (1000 * 60 * 10);
    }
    const milliseconds = date - Date.now();

    let hours = Math.trunc(milliseconds / 1000 / 60 / 60);
    const millisecondsFromHours = hours * 1000 * 60 * 60;
    const millisecondsLeftWithoutHours = milliseconds - millisecondsFromHours;

    let minutes = Math.trunc(millisecondsLeftWithoutHours / 1000 / 60);
    const millisecondsFromMinutes = minutes * 1000 * 60;
    const millisecondsLeftWithoutMinutes = millisecondsLeftWithoutHours - millisecondsFromMinutes;

    let seconds = Math.trunc(millisecondsLeftWithoutMinutes / 1000);

    const timerEnded = hours <= 0 && minutes <= 0 && seconds <= 0;

    if(timerEnded) {
        return null;
    }

    if (hours < 10) {
        hours = `0${hours}`;
    }
    if (minutes < 10) {
        minutes = `0${minutes}`;
    }
    if (seconds < 10) {
        seconds = `0${seconds}`;
    }

    return `${hours}:${minutes}:${seconds}`;
}
