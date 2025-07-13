import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉')

document.addEventListener('DOMContentLoaded', () => {
    initDataTables()
})

function initDataTables() {
    document.querySelectorAll('.js-datatable').forEach(el => new DataTable('#' + el.id))
}

function Modal(url, title, callback){

}