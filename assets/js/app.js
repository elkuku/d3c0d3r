import $ from 'jquery'

import 'bootstrap'
import 'bootswatch/dist/darkly/bootstrap.min.css';

require('open-iconic/font/css/open-iconic-bootstrap.css')

import '../css/app.css';

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})

$(function () {
    $('[data-toggle="popover"]').popover()
})
