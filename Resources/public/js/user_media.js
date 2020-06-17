/*
    Show
 */
let shows = document.querySelectorAll('.user-media-show')

shows.forEach(function(element) {
    let _this = element

    element.onclick = (link) => {
        link.preventDefault()

        console.log('ok')
    }
})

/*
    Likes
 */
let likes = document.querySelectorAll('.user-media-like')

likes.forEach(function(element) {
    let _this = element

    element.onclick = (link) => {
        link.preventDefault()

        let httpRequest = new XMLHttpRequest()
        httpRequest.open('GET', _this.getAttribute('href'))
        httpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
        httpRequest.send()
    }
})

/*
    Comments
 */
export const FormCommentListener = () => {
    let formComment = document.querySelector('form[name="user_media_comment"]')

    if (null !== formComment) {
        formComment.onsubmit = (form) => {
            form.preventDefault()

            let user_media_comment_content = document.querySelector('#user_media_comment_content')
            let user_media_comment__token = document.querySelector('#user_media_comment__token')

            let httpRequest = new XMLHttpRequest()
            httpRequest.open('POST', formComment.getAttribute('action'))
            httpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
            httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
            httpRequest.send(user_media_comment_content.getAttribute('name') + '=' + encodeURIComponent(user_media_comment_content.value) + '&' + user_media_comment__token.getAttribute('name') + '=' + user_media_comment__token.value)
            httpRequest.onreadystatechange = () => {
                if (
                  httpRequest.readyState === XMLHttpRequest.DONE
                    &&
                  httpRequest.status === 200
                ) {
                    let json = JSON.parse(httpRequest.responseText)

                    if ('success' === json.status) {
                        let errorsMessages = formComment.querySelectorAll('.invalid-feedback')

                        errorsMessages.forEach(function(error) {
                            error.remove()
                        })

                        let errorsFormClass = formComment.querySelectorAll('.form-control.is-invalid')

                        errorsFormClass.forEach(function(error) {
                            error.classList.remove('is-invalid')
                        })

                        formComment.reset()
                    } else if ('form_error' === json.status) {
                        let formHtml = document.createElement('div')
                        formHtml.innerHTML = json.formHtml
                        formComment.replaceWith(formHtml.firstChild)

                        FormCommentListener()
                    }
                }
            }
        }
    }
}

FormCommentListener()
