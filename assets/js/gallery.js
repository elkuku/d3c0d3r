import $ from 'jquery'

require('../css/gallery.css')
require('form-serializer')

let selectedPoints = []
let id, img, modal

function updateSelectedPoints() {
    // $('#selectedPoints').html(selectedPoints)
}

$('#galleryModal').find('.btnSelect').on('click', function () {
    const index = selectedPoints.indexOf(id)

    if (index > -1) {
        selectedPoints.splice(index, 1)
        img.removeClass('wpSelected')
        modal.modal('hide')
    } else {
        selectedPoints.push(id)
        img.addClass('wpSelected')
        modal.modal('hide')
    }

    updateSelectedPoints()
})

$('#decodeSelected').on('click', function () {
    if (!selectedPoints.length) {
        alert('Please select some points first!')
    } else if (1 === selectedPoints.length) {
        alert('OK now select some more points...')
    } else {
        window.location.href = '/collection/d3c0de?ids=' + selectedPoints.join(',')
    }
})

$('.card-img-top').on('click', function () {
    const jsData = $('#js-data')
    console.log(jsData.data('uploadedAssetPath'))
    modal = $('#galleryModal')
    img = $(this)
    id = $(this).data('id')

    modal.find('.btnSelect').text(selectedPoints.indexOf(id) > -1 ? 'Deselect' : 'Select')
    modal.find('h5').html($(this).data('originalTitle'))
    // modal.find('img').attr('src', '/wp_images/' + $(this).data('guid') + '.jpg')
    modal.find('img').attr('src', jsData.data('uploadedAssetPath') + '/wp_images/' + $(this).data('guid') + '.jpg')
    modal.find('.intelLink').attr('href', $(this).data('intelLink'))
    modal.find('.editLink').attr('href', $(this).data('editLink'))
    let detailsLink = $(this).data('detailsLink')
    $.get(detailsLink, function (data) {
        const form = modal.find('.detailsForm')
        form.html(data)
        form.find('button').on('click', function () {
            let data = form.find('form').serializeObject()
            data.redirectUri = window.location.href
            $.post(detailsLink,
                data,
                function (result) {
                    location.reload()
                }
            )
            return false
        })
    })

    modal.modal()
})
