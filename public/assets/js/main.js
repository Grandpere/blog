const url = $('.tag-input').data('autocomplete-url');

var tags = new Bloodhound({
    prefetch: url,
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('title'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
});

$('.tag-input').tagsinput({
    tagClass: 'badge badge-secondary', // because label label-info not supported in bootstrap4
    confirmKeys: [13, 44],
    trimValue: true,
    typeaheadjs: [{
        highlights: true
    }, {
        name: 'tags',
        displayKey: 'title',
        valueKey: 'title',
        source: tags
    }]
});