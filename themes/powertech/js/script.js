$(function(){
  href = window.location.pathname;

  $("a[href='" + href + "']").parent("li").addClass("active");
  console.log(href);
});