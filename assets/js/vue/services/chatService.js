import axios from "axios";
import {messageService} from "@/services/message";

const chatService = {
    async openSimpleThread(userId, eventId = null, type = null) {
        return new Promise((resolve, reject) => {
            axios.get("/v-chat-load-simple-thread", {params: { user: userId, event: eventId, type: type }}).then(function(response){
                if(response.data.thread != null) {
                    openThreadWidget(response.data.thread);
                    resolve(true, response.data);
                }
            }).catch(function (error) {
                messageService.showMessageFromResponse(error.response);
                resolve(false, error);
            });
        });
    },
    openThread(thread) {
        if(thread != null) {
            openThreadWidget(thread);
            return true;
        }
        return false;
    },
}

export { chatService }