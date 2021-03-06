//import $ from 'jquery'; // works without jquery import because autoProvidejQuery() is enabled?
//import 'bootstrap';     // works without bootstrap import because autoProvidejQuery() is enabled?
import 'bootstrap-tagsinput';
import Bloodhound from 'typeahead.js';

import 'bootstrap-tagsinput/dist/bootstrap-tagsinput.css';
import 'bootstrap-tagsinput/dist/bootstrap-tagsinput-typeahead.css';
import  '../css/components/_bootstrap_tag-input.scss';


$(document).ready(function() {
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

    $('.bootstrap-tagsinput').addClass('form-control bti'); // add form-control class for bootstrap4 class & custom bti class for height:auto
});