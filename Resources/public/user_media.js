import {
    FindElement as UserMediaCommentFindElement,
    EventSourceListener as UserMediaCommentEventSourceListener
} from './js/mercure/UserMediaComment'
import {
    FindElement as UserMediaLikeFindElement,
    EventSourceListener as UserMediaLikeEventSourceListener
} from './js/mercure/UserMediaLike'

const url = new URL('http://localhost:3000/.well-known/mercure')
UserMediaLikeFindElement(url)
UserMediaCommentFindElement(url)

const eventSource = new EventSource(url, {
    withCredentials: true
})

UserMediaLikeEventSourceListener(eventSource)
UserMediaCommentEventSourceListener(eventSource)
