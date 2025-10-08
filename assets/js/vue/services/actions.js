import axios from "axios";

export function toogleField(id, value, field, actionURL, entityType) {
    axios.post(actionURL,
        { id: id, value: value, entityType: entityType, field: field}
    ).then((response) => {}).catch((error) => {
        let message = "Une erreur s'est produite !";
        if(typeof error.response.data.message != "undefined") {
            message = error.response.data.message;
        }
        this.$toast.open({
            message: message,
            type: "error",
            duration: 2000,
            dismissible: true,
            position: 'top-right',
        });
    });
}