document.addEventListener('click', (e) => {
    let like = e.target.closest('.user-media-like')

    if (null !== like) {
        e.preventDefault()

        let httpRequest = new XMLHttpRequest()
        httpRequest.open('GET', like.getAttribute('href'))
        httpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
        httpRequest.send()
    }
}, false)
