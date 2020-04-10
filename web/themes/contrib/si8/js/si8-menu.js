// Header
jQuery(window).on("scroll touchmove", function () {
    jQuery('#mainheader').toggleClass('ridotto', jQuery(document).scrollTop() > 32);
    jQuery('body').toggleClass('ridotto', jQuery(document).scrollTop() > 32);
});

// Ricerca
jQuery("#edit-submit-ricerca-responsive").on("click", function () {
   if(jQuery(".ricerca section").is(':visible') && jQuery('.ricerca input').val() != ''){
       jQuery("#edit-submit-ricerca").click();
       jQuery('.ricerca input').val('');
   }
    jQuery(".ricerca section").slideToggle();
});

// Main Menu
jQuery('.hamburger-toggle').click(function() {
    jQuery('body,html').animate({scrollTop:0},200);
    jQuery('body').toggleClass('main-menu-opened');
    jQuery('#block-menu-hamburger').toggleClass('active', 1000);
});
jQuery('#hamburger-close').on('click',function(e){
    e.preventDefault();
    jQuery('body').toggleClass('main-menu-opened');
    jQuery('#block-menu-hamburger').toggleClass('active', 1000);
});
jQuery('.accordion-toggle').click(function(){
    if ( jQuery(this).hasClass('active') ){
        jQuery(this).removeClass('active');
        jQuery(this).removeClass('expanded');
        jQuery(this).addClass('collapsed');
        jQuery(this).children().addClass('fa-angle-down');
        jQuery(this).children().removeClass('fa-angle-up');
        jQuery('.accordion-toggle').siblings().hide();
    } else {
        jQuery('.accordion-toggle').removeClass('active');
        jQuery(this).addClass('active');
        jQuery('.accordion-toggle').siblings().hide();
        jQuery(this).removeClass('collapsed');
        jQuery(this).addClass('expanded');
        jQuery('.accordion-toggle').children().removeClass('fa-angle-up');
        jQuery('.accordion-toggle').children().addClass('fa-angle-down');
        jQuery(this).children().removeClass('fa-angle-down');
        jQuery(this).children().addClass('fa-angle-up');
        jQuery(this).siblings().slideToggle();
    }
});




//Secondary Menu
if (jQuery('aside.popup').length > 0) {

} else {
    jQuery(function() {
        jQuery('#secondary-menu-btn').addClass('invisibile');
    });
}

jQuery('#secondary-menu-btn').click(function() {
    jQuery('body,html').animate({scrollTop:0},200);
    jQuery('body').toggleClass('secondary-menu-opened');
    jQuery('aside').toggleClass('active', 1000);
});
jQuery('#hamburger-secondary-close').click(function(e) {
    e.preventDefault();
    jQuery('body').toggleClass('secondary-menu-opened');
    jQuery('aside').toggleClass('active', 1000);
});

//Secondary Menu nasconde pari livello quando non ci sono figli
if (jQuery('#block-navigazioneprincipale>ul>li.expanded').length > 0) {
    jQuery(function () {
        jQuery('#block-navigazioneprincipale>ul>li').addClass('invisibile');
        jQuery('#block-navigazioneprincipale>ul>li.expanded').removeClass('invisibile');
    });
} else {
    jQuery(document).ready(function(){
        //jQuery('#block-navigazioneprincipale>ul>li.active').prependTo("#block-navigazioneprincipale>ul");
        //jQuery('#block-navigazioneprincipale>ul>li.active').addClass('selected');
        jQuery('#block-navigazioneprincipale').addClass('invisibile');
        jQuery('#secondary-menu-btn').addClass('invisibile');
    })
}

if (jQuery('aside #block-elencoargomenti').length > 0 ||
    jQuery('aside #block-inevidenza-menu').length > 0 ||
    jQuery('aside section.block-facets').length > 0
) {
  jQuery(document).ready(function (){
    jQuery('#secondary-menu-btn').removeClass('invisibile');
  });
}