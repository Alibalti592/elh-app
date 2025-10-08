const dateService = {
    //date of type Date()
    intlDate(date, weekday = "long", year = "numeric", month = "long", day = "numeric") {
        let options = { weekday: weekday, year: year, month: month, day: day };
        return new Intl.DateTimeFormat("fr-FR", options).format(date);
    },
    //date string wich fix timezone issue !
    dateString(date) {
        return new Date(date.getTime() - date.getTimezoneOffset()*60000).toISOString().slice(0,10);
    },
    diffDates(date1,date2,interval) {
        var second=1000, minute=second*60, hour=minute*60, day=hour*24, week=day*7;
        date1 = new Date(date1);
        date2 = new Date(date2);
        var timediff = date2 - date1;
        if (isNaN(timediff)) return NaN;
        switch (interval) {
            case "years": return date2.getFullYear() - date1.getFullYear();
            case "months": return (
                ( date2.getFullYear() * 12 + date2.getMonth() )
                -
                ( date1.getFullYear() * 12 + date1.getMonth() )
            );
            case "weeks"  : return Math.floor(timediff / week);
            case "days"   : return Math.floor(timediff / day);
            case "hours"  : return Math.floor(timediff / hour);
            case "minutes": return Math.floor(timediff / minute);
            case "seconds": return Math.floor(timediff / second);
            default: return undefined;
        }
    },
    getFirstDayOfWeek(date) {
        const result = new Date(date);
        while (result.getDay() !== 1) {
            result.setDate(result.getDate() - 1);
        }
        return result;
    },
    getLastDayOfWeek(date) {
        const result = new Date(date);
        while (result.getDay() !== 0) {
            result.setDate(result.getDate() + 1);
        }
        return result;
    },
    getNextMonday(date) {
        date.setDate(date.getDate() + ((7 - date.getDay()) % 7 + 1) % 7);
        return date;
    },
    addDays(date, nbDays) {
        date.setDate(date.getDate() + nbDays);
        return date;
    },
    getWeekNumber(date) {
        let oneJan = new Date(date.getFullYear(), 0, 1);
        let numberOfDays = Math.floor((date - oneJan) / (24 * 60 * 60 * 1000));
        let result = Math.ceil(( date.getDay() + 1 + numberOfDays) / 7);
        return result;
    }
}

export { dateService }

