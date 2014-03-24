$(function(){
  href = window.location.pathname;

  $("a[href='" + href + "']").parent("li").addClass("active");


  li = $(".sidebar li.is-current, .sidebar li.is-parent");


// $(".sidebar li.is-parent").find("ul").slideDown();
  li.find("ul").show();

});