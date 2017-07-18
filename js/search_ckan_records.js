jQuery(document).ready(function() {

  jQuery( ".filter_box" ).select2();

  var host = jQuery('#search_field').data("solr-host");
  var scheme = jQuery('#search_field').data("solr-scheme");
  var path = jQuery('#search_field').data("solr-path");
  var coreUnified = jQuery('#search_field').data("solr-core-unified");
  var currentLang = jQuery('#search_field').data("odm-current-lang");
  var currentCountry = jQuery('#search_field').data("odm-current-country");
  var showRegionalContents = jQuery('#search_field').data("odm-show-regional-contents");

  jQuery('#search_field').autocomplete({
    source: function( request, response ) {
      var suggestionsUrl = scheme + "://" + host  + path + coreUnified + "/suggestions/?q=" + request.term + "&wt=json&json.wrf=callback";
      suggestionsUrl += '&fq=dataset_type:("dataset" OR "library_record" Or "laws_record" OR "agreement")';
      
      jQuery.ajax({
        url: suggestionsUrl,
        dataType: "jsonp",
        jsonpCallback: 'callback',
        contentType: "application/json",
        success: function( data ) {
          var titles = new Array();
          if (data){
            if(data.response){
              $("#search_results").html("");
              var dataResponse = data.response;
              if (dataResponse.docs){
                var docs = dataResponse.docs;
                for (var i = 0; i < docs.length; i ++) {
                  if (docs[i].title){
                    $("#search_results").append('<p><a target="_blank" href="'  + docs[i].permalink + '">[' + docs[i].dataset_type + '] [' + docs[i].capacity + '] ' + docs[i].title + '</a></p>');
                    /*titles.push({
                      'id': docs[i].index_id,
                      'title': docs[i].title,
                      'permalink': docs[i].permalink,
                      'dataset_type': docs[i].dataset_type
                    });*/
                  }
                }
              }
            }
          }
          
        }
      });
    },
    minLength: 2,
    select: function( event, ui ) {
      var terms = this.value.split(" ");
      terms.pop();
      terms.push( ui.item.value );
      this.value = terms.join( " " );
      return false;
    }
  });

});
