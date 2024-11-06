$('span.indexEntry').click(function(event){
    if($(this).hasClass('indexEntrySelected')){
        $('#indexEntryId').val('');
        $(this).removeClass('indexEntrySelected');
    } else {
        $('#indexEntryId').val($(this).attr('id').replace('i_', ''));
        $('span.indexEntrySelected').removeClass('indexEntrySelected');
        $(this).addClass('indexEntrySelected');
    }
});

$('span.correction').click(function(event){
    if($(this).hasClass('correctionSelected')){
        $('#correctionId').val('');
        $(this).removeClass('correctionSelected');
        } else {
        $('#correctionId').val($(this).attr('id').replace('c_', ''));
        $('span.correctionSelected').removeClass('correctionSelected');
        $(this).addClass('correctionSelected');
        }
});

$('span.release').click(function(event){
    console.log($(this).attr('id').replace(/i_\d+_c_/, ''));
    $('#releaseCorrectionId').val($(this).attr('id').replace(/i_\d+_c_/, ''));
    $('#releaseIndexEntryId').val($(this).attr('id').replace(/i_/, '').replace(/_c_\d+/, ''));
    $('#release').click();
});
