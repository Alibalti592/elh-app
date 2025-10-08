import encHex from "crypto-js/enc-hex";
import aes from "crypto-js/aes";
import padZeroPadding from "crypto-js/pad-zeropadding";

const utilsService = {
    debounceAction(timeout, action, delay) {
        if (timeout) {
            clearTimeout(timeout);
        }
        timeout = setTimeout(() => {
            action();
        }, delay);
    },
    encryptFront(text) {
        let key = encHex.parse("dba92da2fc4f8c68951f2f2e19cd7b2c");
        let iv =  encHex.parse("9cb8d70a5d525b31916c793cb0bbd6e3");
        return  aes.encrypt(text, key, {iv:iv, padding:padZeroPadding}).toString();
    }
}
export {utilsService}