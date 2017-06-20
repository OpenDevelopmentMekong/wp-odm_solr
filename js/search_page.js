jQuery(document).ready(function() {

  jQuery( ".filter_box" ).select2();
  
  var host = jQuery('#search_field').data("solr-host");
  var scheme = jQuery('#search_field').data("solr-scheme");
  var path = jQuery('#search_field').data("solr-path");
  var core_unified = jQuery('#search_field').data("solr-core-unified");
  
  jQuery('#search_field').autocomplete({
    source: function( request, response ) {
      var suggestions_url = scheme + "://" + host  + path + core_unified + "/suggestions";
      jQuery.ajax({
        url: suggestions_url,
        data: {'wt':'json', 'q':request.term, 'json.wrf': 'callback'},
        dataType: "jsonp",
        jsonpCallback: 'callback',
        contentType: "application/json",
        success: function( data ) {
          console.log("unified autocompletion suggestions: " + JSON.stringify(data));
          var options = [];
          if (data){
            if(data.spellcheck){
              var spellcheck = data.spellcheck;
              if (spellcheck.suggestions){
                var suggestions = spellcheck.suggestions;
                if (suggestions[1]){
                  var suggestionObject = suggestions[1];
                  options = suggestionObject.suggestion;
                }
              }
            }
          }
          response( options );
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
  
  var entered_query = jQuery('#search_field').val();
  var spell_url = scheme + "://" + host  + path + core_unified + "/spell";
  jQuery.ajax({
    url: spell_url,
    data: {'wt':'json', 'q':entered_query, 'json.wrf': 'callback'},
    dataType: "jsonp",
    jsonpCallback: 'callback',
    contentType: "application/json",
    success: function( data ) {
      console.log("unified autocompletion spell: " + JSON.stringify(data));
      var options = [];
      if (data){
        if(data.spellcheck){
          var spellcheck = data.spellcheck;
          if (spellcheck.suggestions){
            var suggestions = spellcheck.suggestions;
            var suggestedString = null;
            for (var i = 1; i < suggestions.length; i += 2) {
              if (suggestions[i].suggestion){
                var suggestion = suggestions[i].suggestion;
                if (suggestion[0]){
                  suggestedString += suggestion[0];
                  if (i < suggestions.length - 1){
                    suggestedString += " "
                  }
                }
              }
            }
            if (suggestedString !== null){
              jQuery('#spell').append('<a href="/?s=' + suggestedString + '"> ' + suggestedString + '</a>');
              jQuery('#spell').show();
            }
          }
        }
      }
    }
  });
  
});