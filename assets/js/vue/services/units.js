function pad(num) {
    return ("0"+num).slice(-2);
}

function round(num, decimalPlaces = 0) {
    let p = Math.pow(10, decimalPlaces);
    let n = (num * p) * (1 + Number.EPSILON);
    return Math.round(n) / p;
}

const unitsService = {
    hhmmss(secs) {
        let minutes = Math.floor(secs / 60);
        secs = Math.round(secs%60);
        let hours = Math.floor(minutes/60);
        minutes = Math.round(minutes%60);
        if(hours > 0) {
            return `${hours}:${pad(minutes)}:${pad(secs)}`;
        }
        return `${pad(minutes)}:${pad(secs)}`;
    },
    HHMM(secs) {
        let minutes = Math.floor(secs / 60);
        let hours = Math.floor(minutes/60)
        minutes = Math.round(minutes%60);
        if(hours > 0) {
            return `${hours}h${pad(minutes)}`;
        }
        return `${pad(minutes)}min`;
    },
    HHMMSS(secs) {
        let minutes = Math.floor(secs / 60);
        secs = Math.round(secs%60);
        let hours = Math.floor(minutes/60)
        minutes = Math.round(minutes%60);
        if(hours > 0) {
            if(secs > 0) {
                return `${hours}h${pad(minutes)}m${pad(secs)}`;
            } else {
                return `${hours}h${pad(minutes)}`;
            }
        }
        return `${pad(minutes)}m${pad(secs)}s`;
    },
    HHMMFromH(hours) {
        let roundHours = Math.floor(hours);
        let minutes = Math.floor((roundHours - hours)*60);
        if(roundHours > 0) {
            return `${roundHours}h${pad(minutes)}`;
        }
        return `${pad(minutes)}min`;
    },
    roundDistanceInKm(meters) {
        return round(meters/1000, 2);
    },
    setReadableSpeedForRun(speedInMetersPerSecond) {
        let speed = 20;
        if(speedInMetersPerSecond > 0) {
            speed  = Math.round(16.666666667/speedInMetersPerSecond*100)/100; //round 2
        }
        let minutes = Math.floor(speed);
        let secondes = Math.round((speed - minutes)*60);
        if(secondes < 10) {
            secondes = '0'+secondes;
        }
        return   minutes+":"+secondes;
    },
    formatPrice(price) {
        return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(price);
    },
    getDistanceInKilometers(distanceInMeters) {
        return round(distanceInMeters / 1000, 2);
    },
    formatAllureswim(speedKmh) {
        if(speedKmh > 0) {
            let nbSeconds = Math.round(6/(speedKmh)*60);
            return unitsService.hhmmss(nbSeconds);
        }
        return null;
    },
    round(num, decimalPlaces = 0) {
        let p = Math.pow(10, decimalPlaces);
        let n = (num * p) * (1 + Number.EPSILON);
        return Math.round(n) / p;
    }
}

export { unitsService }