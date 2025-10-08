
const mercureHub = {
    getEventSource(hubUrl, topic) {
        let url = new URL(hubUrl);
        url.searchParams.append('topic', topic);
        return  new EventSource(url.toString());
    }
}

export { mercureHub }