//import $ from 'jquery'; // works without jquery import because autoProvidejQuery() is enabled?

$('.reply').click(function(e){
    var $this = $(this);
    var parentId = $this.data('commentid');

    $('#parentId').val(parentId);
})