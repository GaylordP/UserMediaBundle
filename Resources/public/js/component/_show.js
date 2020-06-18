import $ from "jquery";

$(document).ready(() => {
    let shows = document.querySelectorAll('.user-media-show')

    shows.forEach((element) => {
        let _this = element

        element.onclick = (link) => {
            link.preventDefault()

            let httpRequest = new XMLHttpRequest()
            httpRequest.open('GET', _this.getAttribute('href'))
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
