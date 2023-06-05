function askPermission() {
    return new Promise(function (resolve, reject) {
        const permissionResult =
            Notification.requestPermission(function (result) {
                resolve(result);
            });

        if (permissionResult) {
            permissionResult.then(resolve, reject);
        }
    }).then(function (permissionResult) {
        if (permissionResult !== 'granted') {
            throw new Error("We weren't granted permission.");
        }
    });
}

function subscribeUserToPush() {
    return askPermission()
        .then((permissionResult) => {
            navigator.serviceWorker
                .register('/service-worker.js')
                .then((registration) => {
                    registration.pushManager.getSubscription()
                        .then(async function (subscription) {
                            if (subscription) return subscription;

                            const response = await fetch('/vapidPublicKey');
                            const vapidPublicKey = await response.text();
                            const convertedVapidKey = urlBase64ToUint8Array(vapidPublicKey);

                            return registration.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: convertedVapidKey
                            });
                        });
                })
                .then((subscription) => {
                    //console.log('User is subscribed:', subscription);
                    fetch('/register', {
                        method: 'post',
                        headers: {
                            'Content-type': 'application/json'
                        },
                        body: JSON.stringify({
                            subscription: subscription
                        }),
                    });
                });
        });
}

// (Web-Push) base64 to Uint
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}
