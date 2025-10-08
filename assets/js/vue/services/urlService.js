const urlService = {
    getUrlParam(key) {
        let urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(key);
    },
    //String url
    updateURLParam(uri, key, value) {
        let re = new RegExp("([?&])" + key + "=.*?(&|#|$)", "i");
        if (uri.match(re)) {
            return uri.replace(re, '$1' + key + "=" + value + '$2');
        } else {
            let hash =  '';
            if( uri.indexOf('#') !== -1 ){
                hash = uri.replace(/.*#/, '#');
                uri = uri.replace(/#.*/, '');
            }
            let separator = uri.indexOf('?') !== -1 ? "&" : "?";
            return uri + separator + key + "=" + value + hash;
        }
    },
    removeURLParam(urlString, key) {
        if (typeof URLSearchParams !== 'undefined') { //modern browsers
            let urlObj = new URL(urlString);
            urlObj.searchParams.delete(key);
            return urlObj.toString();
        } else {
            let urlparts = urlString.split('?');
            if (urlparts.length >= 2) {
                let prefix = encodeURIComponent(key) + '=';
                let pars = urlparts[1].split(/[&;]/g);
                for (let i = pars.length; i-- > 0;) {
                    if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                        pars.splice(i, 1);
                    }
                }
                return urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : '');
            }
            return urlString;
        }
    },
    copyClipBoard(link) {
        if (!navigator.clipboard){ //deprecated version, old browsers
            const el = document.createElement('textarea');
            el.value = link;
            el.setAttribute('readonly', '');
            el.style.position = 'fixed';
            el.style.left = '-9999px';
            document.body.appendChild(el);
            const selected =  document.getSelection().rangeCount > 0  ? document.getSelection().getRangeAt(0) : false;
            el.select();
            el.focus();
            try {
                document.execCommand('copy');
            } catch (err) {
                alert("Impossible de copier le lien");
            }
            document.body.removeChild(el);
            if(selected) {
                document.getSelection().removeAllRanges();
                document.getSelection().addRange(selected);
            }
        } else{
            navigator.clipboard.writeText(link).then(function(){
                //success
            }).catch(function() {
                alert("Impossible de copier le lien"); // error
            });
        }

    }
}

export { urlService }

