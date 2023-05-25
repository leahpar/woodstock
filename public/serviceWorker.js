// Doit être à la racine de public
const cacheName = "woodstock-v1"
const offlineFallbackPage = "offline.html"
const assets = [
    "/",
    "/js/pwa.js",
]

self.addEventListener("install", installEvent => {
    installEvent.waitUntil(
        caches.open(cacheName).then(cache => {
            cache.addAll(assets)
        })
    )
})

self.addEventListener("fetch", fetchEvent => {
    fetchEvent.respondWith(
        caches.match(fetchEvent.request).then(res => {
            return res || fetch(fetchEvent.request)
        })
    )
})
