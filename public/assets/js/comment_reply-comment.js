$('.reply').click(function(e){
    var $this = $(this);
    var parentId = $this.data('commentid');

    $('#parentId').val(parentId);
})