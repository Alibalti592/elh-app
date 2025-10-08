import {useToast} from 'vue-toast-notification';
const $toast = useToast();
import 'vue-toast-notification/dist/theme-default.css';

const messageService = {

    showMessage(messageStatus, message) {
        $toast.open({
            message: message,
            type: messageStatus,
            duration: 3000,
            dismissible: true,
            position: 'top-right',
        });
    },

    showMessageFromResponse(response) {
        if(typeof response != "undefined" && typeof response.data.messages != "undefined") {
            response.data.messages.forEach((message) => {
                this.showMessage(message.type, message.text);
            });
        } else if(typeof response != 'undefined' && response.status == 200) {
            let message = 'Mise à jour effectuée !';
            if(typeof response.data.message != "undefined") {
                message = response.data.message;
            }
            this.showMessage('success', message);
        } else {
            let message = "Une erreur s'est produite  !";
            if(typeof response != 'undefined' && typeof response.data.message != "undefined") {
                message = response.data.message;
            }
            this.showMessage('error', message);
        }
    }
}

export {messageService}