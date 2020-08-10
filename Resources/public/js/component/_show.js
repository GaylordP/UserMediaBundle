import $ from 'jquery'
import {
    EventSourceListener as UserMediaLikeEventSourceListener,
    FindElement as UserMediaLikeFindElement
} from '../mercure/UserMediaLike'
import {
    EventSourceListener as UserMediaCommentEventSourceListener,
    FindElement as UserMediaCommentFindElement
} from '../mercure/UserMediaComment'

$(document).ready(() => {
    document.addEventListener('click', (e) => {
        let show = e.target.closest('.user-media-show')

        if (null !== show) {
            e.preventDefault()

            let httpRequest = new XMLHttpRequest()
            httpRequest.open('GET', show.getAttribute('href'))
            httpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
            httpRequest.send()
            httpRequest.onreadystatechange = () => {
                if (
                  httpRequest.readyState === XMLHttpRequest.DONE
                    &&
                  httpRequest.status === 200
                ) {
                    let json = JSON.parse(httpRequest.responseText)

                    BootstrapModal(json.title, json.body)

                    let modalBody = document.querySelector('.modal-body')

                    let url = new URL('http://localhost:3000/.well-known/mercure')
                    UserMediaCommentFindElement(url, modalBody)
                    UserMediaLikeFindElement(url, modalBody)
                    let eventSource = new EventSource(url, {
                        withCredentials: true
                    })

                    /*
                    Attention, seul un des évènements doit être écouter, car l'évènement sur les cliques est lié à l'utilisateur et non le LIKE
                     */
                    UserMediaCommentEventSourceListener(eventSource, modalBody)
                    UserMediaLikeEventSourceListener(eventSource, modalBody)

                    $('#bootstrapModal').on('hidden.bs.modal', function (e) {
                        eventSource.close()
                    })
                }
            }
        }
    })
})
