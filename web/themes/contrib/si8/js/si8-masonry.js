//inizializzazione masonry

if(jQuery("body.path-frontpage").length == 0){
    jQuery(document).ready(function(){
        //box primi livelli
        jQuery('article.page.full .field--name-field-box.field--items>div').attr('style','');
        jQuery('article.page.full .field--name-field-box.field--items').masonry({
            itemSelector: '.field--items>div'
        });
        //video
        jQuery('body.path-video .griglia>div').attr('style','');
        jQuery('body.path-video .griglia').masonry({
            itemSelector: '.griglia>div'
        });
    });
}
//elementi in home
else {
    jQuery(document).ready(function(){
        setTimeout(function(){
            //notizie
            jQuery('body.path-frontpage .paragraph--type--notizie-in-evidenza .field--name-field-collegamenti>div').attr('style','');
            jQuery('body.path-frontpage .paragraph--type--notizie-in-evidenza .field--name-field-collegamenti').masonry({
                itemSelector: '.field--name-field-collegamenti>div'
        });
        },1500);

        setTimeout(function(){
            //agevolazioni
            jQuery('body.path-frontpage .paragraph--type--agevolazioni-in-evidenza .field--items>div').attr('style','');
            jQuery('body.path-frontpage .paragraph--type--agevolazioni-in-evidenza .field--items').masonry({
                itemSelector: '.field--items>div'
            });
        },1500);

        setTimeout(function(){
            //video
            jQuery('body.path-frontpage .paragraph--type--video-in-evidenza .field--items>div').attr('style','');
            jQuery('body.path-frontpage .paragraph--type--video-in-evidenza .field--items').masonry({
                itemSelector: '.field--items>div'
            });
        },2500);
    });
}