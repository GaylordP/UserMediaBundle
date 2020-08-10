export const FindElement = (url, dom) => {
    if (undefined === dom) {
        dom = document
    }

    let elements = dom.querySelectorAll('.user-media-like')

    elements.forEach((element) => {
        let token = element.getAttribute('data-user-media-token')

        url.searchParams.append('topic', 'https://bubble.lgbt/user-media/' + token + '/like')
    })
}

export const EventSourceListener = (eventSource, dom) => {
    if (undefined === dom) {
        dom = document
    }

    eventSource.addEventListener('user_media_like', (e) => {
        let data = JSON.parse(e.data)
        let elements = dom.querySelectorAll('.user-media-like[data-user-media-token="' + data.token + '"]')

        elements.forEach((element) => {
            let badge = element.querySelector('.badge')
            badge.innerText = data.count
        })
    }, false)

    eventSource.addEventListener('user_media_like_click', (e) => {
        let data = JSON.parse(e.data)
        let elements = dom.querySelectorAll('.user-media-like[data-user-media-token="' + data.token + '"]')

        elements.forEach((element) => {
            if (true === data.isLiked) {
                element.classList.replace('btn-secondary', 'btn-red')
            } else {
                element.classList.replace('btn-red', 'btn-secondary')
            }

            element.setAttribute('data-original-title', data.title)
        })
    }, false)
}
