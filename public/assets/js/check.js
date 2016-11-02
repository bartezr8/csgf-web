$(document).on('click', '#checkHash', function () {
    var hash = $('#roundHash').val().trim().toLowerCase();
    var random = $('#roundRandom').val().trim() || '';
    var totalbank = $('#totalbank').val().trim() || 0;

    var result = $('#checkResult');

    if (hex_md5(random) == hash.toLowerCase()) {
        var n = Math.ceil( random * parseFloat(totalbank) );
        var text = 'Хэш соответствует числу раунда и секрету. Победный билет: ' + n;
        result.html(text);
    } else {
        var text = 'Хэш не соответствует числу и секрету';
        result.html(text);
    }
});