import $ from 'jquery'

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
                }
            }
        }
    })
})
