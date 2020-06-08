export const FindElement = (url) => {
    let elements = document.querySelectorAll('.user-media-like')

    elements.forEach(function(element) {
        let token = element.getAttribute('data-user-media-token')

        url.searchParams.append('topic', 'https://bubble.lgbt/user-media/' + token + '/like')
        url.searchParams.append('topic', 'https://bubble.lgbt/user/1211aol-fr')
    })
}

export const EventSourceListener = (eventSource) => {
    eventSource.addEventListener('user_media_like', function(e) {
        let data = JSON.parse(e.data)
        let elements = document.querySelectorAll('.user-media-like[data-user-media-token="' + data.token + '"]')

        elements.forEach(function(element) {
            let badge = element.querySelector('.badge')
            badge.innerText = data.count
        })
    }, false)

    eventSource.addEventListener('user_media_like_click', function(e) {
        let data = JSON.parse(e.data)
        let elements = document.querySelectorAll('.user-media-like[data-user-media-token="' + data.token + '"]')

        elements.forEach(function(element) {
            if (true === data.isLiked) {
                element.classList.replace('btn-secondary', 'btn-red')
            } else {
                element.classList.replace('btn-red', 'btn-secondary')
            }

            element.setAttribute('data-original-title', data.title)
        })
    }, false)
}
