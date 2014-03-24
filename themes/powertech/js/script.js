$(function(){
  slogan = $(".slogan");
  href = window.location.pathname;
  li = $(".sidebar li.is-current, .sidebar li.is-parent");
  msgs = [
    "120+ MW / 4000+ Installations of Energy Savers",
    "Over 300 Clients worldwide",
    "An Integrated Energy Conservation Company",
    "15 Years of Experience in Energy Conservation",
  ];

  $("a[href='" + href + "']").parent("li").addClass("active");

  li.find("ul").show();


  show_message = function(){
    id = parseInt(slogan.data("msg"), 10);

    if(id < msgs.length){
      id++;
    } else {
      id = 1;
    }

    slogan.data("msg", id);
    slogan.hide().html(msgs[id-1]);
    slogan.fadeIn();
    
  };
  setInterval(show_message, 3000);
  show_message();

  
});