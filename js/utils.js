var get_post_type_icon_class = function(type){

	var icon = "fa fa-database";
	if (type == "dataset"){
		icon = "fa fa-database";
	}elseif (type == "library_record"){
		icon = "fa fa-book";
	}elseif (type == "laws_record"){
		icon = "fa fa-gavel";
	}elseif (type == "agreement"){
		icon = "fa fa-handshake-o";
	}elseif (type == "map-layer"){
		icon = "fa fa-map-marker";
	}elseif (type == "news-article"){
		icon = "fa fa-newspaper-o";
	}elseif (type == "topic"){
		icon = "fa fa-list";
	}elseif (type == "profiles"){
		icon = "fa fa-briefcase";
	}elseif (type == "story"){
		icon = "fa fa-lightbulb-o";
	}elseif (type == "announcement"){
		icon = "fa fa-bullhorn";
	}elseif (type == "site-update"){
		icon = "fa fa-flag";
	}

	return icon;
}
