import $ from 'jquery'
import Dropzone from 'dropzone/dist/dropzone'
import Sortable from 'sortablejs'
import 'dropzone/dist/dropzone.css'
import '../css/waypoint-edit.css'
Dropzone.autoDiscover = false

$(document).ready(function() {
    const referenceList = new ReferenceList($('.js-reference-list'))

    initializeDropzone(referenceList);
})

class ReferenceList {
    constructor($element) {
        this.$element = $element
        this.sortable = Sortable.create(this.$element[0], {
            handle: '.drag-handle',
            animation: 150,
            onEnd: () => {
                $.ajax({
                    url: this.$element.data('url')+'/reorder',
                    method: 'POST',
                    data: JSON.stringify(this.sortable.toArray())
                });
            }
        });
        this.references = []
        this.render()

        this.$element.on('click', '.js-reference-delete', (event) => {
            this.handleReferenceDelete(event);
        });

        this.$element.on('blur', '.js-edit-filename', (event) => {
            this.handleReferenceEditFilename(event);
        });

        $.ajax({
            url: this.$element.data('url')
        }).then(data => {
            this.references = data
            this.render()
        })
    }

    addReference(reference) {
        this.references.push(reference);
        this.render();
    }

    handleReferenceDelete(event) {
        const $li = $(event.currentTarget).closest('.list-group-item');
        const id = $li.data('id');
        $li.addClass('disabled');

        $.ajax({
            url: '/waypoint/references/'+id,
            method: 'DELETE'
        }).then(() => {
            this.references = this.references.filter(reference => {
                return reference.id !== id;
            });
            this.render();
        });
    }

    handleReferenceEditFilename(event) {
        const $li = $(event.currentTarget).closest('.list-group-item');
        const id = $li.data('id');

        const reference = this.references.find(reference => {
            return reference.id === id;
        });

        reference.originalFilename = $(event.currentTarget).val();

        $.ajax({
            url: '/waypoint/references/'+id,
            method: 'PUT',
            data: JSON.stringify(reference)
        });
    }

    render() {
        const itemsHtml = this.references.map(reference => {
            return `
<li class="list-group-item d-flex justify-content-between align-items-center" data-id="${reference.id}">
<span class="drag-handle fa fa-reorder">O</span>
    <input type="text" value="${reference.originalFilename}" class="form-control js-edit-filename" style="width: auto;">
    
    <span>
        <a href="/waypoint/references/${reference.id}/download"><span class="fa fa-download">D</span></a>
        <button class="js-reference-delete btn btn-link"><span class="fa fa-trash">X</span></button>
    </span>
</li>
`
        })
        this.$element.html(itemsHtml.join(''))
    }
}

/**
 * @param {ReferenceList} referenceList
 */
function initializeDropzone(referenceList) {
    const formElement = document.querySelector('.js-reference-dropzone')
    if (!formElement) {
        return;
    }
    const dropzone = new Dropzone(formElement, {
        paramName: 'reference',
        init: function () {
            this.on('success', function (file, data) {
                referenceList.addReference(data)
            })
            this.on('error', function (file, data) {
                if (data.detail) {
                    this.emit('error', file, data.detail)
                }
            })
        }
    })
}
