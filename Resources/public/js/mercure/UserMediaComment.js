export const FindElement = (url) => {
    let elements = document.querySelectorAll('.user-media-comment')

    elements.forEach((element) => {
        let token = element.getAttribute('data-user-media-token')

        url.searchParams.append('topic', 'https://bubble.lgbt/user-media/' + token + '/comment')
    })
}

export const EventSourceListener = (eventSource) => {
    eventSource.addEventListener('user_media_comment', (e) => {
        let data = JSON.parse(e.data)
        let elements = document.querySelectorAll('.user-media-comment[data-user-media-token="' + data.token + '"]')

        elements.forEach((element) => {
            let badge = element.querySelector('.badge')
            badge.innerText = data.count
        })

        let show = document.querySelector('#user-media-' + data.token)

        if (null !== show) {
            let userMediaNoComment = show.querySelector('.user-media-no-comment')
            if (null !== userMediaNoComment) {
                userMediaNoComment.parentNode.removeChild(userMediaNoComment)
            }

            let titleBadge = show.querySelector('h2').querySelector('.badge')
            titleBadge.innerText = data.count

            let commentsContainer = show.querySelector('.comments')
            let commentHtml = document.createElement('div')
            commentHtml.innerHTML = data.commentHtml
            commentsContainer.appendChild(commentHtml.firstChild)
        }
    }, false)
}
