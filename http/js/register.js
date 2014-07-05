function distribution_click() {
    console.log('dwedwedw');
    if ($('#distribution').val() == 'gallery') {
        var res = $.ajax({
            url: 'gallery.php',
            async: false,
        });
        if (!res.responseText) {
            alert('Failed to get gallery');
            return;
        }
        var data = eval('(' + res.responseText + ')');
        var html = '<p><span>Gallery</span></p>';
        html += '<div>';
        if (data.length == 0) {
            html += 'Sorry, the gallery is empty.';
        }
        for (var id in data) {
            var row = data[id];
            html += '<div>';
            html += '  <input type="radio" style="display:none" id="gallery-input-' + id + '" name="gallery-id" value="' + id + '" />';
            html += '  <div class="gallery-option" id="gallery-option-' + id + '" onclick="setGallery(' + id + ')">';
            html += '    <label for="gallery-input-' + id + '">';
            html += '      <p><strong>' + row.name + '</strong> (based on ' + row.distribution + ')</p>';
            html += '      <p>' + row.description + '</p>';
            html += '    </label>';
            html += '  </div>';
            html += '</div>';
        }
        html += '</div>';
        $('#gallery').html(html);
        $('#gallery').show();
    } else {
        $('#gallery').hide();
        $('#galleryfail').html('');
    }
}

function setGallery(id) {
    console.log(id);
    $('.gallery-option').removeClass('gallery-option-selected');
    $('#gallery-option-' + id).addClass('gallery-option-selected');
}
