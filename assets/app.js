import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉')

document.addEventListener('turbo:load', function () {
    console.log("Turbo page loaded");

    initDataTables()

    document.querySelectorAll('form').forEach(el => {
        if (el.dataset.boundSubmit) {
            return
        }

        el.addEventListener('submit', function (e) {

            const buttons = e.currentTarget.querySelectorAll('button[type="submit"]')
            if (!buttons.length) {
                return
            }

            buttons.forEach(btn => btn.disabled = 'disabled')
        })

        el.dataset.boundSubmit = 'true'
    })
})

function initDataTables() {
    document.querySelectorAll('.js-datatable').forEach(function (el) {
        new DataTable('#' + el.id, {
            responsive: true,
            "pageLength": 25,
            "bLengthChange": false,
            order: [] // remove default sorting
        })
    })
}