var get_post_type_icon_class = function(type){

	var icon = "fa fa-database";
	if (type == "dataset"){
		icon = "fa fa-database";
	}else if (type == "library_record"){
		icon = "fa fa-book";
	}else if (type == "laws_record"){
		icon = "fa fa-gavel";
	}else if (type == "agreement"){
		icon = "fa fa-handshake-o";
	}else if (type == "map-layer"){
		icon = "fa fa-map-marker";
	}else if (type == "news-article"){
		icon = "fa fa-newspaper-o";
	}else if (type == "topic"){
		icon = "fa fa-list";
	}else if (type == "profiles"){
		icon = "fa fa-briefcase";
	}else if (type == "story"){
		icon = "fa fa-lightbulb-o";
	}else if (type == "announcement"){
		icon = "fa fa-bullhorn";
	}else if (type == "site-update"){
		icon = "fa fa-flag";
	}

	return icon;
}
