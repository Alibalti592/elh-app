//apllied on array directly
Array.prototype.move = function(from, to) {
    this.splice(to, 0, this.splice(from, 1)[0]);
    return this;
};

function round(num, decimalPlaces = 0) {
    let p = Math.pow(10, decimalPlaces);
    let n = (num * p) * (1 + Number.EPSILON);
    return Math.round(n) / p;
}

const ArrayObjectService = {
    cloneOject(object) {
        return JSON.parse(JSON.stringify(object));
    },
    moveUp(arr, index) {
        if (index > 0) {
            let el = arr[index];
            arr[index] = arr[index - 1];
            arr[index - 1] = el;
        }
        return arr;
    },
    moveDown(arr, index) {
        if (index !== -1 && index < arr.length - 1) {
            let el = arr[index];
            arr[index] = arr[index + 1];
            arr[index + 1] = el;
        }
        return arr;
    },
    indexWhere(array, conditionFn) {
        const item = array.find(conditionFn)
        return array.indexOf(item)
    },
    //remove array in array
    arrayRemove(array, key, value) {
        return array.filter(function(element) {
            return element[key] != value;
        });
    },
    arrayRemoveValue(array, value) {
        return array.filter(function(element) {
            return element != value;
        });
    },
    calculateAverageAndMax(arr,start, end, precision, removeZeros = false, timeSerie = false){
        let total = 0;
        let max = 0;
        let min = 1000;
        let nbPoints = 1;
        for(let i = start; i <= end; i++){
            let val = arr[i];
            if(typeof arr[i] != 'undefined' && (!removeZeros || val != 0)) {
                if(timeSerie && typeof timeSerie[i] != 'undefined' && typeof timeSerie[i + 1] != 'undefined') {
                    let numberOfSeconds = timeSerie[i + 1] -  timeSerie[i];
                    total+= (val*numberOfSeconds);
                    nbPoints = nbPoints + numberOfSeconds;
                } else {
                    total+= val;
                    nbPoints++;
                }
            }
            if(val > max) {
                max = val;
            }
            if(val < min) {
                min = val;
            }
        }
        let diff = (end-start)+1;
        let avg = round(total/nbPoints, precision);
        return {
            average : avg,
            max : max,
            min : min,
        };
    }
}

export { ArrayObjectService }

