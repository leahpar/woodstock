// Doit être à la racine de public

self.addEventListener("install", installEvent => {
    // installEvent.waitUntil(
    //     caches.open(cacheName).then(cache => {
    //         cache.add(offlineFallbackPage);
    //     })
    // )
    console.log("[PWA] Install done");
})

// self.addEventListener("fetch", fetchEvent => {
//     fetchEvent.respondWith(
//         caches.match(fetchEvent.request).then(res => {
//             return res || fetch(fetchEvent.request)
//         })
//     )
// })

// This is an event that can be fired from your page to tell the SW to update the offline page
// (WIP)
// self.addEventListener("refreshOffline", event => {
//     const offlinePageRequest = new Request(offlineFallbackPage);
//     fetch(offlineFallbackPage).then(function (response) {
//         return caches.open(cacheName).then(function (cache) {
//             console.log("[PWA Builder] Offline page updated from refreshOffline event: " + response.url);
//             return cache.put(offlinePageRequest, response);
//         });
//     });
// });

// Register event listener for the 'push' event.
self.addEventListener('push', event => {
    // Retrieve the textual payload from event.data (a PushMessageData object).
    // Other formats are supported (ArrayBuffer, Blob, JSON), check out the documentation
    // on https://developer.mozilla.org/en-US/docs/Web/API/PushMessageData.
    const payload = event.data ? event.data.json() : [];

    // Keep the service worker alive until the notification is created.
    event.waitUntil(
        // Show a notification with title 'ServiceWorker Cookbook' and use the payload
        // as the body.
        self.registration.showNotification(
            payload.title, {
                icon: '/img/icon.png',
                body: payload.message,
            })
    );
});

