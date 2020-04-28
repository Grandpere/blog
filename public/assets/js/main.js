var tags = new Bloodhound({
    prefetch: '../api/v1/tags.json',
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('title'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
})

$('.tag-input').tagsinput({
    tagClass: 'badge badge-secondary', // because label label-info not supported in bootstrap4
    typeaheadjs: [{
        highlights: true
    }, {
        name: 'tags',
        display: 'title',
        value: 'title',
        source: tags
    }]
})