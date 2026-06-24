(function () {
    const root = document.getElementById('event-participants-root');

    if (!root) {
        return;
    }

    const eventId = Number(root.dataset.eventId);

    function updateParticipants(count) {
        const counter = document.getElementById(`participants-count-${eventId}`);
        if (counter) {
            counter.textContent = count;
        }
    }

    function connect() {
        const protocol = window.location.protocol === 'https:' ? 'wss' : 'ws';
        const wsUrl = `${protocol}://${window.location.host}/ws`;

        const socket = new WebSocket(wsUrl);

        socket.onmessage = function (message) {
            try {
                const data = JSON.parse(message.data);

                if (
                    data.type === 'event_participants_updated' &&
                    Number(data.event_id) === eventId
                ) {
                    updateParticipants(data.participants_count);
                }
            } catch (error) {
                console.error('Invalid WebSocket message', error);
            }
        };

        socket.onclose = function () {
            setTimeout(connect, 3000);
        };
    }

    connect();
})();
