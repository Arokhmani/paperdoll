(function ($) {
'use strict';

$(function () {
$('[data-sortable]').sortable();

$(document).on('click', '.paperdoll-media-upload', function (event) {
event.preventDefault();
const $btn = $(this);
const $container = $btn.closest('div');
const $input = $container.find('.paperdoll-media-id').first();
const $preview = $container.find('.paperdoll-image-preview').first();

const frame = wp.media({
title: 'Pilih Gambar',
button: { text: 'Gunakan gambar ini' },
multiple: false
});

frame.on('select', function () {
const attachment = frame.state().get('selection').first().toJSON();
$input.val(attachment.id);
$preview.html('<img src="' + attachment.url + '" alt="preview" />');
});

frame.open();
});

$(document).on('change', '.paperdoll-check-all', function () {
const checked = $(this).is(':checked');
$(this).closest('table').find('tbody input[type="checkbox"]').prop('checked', checked);
});

$('#paperdoll-add-carousel-item').on('click', function () {
const $picker = $('#paperdoll-carousel-product-picker');
const productId = $picker.val();
const title = $picker.find(':selected').data('title');
if (!productId || !title) {
return;
}

if ($('#paperdoll-carousel-list input[value="' + productId + '"]').length > 0) {
return;
}

const $li = $('<li/>');
const $input = $('<input/>', {
type: 'hidden',
name: 'carousel_product_ids[]',
value: productId
});
const $title = $('<span/>').text(String(title));
const $remove = $('<button/>', {
type: 'button',
class: 'button-link-delete paperdoll-remove-sort-item',
text: '×'
});
$li.append($input, $title, $remove);
$('#paperdoll-carousel-list').append($li);
});

$(document).on('click', '.paperdoll-remove-sort-item', function () {
$(this).closest('li').remove();
});
});
})(jQuery);
