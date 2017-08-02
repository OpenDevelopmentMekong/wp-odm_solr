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
      $("#search_field").addClass("loading_icon");
      var suggestionsUrl = scheme + "://" + host  + path + coreUnified + "/suggestions/?q=" + request.term + "&wt=json&json.wrf=callback";
      if (currentCountry != 'mekong'){
        suggestionsUrl += "&fq=extras_odm_language:" + currentLang;
        if (showRegionalContents == "on"){
          suggestionsUrl += "+extras_odm_spatial_range:(\"mekong\" OR \"" + currentCountry + "\")";
        }else{
          suggestionsUrl += "+extras_odm_spatial_range:" + currentCountry;
        }
      }
      jQuery.ajax({
        url: suggestionsUrl,
        dataType: "jsonp",
        jsonpCallback: 'callback',
        contentType: "application/json",
        success: function( data ) {
          var titles = new Array();
          if (data){
            if(data.response){
              var dataResponse = data.response;
              if (dataResponse.docs){
                var docs = dataResponse.docs;
                for (var i = 0; i < docs.length; i ++) {
                  if (docs[i].title){
                    var link = docs[i].permalink ? docs[i].permalink : "/dataset/?id=" + docs[i].id;
                    titles.push({
                      'id': docs[i].index_id,
                      'title': docs[i].title,
                      'permalink': link,
                      'dataset_type': docs[i].dataset_type
                    });
                  }
                }
              }
            }
          }
          $("#search_field").removeClass("loading_icon");
          response( titles );
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
  }).autocomplete( "instance" )._renderItem = function( ul, item ) {
    return $( "<li>" )
      .append( "<h5><a href=\"" + item.permalink + "\"><i class=\"" + get_post_type_icon_class(item.dataset_type)+ "\"> " + item.title + "</div></h5>" )
      .appendTo( ul );
  };

  var enteredQuery = jQuery('#search_field').val();
  var splittedQuery = enteredQuery.split(" ");
  var queryTerms = {};
  for (var i = 0; i < splittedQuery.length; i ++) {
    queryTerms[splittedQuery[i]] = {
      "isCorrect": true,
      "suggestions": []
    };
  }
  var spellUrl = scheme + "://" + host  + path + coreUnified + "/spell/?q=" + enteredQuery + "&wt=json&json.wrf=callback";
  if (currentCountry != 'mekong'){
    spellUrl += "&fq=extras_odm_language:" + currentLang + "+extras_odm_spatial_range:(\"mekong\" OR \"" + currentCountry + "\")";
  }
  jQuery.ajax({
    url: spellUrl,
    dataType: "jsonp",
    jsonpCallback: 'callback',
    contentType: "application/json",
    success: function( data ) {
      if (data){
        if(data.spellcheck){
          var maxNumSuggestions = 0;
          var spellcheck = data.spellcheck;
          if (spellcheck.suggestions){
            var suggestions = spellcheck.suggestions;
            for (var i = 1; i < suggestions.length; i += 2) {
              if (suggestions[i].suggestion){
                var suggestion = suggestions[i].suggestion;
                var wrongTerm = suggestions[i-1];
                queryTerms[wrongTerm]["isCorrect"] = false;
                for (var j = 0; j < suggestion.length; j++) {
                  queryTerms[wrongTerm]["suggestions"].push(suggestion[j]);
                  if (j >= maxNumSuggestions ){
                    maxNumSuggestions = j + 1 ;
                  }
                }
              }
            }
            if (maxNumSuggestions > 0){
              for (var k = 0; k< maxNumSuggestions; k++){
                var suggestedString = "";
                for (var l = 0; l< splittedQuery.length; l++){
                  var term = queryTerms[splittedQuery[l]];
                  if (term["isCorrect"]){
                    suggestedString += splittedQuery[l];
                  }else{
                    suggestedString += term["suggestions"][k];
                  }
                  if (l < splittedQuery.length - 1){
                    suggestedString += " "
                  }
                }
                jQuery('#spell').append('<a href="/?s=' + suggestedString + '"> ' + suggestedString + '</a>');
                if (k < maxNumSuggestions - 1){
                  jQuery('#spell').append(',');
                }
              }
              jQuery('#spell').show();
            }
          }
        }
      }
    }
  });

});
