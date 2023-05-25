// Doit être à la racine de public
const cacheName = "woodstock-v1"
const offlineFallbackPage = "offline.html"
const assets = [
    "/",
    "/index.html",
    "/js/pwa.js",
    //offlineFallbackPage,
    // "/css/style.css",
    // TODO : css, icons, images, fonts, etc.
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
